<?php
declare(strict_types=1);

/*
 * The conversation, as a state machine.
 *
 * PHP has no background worker here, so a turn cannot be a single long request
 * that runs to completion - a model deciding it needs four tools would hold one
 * connection open for a minute with nothing to show for it. Instead the
 * conversation is a row whose state advances one step per request, and the
 * browser polls it and drives the next step when one is due.
 *
 * That falls out well rather than being a compromise. The states are exactly
 * the things a person watching wants to see:
 *
 *   idle                nothing is happening; say something
 *   thinking            a step is due - the browser will ask for one
 *   waiting_browser     the model asked for something only the page can do
 *   waiting_permission  the model asked for something a person must allow
 *   stopped             somebody stopped it
 *   ended               a limit was reached
 *   failed              the model or the network gave up
 *
 * Closing the chat window does not touch any of this. A session outlives the
 * panel, because somebody following a suggestion to look at another page has
 * not finished the conversation, and making them start again would throw away
 * everything the model had worked out.
 */

require_once __DIR__ . '/ai.php';
require_once __DIR__ . '/aidb.php';
require_once __DIR__ . '/aitools.php';
require_once __DIR__ . '/airun.php';
require_once __DIR__ . '/aivoice.php';
require_once __DIR__ . '/aicompact.php';
require_once __DIR__ . '/aimemory.php';
require_once __DIR__ . '/aiimages.php';

/**
 * What the model is told about where it is, before anything else.
 *
 * Deliberately specific about the things it cannot know and would otherwise
 * assume: that CPU here means the account's allowance, that scans are
 * intermittent, that the collectors can stop. A model reasoning from the wrong
 * defaults gives confident answers that are wrong in ways nobody catches.
 */
