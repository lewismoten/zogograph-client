<?php
declare(strict_types=1);

/*
 * The assistant's opinion of an alert, and its reports on a schedule.
 *
 * Two features that share a shape: both run without anybody watching, both cost
 * money, and both put a model's words in front of somebody who was not there
 * when it wrote them. That last part governs the whole design.
 *
 * ON SILENCING. An alert rule is a threshold. It has no idea that the disk
 * filled because a backup was running and has since finished, or that the CPU
 * spike was the nightly reindex that happens every night at two. The assistant
 * has tools that can find those things out, so it can often say "this one does
 * not warrant waking anybody" with more information than the rule had.
 *
 * What it must never do is make an alert disappear. A silenced alert is still
 * raised, still unread, still in the list, still on the timeline. The only
 * thing that changes is that it did not go out by email - and beside it is the
 * assistant's reason for that, plus what it thinks would let the RULE make the
 * same call next time without asking a model. That last part matters: a
 * silencer that never improves the rules is a permanent tax, and the honest end
 * state is one where the assistant is not needed for this.
 *
 * Every silence is reversible, visible, and attributed. If the model is wrong,
 * the operator can see exactly what it thought and why.
 */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/spans.php';
require_once __DIR__ . '/ai.php';
require_once __DIR__ . '/aitools.php';
require_once __DIR__ . '/airun.php';
require_once __DIR__ . '/aisession.php';

/** What it may be asked to do about an alert, and what each costs. */
function aiAlertSettings(array $config): array
{
    $set = $config['ai']['alerts'] ?? [];

    return [
        /*
         * Three levels, and they are cumulative.
         *
         *   off       nothing; alerts behave exactly as they always have
         *   comment   it reads the alert and writes a paragraph, appended to
         *             the email and shown in the list
         *   look      it may also run its read-only tools first, so the
         *             paragraph is based on more than the alert text
         *   decide    it may also conclude that an alert did not warrant
         *             sending, with its reasoning recorded
         */
        'mode' => in_array(
            $set['mode'] ?? 'off',
            ['off', 'comment', 'look', 'decide'],
            true,
        )
            ? (string) ($set['mode'] ?? 'off')
            : 'off',

        /*
         * Never for the worst ones, by default.
         *
         * A critical alert is the one case where a delay and a model's opinion
         * are both worse than a plain fast message. This is the floor at which
         * it stops being consulted at all.
         */
        'never_above' => in_array(
            $set['never_above'] ?? 'error',
            ['none', 'critical', 'error', 'warning'],
            true,
        )
            ? (string) ($set['never_above'] ?? 'error')
            : 'error',

        // Most alerts it will look at in one run, so a storm cannot run up a bill.
        'per_run' => max(1, min(20, (int) ($set['per_run'] ?? 5))),

        /* ---- reports ---- */
        'daily' => (bool) ($set['daily'] ?? false),
        'weekly' => (bool) ($set['weekly'] ?? false),
        'monthly' => (bool) ($set['monthly'] ?? false),
        'report_to' => (string) ($set['report_to'] ?? ''),
    ];
}

/** The severities, worst first, for comparing against `never_above`. */
function aiAlertRank(string $severity): int
{
    return ['critical' => 0, 'error' => 1, 'warning' => 2, 'info' => 3][
        $severity
    ] ?? 3;
}

/**
 * Whether this alert is one the assistant may be asked about at all.
 *
 * Deliberately conservative. The cost of consulting it about something urgent
 * is a delay on the one message that needed to be fast, and the cost of not
 * consulting it about something minor is a slightly noisier inbox.
 */
function aiMayJudge(array $config, string $severity): bool
{
    $settings = aiAlertSettings($config);

    if ($settings['mode'] === 'off') {
        return false;
    }
    if ($settings['never_above'] === 'none') {
        return true;
    }

    return aiAlertRank($severity) > aiAlertRank($settings['never_above']);
}

