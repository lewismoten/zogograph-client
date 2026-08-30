<?php
declare(strict_types=1);

/*
 * Every chart, named so that three parties can talk about the same one.
 *
 * The person looking at it, the model reasoning about it, and the code drawing
 * it all needed a shared word. Without one, a conversation goes "the memory
 * graph" - and there are four - or the model asks for a screenshot of the whole
 * page because it has no way to name the part it wants.
 *
 * So each chart has an id, a name, the page it lives on, and a list of the
 * measurements it plots. From that one definition comes:
 *
 *   the label      shown on the chart itself, on hover, with its range
 *   the data tool  which returns the numbers behind it
 *   the image tool which returns a picture of it, for the rare case where the
 *                  numbers genuinely cannot answer the question
 *   the chat card  which draws it inside the conversation
 *
 * The order matters. Numbers first, always: a chart is a rendering of data the
 * model can have directly, and asking for a picture of it instead is asking for
 * a worse copy at fifty times the cost. The image is for when somebody says
 * "there's a spike here" and points - the shape of a thing, not its values.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/spans.php';

/**
 * The charts, by id.
 *
 * `series` names the measurements. Each is [key, label, unit] where key is one
 * of the fixed expressions in aiChartExpression - never a column name from
 * anywhere but this file.
 *
 * @return array<string, array<string, mixed>>
 */
function aiChartCatalogue(): array
{
    return [
        'roomChart' => [
            'name' => 'Room left',
            'view' => 'charts',
            'what' =>
                'Free disk space and free inodes over time. Either can run out on ' .
                'its own - a disk with free gigabytes and no inodes still refuses to ' .
                'create a file.',
            'series' => [
                ['disk_free_bytes', 'Disk free', 'bytes'],
                ['inodes_free', 'Inodes free', 'count'],
            ],
        ],
        'cpuChart' => [
            'name' => 'CPU and memory',
            'view' => 'charts',
            'what' =>
                'Processor and memory use as a share of what this ACCOUNT may use, ' .
                'not of the machine. Inside CageFS this can sit at 100 while the machine ' .
                'is idle, which is what being throttled looks like.',
            'series' => [
                ['cpu_percent', 'CPU', 'percent'],
                ['mem_used_percent', 'Memory', 'percent'],
            ],
        ],
        'ioChart' => [
            'name' => 'Disk I/O',
            'view' => 'charts',
            'what' =>
                'Bytes actually reaching a disk, not bytes served from cache. Read ' .
                'and written separately.',
            'series' => [
                ['io_read_bytes', 'Read', 'bytes'],
                ['io_write_bytes', 'Written', 'bytes'],
            ],
        ],
        'loadChart' => [
            'name' => 'Load average',
            'view' => 'charts',
            'what' =>
                'How many processes wanted to run at once. Not a percentage - ' .
                'compare it to the core count. At one per core the machine is busy; ' .
                'above that, work is queueing.',
            'series' => [
                ['load1', 'Load, one minute', 'count'],
                ['load_per_core', 'Per core', 'percent'],
            ],
        ],
        'netChart' => [
            'name' => 'Network',
            'view' => 'charts',
            'what' =>
                'Bytes a second in and out, summed over the real interfaces. ' .
                'Loopback is not counted.',
            'series' => [
                ['net_in_bytes', 'Arriving', 'bytes'],
                ['net_out_bytes', 'Leaving', 'bytes'],
            ],
        ],
        'diskUsedChart' => [
            'name' => 'How full the disk is',
            /*
             * No page has this one.
             *
             * Its numbers are real and show_chart can draw it in the
             * conversation, but there is no canvas for it anywhere - so telling
             * somebody to "look at it on Over time" sends them to a page that
             * does not have it. That happened because the catalogue's `view`
             * was read as "where this is" when it only ever meant "where the
             * data comes from".
             */
            'view' => null,
            'what' =>
                'Disk and inode use as percentages. The same readings as Room left, ' .
                'the other way up - this is the one to use when the question is "how ' .
                'close to full", and Room left when it is "how much is there".',
            'series' => [
                ['disk_used_percent', 'Disk used', 'percent'],
                ['inodes_used_percent', 'Inodes used', 'percent'],
            ],
        ],
    ];
}

/**
 * How one measurement is calculated, as SQL over the vitals table.
 *
 * Every expression here is a literal written in this file and selected by a key
 * that has already been checked against the catalogue. Nothing the model says
 * ever becomes part of one.
 */