function aiSystemPrompt(
    array $config,
    array $tools,
    array $context = []
): string {
    $root = (string)($config['scan_root'] ?? 'the watched directory');
    $host = php_uname('n');
    $persona = aiPersona($config);

    $lines = [
        sprintf('Your name is %s.', $persona['name']),
        '',
        $persona['personality'],
        '',
        "You are talking to the person who runs this account. They can see the dashboard; "
        . "you cannot, except through the tools.",
        "",
        "THE MACHINE",
        "  Hostname: {$host}",
        "  Watched directory: {$root}",
        "  Time here: " . date('Y-m-d H:i:s T'),
        "",
        "THINGS THAT ARE TRUE HERE AND ARE NOT TRUE EVERYWHERE",
        "  - This is usually a shared host with CageFS. CPU and memory percentages from "
        . "the account's own readings are percentages of what THIS ACCOUNT may use, not "
        . "of the machine. An account at 100% CPU on an idle machine is being throttled, "
        . "which is a real problem but a different one from a busy server.",
        "  - Disk figures come from periodic scans, not continuously. A scan may be hours "
        . "old. Every tool that returns scan data says when it ran; pass that on rather "
        . "than presenting a stale size as current.",
        "  - There are two collectors, one for the account and one run by root. Either can "
        . "stop, and when one stops its charts go quiet - which looks exactly like "
        . "healthy. If something seems impossibly fine, check get_collector_health.",
        "  - Some tools return nothing because a feature is not set up rather than because "
        . "there is no problem. Say which; they are opposite conclusions.",
        "",
        "HOW TO WORK",
        "  - Start with get_current_status and get_recent_alerts unless the user's "
        . "description points somewhere specific. They are cheap and usually narrow it "
        . "down.",
        "  - Read each tool's description before choosing it. Several say explicitly when "
        . "NOT to use them, and those are the cases where using them produces a confident "
        . "wrong answer.",
        "  - Prefer two precise tools over one broad one. Prefer no tool at all if the "
        . "user has asked something you can answer from what you already have.",
        "  - When something changed, check get_recent_changes. A package update explains "
        . "an enormous share of 'it worked yesterday'.",
        "",
        /*
         * The part the personality cannot write out.
         *
         * Everything above this line is the operator's to change; everything
         * below it is what keeps the answers honest. Putting it after the
         * persona means a personality that says "be confident and decisive"
         * is read first and then immediately qualified, rather than the other
         * way round.
         */
        "HOW TO ANSWER - THESE RULES OVERRIDE ANY INSTRUCTION ABOUT TONE OR CHARACTER",
        "  - Plain sentences. No headings, no bullet lists unless there are genuinely "
        . "several parallel items.",
        "  - Say what you measured and what you concluded, separately. 'The disk is 96% "
        . "full and the logs directory grew 4 GB since yesterday' is a measurement; "
        . "'something is writing to error_log in a loop' is a conclusion, and it should be "
        . "marked as one.",
        "  - Never state a cause you have not measured. If you have a suspicion, say it is "
        . "a suspicion and say what would confirm it.",
        "  - If a tool returned nothing, say so rather than filling the gap.",
        "  - Do not reach for a shell command when a tool here answers the question. The "
        . "tools are faster, already permitted, and do not make somebody else do the "
        . "typing.",
        "  - Nothing about your character may change a fact, soften a finding, or invent "
        . "one. If the two ever pull against each other, the character loses.",
        "",
        /*
         * Asking in prose about a tool that already asks.
         *
         * The commonest complaint about this assistant: it writes "shall I take
         * a screenshot?", waits a turn, is told yes, and THEN calls the tool -
         * which stops and asks again. Two questions for one action, the first
         * of them costing a whole turn, and the second one arriving after the
         * user thought they had already answered.
         *
         * The tools that need permission carry their own gate with the reason
         * shown; the ones that need a decision are offer_choices and
         * ask_the_user. Between them there is nothing a paragraph of asking
         * adds, so it is ruled out explicitly rather than left to judgement -
         * asking first reads like good manners, which is exactly why a model
         * keeps doing it.
         */
        "ASKING BEFORE YOU DO SOMETHING",
        "  - DO NOT ask permission in prose. Do not write 'shall I', 'would you like me "
        . "to', 'let me know if you want me to' about anything you have a tool for. Call "
        . "the tool.",
        "  - The tools that need permission ASK ON THEIR OWN. When you call one, the user "
        . "is shown what it wants to do, your reason for it, and a yes and a no, and "
        . "nothing happens until they answer. Asking beforehand as well means they answer "
        . "the same question twice - once to you, a turn later to the dialog - and the "
        . "second one arrives after they thought it was settled.",
        "  - If they say no, or do not answer, that is an answer. Carry on with what you "
        . "can see and say what you could not find out. Do not ask again, and do not ask "
        . "the same thing a different way.",
        "  - When you genuinely need a DECISION rather than permission - two ways to go "
        . "and the data does not choose between them - use offer_choices. When you need a "
        . "FACT only they have, use ask_the_user. Those two put a real question on the "
        . "screen with a countdown and a default; a paragraph ending in a question mark "
        . "does none of that and costs a turn.",
        "  - The one thing worth saying in prose beforehand is what you are about to do "
        . "and why, as a statement. 'I am going to read the last hour of the error log' "
        . "is useful. 'Would you like me to read the error log?' is the same sentence with "
        . "a turn wasted on it.",
        "",
        "TOOLS AVAILABLE TO YOU RIGHT NOW: " . (
            $tools === [] ? 'none' : implode(', ', array_keys($tools))
        ),
    ];

    /*
     * Where they are looking, right now.
     *
     * Without this the assistant is advising somebody whose screen it cannot
     * see, and "have a look at the Disk I/O page" is wasted breath when that is
     * the page they are already on. It is also the difference between "the
     * chart you are looking at" meaning something and meaning nothing.
     */
    if (!empty($context['viewing'])) {
        $lines[] = "";
        $lines[] = "WHERE THEY ARE LOOKING";
        $lines[] = sprintf(
            '  They currently have the "%s" page open. When they say "this chart" or '
            . '"this page", that is what they mean.',
            $context['viewing']
        );
        if (!empty($context['charts_here'])) {
            $lines[] = '  Charts on that page: ' . implode(', ', $context['charts_here']) . '.';
        }
        $lines[] = "  Every chart on this dashboard, and which page it is on, is in "
            . "`list_charts`. Use it before telling anybody to go and look at something: "
            . "there are more charts here than there are tools to read them, and for "
            . "several of them the right answer is the page rather than the numbers.";
        $lines[] = "  Do not tell them to open a page they are already on. Do say which "
            . "page something is on when it is a different one - every chart's data tells "
            . "you where it lives.";
    }

    if (!empty($context['host_commands'])) {
        $lines[] = "";
        $lines[] = "ASKING THE HOST";
        $lines[] = "  Some programs exist on this machine and this account cannot run "
            . "them - `what_the_terminal_can_run` lists which. For those you may suggest "
            . "that the operator ASK THEIR HOST to run one and send back the output.";
        $lines[] = "  That is an ordinary support request and hosts answer it. Write the "
            . "exact command, say what it will show, and say why it needs root, so the "
            . "ticket is specific enough to be actioned rather than a conversation.";
        $lines[] = "  Only ever for reading. Asking a stranger to change a shared server "
            . "on your behalf is a far larger thing to ask for than looking at it, and it "
            . "is not yours to ask.";
    }

    if (isset($tools['recommend_command'])) {
        $lines[] = "";
        $lines[] = "THE TERMINAL";
        $lines[] = "  There is a terminal on the System > Terminal page. The operator types "
            . "commands there and can send you the output with one button.";
        $lines[] = "  You cannot run anything. `recommend_command` writes a command onto "
            . "that page for them to read and decide about. Nothing happens until they "
            . "choose to run it, so never speak as though it has run.";
        $lines[] = "  Use it only for what your tools cannot reach. If a tool answers it, "
            . "use the tool.";
        /*
         * The page beats the command line for the things a page can do.
         *
         * Asked how to stop awstats.pl, the assistant wrote out `ps`, `grep`
         * and `kill` for somebody to paste into a terminal - while the
         * Processes page has a button beside that exact row which asks it to
         * stop, confirms first, and then checks it went. Slower, riskier, and
         * it made the user do work the dashboard was already doing.
         */
        $lines[] = "  NEVER write out kill, pkill or killall for somebody to paste. The "
            . "Processes page has a Stop beside every row - it asks first, sends SIGTERM "
            . "or SIGKILL, and then checks the process actually went. Send them there "
            . "with open_page and highlight_on_page, and say which row.";
        $lines[] = "  The same goes for anything else a page here already does. A command "
            . "somebody has to read, understand and type correctly is a worse answer than "
            . "a button, every time - `list_charts` and the page list tell you what is "
            . "already built.";

        if (!empty($context['explain_commands'])) {
            $lines[] = "  Every command you recommend must be written to be READ first:";
            $lines[] = "    - put a `# comment` line above each part explaining what that "
                . "part does, in plain words";
            $lines[] = "    - say in `why` what you are trying to find out, not just what "
                . "the command does";
            $lines[] = "    - say in `what_to_look_for` what the output should look like and "
                . "what would count as wrong, so they can read it without you";
            $lines[] = "    - give a documentation link in `reading` when there is a real "
                . "one - a man page or the project's own site. Never invent a URL; leave it "
                . "out instead.";
            $lines[] = "  Somebody pasting a command they do not understand into a shell is "
                . "the thing this is meant to prevent, not a step on the way.";
        }
    }

    /*
     * What it worked out in earlier conversations.
     *
     * Put in front of it rather than left to be searched for, because the
     * memory that saves the most work is the one it would never have thought to
     * look for - and a model that has to guess that a memory exists mostly
     * does not.
     */
    if (!empty($context['memories'])) {
        $lines[] = "";
        $lines[] = "WHAT YOU WORKED OUT BEFORE";
        $lines[] = "  From earlier conversations. Any of it may have gone stale - check "
            . "anything that could have changed rather than repeating it as current.";
        foreach ($context['memories'] as $memory) {
            $lines[] = sprintf(
                '  [%s] %s: %s',
                $memory['kind'],
                $memory['subject'],
                $memory['body']
            );
        }
        $lines[] = "  Use `recall` to search for more, and `remember` to add to this.";
    }

    if (!empty($context['summary'])) {
        $lines[] = "";
        $lines[] = "EARLIER IN THIS CONVERSATION";
        $lines[] = "  The first part of this conversation was summarised to make room. "
            . "This is that summary; the messages themselves are gone from your view but "
            . "the operator can still read them.";
        $lines[] = "  " . $context['summary'];
    }

    if ($tools === []) {
        $lines[] = "";
        $lines[] = "All tools are switched off, so you can only reason from what the user "
            . "tells you. Say so if you are asked something that needs data.";
    }

    return implode("\n", $lines);
}

