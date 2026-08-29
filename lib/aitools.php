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

        /* ------------------------------------------------- what is wrong -- */



        'find_largest_paths' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'The largest and fastest-growing files and directories.',
            'description' =>
                "Returns the biggest paths under the watched directory, or the ones that "
                . "have grown most since the previous scan, with their sizes and rates.\n\n"
                . "USE THIS ONLY FOR DISK SPACE PROBLEMS - a disk filling up, a disk "
                . "already full, or a question about what is using the space. Sort by "
                . "'growth' rather than 'size' when the question is 'what changed', "
                . "because the largest directory on a server is usually the largest "
                . "directory on that server every day and is not news.\n\n"
                . "DO NOT use this for inode exhaustion. Running out of inodes is about "
                . "the NUMBER of files, not their size, and the directory responsible is "
                . "often tiny - use find_crowded_directories.\n\n"
                . "DO NOT use this for CPU, memory or network problems. It reads a stored "
                . "scan and has nothing to say about any of them.\n\n"
                . "The figures come from the last completed scan, which may be hours old. "
                . "The reply says when it ran; say so to the user rather than presenting "
                . "stale sizes as current.",
            'parameters' => [
                'sort_by' => [
                    'type' => 'string', 'enum' => ['size', 'growth'],
                    'required' => true,
                    'description' => '"size" for the biggest, "growth" for what has grown '
                        . 'most since the previous scan. Use "growth" for "what changed" '
                        . 'and "size" for "what is using all the space".',
                ],
                'category' => [
                    'type' => 'string',
                    'enum' => ['any', 'logs', 'backups', 'cache', 'temp', 'trash',
                               'media', 'code', 'other'],
                    'required' => false, 'default' => 'any',
                    'description' => 'Narrow to one kind of file. Use "logs" when a log is '
                        . 'suspected, "backups" when a backup job is suspected.',
                ],
                'limit' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 40,
                    'required' => false, 'default' => 15,
                    'description' => 'How many paths to return.',
                ],
            ],
        ],

        /* ------------------------------------------------------- the charts -- */

        'get_chart_data' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'The numbers behind one named chart.',
            'description' =>
                "Returns the measurements a named chart is drawn from, bucketed over a "
                . "period, with the lowest, highest and average of each bucket.\n\n"
                . "PREFER THIS OVER EVERY OTHER WAY OF LOOKING AT A CHART. A chart is a "
                . "rendering of data you can have directly; asking for a picture of it "
                . "instead is asking for a worse copy at roughly fifty times the cost in "
                . "tokens, and you cannot read exact values off an image.\n\n"
                . "USE THIS when the user names a chart, or describes something a chart "
                . "shows, or when you want a measurement over time rather than right now.\n\n"
                . "Each bucket carries its highest value as well as its average, because "
                . "the average of an hour containing a two-minute spike hides the spike - "
                . "and the spike is usually the thing being looked for.",
            'parameters' => [
                'chart' => [
                    'type' => 'string',
                    'enum' => [],
                    'required' => true,
                    'description' => 'Which chart. The user may name one; every chart on '
                        . 'this dashboard shows its own id when hovered, so they can tell '
                        . 'you exactly which one they mean.',
                ],
                'hours' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 8760,
                    'required' => true,
                    'description' => 'How far back. 24 for today, 168 for a week.',
                ],
            ],
        ],

        'show_chart' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'draw a chart inside this conversation.',
            'description' =>
                "Draws a named chart in the chat itself, where the user can see it without "
                . "leaving the page they are on.\n\n"
                . "USE THIS whenever you are talking about something a chart shows and it "
                . "would help the user to see it. It is far better than open_page for this: "
                . "it does not move them away from whatever they were doing, and the chart "
                . "appears right next to the sentence about it.\n\n"
                . "This shows the user a picture. It does NOT show you one - you get "
                . "nothing back but a confirmation. If you need the numbers, call "
                . "get_chart_data; if you genuinely need to see the rendering, call "
                . "get_chart_image.\n\n"
                . "Say what you want them to notice in the same message. A chart with no "
                . "sentence attached is a shrug.",
            'parameters' => [
                'chart' => [
                    'type' => 'string', 'enum' => [], 'required' => true,
                    'description' => 'Which chart to draw.',
                ],
                'hours' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 8760,
                    'required' => true,
                    'description' => 'The period it should cover.',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 200, 'required' => true,
                    'description' => 'One short line shown as the chart\'s caption, saying '
                        . 'what to look at. Written for the reader: "the step at 09:20 is '
                        . 'where it jumped" rather than "displaying disk usage".',
                ],
            ],
        ],

        'present_visual' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'present supplied data as a line chart, pie, or gauge.',
            'description' =>
                "Presents data you already have in the conversation as a small interactive "
                . "visual. Unlike show_chart, this is not limited to a chart built into "
                . "Zogograph. Use it to make a trend, composition, or current-versus-limit "
                . "clear. Do not invent measurements: the data must come from the user or "
                . "a tool result. The browser shows it beside the conversation; you only "
                . "receive confirmation that it was shown.\n\n"
                . "data is JSON. For line use {series:[{key,label}],points:[{at,key:value}]}; "
                . "for pie use {slices:[{label,value}]}; for gauge use {value,max,label}.",
            'parameters' => [
                'kind' => [
                    'type' => 'string', 'enum' => ['line', 'pie', 'gauge'], 'required' => true,
                    'description' => 'The visual type that best fits the supplied data.',
                ],
                'title' => [
                    'type' => 'string', 'maxLength' => 100, 'required' => true,
                    'description' => 'A concise title for the reader.',
                ],
                'data' => [
                    'type' => 'string', 'maxLength' => 12000, 'required' => true,
                    'description' => 'Valid compact JSON in the shape described above.',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 200, 'required' => true,
                    'description' => 'One sentence telling the reader what to notice.',
                ],
            ],
        ],

        'get_chart_image' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'get a picture of one chart.',
            'description' =>
                "Renders one named chart and returns the image to YOU.\n\n"
                . "THIS IS A LAST RESORT. Call get_chart_data first, always. The data is "
                . "exact, cheap, and answers almost every question a chart can. An image "
                . "costs roughly fifty times as many tokens and you cannot read a value "
                . "off it.\n\n"
                . "USE THIS ONLY when the user is describing something about a chart's "
                . "SHAPE that the numbers do not settle: a pattern they can see and you "
                . "cannot infer, something they say looks wrong, or a disagreement between "
                . "what they describe and what the data says. In that last case the point "
                . "is to find out whether you are both looking at the same thing.\n\n"
                . "STILL BETTER THAN take_screenshot. This gives you one chart at a known "
                . "size with nothing else in the frame; a screenshot gives you the whole "
                . "window, costs more, and may contain things the user would rather not "
                . "send. Only fall back to a screenshot when what matters is not a chart "
                . "at all.",
            'parameters' => [
                'chart' => [
                    'type' => 'string', 'enum' => [], 'required' => true,
                    'description' => 'Which chart to render.',
                ],
                'hours' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 8760,
                    'required' => true,
                    'description' => 'The period it should cover.',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 200, 'required' => true,
                    'description' => 'What you expect to see and why the numbers from '
                        . 'get_chart_data did not settle it. If you cannot answer that, '
                        . 'call get_chart_data instead.',
                ],
            ],
        ],

        /* --------------------------------------------------- saying things -- */

        'get_symlink_problems' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'symlinks that are broken, looping or leave the account.',
            'description' =>
                "The result of the last symlink walk: links that point at nothing, links "
                . "that form a loop, links into their own ancestors, and links that leave "
                . "the watched directory.\n\n"
                . "USE THIS when a backup takes forever or never finishes, when `du` and "
                . "the dashboard disagree about a directory's size, or when something "
                . "reports an impossible amount of data. A link pointing at one of its "
                . "own ancestors is an infinitely deep directory to anything that follows "
                . "links - and backups, du, tar and web crawlers all do - which is how a "
                . "disk fills with one file copied ten thousand times.\n\n"
                . "This reads the STORED result of the last walk rather than walking the "
                . "tree, which takes tens of seconds. If nothing has been checked it says "
                . "so; the operator can run one from the Symlinks page.",
            'parameters' => [
                'kind' => [
                    'type' => 'string', 'required' => false,
                    'enum' => ['any', 'broken', 'loop', 'ancestor', 'outside'],
                    'default' => 'any',
                    'description' => 'Narrow it to one kind. "loop" and "ancestor" are '
                        . 'the two that cause runaway descent; "broken" is untidy rather '
                        . 'than dangerous; "outside" makes measured sizes misleading.',
                ],
                'limit' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 100,
                    'required' => false, 'default' => 25,
                    'description' => 'How many to return. The counts are always for all '
                        . 'of them, however few are listed.',
                ],
            ],
        ],

        'get_backups' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'what backups exist and how old each one is.',
            'description' =>
                "Backups found on this machine, with when each was made and how large.\n\n"
                . "USE THIS before suggesting anything destructive, and whenever somebody "
                . "asks whether it is safe to delete something. \"There is a backup from "
                . "four hours ago\" and \"the newest backup is from March\" lead to "
                . "completely different advice, and you cannot tell which without asking.\n\n"
                . "ALSO USE IT when the disk is filling: backups are frequently most of "
                . "what is on a disk, and an old one nobody meant to keep is the easiest "
                . "space there is to recover.\n\n"
                . "Absence of backups here is not proof there are none - this finds the "
                . "ones in the usual places. Say that rather than telling somebody they "
                . "have no backups.",
            'parameters' => [],
        ],

        'get_domain_expiry' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'watched domain names and when each expires.',
            'description' =>
                "Every domain being watched, when it expires, and who it is registered "
                . "with.\n\n"
                . "USE THIS when a site has stopped answering and nothing on the machine "
                . "explains it. An expired domain looks exactly like a network fault from "
                . "inside the machine, and it is the one cause that no amount of looking "
                . "at the server will find.\n\n"
                . "The dates come from whois, which the host collector runs, so they are "
                . "as fresh as the last lookup. Each row says when it was checked.",
            'parameters' => [],
        ],

        'get_services' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'systemd units, with the broken ones first.',
            'description' =>
                "Every service the machine knows about, worst state first: failed, "
                . "masked, running-when-it-should-not-be, and meant-to-be-running-and-is-"
                . "not.\n\n"
                . "USE THIS when something is not answering. A FAILED unit is invisible "
                . "everywhere else on this dashboard - it uses no CPU and no memory, so "
                . "it does not appear in any list of what is busy - and it is one of the "
                . "few faults where the cause and the fix are both a single line.\n\n"
                . "A MASKED unit cannot be started at all until somebody unmasks it, "
                . "which is worth saying out loud because \"start it\" will fail with a "
                . "message people find confusing.\n\n"
                . "This needs the host collector. Without one there is no service list "
                . "and it says so.",
            'parameters' => [
                'state' => [
                    'type' => 'string', 'required' => false,
                    'enum' => ['problems', 'all', 'failed', 'masked', 'running',
                               'stopped'],
                    'default' => 'problems',
                    'description' => '"problems" is failed, masked, and anything running '
                        . 'when it should not be or not running when it should. That is '
                        . 'chosen by state and never by name - no service here is more '
                        . 'important than another, only more broken.',
                ],
                'limit' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 100,
                    'required' => false, 'default' => 40,
                    'description' => 'How many to return.',
                ],
            ],
        ],

        'get_uptime' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'when the machine restarted and when each collector was running.',
            'description' =>
                "Two records over a period: when the machine was up, and when each "
                . "collector was actually writing readings.\n\n"
                . "USE THIS whenever a chart has a gap in it, and whenever a cluster of "
                . "alerts arrives together. A gap where the collector stopped and a gap "
                . "where the machine was off look identical on every chart here and mean "
                . "opposite things - one is missing data about a working machine, the "
                . "other is a machine that was not there.\n\n"
                . "A run of warnings just after a restart is usually a machine coming "
                . "back rather than a machine in trouble. The same run with no restart "
                . "under it is something else entirely.",
            'parameters' => [
                'hours' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 2160,
                    'required' => false, 'default' => 168,
                    'description' => 'How far back to look.',
                ],
            ],
        ],

        'get_notes' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'what people have written down against pages and charts.',
            'description' =>
                "Notes the operator or their colleagues have written, with when each one "
                . "is ABOUT - which is not always when it was written.\n\n"
                . "USE THIS when a chart bends and you are about to explain why. Somebody "
                . "may already have: \"moved the backup window\", \"upgraded PHP\", "
                . "\"cleared the cache\" are the explanations for most sudden steps in "
                . "these charts, and a note saying so is worth more than any amount of "
                . "inference from the readings.\n\n"
                . "These are people's own words about their own machine. Treat them as "
                . "the most reliable thing you have, and quote them rather than "
                . "paraphrasing.",
            'parameters' => [
                'hours' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 8760,
                    'required' => false, 'default' => 720,
                    'description' => 'How far back to look, by what the note is about.',
                ],
                'limit' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 50,
                    'required' => false, 'default' => 20,
                    'description' => 'How many to return.',
                ],
            ],
        ],

        'what_the_terminal_can_run' => [
            'group' => 'terminal',
            'where' => 'server',
            'permission' => false,
            'summary' => 'list the commands the operator could actually run.',
            'description' =>
                "Lists which common diagnostic programs exist on this machine and are on "
                . "the PATH the terminal uses, so a command you suggest can actually run.\n\n"
                . "USE THIS BEFORE `recommend_command`, the first time in a conversation "
                . "that you are about to suggest something. Shared hosts are missing "
                . "things you would assume are always present - `ss`, `lsof`, `dig` and "
                . "`strace` are all commonly absent, and CageFS hides most of /usr/sbin "
                . "from the account entirely. Suggesting a command that is not there wastes "
                . "somebody's time and makes the next suggestion less trusted.\n\n"
                . "It also says whether the terminal is switched on at all. If it is not, "
                . "do not suggest commands - say that the terminal is off and where to turn "
                . "it on.\n\n"
                . "This checks for the programs on a fixed list. A program not on the list "
                . "is not thereby absent; it was simply not looked for.",
            'parameters' => [],
        ],

        'what_the_host_can_be_asked' => [
            'group' => 'host',
            'where' => 'server',
            'permission' => false,
            'summary' => 'list what root\'s collector can be asked to do.',
            'description' =>
                "Lists the jobs this dashboard can ask the host's root collector to run on "
                . "the account's behalf, and what each one answers.\n\n"
                . "This is the route to everything the account cannot see for itself: the "
                . "whole machine's disk use, other accounts, /var and /backup, the process "
                . "table as root sees it, what programs exist outside CageFS.\n\n"
                . "IT IS NOT IMMEDIATE, and this is the part to get right when you talk "
                . "about it. A request is left in a table; root's cron picks it up on its "
                . "next run and does the work then. The answer comes back minutes later. "
                . "The tool tells you how often that cron runs and when it last ran, so "
                . "you can say how long it will be rather than guessing - and you can draw "
                . "a progress bar with `show_progress` if the wait is worth showing.\n\n"
                . "You cannot make the request. Each job names the page where the operator "
                . "presses the button; send them there.",
            'parameters' => [],
        ],

        'get_pending_host_requests' => [
            'group' => 'host',
            'where' => 'server',
            'permission' => false,
            'summary' => 'see what the host has been asked for, and whether it ran.',
            'description' =>
                "Everything asked of root's collector: what is still waiting, what has "
                . "already been answered, when the collector last ran, and when the next "
                . "run is due.\n\n"
                . "USE THIS BEFORE request_from_host, so you do not queue a job that is "
                . "already waiting or ask again for something answered five minutes ago. "
                . "USE IT AFTER, to find out whether the answer has landed.\n\n"
                . "READ `last_run` BEFORE READING THE QUEUE. Pending jobs mean one thing "
                . "when the collector ran two minutes ago and something completely "
                . "different when it says \"never\": in that case nothing in the queue is "
                . "ever going to happen, because the job was never installed in root's "
                . "crontab, and no amount of waiting will change it. Say so rather than "
                . "telling somebody to wait for a run that is not coming.\n\n"
                . "`collector_has_run_since` on a pending job is the other thing worth "
                . "reading. True means the collector has been past without answering it, "
                . "which usually means it cannot do what was asked rather than that it has "
                . "not got to it yet.\n\n"
                . "The wait is an estimate from the collector's recent behaviour, not a "
                . "promise.",
            'parameters' => [],
        ],

        'get_collection_schedule' => [
            'group' => 'host',
            'where' => 'server',
            'permission' => false,
            'summary' => 'see how often each collector runs and when it last did.',
            'description' =>
                "How often the account's cron and the host's cron actually run, when each "
                . "last ran, and when each is next due.\n\n"
                . "USE THIS whenever a wait is involved: before telling somebody to check "
                . "back, before asking them to wait for a request, and whenever a reading "
                . "looks stale. It is the difference between \"in about four minutes\" and "
                . "\"shortly\".\n\n"
                . "ALSO USE IT when something looks impossibly fine. A collector that has "
                . "stopped produces charts that go flat and quiet, which looks exactly like "
                . "a healthy machine - and this is the tool that tells the two apart.\n\n"
                . "The intervals are measured from the gaps between actual runs, not read "
                . "from a crontab, so they are what is really happening rather than what "
                . "was configured.",
            'parameters' => [],
        ],

        'set_time_range' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'change the range the dashboard is showing.',
            'description' =>
                "Changes the time range every chart on the dashboard is drawn over, and "
                . "optionally turns on live sampling.\n\n"
                . "USE THIS when the detail you need is finer than the range being shown. "
                . "At a day's range a spike lasting ninety seconds is one pixel and may be "
                . "averaged away entirely; at ten minutes it is the whole chart.\n\n"
                . "LIVE takes a fresh reading every few seconds rather than waiting for the "
                . "collector's next run. It is the only way to watch something as it "
                . "happens, and it only works at the shortest range. Turn it on when "
                . "somebody is watching a problem occur; leave it off otherwise, because it "
                . "writes a reading every few seconds for as long as it is on.\n\n"
                . "This changes what the OPERATOR sees. Say why you are doing it.",
            'parameters' => [
                'range' => [
                    'type' => 'string', 'required' => true,
                    'enum' => ['10m', '1h', '6h', '24h', '7d', '30d'],
                    'description' => 'How far back to show. 10m is the finest and is the '
                        . 'only one that can be live.',
                ],
                'live' => [
                    'type' => 'boolean', 'required' => false, 'default' => false,
                    'description' => 'Take readings continuously while they watch. Only '
                        . 'has an effect at the 10m range.',
                ],
                'why' => [
                    'type' => 'string', 'required' => true, 'maxLength' => 200,
                    'description' => 'What you expect to become visible at this range, in '
                        . 'one sentence. Shown to them.',
                ],
            ],
        ],

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

        'recommend_command' => [
            'group' => 'terminal',
            'where' => 'server',
            'permission' => false,
            'summary' => 'put a command in the terminal for the operator to run.',
            'description' =>
                "Writes a shell command onto the operator's terminal page, where they can "
                . "read it and choose to run it. YOU DO NOT RUN ANYTHING. There is no tool "
                . "here that executes a command, and there will not be one - the person "
                . "reading it decides.\n\n"
                . "USE THIS when the answer is somewhere your tools cannot reach: the "
                . "process table as root sees it, a log this account cannot open, the "
                . "output of a host utility, the state of something after a change.\n\n"
                . "DO NOT use it for anything a tool here already answers. If there is a "
                . "tool for it, use the tool - it is faster, it is already permitted, and "
                . "asking a person to type something you could have looked up yourself is "
                . "how an assistant becomes a chore.\n\n"
                . "REQUIREMENTS, all of them, every time:\n"
                . "  - the command must be safe to run and must not change anything unless "
                . "the operator has asked for exactly that change. Prefer reading over "
                . "writing. Never suggest something destructive as a diagnostic.\n"
                . "  - `reading` must explain what the output will look like and how to tell "
                . "a good result from a bad one BEFORE they run it. Somebody staring at "
                . "unfamiliar output with no idea what they are looking for is worse off "
                . "than before they ran it.\n"
                . "  - the command runs as the web account, not as root, unless they have "
                . "told you otherwise. Say so if it needs privileges it will not have.\n\n"
                . "After it runs they can send you the output with one button. Wait for "
                . "that rather than guessing what it said.",
            'parameters' => [
                'command' => [
                    'type' => 'string', 'required' => true, 'maxLength' => 2000,
                    'description' => 'The command itself, exactly as it should be typed. One '
                        . 'command - use a pipeline if you need several steps, not several '
                        . 'calls to this tool. No leading prompt character.',
                ],
                'why' => [
                    'type' => 'string', 'required' => true, 'maxLength' => 400,
                    'description' => 'Why you want this run, in one or two sentences, in '
                        . 'terms of what you are trying to find out.',
                ],
                'what_to_look_for' => [
                    'type' => 'string', 'required' => true, 'maxLength' => 600,
                    'description' => 'What the output will look like and what would count as '
                        . 'a problem in it. Written so somebody can read the output without '
                        . 'you.',
                ],
                'reading' => [
                    'type' => 'string', 'required' => false, 'maxLength' => 400,
                    'description' => 'A link to documentation for the main command, if there '
                        . 'is an obvious one - a man page or the project site. Leave it out '
                        . 'rather than inventing a URL.',
                ],
            ],
        ],

        'show_progress' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'show progress on something that is running.',
            'description' =>
                "Draws a labelled progress indicator in the conversation.\n\n"
                . "USE THIS when something is running that the user is waiting on. Three "
                . "kinds, and picking the right one matters more than any other choice "
                . "here, because each one makes a different promise:\n\n"
                . "  kind=\"time\"    a bar and a clock. Requires percent, and "
                . "seconds_left is what makes it worth drawing. Use it ONLY when you "
                . "genuinely know roughly how long is left - a scan whose usual duration "
                . "you have looked up, a capture with a fixed length.\n\n"
                . "  kind=\"count\"   a bar and a tally: \"7 of 12 checks done\". "
                . "Requires done and total. Use it when the work is a known number of "
                . "steps and you are working through them. This is the honest answer far "
                . "more often than a clock is, because you usually know how many things "
                . "are left and not how long each takes.\n\n"
                . "  kind=\"working\" a moving indicator with no end point, for work "
                . "whose size you do not know. Takes neither a percent nor a total. Use it "
                . "rather than inventing either.\n\n"
                . "NEVER invent a duration, a percentage or a total. A made-up clock is "
                . "worse than no clock, because people plan around it - and \"working\" "
                . "is always available and always true. If you do not know how far through "
                . "something is, that is what kind=\"working\" is for.\n\n"
                . "DO NOT use any of them decoratively. This is for work the user is "
                . "waiting on, not for narrating your own thinking.\n\n"
                . "It draws once and does not animate its own value. Call it again to move "
                . "it; each call replaces the last one for the same `what`.",
            'parameters' => [
                'what' => [
                    'type' => 'string', 'maxLength' => 80, 'required' => true,
                    'description' => 'What is running, in a few words. "Walking the disk", '
                        . '"Resolving addresses". Calling again with the same text moves '
                        . 'that bar rather than drawing a second one.',
                ],
                'kind' => [
                    'type' => 'string',
                    'enum' => ['time', 'count', 'working'],
                    'required' => true,
                    'description' => '"time" for a clock (needs percent, and should have '
                        . 'seconds_left), "count" for a tally (needs done and total), '
                        . '"working" when the size of the job is unknown.',
                ],
                'percent' => [
                    'type' => 'integer', 'minimum' => 0, 'maximum' => 100,
                    'required' => false,
                    'description' => 'For kind="time" only. How far through it is, from '
                        . 'what the tools told you rather than a guess. Ignored otherwise, '
                        . 'since "count" works it out from done and total.',
                ],
                'seconds_left' => [
                    'type' => 'integer', 'minimum' => 0, 'maximum' => 604800,
                    'required' => false,
                    'description' => 'For kind="time". Roughly how many seconds remain. '
                        . 'Leave it out rather than inventing one - a bar with no clock is '
                        . 'still honest.',
                ],
                'done' => [
                    'type' => 'integer', 'minimum' => 0, 'maximum' => 1000000,
                    'required' => false,
                    'description' => 'For kind="count". How many steps are finished.',
                ],
                'total' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 1000000,
                    'required' => false,
                    'description' => 'For kind="count". How many there are altogether. Only '
                        . 'give this if you actually know it.',
                ],
                'unit' => [
                    'type' => 'string', 'maxLength' => 24, 'required' => false,
                    'description' => 'For kind="count". What is being counted, plural: '
                        . '"checks", "files", "domains". Defaults to "steps".',
                ],
            ],
        ],

        /* ------------------------------------------- asking the user -- */

        /* --------------------------------------- what somebody just ran -- */

        'get_terminal_commands' => [
            'group' => 'terminal',
            'where' => 'server',
            'permission' => false,
            'summary' => 'see what commands the user has run in the terminal.',
            'description' =>
                "Returns the commands run in the most recent terminal session - what was "
                . "typed, when, how long each took, and how it ended. NOT what any of them "
                . "printed.\n\n"
                . "USE THIS WHEN THE USER IS ASKING ABOUT SOMETHING THEY JUST DID. \"I ran "
                . "the thing you said and it did not work\" is unanswerable without knowing "
                . "what they actually ran, and asking them to paste it back is asking them "
                . "to do work you can do yourself. It is also how you find out that the "
                . "command you suggested was typed with a different flag.\n\n"
                . "THE EXIT CODE IS THE MOST USEFUL FIELD. 0 means it worked, 1 usually "
                . "means it ran and refused, 127 means the program does not exist, 126 "
                . "means it exists and is not executable, and anything over 128 means a "
                . "signal killed it - 130 is Ctrl-C, 137 is SIGKILL, 143 is SIGTERM. "
                . "`stopped_by` means the user stopped it on purpose, and `timed_out` "
                . "means it hit the terminal's own limit.\n\n"
                . "THIS DOES NOT SHOW OUTPUT, on purpose. A command line is what somebody "
                . "chose to type; its output is whatever happened to be in a file, which "
                . "may be a password, a key or a customer's details. If you need what it "
                . "printed, that is get_terminal_output, which is a separate tool with its "
                . "own permission - and the operator may well have allowed one and not the "
                . "other.\n\n"
                . "A command line can still hold a secret - somebody types a password into "
                . "one now and then. Do not repeat one back in full unless the user needs "
                . "to see it.",
            'parameters' => [
                'limit' => [
                    'type' => 'integer', 'minimum' => 1, 'maximum' => 50,
                    'required' => false, 'default' => 20,
                    'description' => 'How many of the most recent commands. The newest are '
                        . 'almost always the relevant ones.',
                ],
            ],
        ],

        'get_terminal_output' => [
            'group' => 'terminal',
            'where' => 'server',
            'permission' => true,
            'summary' => 'read what a command the user ran printed back.',
            'description' =>
                "Returns what one terminal command printed, or what the most recent one "
                . "did.\n\n"
                . "USE THIS ONLY WHEN THE OUTPUT IS THE QUESTION. \"It said something about "
                . "permission denied and I do not understand it\", or a command whose exit "
                . "code says it failed and whose reason is only in what it printed. Call "
                . "get_terminal_commands first: the command line and the exit code answer a "
                . "surprising number of these on their own, and they carry far less than "
                . "the output does.\n\n"
                . "THIS IS THE MOST SENSITIVE TOOL YOU HAVE. It returns whatever a program "
                . "wrote to the screen on a machine you are helping with: `cat` of a config "
                . "file is a database password, a mail log is customer addresses, an "
                . "environment dump is every key the account holds. It asks the user before "
                . "every call for that reason, and the answer may be no - which is a "
                . "legitimate answer and not an obstacle to work around. Carry on with the "
                . "command and the exit code and say what you cannot see.\n\n"
                . "DO NOT QUOTE MORE OF IT BACK THAN THE POINT NEEDS. Repeating a whole "
                . "config file into the conversation puts it somewhere it was not before. "
                . "Quote the line that matters.\n\n"
                . "Long output comes back from the end rather than the beginning: what a "
                . "command was doing when it stopped is almost always the half worth "
                . "reading, and an error is usually the last thing said.",
            'parameters' => [
                'command_id' => [
                    'type' => 'string', 'maxLength' => 16, 'required' => false,
                    'description' => 'Which command, from get_terminal_commands. Left out, '
                        . 'you get the most recent one - which is what the user means by '
                        . '"it" almost every time.',
                ],
                'bytes' => [
                    'type' => 'integer', 'minimum' => 256, 'maximum' => 65536,
                    'required' => false, 'default' => 8192,
                    'description' => 'How much of it to read, from the end. Ask for what '
                        . 'you need rather than the maximum: everything you pull in is '
                        . 'something that has left that machine.',
                ],
            ],
        ],

        'offer_choices' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'ask the user which way to go next.',
            'description' =>
                "Shows the user a small set of directions and waits for them to pick one. "
                . "Returns which they chose.\n\n"
                . "USE THIS AT A FORK, not for confirmation. The moment for it is when you "
                . "have found something and there are two or three genuinely different "
                . "things to do about it, and which one is right depends on what they want "
                . "rather than on what the data says: investigate further, leave it alone, "
                . "explain what it means, fix it. Guessing wastes a turn and sometimes "
                . "takes the conversation somewhere they did not want to go.\n\n"
                . "DO NOT use it to ask permission - the tools that need permission ask on "
                . "their own. DO NOT use it to ask a factual question; that is "
                . "ask_the_user. DO NOT offer a choice where one option is obviously "
                . "correct: make the obvious move and say you made it.\n\n"
                . "Each choice needs a short label and a line saying what happens if it is "
                . "picked, written so somebody can decide between them without asking you "
                . "first.\n\n"
                . "IF THEY DO NOT ANSWER, this returns after the timeout with "
                . "answered=false and the default you named. Nobody is obliged to answer a "
                . "question, and a conversation that stops dead because somebody walked "
                . "away is worse than one that carries on sensibly. Pick a default that is "
                . "safe to do unattended - which almost always means the one that only "
                . "looks and never changes anything.",
            'parameters' => [
                'question' => [
                    'type' => 'string', 'maxLength' => 200, 'required' => true,
                    'description' => 'What you are asking, in one line. "The disk is '
                        . 'filling from /var/log - what would you like to do?"',
                ],
                'choices' => [
                    'type' => 'array', 'maxItems' => 5, 'required' => true,
                    'items' => [
                        'key' => ['type' => 'string', 'maxLength' => 32,
                            'description' => 'A short identifier you get back, like '
                                . '"investigate".'],
                        'label' => ['type' => 'string', 'maxLength' => 60,
                            'description' => 'What the button says.'],
                        'what_happens' => ['type' => 'string', 'maxLength' => 160,
                            'description' => 'One line saying what you will do if they '
                                . 'pick it. Not a restatement of the label.'],
                    ],
                    'description' => 'Two to five directions. Fewer is better; five is a '
                        . 'menu, and a menu is what you offer when you have not thought '
                        . 'about it.',
                ],
                'default_key' => [
                    'type' => 'string', 'maxLength' => 32, 'required' => true,
                    'description' => 'Which choice to take if nobody answers. Must be one '
                        . 'of the keys above, and must be safe to do unattended.',
                ],
                'seconds' => [
                    'type' => 'integer', 'minimum' => 15, 'maximum' => 900,
                    'required' => false, 'default' => 120,
                    'description' => 'How long to wait before taking the default. The user '
                        . 'sees the countdown and can always answer during it.',
                ],
            ],
        ],

        'ask_the_user' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'ask the user for something only they know.',
            'description' =>
                "Puts one question to the user with a typed answer box and waits for it.\n\n"
                . "USE THIS ONLY FOR WHAT THE TOOLS CANNOT TELL YOU. What changed on "
                . "Tuesday, which of these sites matters, what the application is supposed "
                . "to be doing, how much downtime is acceptable. These are facts about "
                . "intent and history that are not in any database on this machine.\n\n"
                . "CHECK THE TOOLS FIRST, ALWAYS. Asking the user something you could have "
                . "looked up is the fastest way to make this tool feel useless - they came "
                . "here so they would not have to know. If a tool can answer it, call the "
                . "tool.\n\n"
                . "ONE QUESTION. Not a form, not a survey. If you need three things, ask "
                . "for the one that changes what you do next and work out the rest.\n\n"
                . "Four shapes of answer:\n"
                . "  choice     one of a list. Give options.\n"
                . "  checklist  any number from a list. Give options.\n"
                . "  number     a figure, with min and max if they mean something.\n"
                . "  text       free text, for anything that is not one of the above.\n\n"
                . "Prefer choice and checklist where the answers are knowable, because "
                . "picking is faster than typing and the answer comes back in a shape you "
                . "can rely on.\n\n"
                . "IF THEY DO NOT ANSWER, this returns after the timeout with "
                . "answered=false and no value. Carry on without it and say what you "
                . "assumed - do not ask again.",
            'parameters' => [
                'question' => [
                    'type' => 'string', 'maxLength' => 300, 'required' => true,
                    'description' => 'The question, in one line. Say why you are asking if '
                        . 'it is not obvious - people answer a question they understand '
                        . 'the point of.',
                ],
                'answer' => [
                    'type' => 'string',
                    'enum' => ['choice', 'checklist', 'number', 'text'],
                    'required' => true,
                    'description' => 'What shape the answer takes.',
                ],
                'options' => [
                    'type' => 'array', 'maxItems' => 12, 'required' => false,
                    'items' => [
                        'key' => ['type' => 'string', 'maxLength' => 32,
                            'description' => 'What you get back.'],
                        'label' => ['type' => 'string', 'maxLength' => 80,
                            'description' => 'What is shown.'],
                    ],
                    'description' => 'Required for choice and checklist, ignored '
                        . 'otherwise. Include an option for "none of these" where that is '
                        . 'a real possibility, or you will get a wrong answer rather than '
                        . 'no answer.',
                ],
                'minimum' => [
                    'type' => 'integer', 'minimum' => -1000000, 'maximum' => 1000000,
                    'required' => false,
                    'description' => 'For answer="number". The lowest sensible value.',
                ],
                'maximum' => [
                    'type' => 'integer', 'minimum' => -1000000, 'maximum' => 1000000,
                    'required' => false,
                    'description' => 'For answer="number". The highest sensible value.',
                ],
                'unit' => [
                    'type' => 'string', 'maxLength' => 24, 'required' => false,
                    'description' => 'For answer="number". What the figure is in, shown '
                        . 'beside the box: "GB", "days", "requests a second".',
                ],
                'seconds' => [
                    'type' => 'integer', 'minimum' => 15, 'maximum' => 900,
                    'required' => false, 'default' => 180,
                    'description' => 'How long to wait before giving up on an answer.',
                ],
            ],
        ],

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

        'show_reaction' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'show a small reaction image.',
            'description' =>
                "Finds and shows a short animated image matching a mood.\n\n"
                . "USE THIS EXTREMELY SPARINGLY - at most once in a conversation, and only "
                . "when something genuinely warrants it: a problem solved after a long "
                . "hunt, or an absurd finding. It is an aside, not a punctuation mark.\n\n"
                . "DO NOT use it when somebody is having a bad day, when the news is bad, "
                . "or when a real problem is still unresolved. A cheerful animation over an "
                . "unfixed outage is the single fastest way to make this whole tool feel "
                . "like it is not taking the situation seriously.\n\n"
                . "You do not choose the image, only the mood. What comes back may not be "
                . "what you pictured, so never refer to its contents in your reply.",
            'parameters' => [
                'mood' => [
                    'type' => 'string', 'maxLength' => 40, 'required' => true,
                    'description' => 'Two or three words describing the feeling. '
                        . '"mission accomplished", "slow clap", "everything is fine".',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 120, 'required' => true,
                    'description' => 'A caption shown under it, in your own voice.',
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

        'redraw_yourself' => [
            'group' => 'pictures',
            'where' => 'server',
            'permission' => true,
            'summary' => 'redraw your own portrait.',
            'description' =>
                "Generates a new portrait for yourself, which becomes the picture shown "
                . "beside your name.\n\n"
                . "This is a novelty. It costs money, it shares the hourly picture limit, "
                . "and it needs permission every time.\n\n"
                . "USE IT ALMOST NEVER - when the mood of a long session genuinely calls "
                . "for it, and not otherwise. Describe yourself however you see fit; the "
                . "result is squared off and shrunk to a thumbnail, so anything intricate "
                . "is wasted.\n\n"
                . "DO NOT do this in place of answering a question, and DO NOT ask twice "
                . "in a session.",
            'parameters' => [
                'description' => [
                    'type' => 'string', 'maxLength' => 400, 'required' => true,
                    'description' => 'How you wish to be depicted. This is stored and '
                        . 'shown as the portrait\'s caption, so write it as something a '
                        . 'person will read.',
                ],
                'why' => [
                    'type' => 'string', 'maxLength' => 160, 'required' => true,
                    'description' => 'What prompted it. Shown when asking permission.',
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
