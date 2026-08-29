<?php
declare(strict_types=1);

/*
 * What the assistant is allowed to do, described so that it does the right one.
 *
 * A tool description is a specification the model reads once and then acts on
 * for the rest of the conversation, and a vague one produces a confident wrong
 * call. So each description says three things explicitly: what the tool
 * returns, when to use it, and - the part usually left out - when *not* to.
 * "Only for network problems" written down is worth more than any amount of
 * cleverness in the prompt.
 *
 * The hard rule of this file: the model never writes SQL, never names a table
 * or a column, and never supplies a fragment of a query. Every tool is a
 * predefined statement with typed, bounded, enumerated parameters bound to it.
 * A model that could pass a string into a query would eventually pass one that
 * was not what anybody meant - not from malice, just from being a language
 * model - and there is no version of that which ends well on a database that
 * holds a machine's history.
 *
 * Three kinds of tool, and the difference matters at every layer:
 *
 *   server   read-only questions answered here, from the databases. Safe, fast,
 *            and the majority.
 *   browser  things only the page can do - move the user to another view, take
 *            a screenshot. These leave the server, wait for the browser, and
 *            come back.
 *   ask      anything that changes something. None are enabled by default and
 *            each one asks a person first.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/spans.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/aicharts.php';
require_once __DIR__ . '/aiimages.php';
require_once __DIR__ . '/ai-tools/current-status.php';
require_once __DIR__ . '/ai-tools/measurement-history.php';
require_once __DIR__ . '/ai-tools/recent-alerts.php';
require_once __DIR__ . '/ai-tools/recent-log-lines.php';
require_once __DIR__ . '/ai-tools/repeated-log-lines.php';
require_once __DIR__ . '/ai-tools/crowded-directories.php';
require_once __DIR__ . '/ai-tools/busiest-processes.php';
require_once __DIR__ . '/ai-tools/site-check-results.php';
require_once __DIR__ . '/ai-tools/network-summary.php';
require_once __DIR__ . '/ai-tools/collector-health.php';
require_once __DIR__ . '/ai-tools/recent-changes.php';
require_once __DIR__ . '/ai-tools/action-history.php';
require_once __DIR__ . '/ai-tools/gauges.php';
require_once __DIR__ . '/ai-tools/list-charts.php';
require_once __DIR__ . '/ai-tools/filesystems.php';
require_once __DIR__ . '/ai-tools/symlink-problems.php';
require_once __DIR__ . '/ai-tools/backups.php';
require_once __DIR__ . '/ai-tools/domain-expiry.php';
require_once __DIR__ . '/ai-tools/uptime.php';
require_once __DIR__ . '/ai-tools/notes.php';
require_once __DIR__ . '/ai-tools/terminal-capabilities.php';
require_once __DIR__ . '/ai-tools/host-request-capabilities.php';
require_once __DIR__ . '/ai-tools/pending-host-requests.php';
require_once __DIR__ . '/ai-tools/collection-schedule.php';
require_once __DIR__ . '/ai-tools/services.php';
require_once __DIR__ . '/ai-tools/time-range.php';
require_once __DIR__ . '/ai-tools/largest-paths.php';
require_once __DIR__ . '/ai-tools/chart-data.php';
require_once __DIR__ . '/ai-tools/show-chart.php';
require_once __DIR__ . '/ai-tools/chart-image.php';
require_once __DIR__ . '/ai-tools/present-visual.php';
require_once __DIR__ . '/ai-tools/recommend-command.php';
require_once __DIR__ . '/ai-tools/show-progress.php';
require_once __DIR__ . '/ai-tools/show-reaction.php';
require_once __DIR__ . '/ai-tools/redraw-portrait.php';
require_once __DIR__ . '/ai-tools/terminal-commands.php';
require_once __DIR__ . '/ai-tools/terminal-output.php';
require_once __DIR__ . '/ai-tools/offer-choices.php';
require_once __DIR__ . '/ai-tools/ask-user.php';

/**
 * Every tool, with its schema and how it runs.
 *
 * `where` is server, browser or ask. `permission` is whether a person must
 * agree before each use. `group` is what the settings page uses to let somebody
 * turn a whole category off.
 *
 * @return array<string, array<string, mixed>>
 */