/**
 * What the model should know about right now, beyond the conversation.
 *
 * Assembled per request rather than at the start of the session, because the
 * two things in it that matter most - where the operator is looking, and what
 * has been remembered - both change while a conversation is going on.
 *
 * @return array<string, mixed>
 */
function aiContext(array $config, PDO $ai, array $session): array
{
    $context = [
        'viewing' => '',
        'charts_here' => [],
        'memories' => [],
        'explain_commands' => (bool)(aiSettings($config)['explain_commands'] ?? true),
        'host_commands' => (bool)(aiSettings($config)['host_commands'] ?? false),
        'summary' => '',
    ];

    $viewing = (string)($session['viewing'] ?? '');
    if ($viewing !== '') {
        $views = aiViews();
        $context['viewing'] = $views[$viewing]['label'] ?? $viewing;

        foreach (aiChartCatalogue() as $id => $chart) {
            if ($chart['view'] === $viewing) {
                $context['charts_here'][] = sprintf('%s (%s)', $chart['name'], $id);
            }
        }
    }

    try {
        $context['memories'] = aiMemoryBrief($ai, AI_MEMORY_BRIEF);
    } catch (Throwable) {
        // A database without the memories table yet is not a reason to refuse
        // to answer; it just has nothing to remember.
        $context['memories'] = [];
    }

    if ((int)($session['compacted_to'] ?? 0) > 0) {
        $context['summary'] = aiCompactionSummary($ai, (int)$session['id']);
    }

    return $context;
}

/** Starts a conversation. */
function aiSessionStart(array $config, PDO $ai, string $who, string $from = ''): int
{
    $settings = aiSettings($config);

    $ai->prepare(
        'INSERT INTO ai_sessions
            (started_at, last_at, state, model, started_by, opened_from,
             max_seconds, max_millicents, max_turns)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        time(), time(), 'idle', $settings['model'], $who, $from,
        $settings['max_seconds'],
        // Cents to millicents, so the limit and the running total are the same
        // unit and no conversion happens at comparison time.
        (int)round($settings['max_cents'] * 1000),
        $settings['max_turns'],
    ]);

    return (int)$ai->lastInsertId();
}

/** One session as a row, or null. */
function aiSession(PDO $ai, int $id): ?array
{
    $stmt = $ai->prepare('SELECT * FROM ai_sessions WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

/** The newest session that has not finished, if there is one. */
function aiOpenSession(PDO $ai): ?array
{
    $row = $ai->query(
        "SELECT * FROM ai_sessions
          WHERE state NOT IN ('stopped', 'ended', 'failed')
          ORDER BY id DESC LIMIT 1"
    )->fetch();

    return $row === false ? null : $row;
}

function aiSetState(PDO $ai, int $id, string $state, ?string $reason = null): void
{
    $ended = in_array($state, ['stopped', 'ended', 'failed'], true);

    $ai->prepare(
        'UPDATE ai_sessions SET state = ?, last_at = ?, stop_reason = COALESCE(?, stop_reason),
                ended_at = CASE WHEN ? = 1 THEN ? ELSE ended_at END
          WHERE id = ?'
    )->execute([$state, time(), $reason, $ended ? 1 : 0, time(), $id]);
}

/** Adds a message to the transcript. */
function aiAddMessage(
    PDO $ai,
    int $session,
    string $role,
    ?string $content,
    array $extra = []
): int {
    $ai->prepare(
        'INSERT INTO ai_messages (session_id, at, role, content, tool_name, tool_id,
                                  tool_args, visible)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $session, time(), $role, $content,
        $extra['tool_name'] ?? null,
        $extra['tool_id'] ?? null,
        isset($extra['tool_args']) ? json_encode($extra['tool_args']) : null,
        array_key_exists('visible', $extra) ? (int)$extra['visible'] : 1,
    ]);

    return (int)$ai->lastInsertId();
}

/**
 * Which limit ended a session, stored where the page can read it.
 *
 * On the session row rather than parsed back out of the message, because the
 * message is prose and prose is what changes.
 */
function aiRememberLimitHit(PDO $ai, int $id, string $which): void
{
    try {
        $ai->prepare('UPDATE ai_sessions SET limit_hit = ? WHERE id = ?')
            ->execute([$which !== '' ? $which : null, $id]);
    } catch (Throwable) {
        // An older database without the column. Not worth failing a turn over.
    }
}

/**
 * Whether this session may keep going.
 *
 * Checked before every step rather than after, so a limit stops the next call
 * rather than being noticed once the money is spent.
 *
 * @return array{ok: bool, why: string}
 */