/**
 * Asks the assistant what it makes of one alert.
 *
 * Returns null when it should not be asked, could not be reached, or answered
 * with something unusable. Every one of those means the alert goes out exactly
 * as it would have without this feature - which is the only acceptable failure
 * mode for something sitting between a machine noticing a problem and a person
 * being told about it.
 *
 * @param callable|null $ask seam for tests, same shape as aiAsk
 * @return array{summary: string, verdict: string, reason: string, advice: string,
 *               tokens_in: int, tokens_out: int, millicents: int}|null
 */
function aiJudgeAlert(
    array $config,
    PDO $pdo,
    array $alert,
    ?callable $ask = null,
): ?array {
    $settings = aiAlertSettings($config);
    $ready = aiReady($config);

    if (!$ready['ok'] || !aiMayJudge($config, (string) $alert['severity'])) {
        return null;
    }

    $mayLook = in_array($settings['mode'], ['look', 'decide'], true);
    $mayDecide = $settings['mode'] === 'decide';

    /*
     * Only the tools that need nobody.
     *
     * This runs from cron with no one at a keyboard, so anything that would ask
     * permission, open a page, or draw something is not merely unhelpful - it
     * would park the whole thing waiting for an answer that is never coming.
     */
    $tools = $mayLook ? aiUnattendedTools($config) : [];

    $model = aiSettings($config);
    $ask ??= 'aiAsk';

    $transcript = [
        [
            'role' => 'user',
            'content' => aiAlertQuestion($alert, $mayDecide),
        ],
    ];

    $spent = ['in' => 0, 'out' => 0, 'cents' => 0];
    $answer = null;

    /*
     * A handful of turns at most.
     *
     * Enough to look at two or three things and then answer; not enough for a
     * loop to run up a bill at three in the morning with nobody watching.
     */
    for ($turn = 0; $turn < 4; $turn++) {
        $answer = $ask(
            $model,
            $transcript,
            $tools === [] ? [] : aiToolSchemas($tools),
            aiAlertPrompt($config, $mayDecide, $tools),
        );

        $spent['in'] += (int) ($answer['tokens_in'] ?? 0);
        $spent['out'] += (int) ($answer['tokens_out'] ?? 0);

        if (!$answer['ok']) {
            /*
             * Reached for and not available.
             *
             * Logged rather than thrown: an alert that failed to be commented
             * on must still be an alert that goes out.
             */
            logProblem(
                'warning',
                'alert triage could not reach the model: ' .
                    (string) ($answer['error'] ?? 'unknown'),
            );
            return null;
        }

        $calls = $answer['tool_calls'] ?? [];
        if ($calls === []) {
            break;
        }

        $transcript[] = [
            'role' => 'assistant',
            'content' => (string) ($answer['text'] ?? ''),
            'tool_calls' => array_map(
                fn(array $call): array => [
                    'id' => (string) $call['id'],
                    'type' => 'function',
                    'function' => [
                        'name' => (string) $call['name'],
                        'arguments' => json_encode($call['arguments'] ?? []),
                    ],
                ],
                $calls,
            ),
        ];

        foreach ($calls as $call) {
            $name = (string) $call['name'];
            $result = isset($tools[$name])
                ? aiRunTool(
                    $name,
                    (array) ($call['arguments'] ?? []),
                    $config,
                    $pdo,
                    null,
                    0,
                    false,
                )
                : ['error' => 'That tool is not available for this.'];

            $transcript[] = [
                'role' => 'tool',
                'tool_call_id' => (string) $call['id'],
                'content' => aiEncodeResult($result),
            ];
        }
    }

    $spent['cents'] = aiCost($model, $spent['in'], $spent['out'], 0);
    $said = trim((string) ($answer['text'] ?? ''));

    if ($said === '') {
        return null;
    }

    return aiReadVerdict($said, $mayDecide) + [
        'tokens_in' => $spent['in'],
        'tokens_out' => $spent['out'],
        'millicents' => $spent['cents'],
    ];
}

/**
 * The tools that can be run with nobody at a keyboard.
 *
 * Read-only, server-side, and needing no permission. Everything else either
 * asks a person something or draws something for a person to look at, and
 * neither is possible from cron.
 */