function aiChartExpression(string $key): ?string
{
    return [
        'disk_free_bytes' => 'CAST("disk_free_bytes" AS REAL)',
        'inodes_free' => 'CAST("inodes_free" AS REAL)',
        'disk_used_percent' => 'CASE WHEN "disk_total_bytes" > 0
                  THEN (CAST("disk_used_bytes" AS REAL) / "disk_total_bytes") * 100 END',
        'inodes_used_percent' => 'CASE WHEN "inodes_total" > 0
                  THEN (CAST("inodes_used" AS REAL) / "inodes_total") * 100 END',
        'cpu_percent' => 'CAST("cpu_percent" AS REAL)',
        'mem_used_percent' => 'CAST("mem_used_percent" AS REAL)',
        'load1' => 'CAST("load1" AS REAL)',
        'load_per_core' => 'CASE WHEN "cpu_cores" > 0
                  THEN (CAST("load1" AS REAL) / "cpu_cores") * 100 END',
        'io_read_bytes' => 'CAST("io_read_bytes" AS REAL)',
        'io_write_bytes' => 'CAST("io_write_bytes" AS REAL)',
        'net_in_bytes' => 'CAST("net_in_bytes" AS REAL)',
        'net_out_bytes' => 'CAST("net_out_bytes" AS REAL)',
    ][$key] ?? null;
}

/**
 * The numbers behind one chart, bucketed.
 *
 * Bucketed because a month of readings is eight thousand numbers, which would
 * fill a context window and say less than twenty would. Each bucket carries its
 * lowest and highest as well as its average: the average of an hour containing
 * a two-minute spike hides the spike, and the spike is the thing being looked
 * for.
 *
 * @return array<string, mixed>
 */
function aiChartData(PDO $pdo, string $chartId, int $hours): array
{
    $catalogue = aiChartCatalogue();

    if (!isset($catalogue[$chartId])) {
        return [
            'error' => sprintf(
                'There is no chart called "%s". The charts are: %s.',
                $chartId,
                implode(', ', array_keys($catalogue)),
            ),
        ];
    }

    $chart = $catalogue[$chartId];
    $since = time() - $hours * SPAN_HOUR;
    $bucket = max(60, (int) floor(($hours * SPAN_HOUR) / 24));

    $out = [
        'chart' => $chartId,
        'name' => $chart['name'],
        'shown_on' => $chart['view'],
        'page' => $chart['view'],
        'on_a_page' => $chart['view'] !== null,
        'what' => $chart['what'],
        'period' => 'the last ' . plainSpan($hours * SPAN_HOUR, true),
        'bucket' => plainSpan($bucket),
        // The browser formats its axis from this; every series on one chart
        // shares a unit, so the first one is the chart's.
        'units' => $chart['series'][0][2] ?? '',
        'series' => [],
    ];

    foreach ($chart['series'] as [$key, $label, $unit]) {
        $expression = aiChartExpression($key);
        if ($expression === null) {
            continue;
        }

        $stmt = $pdo->prepare(
            sprintf(
                'SELECT MIN(taken_at) AS from_at, MAX(taken_at) AS to_at, COUNT(*) AS readings,
                    MIN(%1$s) AS low, MAX(%1$s) AS high, AVG(%1$s) AS mean,
                    (taken_at / :bucket) AS slot
               FROM vitals
              WHERE taken_at >= :since AND %1$s IS NOT NULL
              GROUP BY slot ORDER BY slot',
                $expression,
            ),
        );
        $stmt->execute([':since' => $since, ':bucket' => $bucket]);
        $rows = $stmt->fetchAll();

        /*
         * Two forms of the same moment, and both are needed.
         *
         * `when` is for the model, which cannot do anything sensible with an
         * epoch second. `at` is the epoch second, for the browser, which draws
         * the chart from this same answer and cannot plot a string - it was
         * getting only the string, so every chart the assistant drew in the
         * conversation came out empty while the numbers underneath it were
         * perfectly good.
         */
        $points = array_map(
            fn(array $row): array => [
                'at' => (int) $row['from_at'],
                'when' => date('Y-m-d H:i', (int) $row['from_at']),
                'low' => round((float) $row['low'], 2),
                'high' => round((float) $row['high'], 2),
                'average' => round((float) $row['mean'], 2),
            ],
            $rows,
        );

        $out['series'][] = [
            'measurement' => $key,
            'label' => $label,
            'unit' => $unit,
            'points' => $points,
            'moved_by' =>
                count($points) < 2
                    ? null
                    : round(
                        $points[count($points) - 1]['average'] -
                            $points[0]['average'],
                        2,
                    ),
        ];
    }

    $any = array_filter(
        $out['series'],
        fn(array $series): bool => $series['points'] !== [],
    );

    if ($any === []) {
        $out['note'] =
            'No readings in that period for anything this chart plots. Either ' .
            'the collector was not running - check get_collector_health - or the period ' .
            'is older than the retention settings keep.';
        return $out;
    }

    $out['note'] =
        'These are the numbers the chart is drawn from. Only ask for a picture ' .
        'of it if the person is describing something about its SHAPE that these cannot ' .
        'tell you.';

    return $out;
}
/**
 * Every chart on the dashboard, whether or not its data can be fetched.
 *
 * Two different questions, and conflating them was a real fault: the catalogue
 * above is "charts whose numbers this can serve", and somebody reading it as
 * "charts that exist" gets sent to a page for a chart that is not on it.
 * `diskUsedChart` is exactly that - its data is real and it is drawn on demand
 * in the conversation, but no page has one.
 *
 * This list is the other half: what is on which page. It is written down rather
 * than discovered, because the only place the truth lives is the markup, and
 * the model cannot read the markup.
 *
 * @return list<array<string, mixed>>
 */