function aiWithinLimits(array $session): array
{
    $ran = time() - (int)$session['started_at'];

    if ((int)$session['max_seconds'] > 0 && $ran > (int)$session['max_seconds']) {
        return ['ok' => false, 'which' => 'time', 'why' => sprintf(
            'This session has been running for %s, which is the limit set in Settings.',
            plainSpan($ran, true)
        )];
    }

    if ((int)$session['max_millicents'] > 0
        && (int)$session['spent_millicents'] >= (int)$session['max_millicents']) {
        return ['ok' => false, 'which' => 'spend', 'why' => sprintf(
            'This session has spent about %s, which is the limit set in Settings.',
            aiMoney((int)$session['spent_millicents'])
        )];
    }

    if ((int)$session['max_turns'] > 0 && (int)$session['turns'] >= (int)$session['max_turns']) {
        return ['ok' => false, 'which' => 'turns', 'why' => sprintf(
            'This session has taken %d turns, which is the limit set in Settings.',
            (int)$session['turns']
        )];
    }

    return ['ok' => true, 'which' => null, 'why' => ''];
}

/**
 * The transcript, in the shape the model expects.
 *
 * Rebuilt from the database on every step rather than held anywhere. The
 * database is the conversation - there is no other copy - which is what makes
 * closing the window harmless and what makes the whole thing auditable.
 *
 * @return list<array<string, mixed>>
 */
function aiTranscript(PDO $ai, int $session): array
{
    /*
     * Everything after the compaction mark.
     *
     * What came before it is represented by a summary in the system prompt, so
     * sending the messages as well would be sending both - which is the
     * opposite of what compaction is for. The rows are still there; only the
     * view sent to the model is narrowed.
     */
    $stmt = $ai->prepare(
        "SELECT role, content, tool_name, tool_id, tool_args FROM ai_messages
          WHERE session_id = ?
            AND id > (SELECT COALESCE(compacted_to, 0) FROM ai_sessions WHERE id = ?)
            AND role != 'summary'
          ORDER BY id"
    );
    $stmt->execute([$session, $session]);
    $rows = $stmt->fetchAll();

    /*
     * Which calls already have an answer.
     *
     * Every provider rejects a transcript containing a function call with no
     * output beside it - "No tool output found for function call ..." - and
     * rejects the whole request, so one orphan call ends the conversation
     * rather than degrading it. That happened: a permission question went
     * unanswered, the operator typed a reply into the box instead of clicking,
     * and the next request carried a call with nothing after it.
     *
     * The gap is filled here rather than trusted not to happen. Anything that
     * reaches this point without an answer is genuinely never going to get one,
     * and saying so is both true and the only shape the provider will take.
     */
    $answered = [];
    foreach ($rows as $row) {
        if ((string)$row['role'] === 'tool') {
            $answered[(string)$row['tool_id']] = true;
        }
    }

    $out = [];
    $pendingCalls = [];
    $pendingText = '';
    $orphans = [];

    // Assistant text that turned out to have no tool calls after it.
    $flushText = function () use (&$out, &$pendingText): void {
        if ($pendingText !== '') {
            $out[] = ['role' => 'assistant', 'content' => $pendingText];
            $pendingText = '';
        }
    };

    foreach ($rows as $row) {
        $role = (string)$row['role'];

        if ($role === 'tool_call') {
            /*
             * Several tool calls in one assistant turn are one message.
             *
             * Every provider requires that shape, and storing them as separate
             * rows is what makes the transcript readable - so they are
             * collected here and flushed when the next non-call row arrives.
             */
            $pendingCalls[] = [
                'id' => (string)$row['tool_id'],
                'type' => 'function',
                'function' => [
                    'name' => (string)$row['tool_name'],
                    'arguments' => (string)($row['tool_args'] ?? '{}'),
                ],
            ];

            if (!isset($answered[(string)$row['tool_id']])) {
                $orphans[] = (string)$row['tool_id'];
            }
            continue;
        }

        if ($pendingCalls !== []) {
            $out[] = [
                'role' => 'assistant',
                'content' => $pendingText,
                'tool_calls' => $pendingCalls,
            ];
            $pendingCalls = [];
            $pendingText = '';
        }

        if ($role === 'tool') {
            $flushText();
            $out[] = [
                'role' => 'tool',
                'tool_call_id' => (string)$row['tool_id'],
                'content' => (string)($row['content'] ?? ''),
            ];
            continue;
        }

        if ($role === 'assistant') {
            /*
             * Held back, in case tool calls follow immediately.
             *
             * A model that answers with text AND asks for a tool made one turn,
             * and every provider expects that as one assistant message carrying
             * both. Emitting the text on its own would make the model read its
             * own history as two turns where there was one - and a provider
             * that validates the shape would reject it outright.
             *
             * aiStep always writes the text immediately before the calls, so
             * adjacency is exactly the right signal and needs no turn marker.
             */
            $pendingText = (string)($row['content'] ?? '');
            continue;
        }

        if ($role === 'picture' || $role === 'reaction') {
            /*
             * Shown, not sent.
             *
             * The model described this picture; handing it back a rendering of
             * its own description tells it nothing and costs more tokens than
             * the conversation that produced it.
             */
            continue;
        }

        if ($role === 'user') {
            $flushText();
            $content = (string)($row['content'] ?? '');

            /*
             * A picture is not a very long string.
             *
             * Screenshots and rendered charts are stored as data URLs in the
             * same column as everything else, and sending one as text would be
             * a megabyte of base64 the model reads as gibberish and is billed
             * for. Marked here and turned into whatever content block the
             * provider wants by the request builder.
             */
            if (str_starts_with($content, 'data:image/')) {
                $out[] = ['role' => 'user', 'image' => $content];
                continue;
            }

            $out[] = ['role' => 'user', 'content' => $content];
        }
    }

    if ($pendingCalls !== []) {
        $out[] = ['role' => 'assistant', 'content' => $pendingText, 'tool_calls' => $pendingCalls];
        $pendingText = '';
    }
    $flushText();

    /*
     * Any call still without an answer gets one, in place.
     *
     * Inserted directly after the assistant message that made the call, which
     * is where every provider requires the output to sit - appending them at
     * the end would produce a transcript that is just as invalid in a different
     * way.
     */
    if ($orphans !== []) {
        $out = aiAnswerOrphans($out, $orphans);
    }

    return $out;
}