function aiToolCatalogue(): array
{
    return [

        ...aiToolCurrentStatusPackage(),
        ...aiToolMeasurementHistoryPackage(),
        ...aiToolRecentAlertsPackage(),
        ...aiToolRecentLogLinesPackage(),
        ...aiToolRepeatedLogLinesPackage(),
        ...aiToolCrowdedDirectoriesPackage(),
        ...aiToolBusiestProcessesPackage(),
        ...aiToolSiteCheckResultsPackage(),
        ...aiToolNetworkSummaryPackage(),
        ...aiToolCollectorHealthPackage(),
        ...aiToolRecentChangesPackage(),
        ...aiToolActionHistoryPackage(),
        ...aiToolGaugesPackage(),
        ...aiToolListChartsPackage(),
        ...aiToolFilesystemsPackage(),
        ...aiToolSymlinkProblemsPackage(),
        ...aiToolBackupsPackage(),
        ...aiToolDomainExpiryPackage(),
        ...aiToolUptimePackage(),
        ...aiToolNotesPackage(),
        ...aiToolTerminalCapabilitiesPackage(),
        ...aiToolHostRequestCapabilitiesPackage(),
        ...aiToolPendingHostRequestsPackage(),
        ...aiToolCollectionSchedulePackage(),
        ...aiToolServicesPackage(),
        ...aiToolTimeRangePackage(),
        ...aiToolLargestPathsPackage(),
        ...aiToolChartDataPackage(),
        ...aiToolShowChartPackage(),
        ...aiToolChartImagePackage(),
        ...aiToolPresentVisualPackage(),
        ...aiToolRecommendCommandPackage(),
        ...aiToolShowProgressPackage(),
        ...aiToolShowReactionPackage(),
        ...aiToolRedrawPortraitPackage(),
        ...aiToolTerminalCommandsPackage(),
        ...aiToolTerminalOutputPackage(),
        ...aiToolOfferChoicesPackage(),
        ...aiToolAskUserPackage(),

        /* ------------------------------------------------- what is wrong -- */



        /* ------------------------------------------------------- the charts -- */

        /* --------------------------------------------------- saying things -- */

        'remember' => [
            'group' => 'memory',
            'where' => 'server',
            'permission' => false,
            'summary' => 'write something down for future conversations.',
            'description' =>
                "Stores one fact so that a LATER conversation can use it. This survives "
                . "the end of this session; nothing else you do here does.\n\n"
                . "USE THIS for things that will still be true next month and that cost "
                . "real work to establish:\n"
                . "  - which of several installed versions of something the site actually "
                . "uses, when that is not the obvious one\n"
                . "  - what a recurring pattern in the readings turns out to be (a nightly "
                . "backup, a cron job, a neighbour on the same host)\n"
                . "  - a conclusion you reached from evidence, WITH what it rested on\n"
                . "  - something the operator told you about how they want to be helped\n\n"
                . "DO NOT use it for:\n"
                . "  - anything already visible in the dashboard - the readings, the disk "
                . "sizes, the alert list. Those are looked up, not remembered, and a "
                . "remembered copy is a stale copy.\n"
                . "  - what is happening right now. \"CPU is at 94%\" is a reading, not a "
                . "memory, and next week it will be a lie.\n"
                . "  - anything you were told in confidence, and never a password, key or "
                . "token. The store is refused outright if it looks like a credential, and "
                . "everything in it is shown to the operator on a page.\n"
                . "  - a guess. If you are not sure, do not write it down; a remembered "
                . "guess reads exactly like a remembered fact six weeks later.\n\n"
                . "Writing the same SUBJECT twice replaces what was there. Use that to "
                . "correct yourself rather than writing a second, contradicting memory.",
            'parameters' => [
                'kind' => [
                    'type' => 'string', 'required' => true,
                    'enum' => ['fact', 'preference', 'finding', 'todo'],
                    'description' => 'fact = something true of this machine. preference = '
                        . 'how the operator wants to be helped. finding = a conclusion you '
                        . 'reached and what it rested on. todo = something identified and '
                        . 'not yet done.',
                ],
                'subject' => [
                    'type' => 'string', 'required' => true, 'maxLength' => 120,
                    'description' => 'What this memory is ABOUT, as a short phrase, in lower '
                        . 'case. This is the key: writing the same subject again replaces '
                        . 'the memory. "the nightly io spike", not "I found out that the '
                        . 'nightly io spike is the backup".',
                ],
                'body' => [
                    'type' => 'string', 'required' => true, 'maxLength' => 1200,
                    'description' => 'The fact itself, in full sentences, including what it '
                        . 'was based on. Written for a version of you that has none of this '
                        . 'conversation - so no "as we discussed" and no pronouns pointing '
                        . 'at things that will not be there.',
                ],
            ],
        ],

        'recall' => [
            'group' => 'memory',
            'where' => 'server',
            'permission' => false,
            'summary' => 'look up what was remembered from earlier conversations.',
            'description' =>
                "Searches everything written down by `remember` in past conversations.\n\n"
                . "The most useful memories are already put in front of you at the start of "
                . "a conversation, so use this when you want something SPECIFIC that is not "
                . "there - you are about to investigate a subsystem and want to know whether "
                . "it has been looked at before, or the operator refers to something that "
                . "sounds like it happened previously.\n\n"
                . "Plain substring matching on the subject and the body. It is not a "
                . "semantic search: search for a word that would literally appear, and if "
                . "nothing comes back, nothing was remembered about it - do not treat an "
                . "empty result as evidence that a thing did not happen.",
            'parameters' => [
                'about' => [
                    'type' => 'string', 'required' => false, 'maxLength' => 120,
                    'description' => 'A word or phrase to look for. Leave it out to get the '
                        . 'most recently useful memories.',
                ],
                'limit' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'required' => false,
                    'default' => 20,
                    'description' => 'How many to return at most.',
                ],
            ],
        ],

        /* ------------------------------------------- asking the user -- */

        /* --------------------------------------- what somebody just ran -- */

        /* --------------------------------------------- pointing at things -- */

        'highlight_on_page' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'point at something on the page.',
            'description' =>
                "Briefly outlines and pulses one thing on the page the user is looking at, "
                . "and scrolls it into view.\n\n"
                . "USE THIS WITH A SENTENCE ABOUT WHAT YOU ARE POINTING AT. \"The row for "
                . "clamd\" in prose is something the user then has to find in ninety rows; "
                . "this puts their eye on it. It is at its best straight after open_page, "
                . "which gets them to the right page but not to the right line.\n\n"
                . "It highlights and fades. It does not select, change or open anything, "
                . "and it never leaves a mark behind.\n\n"
                . "IF NOTHING MATCHES, that is what comes back - `found: false` - and you "
                . "should say so rather than describing a highlight that did not happen. "
                . "The user may be on a different page, or the thing may not be on screen.\n\n"
                . "DO NOT use it repeatedly. Pointing at six things in a row is pointing "
                . "at nothing.",
            'parameters' => [
                'find' => [
                    'type' => 'string', 'maxLength' => 120, 'required' => true,
                    'description' => 'The text to look for on the page - a process name, a '
                        . 'path, a setting label, a chart title. Matched inside the page '
                        . 'the user currently has open, ignoring case. Use the exact text '
                        . 'you saw in a tool result rather than a description of it.',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 160, 'required' => true,
                    'description' => 'What you want them to notice about it. Shown beside '
                        . 'the highlight, so it reads as a caption rather than a label.',
                ],
            ],
        ],

        /* --------------------------------------------- asking the host -- */

        'request_from_host' => [
            'group' => 'host',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'ask root\'s collector to do something on its next run.',
            'description' =>
                "Asks the user to approve a job for root's collector, and queues it if they "
                . "agree.\n\n"
                . "USE THIS WHEN A TOOL SAID SOMETHING IS NOT AVAILABLE TO THIS ACCOUNT. "
                . "That is the whole point: this account is inside a cage and cannot see "
                . "the process table, run whois, or walk /var - but root's collector can, "
                . "and it will leave the answer where the next tool call can read it.\n\n"
                . "IT DOES NOT RETURN THE ANSWER. It queues a job. The collector runs on "
                . "its own schedule - often every five minutes, sometimes not at all if it "
                . "was never installed - and the answer appears afterwards. Say that "
                . "plainly, and use get_pending_host_requests to find out whether it has landed rather "
                . "than assuming it has. wait_for_new_readings is how you wait for it "
                . "without ending the conversation.\n\n"
                . "THE USER IS ASKED FIRST, EVERY TIME. These jobs run as root on a machine "
                . "that may be shared, and some of them are expensive - a disk walk on a "
                . "large filesystem takes minutes and costs I/O the whole time. If they do "
                . "not answer within the timeout, nothing is queued and this returns "
                . "queued=false. Treat that as a no and carry on.\n\n"
                . "CHECK get_pending_host_requests FIRST. Asking for a job that is already waiting "
                . "does not make it happen sooner, and it does put the same question in "
                . "front of the user twice.",
            'parameters' => [
                'job' => [
                    'type' => 'string',
                    /*
                     * A fixed list, not a command.
                     *
                     * This is the one tool that reaches something running as root, so
                     * what it can ask for is enumerated here and validated again by the
                     * collector before it acts. There is no free-text path through it.
                     */
                    'enum' => ['capabilities', 'disk-walk'],
                    'required' => true,
                    'description' => '"capabilities" re-checks which programs exist on the '
                        . 'host and their versions, which is what answers "is that '
                        . 'installed on the machine even though this account cannot see '
                        . 'it". "disk-walk" walks the whole filesystem as root, reaching '
                        . 'other accounts and /var - expensive, minutes on a large disk, '
                        . 'and the only thing that finds space the account cannot. These '
                        . 'two and no others: the process list and the service list are '
                        . 'taken on every run already, so asking for them would queue '
                        . 'something nothing acts on.',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 200, 'required' => true,
                    'description' => 'What you are trying to find out, shown to the user '
                        . 'when they are asked. It has to be a real reason they can judge '
                        . '- "the account cannot see other users\' processes and the '
                        . 'memory does not add up" rather than "to gather information".',
                ],
                'seconds' => [
                    'type' => 'integer', 'minimum' => 15, 'maximum' => 900,
                    'required' => false, 'default' => 120,
                    'description' => 'How long to wait for them to decide before treating '
                        . 'it as a no.',
                ],
            ],
        ],

        'wait_for_new_readings' => [
            'group' => 'diagnostic',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'wait until fresh measurements arrive.',
            'description' =>
                "Waits for the next collector run to land, then returns. Use it when the "
                . "answer you need does not exist yet.\n\n"
                . "USE THIS AFTER request_from_host, or when a reading is a few minutes old "
                . "and the question is about right now, or when you have asked somebody to "
                . "change something and want to see the effect. It is the difference "
                . "between \\\"try again in five minutes\\\" and staying in the conversation "
                . "until the data arrives.\n\n"
                . "which=\\\"account\\\" is this dashboard's own collector, which runs "
                . "frequently. which=\\\"host\\\" is root's, which runs less often and may "
                . "not be installed at all - check get_collector_health or "
                . "get_pending_host_requests "
                . "first, because waiting for a collector that has never run wastes the "
                . "whole timeout.\n\n"
                . "RETURNS EITHER WAY. `arrived: true` means new data landed and you should "
                . "re-read whatever you were waiting on - the tool results you already have "
                . "are from before it. `arrived: false` means the timeout passed with "
                . "nothing new, which is itself a finding worth reporting: the collector is "
                . "not running.\n\n"
                . "DO NOT use it to pad a conversation, and do not wait twice in a row for "
                . "the same thing.",
            'parameters' => [
                'which' => [
                    'type' => 'string',
                    'enum' => ['account', 'host'],
                    'required' => true,
                    'description' => '"account" for this dashboard\'s own collector, '
                        . '"host" for root\'s.',
                ],
                'seconds' => [
                    'type' => 'integer', 'minimum' => 30, 'maximum' => 1800,
                    'required' => false, 'default' => 300,
                    'description' => 'How long to wait before giving up. Pick it from how '
                        . 'often that collector actually runs, which get_collection_'
                        . 'schedule will tell you.',
                ],
            ],
        ],

        /* ------------------------------------------ naming the session -- */

        'set_session_topic' => [
            'group' => 'memory',
            'where' => 'server',
            'permission' => false,
            'summary' => 'give this conversation a title.',
            'description' =>
                "Names the conversation, so that a list of past sessions reads as a list of "
                . "subjects rather than of timestamps.\n\n"
                . "USE THIS ONCE, EARLY, as soon as you know what the conversation is "
                . "actually about - which is usually after the first tool result rather "
                . "than after the first message, since what somebody opens with is often "
                . "not what turns out to be wrong.\n\n"
                . "WRITE THE SUBJECT, NOT THE ACTIVITY. \\\"/var/log/exim filling at 2 GB a "
                . "day\\\" is a title. \\\"Disk investigation\\\" is what every session "
                . "would be called, and a list of those is no better than a list of dates. "
                . "Name the thing: the path, the service, the symptom.\n\n"
                . "IT WILL NOT OVERWRITE A TITLE THE USER SET. If they have named it, "
                . "yours is ignored and the reply says so - what they called it is what "
                . "they will look for.\n\n"
                . "Change it later only if the conversation genuinely turned out to be "
                . "about something else, not to refine the wording.",
            'parameters' => [
                'topic' => [
                    'type' => 'string', 'maxLength' => 80, 'required' => true,
                    'description' => 'The subject, in a few words. Specific enough to pick '
                        . 'out of a list of thirty.',
                ],
            ],
        ],

        'make_picture' => [
            'group' => 'pictures',
            'where' => 'server',
            'permission' => true,
            'summary' => 'have a picture drawn and show it.',
            'description' =>
                "Describes a picture to an image generator and shows the result in the "
                . "conversation.\n\n"
                . "This costs real money per call and takes several seconds. There is an "
                . "hourly limit, and every call needs the user's permission.\n\n"
                . "USE THIS RARELY, and only when a picture does something words cannot: "
                . "illustrating a shape, a layout, or an idea the user is struggling to "
                . "describe.\n\n"
                . "DO NOT use it to illustrate data - that is what the charts are for, and "
                . "a drawing of a chart is a drawing, not a measurement. DO NOT use it as "
                . "decoration. DO NOT use it to be funny more than once in a very long "
                . "while; the joke does not survive repetition and the user is paying for "
                . "each one.\n\n"
                . "Describe the picture in full: the generator sees only your words and "
                . "nothing of this conversation.",
            'parameters' => [
                'description' => [
                    'type' => 'string', 'maxLength' => 600, 'required' => true,
                    'description' => 'What the picture should show, described completely. '
                        . 'The generator has no other context.',
                ],
                'style' => [
                    'type' => 'string', 'enum' => [], 'required' => true,
                    'description' => 'Which generator to use. The list is what this '
                        . 'install allows.',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 160, 'required' => true,
                    'description' => 'Why a picture rather than a sentence. Shown to the '
                        . 'user when asking permission, so it has to be a reason they can '
                        . 'judge.',
                ],
            ],
        ],

        /* ---------------------------------------------------- the browser -- */

        'open_page' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'move you to a page of this dashboard.',
            'description' =>
                "Opens one of this dashboard's pages in the user's browser. The chat stays "
                . "open and the conversation continues - this does not interrupt anything, "
                . "it just changes what is behind the panel.\n\n"
                . "USE THIS when you have found something and the user should see it for "
                . "themselves, or when the page has a control they need - a button to "
                . "empty a directory, a form to add a check. Say why you are taking them "
                . "there in the same message.\n\n"
                . "DO NOT use this to gather information. It shows the user a page; it "
                . "does not tell YOU anything. The diagnostic tools are how you find "
                . "things out, and calling this instead wastes a turn and moves the user "
                . "somewhere for no reason.\n\n"
                . "DO NOT call it more than once per message. Two navigations in a row "
                . "means the user never sees the first.",
            'parameters' => [
                'page' => [
                    'type' => 'string',
                    'enum' => [],
                    'required' => true,
                    'description' => 'Which page to open. The list is fixed; anything not '
                        . 'in it is refused.',
                ],
                'hours' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 8760,
                    'required' => false,
                    'description' => 'Set the page\'s time range to this many hours, so the '
                        . 'user arrives looking at the period being discussed rather than '
                        . 'the default.',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 200, 'required' => true,
                    'description' => 'One short sentence shown to the user explaining why '
                        . 'they are being taken here. Write it for them, not for yourself: '
                        . '"so you can see which directories grew" rather than "navigating '
                        . 'to storage".',
                ],
            ],
        ],

        'request_tool_group' => [
            'group' => 'diagnostic',
            'where' => 'browser',
            'permission' => true,
            'summary' => 'ask to enable a subject-specific set of tools.',
            'description' =>
                "Asks the operator to enable one additional subject of tools, then opens "
                . "the Tools settings page where they can inspect and save that choice. "
                . "It never enables anything by itself.\n\n"
                . "USE THIS only after the diagnostic tools have shown that the next "
                . "question needs a disabled subject, such as host processes, terminal "
                . "commands, navigation, memory analysis, or pictures. Say what that "
                . "subject would let you check before calling it.\n\n"
                . "DO NOT use this as a first step, or to request more than one subject "
                . "at once. The point of the smaller initial set is to keep the operator "
                . "in control of what you can see and do.",
            'parameters' => [
                'subject' => [
                    'type' => 'string',
                    'enum' => ['host', 'memory', 'navigation', 'terminal', 'pictures'],
                    'required' => true,
                    'description' => 'The one tool subject that would answer the next question.',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 200, 'required' => true,
                    'description' => 'A concise explanation for the operator of what this subject would let you check.',
                ],
            ],
        ],

        'take_screenshot' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => true,
            'summary' => 'take a picture of what you can see.',
            'description' =>
                "Captures an image of the page the user currently has open and returns it "
                . "to you.\n\n"
                . "USE THIS SPARINGLY and only when the shape of something matters more "
                . "than its numbers: a chart the user is describing in words, a layout "
                . "problem, or a value they can see and you cannot find. Images cost far "
                . "more tokens than text - a screenshot is worth roughly a thousand words "
                . "of data you could have fetched precisely.\n\n"
                . "DO NOT use this to read data that a diagnostic tool returns. If you "
                . "want the disk figures, call get_current_status; a picture of them is "
                . "worse in every way.\n\n"
                . "DO NOT use it as a first step. Find out what you can from the data "
                . "first, and reach for this when the user's description and the data "
                . "disagree.\n\n"
                . "The user is asked before each capture and may refuse.",
            'parameters' => [
                'why' => [
                    'type' => 'string', 'maxLength' => 200, 'required' => true,
                    'description' => 'What you expect to see and why the data cannot tell '
                        . 'you. This is shown to the user when asking permission, so it '
                        . 'has to be a real reason they can judge.',
                ],
            ],
        ],

        'inspect_current_page_html' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => true,
            'summary' => 'read a reviewed copy of the current page structure.',
            'description' =>
                "Asks the user to send the outer HTML of the current page's main content. "
                . "This is usually more useful and much cheaper than a screenshot for a "
                . "layout problem: it preserves labels, structure, and accessible state. "
                . "The user sees an editable preview first and may redact any elements or "
                . "text before it leaves the browser. Use this for page/layout questions, "
                . "not to obtain diagnostic values that a normal tool can read.",
            'parameters' => [
                'why' => [
                    'type' => 'string', 'maxLength' => 200, 'required' => true,
                    'description' => 'What structural detail you need and why other tools cannot provide it.',
                ],
            ],
        ],
    ];
}

