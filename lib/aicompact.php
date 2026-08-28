<?php
declare(strict_types=1);

/*
 * Keeping a long conversation inside the model's context window.
 *
 * A diagnostic conversation grows in a particular way: a few short questions
 * from a person, and enormous tool results. One `get_recent_log_lines` can be
 * bigger than everything either party has said. So the window fills from the
 * middle out, and it fills faster than anybody watching the message count would
 * expect.
 *
 * What happens when it fills is the part worth designing. The provider rejects
 * the request, which ends the conversation at exactly the moment somebody is
 * deepest into a problem. The alternative - dropping the oldest messages - is
 * quietly worse: the beginning of a conversation is where the symptom was
 * described, and a model that has forgotten the symptom while remembering forty
 * kilobytes of log lines is confidently solving the wrong problem.
 *
 * So: summarise the old part, keep the recent part verbatim.
 *
 *   long-term    everything before the cut becomes one summary written by the
 *                model itself, in the system prompt
 *   short-term   the last few turns stay exactly as they were, because "what
 *                did that last tool say" has to still be answerable
 *
 * Compaction happens with room to spare, on purpose. Summarising is itself a
 * request that has to fit, so a limit that triggers when the window is already
 * full is a limit that cannot act. The default leaves a third of the window
 * free at the moment it fires.
 */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/aidb.php';

/** Characters per token, roughly, for English prose and JSON. */
const AI_CHARS_PER_TOKEN = 3.7;

/** How many messages at the end are never summarised. */
const AI_KEEP_RECENT = 12;

/** Least that can be compacted at once. Below this it is not worth a request. */
const AI_COMPACT_MIN = 8;

/**
 * Roughly how many tokens a conversation is costing.
 *
 * An estimate, and labelled as one everywhere it is shown. The exact number is
 * known only to the provider, comes back only after a request, and differs
 * between models - so a figure that is right to within ten per cent and always
 * available beats an exact one that arrives too late to act on.
 *
 * Counted from the transcript rather than from the last request's token count,
 * because the last request may have been twenty minutes and six tool results
 * ago.
 */
function aiContextSize(PDO $ai, int $session, string $systemPrompt = ''): array
{
    $row = $ai->prepare(
        'SELECT COALESCE(SUM(LENGTH(COALESCE(content, "")) +
                             LENGTH(COALESCE(tool_args, ""))), 0) AS chars,
                COUNT(*) AS messages
           FROM ai_messages
          WHERE session_id = ? AND id > (
                SELECT COALESCE(compacted_to, 0) FROM ai_sessions WHERE id = ?)'
    );
    $row->execute([$session, $session]);
    $found = $row->fetch();

    $chars = (int)($found['chars'] ?? 0) + strlen($systemPrompt);

    return [
        'tokens' => (int)ceil($chars / AI_CHARS_PER_TOKEN),
        'messages' => (int)($found['messages'] ?? 0),
        'estimated' => true,
    ];
}

/**
 * The window, the soft limit, and whether it is time.
 *
 * @return array<string, mixed>
 */
function aiContextState(array $config, PDO $ai, int $session, string $systemPrompt = ''): array
{
    $settings = aiSettings($config);
    $window = max(4000, (int)($settings['context_tokens'] ?? 128000));

    /*
     * The soft limit leaves room for the compaction request itself.
     *
     * Summarising means sending the part being summarised, so a limit set at
     * the window is a limit that fires when there is no room left to act on it.
     * Clamped rather than trusted: a share typed as 95 would produce exactly
     * that failure.
     */
    $share = min(85, max(30, (int)($settings['compact_at'] ?? 70)));
    $soft = (int)floor($window * ($share / 100));

    $size = aiContextSize($ai, $session, $systemPrompt);

    return [
        'tokens' => $size['tokens'],
        'messages' => $size['messages'],
        'window' => $window,
        'soft' => $soft,
        'share' => $window > 0 ? min(1.0, $size['tokens'] / $window) : 0.0,
        'full' => $size['tokens'] >= $soft,
        'estimated' => true,
        'automatic' => (bool)($settings['compact_automatically'] ?? true),
    ];
}