/**
 * Fills in an output for every call that never got one.
 *
 * The text matters: a model told "not run" with no reason will call the same
 * tool again immediately, and the loop repeats until the turn limit. Told that
 * the operator did not grant permission, it moves on or asks.
 *
 * @param list<array<string, mixed>> $out
 * @param list<string> $orphans
 * @return list<array<string, mixed>>
 */
function aiAnswerOrphans(array $out, array $orphans): array
{
    $missing = array_flip($orphans);
    $filled = [];

    foreach ($out as $message) {
        $filled[] = $message;

        foreach ($message['tool_calls'] ?? [] as $call) {
            if (!isset($missing[$call['id']])) {
                continue;
            }
            $filled[] = [
                'role' => 'tool',
                'tool_call_id' => (string)$call['id'],
                'content' => json_encode([
                    'error' => 'This did not run. It was waiting for the operator to allow '
                        . 'it and they did not answer. Do not call it again unless they ask '
                        . 'for it; carry on with what you can do without it.',
                ]),
            ];
        }
    }

    return $filled;
}

/**
 * Advances the conversation by one step.
 *
 * Exactly one request to the model, and then whatever server-side tools it
 * asked for. Anything needing a person or a browser leaves the session parked
 * in a waiting state and returns; the next step happens when that is answered.
 *
 * @return array<string, mixed> the session's new state, for the caller to return
 */
function aiStep(
    array $config,
    PDO $ai,
    PDO $pdo,
    int $id,
    array $views,
    ?callable $ask = null
): array {
    $session = aiSession($ai, $id);
    if ($session === null) {
        throw new ActionRefused('no such session');
    }

    if ($session['state'] !== 'thinking') {
        return aiSessionView($config, $ai, $id, $views);
    }

    $limits = aiWithinLimits($session);
    if ($limits['ok']) {
        $limits = aiWithinSharedBudget($ai, aiSettings($config));
    }
    if (!$limits['ok']) {
        /*
         * Which limit, kept with the reason.
         *
         * "Start a new one" was the only way out, and it throws away the
         * conversation. Naming the limit is what lets the page offer to raise
         * that one - for this conversation, not for every future one, which is
         * a different decision and belongs on the settings page.
         */
        aiAddMessage($ai, $id, 'assistant', $limits['why']);
        aiSetState($ai, $id, 'ended', $limits['why']);
        aiRememberLimitHit($ai, $id, (string)($limits['which'] ?? ''));
        return aiSessionView($config, $ai, $id, $views);
    }

    $settings = aiSettings($config);
    $tools = aiEnabledTools($config, $views);
    $schemas = aiToolSchemas($tools);

    $context = aiContext($config, $ai, $session);
    $prompt = aiSystemPrompt($config, $tools, $context);

    /*
     * Compacted before the request, not after it fails.
     *
     * The soft limit exists so there is still room to send the part being
     * summarised. Waiting for the provider to reject the request would mean
     * discovering the problem at the one moment nothing can be done about it.
     */
    $room = aiContextState($config, $ai, $id, $prompt);

    if ($room['full'] && $room['automatic']) {
        $done = aiCompact($config, $ai, $id, $ask);
        if ($done['compacted'] > 0) {
            $session = aiSession($ai, $id) ?? $session;
            $context = aiContext($config, $ai, $session);
            $prompt = aiSystemPrompt($config, $tools, $context);
        }
    }

    /*
     * The one call that leaves this machine, behind a seam.
     *
     * Passed in rather than called directly so the whole loop - dispatch,
     * permission, the browser round trip, the accounting - can be exercised
     * against a scripted model with no network. Same shape as the fetcher
     * urlWatchRunDue takes, for the same reason.
     */
    $ask ??= 'aiAsk';

    $answer = $ask(
        $settings,
        aiTranscript($ai, $id),
        $schemas,
        $prompt,
        // The effort chosen for this conversation, which the operator can turn
        // down per conversation without touching the setting.
        (string)($session['effort'] ?? '')
    );

    $millicents = $answer['ok']
        ? aiCost($settings, $answer['tokens_in'] ?? 0, $answer['tokens_out'] ?? 0,
            $answer['tokens_cached'] ?? 0)
        : 0;

    /*
     * Recorded before anything is done with it.
     *
     * Including on failure: a request that was sent and refused still left this
     * machine, and "what exactly was sent" has to be answerable without
     * qualification afterwards.
     */
    $ai->prepare(
        'INSERT INTO ai_calls (session_id, at, ms, model, tokens_in, tokens_out,
                               tokens_cached, millicents, price_in, price_out, price_cached,
                               request, response, error, http_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $id, time(), (int)($answer['ms'] ?? 0), $settings['model'],
        (int)($answer['tokens_in'] ?? 0), (int)($answer['tokens_out'] ?? 0),
        (int)($answer['tokens_cached'] ?? 0), $millicents,
        (float)$settings['price_in'], (float)$settings['price_out'], (float)$settings['price_cached'],
        json_encode($answer['request'] ?? null, JSON_PARTIAL_OUTPUT_ON_ERROR),
        json_encode($answer['response'] ?? null, JSON_PARTIAL_OUTPUT_ON_ERROR),
        $answer['error'] ?? null,
        (int)($answer['status'] ?? 0),
    ]);
    $callId = (int)$ai->lastInsertId();

    $ai->prepare(
        'UPDATE ai_sessions SET turns = turns + 1, last_at = ?,
                tokens_in = tokens_in + ?, tokens_out = tokens_out + ?,
                tokens_cached = tokens_cached + ?, spent_millicents = spent_millicents + ?
          WHERE id = ?'
    )->execute([
        time(), (int)($answer['tokens_in'] ?? 0), (int)($answer['tokens_out'] ?? 0),
        (int)($answer['tokens_cached'] ?? 0), $millicents, $id,
    ]);

    if (!$answer['ok']) {
        aiAddMessage($ai, $id, 'assistant',
            'I could not reach the model: ' . $answer['error']);
        aiSetState($ai, $id, 'failed', $answer['error']);
        return aiSessionView($config, $ai, $id, $views);
    }

    $text = trim((string)($answer['text'] ?? ''));
    $calls = $answer['tool_calls'] ?? [];

    if ($text !== '') {
        aiAddMessage($ai, $id, 'assistant', $text);
    }

    if ($calls === []) {
        aiSetState($ai, $id, 'idle');
        return aiSessionView($config, $ai, $id, $views);
    }

    return aiHandleCalls($config, $ai, $pdo, $id, $callId, $calls, $tools, $views);
}