/**
 * The catalogue with the pages filled in.
 *
 * `open_page` can only name a view that exists, and the list of views lives in
 * the interface rather than here - so it is injected rather than duplicated,
 * which is the difference between one list and two that drift apart.
 */
function aiTools(array $views): array
{
    $catalogue = aiToolCatalogue();

    /*
     * The chart list, injected rather than duplicated.
     *
     * Same reasoning as the page list below: one definition, in
     * lib/aicharts.php, and three tools that read it - otherwise a chart added
     * there is a chart the model cannot name.
     */
    $charts = aiChartCatalogue();
    $chartNames = implode("\n", array_map(
        fn(string $id, array $chart): string => sprintf('  %s - "%s", on the %s page. %s',
            $id, $chart['name'], $chart['view'], $chart['what']),
        array_keys($charts),
        array_values($charts)
    ));

    foreach (['get_chart_data', 'show_chart', 'get_chart_image'] as $name) {
        $catalogue[$name]['parameters']['chart']['enum'] = array_keys($charts);
        $catalogue[$name]['parameters']['chart']['description'] .=
            "\n\nThe charts:\n" . $chartNames;
    }

    $catalogue['open_page']['parameters']['page']['enum'] = array_keys($views);
    $catalogue['open_page']['parameters']['page']['description'] .=
        "\n\nThe pages, and what each is for:\n"
        . implode("\n", array_map(
            fn(string $key, string $what): string => sprintf('  %s - %s', $key, $what),
            array_keys($views),
            array_values($views)
        ));

    return $catalogue;
}