function aiUnattendedTools(array $config): array
{
    $out = [];

    foreach (aiEnabledTools($config, aiViews(), false) as $name => $tool) {
        if ($tool['where'] !== 'server' || $tool['permission']) {
            continue;
        }
        // Writing a memory from an unattended run is a side effect nobody asked
        // for and nobody would see happen.
        if (
            in_array($tool['group'], ['memory', 'pictures', 'terminal'], true)
        ) {
            continue;
        }
        $out[$name] = $tool;
    }

    return $out;
}

/** What it is asked about one alert. */
function aiAlertQuestion(array $alert, bool $mayDecide): string
{
    $lines = [
        'An alert has just been raised on this machine.',
        '',
        'Rule: ' . (string) $alert['rule'],
        'Severity as the rule judged it: ' . (string) $alert['severity'],
        'Subject: ' . (string) $alert['subject'],
        'Detail: ' . (string) $alert['detail'],
        '',
    ];

    $lines[] = $mayDecide
        ? 'Say what you make of it, and whether it warrants sending.'
        : 'Say what you make of it.';

    return implode("\n", $lines);
}

/** What it is told about the job. */
function aiAlertPrompt(array $config, bool $mayDecide, array $tools): string
{
    $persona = aiPersona($config);

    $lines = [
        sprintf('Your name is %s.', $persona['name']),
        '',
        'You are looking at ONE alert that a threshold rule has just raised on a shared ' .
        'hosting account, and writing a short note that will be attached to it - in the ' .
        'email that goes out, and in the list the operator reads later.',
        '',
        'Nobody is watching. This runs from cron. There is no one to ask.',
        '',
        'WHAT TO WRITE',
        '  - Two or three sentences. This is read on a phone, at night, by somebody who ' .
        'was asleep.',
        '  - Say what the alert means for this machine in particular, not what the rule ' .
        'checks - they can read the rule.',
        '  - If you looked something up, say what you found and what it was.',
        '  - Markdown is fine and will be rendered. Keep it to emphasis and short lists.',
    ];

    if ($tools !== []) {
        $lines[] = '';
        $lines[] =
            'YOU MAY LOOK THINGS UP FIRST: ' .
            implode(', ', array_keys($tools)) .
            '.';
        $lines[] =
            '  Use them when the alert is ambiguous without context - whether this ' .
            'is the first time, whether a collector has stopped, what else happened in ' .
            'the same minutes. Two or three at most; this is a note, not an ' .
            'investigation.';
    }

    if ($mayDecide) {
        $lines[] = '';
        $lines[] = 'YOU MAY ALSO SAY IT SHOULD NOT BE SENT.';
        $lines[] =
            '  A rule is a threshold and knows nothing else. You may have found ' .
            'that the spike was a backup that has since finished, or that this fires ' .
            'every night at the same time and always has. That is worth acting on.';
        $lines[] =
            '  Silencing does NOT delete the alert. It stays in the list, unread, ' .
            'marked as silenced, with your reasoning attached. The only thing that ' .
            'changes is that nobody is woken.';
        $lines[] =
            '  Do NOT silence anything you are not sure about. The cost of a ' .
            'needless email is somebody being briefly annoyed; the cost of silencing a ' .
            'real problem is the problem continuing unattended. Those are not ' .
            'comparable, and when they are in tension you send it.';
        $lines[] =
            '  Do NOT silence something merely because it is repetitive. If it ' .
            'keeps firing and keeps being real, it is doing its job.';
    }

    $lines[] = '';
    $lines[] =
        'ANSWER IN EXACTLY THIS FORM, with these labels on their own lines:';
    $lines[] = '';
    $lines[] = 'VERDICT: send';
    if ($mayDecide) {
        $lines[] = '  (or "VERDICT: silence" if it should not go out)';
    }
    $lines[] = 'NOTE: your two or three sentences, on one line or several.';
    $lines[] = 'BECAUSE: one sentence on why you reached that verdict.';
    $lines[] =
        'BETTER RULE: one sentence on what would let the RULE decide this without ' .
        'asking you - a threshold, a time window, something to compare against. Write ' .
        '"none" if you cannot think of one.';
    $lines[] = '';
    $lines[] =
        'Nothing else. No greeting, no sign-off, no heading above the labels.';
    $lines[] = '';
    $lines[] =
        'Never state a cause you have not measured. If you have a suspicion, say ' .
        'it is one. This note is attached to a machine-generated alert and will be read ' .
        'as authoritative, which is a reason to be more careful rather than less.';

    return implode("\n", $lines);
}