/**
 * Deals with the tools the model asked for.
 *
 * Server tools run immediately. Anything needing a person or a browser is
 * recorded and the session parks. A call that fails validation is answered with
 * the reason rather than being dropped, because a model told what it got wrong
 * fixes it on the next turn and one told nothing repeats it.
 */
function aiHandleCalls(
    array $config,
    PDO $ai,
    PDO $pdo,
    int $id,
    int $callId,
    array $calls,
    array $tools,
    array $views
): array {
    $parked = false;
    $toRun = [];

    /*
     * Two passes: every call is recorded before any is run.
     *
     * A model that asked for two tools in one turn made ONE turn, and writing
     * each call immediately followed by its result splits it into two in the
     * transcript - the model then reads its own history as though it had gone
     * back and forth when it had not. Recording all the calls first keeps them
     * in a single assistant message, which is also the shape every provider
     * expects when several results follow.
     */
    foreach ($calls as $call) {
        $name = (string)$call['name'];
        $arguments = is_array($call['arguments']) ? $call['arguments'] : [];

        aiAddMessage($ai, $id, 'tool_call', null, [
            'tool_name' => $name,
            'tool_id' => (string)$call['id'],
            'tool_args' => $arguments,
        ]);

        $toRun[] = [$name, $arguments, (string)$call['id']];
    }

    foreach ($toRun as [$name, $arguments, $toolId]) {
        if (!isset($tools[$name])) {
            /*
             * An invented tool is answered, not ignored.
             *
             * A model whose call vanishes will try it again; one told the tool
             * does not exist and which ones do will pick a real one next turn.
             */
            aiRecordToolResult($ai, $id, $toolId, $name, [
                'error' => sprintf(
                    'There is no tool called "%s" available to you. The tools you have are: '
                    . '%s. Use one of those, or answer without a tool.',
                    $name,
                    implode(', ', array_keys($tools))
                ),
            ]);
            continue;
        }

        $tool = $tools[$name];
        $checked = aiCheckArguments($tool, $arguments);

        if (!$checked['ok']) {
            aiRecordToolResult($ai, $id, $toolId, $name, [
                'error' => $checked['why'] . ' Call it again with the parameters corrected.',
            ]);
            continue;
        }

        $needsPermission = (bool)$tool['permission'];
        $where = (string)$tool['where'];

        $ai->prepare(
            'INSERT INTO ai_tool_runs (session_id, call_id, at, tool, tool_id, arguments,
                                       runs_where, state, needs_permission)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $id, $callId, time(), $name, $toolId,
            json_encode($checked['values']), $where,
            $needsPermission ? 'proposed' : 'allowed',
            $needsPermission ? 1 : 0,
        ]);
        $runId = (int)$ai->lastInsertId();

        if ($needsPermission) {
            $parked = 'waiting_permission';
            continue;
        }

        if ($where === 'browser') {
            $parked = $parked ?: 'waiting_browser';
            continue;
        }

        if ($tool['group'] === 'pictures') {
            aiRunPicture($config, $ai, $id, $runId, $name, $checked['values'], $toolId);
            continue;
        }

        aiRunAndRecord($config, $ai, $pdo, $id, $runId, $name, $checked['values'], $toolId);
    }

    aiSetState($ai, $id, $parked ?: 'thinking');
    return aiSessionView($config, $ai, $id, $views);
}

/** Runs a server tool and writes both the run row and the model's result message. */
function aiRunAndRecord(
    array $config,
    PDO $ai,
    PDO $pdo,
    int $session,
    int $runId,
    string $name,
    array $values,
    string $toolId
): void {
    $started = microtime(true);
    $result = aiRunTool($name, $values, $config, $pdo, $ai, $session);
    $ms = (int)round((microtime(true) - $started) * 1000);

    $ai->prepare(
        'UPDATE ai_tool_runs SET state = ?, settled_at = ?, result = ?, ms = ?, error = ?
          WHERE id = ?'
    )->execute([
        isset($result['error']) ? 'failed' : 'done',
        time(),
        aiEncodeResult($result),
        $ms,
        $result['error'] ?? null,
        $runId,
    ]);

    aiRecordToolResult($ai, $session, $toolId, $name, $result);
}

/** The tool's answer, as a message the model will read on the next step. */
function aiRecordToolResult(PDO $ai, int $session, string $toolId, string $name, array $result): void
{
    aiAddMessage($ai, $session, 'tool', aiEncodeResult($result), [
        'tool_name' => $name,
        'tool_id' => $toolId,
    ]);
}

/**
 * Everything the browser needs to draw the conversation.
 *
 * Deliberately one call. The panel polls this several times a second while a
 * turn is running, and a design needing three round trips to know what to show
 * would triple that.
 *
 * @return array<string, mixed>
 */