/**
 * The tools actually offered, after the operator's settings.
 *
 * A tool switched off is not described to the model at all, rather than being
 * described and then refused. A model told about a tool it cannot use will keep
 * trying it, explain its absence to the user as a fault, and waste turns.
 *
 * @return array<string, array<string, mixed>>
 */
function aiToolDefaultEnabled(array $tool): bool
{
    /*
     * Start with the small, read-only set that answers "what is happening?".
     *
     * The operator can add a subject on the Tools page when the conversation
     * needs it. A model presented with every possible capability at once tends
     * to reach sideways before it has established the basic facts, while a
     * smaller first set makes the next permission decision meaningful.
     */
    return $tool['where'] !== 'ask' && $tool['group'] === 'diagnostic';
}

/**
 * Whether the active dashboard identity may use this tool category.
 *
 * The catalogue is also used by unattended alert triage, where no dashboard
 * identity exists.  `access.php` is intentionally optional there; an API
 * request always has it loaded and is therefore subject to the group policy.
 */
function aiToolAllowedForCurrentIdentity(array $config, array $tool): bool
{
    return !function_exists('accessAllows')
        || accessAllows($config, 'tools', (string)$tool['group']);
}

/** Chart grants may be narrower than the permission to open the containing page. */
function aiChartAllowedForCurrentIdentity(array $config, string $chartId): bool
{
    return !function_exists('accessAllows') || accessAllows($config, 'charts', $chartId);
}