/**
 * Pulls the four labelled parts out of what it wrote.
 *
 * Lenient about the shape and strict about the verdict: anything that is not
 * unambiguously "silence" is treated as "send". A parse that guesses wrong in
 * that direction produces a needless email; guessing wrong in the other
 * direction loses an alert, and those are not comparable.
 *
 * @return array{summary: string, verdict: string, reason: string, advice: string}
 */
function aiReadVerdict(string $said, bool $mayDecide): array
{
    $parts = [
        'verdict' => 'send',
        'summary' => '',
        'reason' => '',
        'advice' => '',
    ];
    $current = 'summary';

    foreach (explode("\n", $said) as $line) {
        $trimmed = trim($line);

        if (preg_match('/^VERDICT:\s*(.*)$/i', $trimmed, $m)) {
            $wanted = strtolower(trim($m[1]));
            $parts['verdict'] =
                $mayDecide && str_starts_with($wanted, 'silence')
                    ? 'silence'
                    : 'send';
            $current = null;
            continue;
        }
        if (preg_match('/^NOTE:\s*(.*)$/i', $trimmed, $m)) {
            $current = 'summary';
            $parts['summary'] = trim($m[1]);
            continue;
        }
        if (preg_match('/^BECAUSE:\s*(.*)$/i', $trimmed, $m)) {
            $current = 'reason';
            $parts['reason'] = trim($m[1]);
            continue;
        }
        if (preg_match('/^BETTER RULE:\s*(.*)$/i', $trimmed, $m)) {
            $current = 'advice';
            $parts['advice'] = trim($m[1]);
            continue;
        }

        if ($current !== null && $trimmed !== '') {
            $parts[$current] = trim($parts[$current] . "\n" . $trimmed);
        }
    }

    /*
     * A silence with no reasoning is not a silence.
     *
     * The whole justification for letting a model suppress an alert is that its
     * reasoning is recorded beside the decision. Without one there is nothing
     * to check and nothing to argue with, so it sends.
     */
    if ($parts['verdict'] === 'silence' && trim($parts['reason']) === '') {
        $parts['verdict'] = 'send';
        $parts['reason'] =
            'It asked for this to be silenced without saying why, so it ' .
            'was sent.';
    }

    if (strtolower(trim($parts['advice'])) === 'none') {
        $parts['advice'] = '';
    }

    return $parts;
}

/** Writes a verdict onto an alert. */
function aiSaveVerdict(PDO $pdo, int $alertId, array $verdict): void
{
    $pdo->prepare(
        'UPDATE alerts
            SET ai_summary = ?, ai_verdict = ?, ai_reason = ?, ai_advice = ?,
                ai_at = ?, silenced = ?
          WHERE id = ?',
    )->execute([
        $verdict['summary'],
        $verdict['verdict'],
        $verdict['reason'],
        $verdict['advice'],
        time(),
        $verdict['verdict'] === 'silence' ? 1 : 0,
        $alertId,
    ]);
}

/* ----------------------------------------------------------- the reports -- */

/** The three periods, and what each covers. */
function aiReportPeriods(): array
{
    return [
        'daily' => [
            'label' => 'Daily',
            'covers' => SPAN_DAY,
            'every' => SPAN_DAY,
        ],
        'weekly' => [
            'label' => 'Weekly',
            'covers' => 7 * SPAN_DAY,
            'every' => 7 * SPAN_DAY,
        ],
        'monthly' => [
            'label' => 'Monthly',
            'covers' => 30 * SPAN_DAY,
            'every' => 30 * SPAN_DAY,
        ],
    ];
}