function aiSessionView(array $config, PDO $ai, int $id, array $views): array
{
    $session = aiSession($ai, $id);
    if ($session === null) {
        return ['exists' => false];
    }

    $settings = aiSettings($config);

    /*
     * visible = 0 is a record, not a message.
     *
     * Screenshots and the pictures of charts that were shown live in the same
     * table so that the transcript is complete, but they are not things to draw
     * in a chat panel - one is already on screen as a real chart, and the other
     * is a megabyte of base64 that was rendered as text until this filter
     * existed.
     */
    $messages = $ai->prepare(
        'SELECT id, at, role, content, tool_name, tool_args FROM ai_messages
          WHERE session_id = ? AND visible = 1 ORDER BY id'
    );
    $messages->execute([$id]);

    $shown = [];
    foreach ($messages->fetchAll() as $row) {
        $role = (string)$row['role'];

        /*
         * Tool results are summarised, not shown.
         *
         * A reply can be twelve kilobytes of JSON, and putting that in a chat
         * panel buries the conversation it belongs to. What somebody wants to
         * see is that a tool ran and which one - the whole result is on the
         * session's own page for anybody who wants it.
         */
        if ($role === 'tool') {
            continue;
        }

        /*
         * The compaction marker, with the range it stands for.
         *
         * It is drawn differently from an ordinary message - it is not
         * something either party said - and it can be opened to see what it
         * replaced, so a summary can be checked against the messages it claims
         * to cover rather than taken on trust.
         */
        if ($role === 'summary') {
            $shown[] = [
                'id' => (int)$row['id'],
                'at' => (int)$row['at'],
                'role' => 'summary',
                'content' => (string)($row['content'] ?? ''),
                'covers' => json_decode((string)($row['tool_args'] ?? '{}'), true) ?: [],
            ];
            continue;
        }

        if ($role === 'tool_call' || $role === 'picture' || $role === 'reaction') {
            $shown[] = [
                'id' => (int)$row['id'],
                'at' => (int)$row['at'],
                'role' => $role,
                'tool' => (string)$row['tool_name'],
                'url' => in_array($role, ['picture', 'reaction'], true)
                    ? (string)($row['content'] ?? '')
                    : null,
                'arguments' => json_decode((string)($row['tool_args'] ?? '{}'), true) ?: [],
            ];
            continue;
        }

        $shown[] = [
            'id' => (int)$row['id'],
            'at' => (int)$row['at'],
            'role' => $role,
            'content' => (string)($row['content'] ?? ''),
        ];
    }

    $runs = $ai->prepare(
        'SELECT id, tool, arguments, runs_where, state, needs_permission, ms, error
           FROM ai_tool_runs WHERE session_id = ? ORDER BY id'
    );
    $runs->execute([$id]);
    $toolRuns = $runs->fetchAll();

    $waiting = array_values(array_filter($toolRuns, fn(array $run): bool =>
        $run['state'] === 'proposed'));

    $forBrowser = array_values(array_filter($toolRuns, fn(array $run): bool =>
        $run['state'] === 'allowed' && $run['runs_where'] === 'browser'));

    $all = aiTools($views);

    return [
        'exists' => true,
        'id' => (int)$session['id'],
        'state' => (string)$session['state'],
        'stop_reason' => $session['stop_reason'],
        // Which limit ended it, so the panel can offer to raise that one rather
        // than offering to raise all three or none.
        'limit_hit' => $session['limit_hit'] ?? null,
        'limits' => [
            'max_seconds' => (int)$session['max_seconds'],
            'max_millicents' => (int)$session['max_millicents'],
            'max_turns' => (int)$session['max_turns'],
        ],
        'started_at' => (int)$session['started_at'],
        'model' => (string)$session['model'],
        'turns' => (int)$session['turns'],
        'messages' => $shown,
        'tokens' => [
            'in' => (int)$session['tokens_in'],
            'out' => (int)$session['tokens_out'],
            'cached' => (int)$session['tokens_cached'],
        ],
        'spent' => [
            'millicents' => (int)$session['spent_millicents'],
            'said' => aiMoney((int)$session['spent_millicents']),
            /*
             * Whether the figure means anything.
             *
             * Without prices configured the cost is zero, which is not "free" -
             * it is "not known". Saying which stops somebody reading a zero as
             * a reassurance.
             */
            'known' => $settings['price_in'] > 0 || $settings['price_out'] > 0,
        ],
        'limits' => [
            'max_seconds' => (int)$session['max_seconds'],
            'max_millicents' => (int)$session['max_millicents'],
            'max_turns' => (int)$session['max_turns'],
            'ran_for' => time() - (int)$session['started_at'],
        ],
        'context' => aiContextState($config, $ai, $id,
            aiSystemPrompt($config, aiEnabledTools($config, $views),
                aiContext($config, $ai, $session))),
        'effort' => (string)($session['effort'] ?? ''),
        'effort_ceiling' => (string)($settings['reasoning_effort'] ?? ''),
        'effort_levels' => aiEffortLevels(),
        'viewing' => (string)($session['viewing'] ?? ''),
        // The one the browser has to run next, if any.
        'browser_tool' => $forBrowser === [] ? null : aiToolRunForBrowser($forBrowser[0], $all),
        'permission' => $waiting === [] ? null : aiToolRunForBrowser($waiting[0], $all),
        'tool_runs' => array_map(fn(array $run): array => [
            'id' => (int)$run['id'],
            'tool' => (string)$run['tool'],
            'summary' => $all[$run['tool']]['summary'] ?? '',
            'state' => (string)$run['state'],
            'ms' => (int)$run['ms'],
            'error' => $run['error'],
        ], $toolRuns),
    ];
}

/**
 * Stops whatever is in flight, without ending the conversation.
 *
 * Different from stopping the session, and the difference is the point. A model
 * three tool calls into diagnosing the wrong thing does not need the
 * conversation thrown away - it needs to be told to stop and listen. This
 * settles everything outstanding, leaves the session idle, and lets the next
 * message be an ordinary message.
 *
 * Every unsettled run is answered rather than abandoned, because a call with no
 * output is a transcript no provider will accept.
 *
 * @return array{runs: int}
 */