/** @return list<string> */
function aiAllowedChartIds(array $config): array
{
    return array_values(array_filter(
        array_keys(aiChartCatalogue()),
        fn(string $id): bool => aiChartAllowedForCurrentIdentity($config, $id)
    ));
}

function aiEnabledTools(array $config, array $views, bool $respectAccess = true): array
{
    $settings = $config['ai']['tools'] ?? [];
    $pictures = aiImageSettings($config);
    $out = [];

    foreach (aiTools($views) as $name => $tool) {
        if ($respectAccess && !aiToolAllowedForCurrentIdentity($config, $tool)) {
            continue;
        }

        if (in_array($name, ['get_chart_data', 'show_chart', 'get_chart_image'], true)) {
            $charts = $respectAccess ? aiAllowedChartIds($config) : array_keys(aiChartCatalogue());
            if ($charts === []) {
                continue;
            }
            $tool['parameters']['chart']['enum'] = $charts;
        }

        /*
         * The picture tools describe themselves from this install's settings.
         *
         * A model offered a style the operator did not enable will use it,
         * have the call refused, and explain the refusal to the user as a
         * fault - so the enum is exactly what is allowed, and the whole tool
         * disappears when nothing is.
         */
        if ($tool['group'] === 'pictures') {
            if (!$pictures['enabled'] || $pictures['api_key'] === '') {
                continue;
            }
            if ($name === 'redraw_yourself' && !$pictures['self_portrait']) {
                continue;
            }
            if (isset($tool['parameters']['style'])) {
                /*
                 * The enum is exactly what this install allows, with the
                 * provider's own name for each.
                 *
                 * There are over a hundred styles and the operator ticks the
                 * few they want; a model offered one that is not ticked will
                 * use it, have the call refused, and explain the refusal to the
                 * user as a fault.
                 */
                $styles = aiImageStyles();
                $tool['parameters']['style']['enum'] = $pictures['styles'];
                $tool['parameters']['style']['description'] .= "\n\n"
                    . implode("\n", array_map(
                        fn(string $key): string => sprintf(
                            '  %s - %s',
                            $key,
                            $styles[$key] ?? $key
                        ),
                        $pictures['styles']
                    ));
            }
        }

        if ($name === 'show_reaction' && !$pictures['reactions']) {
            continue;
        }

        $mine = $settings[$name] ?? [];

        // Enabled unless explicitly turned off, except for anything that acts
        // rather than reads, which is off unless explicitly turned on.
        $on = array_key_exists('enabled', $mine)
            ? (bool)$mine['enabled']
            : aiToolDefaultEnabled($tool);

        if (!$on) {
            continue;
        }

        $tool['permission'] = array_key_exists('permission', $mine)
            ? (bool)$mine['permission']
            : (bool)$tool['permission'];

        // Settled here as well as on the page, because a stored setting from
        // before this existed would otherwise still be in force.
        if (in_array($name, aiToolsThatAsk(), true)) {
            $tool['permission'] = false;
        }

        $out[$name] = $tool;
    }

    return $out;
}