/**
 * Whether a report of this period is due.
 *
 * From when the last one was written rather than from a clock, so a machine
 * that was off overnight writes one when it comes back instead of skipping the
 * day - and one that has been off for a week does not write seven.
 */
function aiReportDue(PDO $pdo, string $period): bool
{
    $periods = aiReportPeriods();
    if (!isset($periods[$period])) {
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT MAX(at) FROM ai_reports WHERE period = ?',
        );
        $stmt->execute([$period]);
        $last = $stmt->fetchColumn();
    } catch (Throwable) {
        return false;
    }

    if ($last === null || $last === false) {
        return true;
    }

    return time() - (int) $last >= $periods[$period]['every'];
}

/**
 * Writes one summary of the last day, week or month.
 *
 * The whole value of this is that somebody reads it instead of nothing. A
 * weekly summary that says "everything was fine" every week is one nobody opens
 * by the fourth week, so the prompt below spends most of its length on what NOT
 * to write.
 *
 * @param callable|null $ask seam for tests
 * @return array{id: int, body: string}|null
 */
function aiWriteReport(
    array $config,
    PDO $pdo,
    string $period,
    ?callable $ask = null,
): ?array {
    $periods = aiReportPeriods();
    if (!isset($periods[$period])) {
        return null;
    }

    $ready = aiReady($config);
    if (!$ready['ok']) {
        return null;
    }

    $to = time();
    $from = $to - $periods[$period]['covers'];

    /*
     * The facts are gathered here, not by the model.
     *
     * A report is a fixed set of questions asked of a fixed period, which is
     * exactly the case where letting a model choose what to look at produces a
     * different report every time and no way to compare this week to last.
     */
    $facts = aiReportFacts($config, $pdo, $from, $to);

    $model = aiSettings($config);
    $ask ??= 'aiAsk';

    $answer = $ask(
        $model,
        [
            [
                'role' => 'user',
                'content' => json_encode($facts, JSON_PRETTY_PRINT),
            ],
        ],
        [],
        aiReportPrompt($config, $periods[$period]['label'], $from, $to),
    );

    if (!$answer['ok']) {
        logProblem(
            'warning',
            'the ' .
                $period .
                ' summary could not be written: ' .
                (string) ($answer['error'] ?? 'unknown'),
        );
        return null;
    }

    $body = trim((string) ($answer['text'] ?? ''));
    if ($body === '') {
        return null;
    }

    $cost = aiCost(
        $model,
        (int) ($answer['tokens_in'] ?? 0),
        (int) ($answer['tokens_out'] ?? 0),
        0,
    );

    $pdo->prepare(
        'INSERT INTO ai_reports
            (at, period, covers_from, covers_to, body, tokens_in, tokens_out, millicents)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
    )->execute([
        $to,
        $period,
        $from,
        $to,
        $body,
        (int) ($answer['tokens_in'] ?? 0),
        (int) ($answer['tokens_out'] ?? 0),
        $cost,
    ]);

    return ['id' => (int) $pdo->lastInsertId(), 'body' => $body];
}

/**
 * The numbers a report is written from.
 *
 * Deliberately the same set every time, so two reports can be compared. What
 * varies is what the model makes of them.
 */