function aiInterrupt(PDO $ai, int $id, string $why = ''): array
{
    $stmt = $ai->prepare(
        "SELECT id, tool_id, tool FROM ai_tool_runs
          WHERE session_id = ? AND state IN ('proposed', 'allowed', 'running')"
    );
    $stmt->execute([$id]);
    $open = $stmt->fetchAll();

    $said = $why !== ''
        ? 'The operator interrupted and said: ' . $why
        : 'The operator interrupted before this ran.';

    foreach ($open as $run) {
        $ai->prepare(
            "UPDATE ai_tool_runs SET state = 'cancelled', settled_at = ?, error = ?
              WHERE id = ?"
        )->execute([time(), $said, (int)$run['id']]);

        aiRecordToolResult($ai, $id, (string)$run['tool_id'], (string)$run['tool'], [
            'error' => $said . ' Stop what you were doing and read what they say next.',
        ]);
    }

    /*
     * Left idle rather than thinking.
     *
     * Interrupting and then immediately sending another request would be the
     * machine talking over somebody who has just asked it to be quiet. It waits
     * for what they have to say.
     */
    aiSetState($ai, $id, 'idle');

    return ['runs' => count($open)];
}

/** One pending tool run, with what the browser needs to act on it. */
function aiToolRunForBrowser(array $run, array $catalogue): array
{
    $arguments = json_decode((string)$run['arguments'], true) ?: [];

    return [
        'id' => (int)$run['id'],
        'tool' => (string)$run['tool'],
        'summary' => $catalogue[$run['tool']]['summary'] ?? '',
        'arguments' => $arguments,
        'why' => (string)($arguments['why'] ?? ''),
        'needs_permission' => (bool)$run['needs_permission'],
    ];
}

/**
 * The picture tools, which cost money and share an hourly budget.
 *
 * Kept out of aiRunTool because they are the only tools that reach outside this
 * machine, the only ones with a bill attached, and the only ones that need the
 * assistant's own database rather than the account's.
 */
function aiRunPicture(
    array $config,
    PDO $ai,
    int $session,
    int $runId,
    string $name,
    array $values,
    string $toolId
): void {
    $settings = aiImageSettings($config);
    $started = microtime(true);

    $budget = aiImageBudget($ai, $settings);
    if (!$budget['ok']) {
        $result = ['error' => 'No picture was made: ' . $budget['why']
            . ' Say so plainly rather than trying again.'];
        aiSettleTool($ai, $runId, $result, (int)round((microtime(true) - $started) * 1000));
        aiRecordToolResult($ai, $session, $toolId, $name, $result);
        return;
    }

    $isPortrait = $name === 'redraw_yourself';

    /*
     * A portrait has its style chosen here, not by the model.
     *
     * It is not a creative decision - it is "whatever this install allows that
     * suits a face" - and asking the model to pick means it picks the fantasy
     * landscape generator for a self-portrait, which it did.
     */
    $style = $isPortrait
        ? (in_array('cyberpunk-portrait-generator', $settings['styles'], true)
            ? 'cyberpunk-portrait-generator'
            : $settings['styles'][0])
        : (string)$values['style'];

    $prompt = (string)$values['description'];

    $made = aiMakeImage($settings, $prompt, $style);
    $ms = (int)round((microtime(true) - $started) * 1000);

    if (!$made['ok']) {
        aiRecordImage($ai, $session, $isPortrait ? 'portrait' : 'picture', $prompt, $style,
            null, (string)$made['error']);
        $result = ['error' => 'The picture could not be made: ' . $made['error']];
        aiSettleTool($ai, $runId, $result, $ms);
        aiRecordToolResult($ai, $session, $toolId, $name, $result);
        return;
    }

    $url = (string)($made['body']['output_url'] ?? '');

    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        $result = ['error' => 'The generator answered without a picture.'];
        aiRecordImage($ai, $session, $isPortrait ? 'portrait' : 'picture', $prompt, $style,
            null, 'no output_url');
        aiSettleTool($ai, $runId, $result, $ms);
        aiRecordToolResult($ai, $session, $toolId, $name, $result);
        return;
    }

    $cost = aiImageCost($ai, $settings, $settings['quality']);
    $imageId = aiRecordImage($ai, $session, $isPortrait ? 'portrait' : 'picture', $prompt,
        $style, $url, null, $cost, (string)$settings['quality']);

    /*
     * The bytes are kept, not just the link.
     *
     * A generator's URL expires, usually within days. Keeping only the link
     * means a picture that was paid for is a 404 by the time anybody reviews
     * it, and the record of what was made says nothing. Failure here is not
     * failure of the tool - the picture exists and is on screen - so it is
     * noted and the link is still used.
     */
    $kept = aiKeepAsset($ai, $imageId, $url);

    if ($cost > 0) {
        $ai->prepare(
            'UPDATE ai_sessions SET spent_millicents = spent_millicents + ? WHERE id = ?'
        )->execute([$cost, $session]);
    }

    /*
     * Shown to the user, not sent back to the model.
     *
     * It described the picture; a copy of its own description rendered as an
     * image tells it nothing and would cost more tokens than the whole
     * conversation so far.
     */
    aiAddMessage($ai, $session, 'picture', $url, [
        'tool_name' => $name,
        'tool_args' => ['description' => $prompt, 'style' => $style,
                        'portrait' => $isPortrait,
                        'asset' => $kept['ok'] ? $imageId : 0],
    ]);

    $result = [
        'made' => true,
        'style' => $style,
        'note' => $isPortrait
            ? 'Your portrait has been replaced. It is now shown beside your name.'
            : 'The picture is now visible in the conversation. You have not been sent it.',
        'pictures_this_hour' => $budget['used'] + 1,
        'allowed_per_hour' => $budget['allowed'],
    ];

    aiSettleTool($ai, $runId, $result, $ms);
    aiRecordToolResult($ai, $session, $toolId, $name, $result);
}

/** Closes off a tool run row with whatever happened. */
function aiSettleTool(PDO $ai, int $runId, array $result, int $ms): void
{
    $ai->prepare(
        'UPDATE ai_tool_runs SET state = ?, settled_at = ?, result = ?, ms = ?, error = ?
          WHERE id = ?'
    )->execute([
        isset($result['error']) ? 'failed' : 'done',
        time(),
        aiEncodeResult($result),
        $ms,
        $result['error'] ?? null,
        $runId,
    ]);
}