/**
 * The tools whose entire purpose is to put a question to the user.
 *
 * They read nothing, change nothing and send nothing anywhere; they draw a
 * question and wait for an answer. Asking permission before one of those is
 * asking a question about a question, and turning it on would produce two
 * prompts in a row for a single interaction - so the setting does not exist for
 * them rather than existing and being a bad idea.
 *
 * A list rather than a flag on each definition, because it is a statement about
 * this handful of tools and not a category anything else will join: any tool
 * that acquires a reason to ask first has a reason worth asking about.
 *
 * @return list<string>
 */
function aiToolsThatAsk(): array
{
    return ['offer_choices', 'ask_the_user'];
}

/**
 * The tool list in the shape a chat completions API expects.
 *
 * @return list<array<string, mixed>>
 */
function aiToolSchemas(array $tools): array
{
    $out = [];

    foreach ($tools as $name => $tool) {
        $properties = [];
        $required = [];

        foreach ($tool['parameters'] as $key => $spec) {
            $optional = empty($spec['required']);

            /*
             * Every parameter is listed as required, optional ones as nullable.
             *
             * Strict function schemas insist on it: "'required' is required to
             * be supplied and to be an array including every key in
             * properties". Leaving a genuinely optional parameter out of the
             * list is rejected outright, so the option is expressed the way the
             * format wants it - the key is always present and its value may be
             * null, which aiCheckArguments already treats as "not given" and
             * fills from the default.
             *
             * Valid on the older endpoint too, where it means the same thing
             * and costs a handful of tokens.
             */
            $property = [
                'type' => $optional ? [$spec['type'], 'null'] : $spec['type'],
                'description' => $spec['description']
                    . ($optional
                        ? sprintf(
                            ' Pass null to leave this at its default (%s).',
                            array_key_exists('default', $spec)
                                ? var_export($spec['default'], true)
                                : 'unset'
                        )
                        : ''),
            ];

            foreach (['enum', 'minimum', 'maximum', 'maxLength'] as $extra) {
                if (isset($spec[$extra]) && $spec[$extra] !== []) {
                    $property[$extra] = $spec[$extra];
                }
            }

            /*
             * A nullable enum has to allow null in the enum as well.
             *
             * Otherwise the two constraints contradict each other - the type
             * permits null and the enum forbids it - and a strict validator
             * refuses the schema rather than the value.
             */
            if ($optional && isset($property['enum'])) {
                $property['enum'] = array_merge($property['enum'], [null]);
            }

            $properties[$key] = $property;
            $required[] = $key;
        }

        $out[] = [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => $tool['description'],
                'parameters' => [
                    'type' => 'object',
                    'properties' => (object)$properties,
                    'required' => $required,
                    /*
                     * No extra keys, ever.
                     *
                     * A model that invents a parameter is a model that has
                     * misunderstood the tool, and accepting the call anyway
                     * hides that until the answer turns out to be wrong.
                     */
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    return $out;
}

/**
 * Checks and coerces what the model passed, against the tool's own schema.
 *
 * Every value that reaches a query goes through here first. Types are coerced
 * where that is unambiguous - a model returning "24" for an integer is
 * extremely common and refusing it would be pedantry - and refused where it is
 * not.
 *
 * @return array{ok: bool, values: array<string, mixed>, why: string}
 */
function aiCheckArguments(array $tool, array $given): array
{
    $values = [];

    foreach ($given as $key => $value) {
        if (!isset($tool['parameters'][$key])) {
            return ['ok' => false, 'values' => [], 'why' => sprintf(
                'There is no parameter called "%s" on this tool. The parameters are: %s.',
                $key,
                implode(', ', array_keys($tool['parameters'])) ?: 'none'
            )];
        }
    }

    foreach ($tool['parameters'] as $key => $spec) {
        $missing = !array_key_exists($key, $given)
            || $given[$key] === null
            || $given[$key] === '';

        if ($missing) {
            if (!empty($spec['required'])) {
                return ['ok' => false, 'values' => [], 'why' => sprintf(
                    'The parameter "%s" is required and was not given.', $key
                )];
            }
            if (array_key_exists('default', $spec)) {
                $values[$key] = $spec['default'];
            }
            continue;
        }

        $value = $given[$key];

        if ($spec['type'] === 'integer') {
            if (!is_numeric($value) || (int)$value != $value) {
                return ['ok' => false, 'values' => [], 'why' => sprintf(
                    'The parameter "%s" must be a whole number; "%s" is not.',
                    $key,
                    is_scalar($value) ? (string)$value : gettype($value)
                )];
            }
            $number = (int)$value;

            /*
             * Clamped rather than refused.
             *
             * A model asking for 100000 rows has misjudged the scale, not made
             * an error worth a round trip - and the reply says what it was
             * given, so the misjudgement is visible rather than silent.
             */
            if (isset($spec['minimum'])) {
                $number = max((int)$spec['minimum'], $number);
            }
            if (isset($spec['maximum'])) {
                $number = min((int)$spec['maximum'], $number);
            }
            $values[$key] = $number;
            continue;
        }

        if (!is_string($value)) {
            return ['ok' => false, 'values' => [], 'why' => sprintf(
                'The parameter "%s" must be a string.', $key
            )];
        }

        if (!empty($spec['enum']) && !in_array($value, $spec['enum'], true)) {
            return ['ok' => false, 'values' => [], 'why' => sprintf(
                'The parameter "%s" must be one of: %s. "%s" is not.',
                $key,
                implode(', ', $spec['enum']),
                mb_substr($value, 0, 60)
            )];
        }

        if (isset($spec['maxLength']) && mb_strlen($value) > (int)$spec['maxLength']) {
            $value = mb_substr($value, 0, (int)$spec['maxLength']);
        }

        $values[$key] = $value;
    }

    return ['ok' => true, 'values' => $values, 'why' => ''];
}

/**
 * The pages the assistant may send somebody to, and what each is for.
 *
 * Written here rather than taken from the interface's own labels, because a
 * navigation label - "Over time" - is written for somebody who already knows
 * where they are, and a model choosing between fifty of them needs a sentence.
 *
 * @return array<string, string>
 */
function aiViews(): array
{
    return [
        'overview' => 'gauges for disk, inodes, CPU, memory and load, plus what the '
            . 'dashboard currently thinks is wrong',
        'charts' => 'those same five measurements plotted over the selected period',
        'services' => 'the services discovered on the host, their state, memory use, and recent changes',
        'storage' => 'disk use by category, the last scan, and a Scan now button',
        'storage-files' => 'the largest files and directories, and what has grown',
        'storage-links' => 'symlinks that are broken, looping, or leave the account',
        'storage-io' => 'how hard the disks are working',
        /*
         * Three pages that have charts on them and were not on this list, so
         * open_page could not reach them - which meant the assistant could tell
         * somebody a treemap existed and not send them to it.
         */
        'storage-map' => 'every directory drawn as a rectangle in proportion to its '
            . 'size, which is the fastest way to see where space went when nobody '
            . 'knows where to start',
        'mem-services' => 'which services are holding memory, over time',
        'mem-goes' => 'where the machine\'s memory goes, broken down',
        'log-stream' => 'the newest lines from every log at once, and repeated messages',
        'host-disk' => 'every filesystem on the machine and how full each is',
        'cpu' => 'processor use for the account and for the machine, with a ranked list '
            . 'of what is actually true about it right now and a link to the page behind '
            . 'each finding - the right place to send somebody who only knows the machine '
            . 'is slow',
        /*
         * Five pages that exist because "the CPU is at 90%" is the same reading
         * for four unrelated problems. An assistant that knows only the parent
         * page can say the processor is busy and nothing more useful.
         */
        'cpu-time' => 'the eight states processor time is charged to - userland, kernel, '
            . 'iowait, steal, interrupts - which is what tells work being done apart from '
            . 'a disk being waited on apart from a hypervisor taking the cores away',
        'cpu-cores' => 'how busy each core is separately, which is the only way to see a '
            . 'single-threaded bottleneck: one core pinned beside seven idle ones is the '
            . 'same average as an evenly busy machine and a completely different problem',
        'cpu-procs' => 'which processes are using the processor, both over the last minute '
            . 'and averaged over their whole lives - the two figures top and ps report, '
            . 'side by side',
        'cpu-queue' => 'run queue depth, blocked tasks, CPU pressure, context switches and '
            . 'fork rate: why a load average of 40 can sit on a machine that is 5% busy',
        'cpu-limits' => 'the account\'s allowance against the machine\'s size, and the '
            . 'CloudLinux fault counts that say whether it is being throttled right now '
            . 'rather than merely sitting near its ceiling',
        'host' => 'memory: what is using it and where it goes',
        /*
         * The stopping is the part worth saying.
         *
         * "What is running" reads as a read-only list, so an assistant that
         * decides something should be stopped writes out kill commands for
         * somebody to paste into a terminal - which is slower, riskier and
         * asks the user to do work the page already does with a button and a
         * confirmation. It happened with awstats.pl, and the description is
         * why: it never mentioned that this page can act.
         */
        'processes' => 'what is running and what it is using, WITH A BUTTON BESIDE EACH '
            . 'ONE TO STOP IT - either asking it to exit (SIGTERM) or forcing it '
            . '(SIGKILL), one process or a whole group of the same name, with a '
            . 'confirmation and a check that it actually went. This is the right place '
            . 'to send somebody who needs to stop something; do not write out kill '
            . 'commands for a terminal instead',
        'units' => 'system services and units, their recent state, and the process or service behind each one',
        'busy' => 'the busiest processes and services right now, sorted by the resource they are consuming',
        'network' => 'listening ports, connections, and packet captures',
        'net-urls' => 'checks that fetch addresses from outside and record the answer',
        'net-rdns' => 'names for addresses seen in captures and logs',
        'domains' => 'domain names being watched, and when each expires',
        'uptime' => 'when the machine was up and when each collector was reporting',
        'alerts' => 'every alert raised, with the evidence captured at the time',
        'capabilities' => 'which programs are installed and usable from this account',
        'cagefs' => 'what the account can see through CageFS and the boundaries that affect monitoring',
        'sys-changed' => 'software that changed underneath - kernel, PHP, OpenSSL',
        'sys-account' => 'what the dashboard is storing and what it will grow to',
        'system' => 'the dashboard’s own system status, configuration health, and maintenance information',
        'sys-retention' => 'how long each kind of record is kept',
        'sys-problems' => "the dashboard's own error log",
        'sys-notes' => 'notes people have written against pages and charts',
        'deletions' => 'the audited record of files and directories removed through the dashboard',
        'schedule' => 'when the collectors run and what they collect',
        'email' => 'alert email settings, severities and thresholds',
        'whoami' => 'the current account identity, server name, and environment facts used by the dashboard',
        'whois' => 'domain registration and expiry lookup results',
        'backups' => 'backups found on the machine and how old each is',
        'calendar' => 'renewals, expiries and events that are coming up',
        'backups-actions' => 'safe next actions for backup storage, with warnings before any destructive work',
        'backups-other' => 'probable non-JetBackup archives discovered on the machine',
        'backups-jetbackup' => 'JetBackup metadata, destinations, dates and reported sizes',
        'disk-deleted' => 'deleted files that are still held open by a process',
        'disk-dirs' => 'the largest directories on the host filesystem',
        'disk-procs' => 'which processes currently have files open on disk',
        'disk-rootfiles' => 'files visible only to the host collector',
        'host-layout' => 'mount points and filesystem layout on the host',
        'mem-users' => 'memory grouped by account user',
        'mem-procs' => 'the processes using the most memory',
        'mem-slab' => 'kernel slab caches and their memory use',
        'net-ports' => 'listening network ports and the processes behind them',
        'net-peers' => 'remote addresses currently connected to the host',
        'net-capture' => 'a safe, bounded packet capture request',
        'net-captures' => 'the saved results of previous packet captures',
        'net-dns' => 'interactive DNS lookups and their answers',
        'net-fetch' => 'an explicit, bounded HTTP request made from this host',
        'net-labels' => 'operator-defined names for addresses and services',
        'dns-records' => 'published DNS records and changes over time',
        'sys-descriptions' => 'operator-maintained explanations for local processes and caches',
        'sys-host-data' => 'what the host collector stores and retains',
        'sys-memory' => 'facts the assistant has retained for later conversations',
        'sys-reports' => 'the assistant’s scheduled written summaries',
        'sys-terminal' => 'a guarded terminal session running as the dashboard account',
        'sys-ai-model' => 'assistant provider and model configuration',
        'sys-ai-prices' => 'model price and budget settings',
        'sys-ai-limits' => 'assistant time, token and threshold limits',
        'sys-ai-images' => 'image-generation and reaction settings',
        'sys-ai-tools' => 'which assistant tools are enabled and need confirmation',
        'sys-ai-try' => 'a form for testing each available assistant tool',
        'sys-ai-billing' => 'assistant usage and cost history',
        'sys-ai-data' => 'data and retained material owned by the assistant',
        'sys-peers' => 'trusted Zogograph servers and their OpenID discovery health',
        'sys-ai' => 'the current assistant conversation',
        'sys-ai-identity' => 'the assistant’s displayed identity and personality',
        'sys-ai-past' => 'past assistant conversations',
        'sys-installed' => 'the current inventory of installed host software',
        'sys-sessions' => 'guarded terminal sessions and their state',
    ];
}