function aiEveryChart(): array
{
    $fetchable = aiChartCatalogue();
    $out = [];

    foreach ($fetchable as $id => $chart) {
        $out[] = [
            'id' => $id,
            'name' => $chart['name'],
            'page' => $chart['view'],
            'shows' => $chart['what'],
            'data_available' => true,
            'note' =>
                $chart['view'] === null
                    ? 'Not on any page. You can draw it in the conversation with show_chart, ' .
                        'but do not send anybody to look at it.'
                    : null,
        ];
    }

    /*
     * The rest: drawn from data this cannot serve as a series.
     *
     * A pie of file types and a treemap of directories are not time series and
     * there is nothing sensible for get_chart_data to return for them - but
     * knowing they exist, and on which page, is what lets the assistant say
     * "the answer to that is the treemap on Tree map" instead of describing
     * numbers at somebody.
     */
    foreach (
        [
            [
                'typePie',
                'What the space is made of',
                'storage',
                'A pie of disk use by file extension, from the last completed scan.',
            ],
            [
                'typeGrowthChart',
                'What grew',
                'storage',
                'How each file type has grown between scans.',
            ],
            [
                'categoryChart',
                'The watched categories',
                'storage',
                'Disk use for the categories that raise alerts and can be emptied.',
            ],
            [
                'historyChart',
                'One path over time',
                'storage-files',
                'The size of a single file or directory across scans.',
            ],
            [
                'treemap',
                'Tree map',
                'storage-map',
                'Every directory as a rectangle in proportion to its size. The fastest way ' .
                'to see where space went when nobody knows where to start.',
            ],
            [
                'hostChart',
                'Memory on the machine',
                'host',
                'What the whole machine is using, as opposed to this account.',
            ],
            [
                'hostServiceChart',
                'Memory per service',
                'mem-services',
                'Which services are holding memory, over time.',
            ],
            [
                'hostPie',
                'Where memory goes',
                'mem-goes',
                'A breakdown of the machine memory that is not free.',
            ],
            [
                'hostIoChart',
                'Disk I/O on the machine',
                'storage-io',
                'Bytes reaching the disks, for the machine rather than the account.',
            ],
            [
                'urlChart',
                'How a site answered',
                'net-urls',
                'Response time and failures for one watched address, with the moments its ' .
                'watched value changed marked on it.',
            ],
            [
                'urlValueChart',
                'What a site reported',
                'net-urls',
                'The number extracted from a watched page, over time.',
            ],
            [
                'capChanges',
                'When programs changed',
                'capabilities',
                'When something the host provides appeared, went, or changed version.',
            ],
            [
                'alertTimeline',
                'When alerts fired',
                'alerts',
                'Alerts per hour or per day, stacked by severity, with restarts marked.',
            ],
            [
                'uptimeChart',
                'What was reporting',
                'uptime',
                'Two tracks: whether the machine was up, and whether each collector was ' .
                'writing readings.',
            ],
            [
                'changedChart',
                'When software changed',
                'sys-changed',
                'Kernel, PHP and library versions changing underneath the account.',
            ],
        ]
        as [$id, $name, $page, $shows]
    ) {
        $out[] = [
            'id' => $id,
            'name' => $name,
            'page' => $page,
            'shows' => $shows,
            'data_available' => false,
        ];
    }

    return $out;
}

/**
 * The gauges on the front page: what each reads now, and what that means.
 *
 * The gauges are the one part of this dashboard designed to be understood at a
 * glance, and the assistant could not see them at all - it could fetch the raw
 * numbers behind them but not the bands that turn a number into "urgent". A
 * model reasoning without those will contradict what the person is looking at,
 * which is worse than saying nothing.
 *
 * The bands below are the ones the page draws, copied from renderGauges in
 * index.js. They are NOT the alert thresholds - those are the operator's
 * settings and fire emails; these are fixed and only colour an arc. Two
 * different numbers that both mean "bad", and telling somebody the wrong one is
 * how a conversation ends up arguing with a screen.
 *
 * @return array<string, mixed>
 */