/**
 * Where to cut, so that nothing is split that must stay together.
 *
 * A tool call and its result are one unit - separating them produces a
 * transcript no provider will accept, which is the same failure this whole file
 * is downstream of. The cut therefore lands immediately before a `user`
 * message, which is the only place in a transcript where nothing is
 * outstanding.
 *
 * Returns the id to compact up to and including, or 0 if there is no safe cut.
 */
function aiCompactionCut(PDO $ai, int $session): int
{
    $stmt = $ai->prepare(
        'SELECT id, role FROM ai_messages
          WHERE session_id = ? AND id > (
                SELECT COALESCE(compacted_to, 0) FROM ai_sessions WHERE id = ?)
          ORDER BY id'
    );
    $stmt->execute([$session, $session]);
    $rows = $stmt->fetchAll();

    if (count($rows) < AI_COMPACT_MIN + AI_KEEP_RECENT) {
        return 0;
    }

    // Everything except the last AI_KEEP_RECENT is a candidate.
    $limit = count($rows) - AI_KEEP_RECENT;

    /*
     * Walk back from there to the last user message.
     *
     * Backwards, so the cut is as late as possible - compacting less than we
     * could means doing it again sooner, which is a wasted request.
     */
    for ($i = $limit; $i > 0; $i--) {
        if ((string)$rows[$i]['role'] === 'user') {
            return (int)$rows[$i - 1]['id'];
        }
    }

    return 0;
}

/**
 * Replaces the old part of a conversation with a summary of it.
 *
 * The summary is written by the model, because the alternative - a mechanical
 * digest of message counts and tool names - throws away the only thing worth
 * keeping, which is what was concluded and why.
 *
 * Nothing is deleted. The messages stay in the database and the operator can
 * still read the whole transcript; only what is SENT changes. A tool that
 * silently destroyed the record of what left the machine would be a bad tool to
 * have.
 *
 * @param callable|null $ask seam for tests, same shape as aiAsk
 * @return array{compacted: int, to: int, summary: string, why: string|null}
 */
function aiCompact(array $config, PDO $ai, int $session, ?callable $ask = null): array
{
    $cut = aiCompactionCut($ai, $session);

    if ($cut <= 0) {
        return [
            'compacted' => 0,
            'to' => 0,
            'summary' => '',
            'why' => 'There is not enough old conversation to be worth summarising yet.',
        ];
    }

    $stmt = $ai->prepare(
        'SELECT id, role, content, tool_name, tool_args FROM ai_messages
          WHERE session_id = ? AND id > (
                SELECT COALESCE(compacted_to, 0) FROM ai_sessions WHERE id = ?)
            AND id <= ?
          ORDER BY id'
    );
    $stmt->execute([$session, $session, $cut]);
    $rows = $stmt->fetchAll();

    if ($rows === []) {
        return ['compacted' => 0, 'to' => 0, 'summary' => '', 'why' => 'Nothing to compact.'];
    }

    $earlier = aiCompactionSummary($ai, $session);
    $plain = aiFlattenForSummary($rows);

    $ask ??= 'aiAsk';
    $settings = aiSettings($config);

    $answer = $ask(
        $settings,
        [[
            'role' => 'user',
            'content' => ($earlier === ''
                ? ''
                : "This is the summary so far, which your new summary must also cover:\n\n"
                    . $earlier . "\n\n---\n\n")
                . "Summarise the troubleshooting conversation below so that it can be "
                . "continued without it.\n\n"
                . $plain,
        ]],
        [],
        aiSummaryPrompt(),
        // Cheap: this is compression, not reasoning, and it happens repeatedly.
        'minimal'
    );

    if (!$answer['ok']) {
        return [
            'compacted' => 0,
            'to' => 0,
            'summary' => '',
            'why' => 'The summary could not be written: ' . $answer['error'],
        ];
    }

    $summary = trim((string)($answer['text'] ?? ''));
    if ($summary === '') {
        return [
            'compacted' => 0,
            'to' => 0,
            'summary' => '',
            'why' => 'The model returned an empty summary, so nothing was replaced.',
        ];
    }

    /*
     * The summary is a visible message, with what it replaced recorded on it.
     *
     * Visible because a conversation that silently loses its first half is a
     * conversation somebody stops trusting - the originals are all still here
     * and still shown; this is a marker saying where the model's view now
     * begins. The range goes on it so the marker can be opened and checked
     * against what it claims to summarise.
     */
    $ai->prepare(
        "INSERT INTO ai_messages (session_id, at, role, content, tool_args, visible)
         VALUES (?, ?, 'summary', ?, ?, 1)"
    )->execute([$session, time(), $summary, json_encode([
        'from_message' => (int)($rows[0]['id'] ?? 0),
        'to_message' => $cut,
        'messages' => count($rows),
        'tokens_before' => aiContextSize($ai, $session)['tokens'],
    ])]);

    $ai->prepare('UPDATE ai_sessions SET compacted_to = ? WHERE id = ?')
        ->execute([$cut, $session]);

    return [
        'compacted' => count($rows),
        'to' => $cut,
        'summary' => $summary,
        'why' => null,
    ];
}

