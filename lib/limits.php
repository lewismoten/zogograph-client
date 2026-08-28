<?php
declare(strict_types=1);

/*
 * What the account is allowed, as against what the machine has.
 *
 * On a shared CloudLinux host these are two different numbers and the dashboard
 * has spent its whole life reporting only the first one. Inside CageFS the
 * account sees a `/proc` written for it: `/proc/stat` counts only its own CPU
 * allowance, `/proc/meminfo` reports its own memory limit as though it were the
 * machine's RAM, and `nproc` answers with the cores it may use. Every one of
 * those looks exactly like a small server, which is why "95% CPU" on this
 * dashboard has never meant the server was busy.
 *
 * Two readings are needed to tell the difference, and the second one has to be
 * taken by root - which is what host-cron.php is for. With both in hand the
 * comparison is simple and the conclusions are strong:
 *
 *   the account sees less  the difference is a limit, and the account will hit
 *                          it while the machine is still idle
 *   the two agree          either there is no cage, or the limit is the whole
 *                          machine - which for a busy host is not reassuring
 *   the account sees more  something is wrong with one of the readings, and
 *                          saying so is better than reporting it as headroom
 *
 * Nothing here reads a limit *configuration*. LVE limits live in files this
 * account cannot open, and inferring the policy from one reading would be
 * guessing. What is reported is only what was measured on both sides.
 */

/** Below this the two readings are treated as the same number. */
const LIMIT_AGREEMENT = 0.05;

/**
 * Compares one account reading against the host's.
 *
 * @return array{
 *   name: string, account: float|null, host: float|null, unit: string,
 *   share: float|null, verdict: string, note: string
 * }
 */
function compareLimit(string $name, ?float $account, ?float $host, string $unit, string $what): array
{
    $out = [
        'name' => $name,
        'account' => $account,
        'host' => $host,
        'unit' => $unit,
        'share' => null,
        'verdict' => 'unknown',
        'note' => '',
    ];

    if ($account === null || $host === null || $host <= 0) {
        $out['note'] = $host === null
            ? 'The host reading is missing, so there is nothing to compare against.'
            : 'This account cannot measure it.';
        return $out;
    }

    $share = $account / $host;
    $out['share'] = round($share, 4);

    if ($share < 1 - LIMIT_AGREEMENT) {
        $out['verdict'] = 'capped';
        $out['note'] = sprintf(
            'This account may use %s of the machine\'s %s. It will run out of %s '
            . 'while the server still has plenty.',
            percentWord($share),
            $what,
            $what
        );
        return $out;
    }

    if ($share > 1 + LIMIT_AGREEMENT) {
        /*
         * Reported rather than smoothed over.
         *
         * An account cannot genuinely have more than the machine, so this is a
         * measurement problem - two collectors that ran minutes apart across a
         * reboot, a host reading from before RAM was added, or a cage reporting
         * something odd. Averaging it away would hide the fault.
         */
        $out['verdict'] = 'disagree';
        $out['note'] = 'This account reports more ' . $what . ' than the host does, '
            . 'which cannot be true. One of the two readings is stale or wrong.';
        return $out;
    }

    $out['verdict'] = 'whole';
    $out['note'] = 'This account sees the whole machine\'s ' . $what
        . ', so nothing is holding it back except everyone else using the same server.';
    return $out;
}

function percentWord(float $share): string
{
    $percent = $share * 100;
    return $percent < 1
        ? sprintf('%.1f%%', $percent)
        : sprintf('%d%%', (int)round($percent));
}

/**
 * The whole comparison, from one account reading and one host reading.
 *
 * Returns a row per thing that can be compared, and only for the ones both
 * sides actually reported. A row saying "unknown" for every metric is noise;
 * the absence of the panel is the same information without the pretence.
 *
 * @return array{available: bool, reason: string, rows: list<array>, taken_at: int|null, host_at: int|null}
 */
function accountLimits(?array $vitals, ?array $host): array
{
    if ($vitals === null) {
        return ['available' => false, 'reason' => 'No reading from this account yet.',
                'rows' => [], 'taken_at' => null, 'host_at' => null];
    }

    if ($host === null) {
        return [
            'available' => false,
            'reason' => 'No host reading. Without one, there is no way to tell a limit '
                . 'on this account from the size of the machine — every number on this '
                . 'dashboard would be the cage\'s answer, presented as the server\'s.',
            'rows' => [],
            'taken_at' => (int)$vitals['taken_at'],
            'host_at' => null,
        ];
    }

    $number = fn(?string $key, array $row): ?float =>
        $key !== null && isset($row[$key]) && $row[$key] !== null ? (float)$row[$key] : null;

    $rows = [
        compareLimit(
            'Cores',
            $number('cpu_cores', $vitals),
            $number('cpu_cores', $host),
            'cores',
            'CPU'
        ),
        compareLimit(
            'Memory',
            $number('mem_total_bytes', $vitals),
            $number('mem_total', $host),
            'bytes',
            'memory'
        ),
    ];

    /*
     * Two CPU percentages that are not the same measurement.
     *
     * Both are "busy time over elapsed time", but over different denominators:
     * the account's is over its own allowance, the host's is over every core on
     * the machine. Dividing one by the other would produce a number with no
     * meaning, so this row carries both and compares neither.
     */
    $rows[] = [
        'name' => 'CPU in use',
        'account' => $number('cpu_percent', $vitals),
        'host' => $number('cpu_percent', $host),
        'unit' => 'percent',
        'share' => null,
        'verdict' => 'sidebyside',
        'note' => 'Not comparable as a ratio: the account\'s percentage is of its own '
            . 'allowance and the host\'s is of every core on the machine. High on the '
            . 'left and low on the right is the ordinary shape of hitting a limit.',
    ];

    return [
        'available' => true,
        'reason' => '',
        'rows' => array_values(array_filter(
            $rows,
            fn(array $row): bool => $row['account'] !== null || $row['host'] !== null
        )),
        'taken_at' => (int)$vitals['taken_at'],
        'host_at' => (int)($host['taken_at'] ?? 0) ?: null,
    ];
}