function aiGauges(array $config, PDO $pdo): array
{
    $row = $pdo
        ->query('SELECT * FROM vitals ORDER BY taken_at DESC, id DESC LIMIT 1')
        ->fetch();

    if ($row === false) {
        return [
            'error' =>
                'No readings have been collected yet, so the gauges on the ' .
                'front page are empty too.',
        ];
    }

    $number = fn(?string $key): ?float => $key === null || $row[$key] === null
        ? null
        : (float) $row[$key];

    /*
     * Worked out the way the page works them out, not read from a column.
     *
     * disk_used_percent exists and the gauge does not use it - it divides used
     * by total itself. On a row where one of those is null the two disagree,
     * and the gauge is the thing being described here.
     */
    $share = function (?float $part, ?float $whole): ?float {
        if ($part === null || $whole === null || $whole <= 0) {
            return null;
        }
        return round(($part / $whole) * 100, 1);
    };

    $band = function (?float $value, float $warn, float $crit): string {
        if ($value === null) {
            return 'not measured';
        }
        return $value >= $crit
            ? 'urgent'
            : ($value >= $warn
                ? 'watch'
                : 'healthy');
    };

    $cores = $number('cpu_cores');
    $load = $number('load1');
    $perCore =
        $load !== null && $cores !== null && $cores > 0
            ? round(($load / $cores) * 100, 1)
            : null;

    $disk = $share($number('disk_used_bytes'), $number('disk_total_bytes'));
    $inodes = $share($number('inodes_used'), $number('inodes_total'));
    $cpu = $number('cpu_percent');
    $memory = $number('mem_used_percent');

    $gauges = [
        [
            'gauge' => 'Disk used',
            'reads' => $disk,
            'units' => 'percent',
            'verdict' => $band($disk, 88, 95),
            'watch_above' => 88,
            'urgent_above' => 95,
            'beneath_it' =>
                $number('disk_total_bytes') === null
                    ? 'not reported'
                    : sprintf(
                        '%s free of %s',
                        humanBytes((int) $row['disk_free_bytes']),
                        humanBytes((int) $row['disk_total_bytes']),
                    ),
            'chart' => 'diskUsedChart',
        ],
        [
            'gauge' => 'Inodes used',
            'reads' => $inodes,
            'units' => 'percent',
            'verdict' => $band($inodes, 88, 95),
            'watch_above' => 88,
            'urgent_above' => 95,
            'beneath_it' =>
                $number('inodes_total') === null
                    ? 'not reported'
                    : number_format((int) $row['inodes_free']) . ' free',
            'chart' => 'roomChart',
            'note' =>
                'An inode is one file. A disk with free gigabytes and no inodes ' .
                'still refuses to create a file, and nothing about the disk gauge ' .
                'shows that.',
        ],
        [
            'gauge' => 'CPU',
            'reads' => $cpu,
            'units' => 'percent',
            'verdict' => $band($cpu, 70, 90),
            'watch_above' => 70,
            'urgent_above' => 90,
            'chart' => 'cpuChart',
            'note' =>
                'A percentage of what THIS ACCOUNT may use, not of the machine. ' .
                '100% here on an idle server is being throttled, which is a real ' .
                'problem and a different one from a busy server.',
        ],
        [
            'gauge' => 'Memory',
            'reads' => $memory,
            'units' => 'percent',
            'verdict' => $band($memory, 80, 92),
            'watch_above' => 80,
            'urgent_above' => 92,
            'beneath_it' =>
                $number('mem_total_bytes') === null
                    ? 'not reported'
                    : sprintf(
                        '%s available of %s',
                        humanBytes((int) $row['mem_available_bytes']),
                        humanBytes((int) $row['mem_total_bytes']),
                    ),
            'chart' => 'cpuChart',
        ],
        [
            'gauge' => 'Load',
            'reads' => $perCore,
            'units' => 'percent of one process per core',
            'verdict' => $band($perCore, 100, 150),
            'watch_above' => 100,
            'urgent_above' => 150,
            'beneath_it' =>
                $load === null
                    ? 'not reported'
                    : ($cores
                        ? sprintf('%.2f over %d cores', $load, (int) $cores)
                        : sprintf('%.2f, cores unknown', $load)),
            'chart' => 'loadChart',
            'note' =>
                'The gauge shows load as a percentage of one process per core, so ' .
                '100 means exactly as much work as there are cores. The raw load ' .
                'average is in beneath_it.',
        ],
    ];

    return [
        'measured' => aiWhen((int) $row['taken_at']),
        'shown_on' => 'overview',
        'gauges' => $gauges,
        'note' =>
            'This is what the operator sees at a glance on the front page. The ' .
            'bands here colour the arcs and are fixed; they are NOT the alert ' .
            'thresholds, which are configurable and are what sends email. If you ' .
            'disagree with a verdict, say you are disagreeing with where the band is ' .
            'drawn rather than stating a different number.',
    ];
}