/** The most recent summary written for a session, or an empty string. */
function aiCompactionSummary(PDO $ai, int $session): string
{
    try {
        $stmt = $ai->prepare(
            "SELECT content FROM ai_messages
              WHERE session_id = ? AND role = 'summary'
              ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$session]);
        $found = $stmt->fetchColumn();
    } catch (Throwable) {
        return '';
    }

    return $found === false ? '' : (string)$found;
}

/** Turns stored messages into something readable enough to summarise. */
function aiFlattenForSummary(array $rows): string
{
    $out = [];

    foreach ($rows as $row) {
        $role = (string)$row['role'];
        $content = (string)($row['content'] ?? '');

        if ($role === 'summary') {
            continue;
        }

        if ($role === 'tool_call') {
            $out[] = sprintf('[asked for the %s tool: %s]',
                (string)$row['tool_name'], (string)($row['tool_args'] ?? '{}'));
            continue;
        }

        /*
         * Tool results are cut short here.
         *
         * They are the bulk of what is being compacted and the least of what
         * needs to survive: what matters is what was concluded from a hundred
         * log lines, not the hundred log lines. The model is told they were
         * shortened so it does not treat the fragment as the whole.
         */
        if ($role === 'tool') {
            $out[] = '[result: ' . mb_substr($content, 0, 1200)
                . (mb_strlen($content) > 1200 ? ' … (shortened)]' : ']');
            continue;
        }

        if (str_starts_with($content, 'data:image/')) {
            $out[] = '[' . $role . ' sent an image]';
            continue;
        }

        $out[] = ucfirst($role) . ': ' . $content;
    }

    return implode("\n\n", $out);
}

/** What the summariser is told to do. */
function aiSummaryPrompt(): string
{
    return implode("\n", [
        "You are compressing the earlier part of a technical troubleshooting conversation "
        . "so that it can continue in a smaller space.",
        "",
        "Write prose, not headings. Aim for under 400 words. Cover, in this order:",
        "  - what the person originally reported, in their terms",
        "  - what was measured, with the actual numbers, and when",
        "  - what was concluded, and marked clearly as a conclusion rather than a "
        . "measurement",
        "  - what was ruled out, and on what evidence - this is the part that stops the "
        . "same ground being covered twice",
        "  - anything the person asked for, preferred, or refused",
        "  - what was about to happen next",
        "",
        "Keep exact figures, paths, times and error strings. A summary that says "
        . "\"the disk was quite full\" has thrown away the only thing worth keeping.",
        "",
        "Do not add anything that was not there. Do not resolve open questions. If "
        . "something was uncertain, it stays uncertain in the summary - a conclusion that "
        . "firms up during compression is a conclusion nobody reached.",
    ]);
}