function aiReportFacts(array $config, PDO $pdo, int $from, int $to): array
{
    $facts = [
        'covers' => [
            'from' => date('Y-m-d H:i', $from),
            'to' => date('Y-m-d H:i', $to),
            'length' => plainSpan($to - $from, true),
        ],
    ];

    $ask = function (string $sql, array $args = []) use ($pdo) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($args);
            return $stmt->fetchAll();
        } catch (Throwable) {
            return [];
        }
    };

    $facts['alerts'] = $ask(
        'SELECT severity, COUNT(*) AS n FROM alerts
          WHERE raised_at BETWEEN ? AND ? GROUP BY severity ORDER BY n DESC',
        [$from, $to],
    );

    $facts['loudest_rules'] = $ask(
        'SELECT rule, COUNT(*) AS n, MAX(raised_at) AS last FROM alerts
          WHERE raised_at BETWEEN ? AND ? GROUP BY rule ORDER BY n DESC LIMIT 8',
        [$from, $to],
    );

    $facts['vitals'] = $ask(
        'SELECT
             ROUND(AVG(cpu_percent), 1) AS cpu_average,
             ROUND(MAX(cpu_percent), 1) AS cpu_peak,
             ROUND(AVG(mem_used_percent), 1) AS memory_average,
             ROUND(MAX(mem_used_percent), 1) AS memory_peak,
             MIN(disk_free_bytes) AS disk_free_low,
             MAX(disk_free_bytes) AS disk_free_high,
             COUNT(*) AS readings
           FROM vitals WHERE taken_at BETWEEN ? AND ?',
        [$from, $to],
    );

    $facts['disk_now'] = $ask(
        'SELECT disk_free_bytes, disk_total_bytes, inodes_free, taken_at
           FROM vitals ORDER BY taken_at DESC LIMIT 1',
    );

    $facts['actions'] = $ask(
        'SELECT action, COUNT(*) AS n FROM actions
          WHERE at BETWEEN ? AND ? GROUP BY action ORDER BY n DESC LIMIT 10',
        [$from, $to],
    );

    /*
     * Whether the collectors were actually running.
     *
     * Without this the model is summarising a quiet week that may in fact have
     * been a week with no measurements - and those look identical in every
     * other figure here.
     */
    $facts['collection'] = aiCronRhythm($config, $pdo);

    return $facts;
}

/** What the summariser is told. */
function aiReportPrompt(
    array $config,
    string $label,
    int $from,
    int $to,
): string {
    $persona = aiPersona($config);

    return implode("\n", [
        sprintf('Your name is %s.', $persona['name']),
        '',
        sprintf(
            'Write the %s summary for a shared hosting account, covering %s to %s.',
            strtolower($label),
            date('j M', $from),
            date('j M', $to),
        ),
        'You are given the figures as JSON. Nothing else is available; do not refer to ' .
        'anything you were not given.',
        '',
        'WHO READS THIS. One person, who runs one account, and who did not ask a ' .
        'question. They are skimming. If nothing needed their attention, the most ' .
        'useful thing you can do is say so in one line and stop.',
        '',
        'WHAT TO WRITE',
        '  - Lead with anything that needs doing. If nothing does, say that first.',
        '  - Numbers, with their units, for anything that moved.',
        '  - Compare against the period rather than against nothing: "the disk lost 2 GB ' .
        'over the week" is a fact; "the disk is 84% full" is a reading they can see.',
        '  - Under 200 words unless something genuinely happened.',
        '  - Markdown. A short heading or two at most; no tables.',
        '',
        'WHAT NOT TO WRITE',
        '  - Do not pad. A week where nothing happened is three lines, and writing four ' .
        'paragraphs about it is how this becomes an email nobody opens - which would ' .
        'cost more than it saves.',
        '  - Do not invent trends from few readings. If `readings` is small, the averages ' .
        'mean little and you should say so instead of describing them.',
        '  - Do not state a cause you were not given evidence for.',
        '  - If a collector stopped during the period, that is the headline, because ' .
        'every other figure here is then measuring a shorter period than it claims.',
        '  - No greeting and no sign-off. It arrives in an email that already has both.',
    ]);
}

/** The reports written so far, newest first. */
function aiReportList(PDO $pdo, int $limit = 30): array
{
    try {
        $stmt = $pdo->prepare(
            'SELECT id, at, period, covers_from, covers_to, body, emailed_at, millicents
               FROM ai_reports ORDER BY at DESC LIMIT ?',
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/** The newest report of any period, for the dashboard. */
function aiLatestReport(PDO $pdo): ?array
{
    try {
        $row = $pdo
            ->query(
                'SELECT id, at, period, covers_from, covers_to, body FROM ai_reports
              ORDER BY at DESC LIMIT 1',
            )
            ->fetch();
    } catch (Throwable) {
        return null;
    }

    return $row === false ? null : $row;
}
