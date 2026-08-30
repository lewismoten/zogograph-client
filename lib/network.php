<?php
declare(strict_types=1);

/*
 * What is talking to this machine, and on which ports.
 *
 * The gap this fills: every other panel here measures a resource being consumed
 * - disk, memory, CPU - and none of them can tell you *why*. A machine pinned at
 * 100% CPU because it is answering four thousand DNS queries a second looks
 * exactly like a machine pinned at 100% CPU for any other reason, and the
 * difference is only visible on the wire.
 *
 * Three levels, in increasing cost and increasing intrusiveness:
 *
 *   listening sockets   free, read from /proc or ss. What is open, and what is
 *                       holding it open. Answers "is that port meant to be there".
 *   established peers   free, same source. Who is connected right now, which is
 *                       enough to spot one address holding a thousand sockets.
 *   a packet sample     expensive and root-only. A bounded tcpdump run,
 *                       summarised into counts. Answers "where is the traffic
 *                       actually coming from" when the first two do not.
 *
 * The capture records counts and endpoints and nothing else. No payloads are
 * kept, ever - a monitor that quietly writes the contents of other people's
 * traffic into a SQLite file on a shared host would be a far worse problem than
 * whatever it was pointed at.
 */

require_once __DIR__ . '/collect.php';

/** Longest a requested capture may run, whatever is asked for. */
const CAPTURE_MAX_SECONDS = 60;

/*
 * A packet ceiling, as a backstop rather than as the bound that usually applies.
 *
 * Counters are kept as packets arrive, so nothing is held in memory and a long
 * capture costs no more than a short one. The previous limit of twenty thousand
 * was reached by a busy web server in fifteen seconds, which quietly turned a
 * sixty-second sample into a fifteen-second one - and the report said "20,004
 * packets" as though that were the period's traffic.
 */
const CAPTURE_MAX_PACKETS = 2000000;

/** How many distinct peers, ports and names to keep from one capture. */
const CAPTURE_KEEP = 40;

/**
 * The network interfaces and what has crossed them since boot.
 *
 * Loopback is reported but marked, rather than dropped: a machine doing a
 * gigabyte a second over `lo` is telling you something real about a local
 * database, and it is also the reason a naive total looks impossible.
 *
 * @return list<array>
 */
function networkInterfaces(string $procRoot = '/proc'): array
{
    $raw = @file_get_contents($procRoot . '/net/dev');
    if ($raw === false) {
        return [];
    }

    $out = [];
    foreach (preg_split('/\R/', $raw) ?: [] as $line) {
        if (!str_contains($line, ':')) {
            continue;
        }
        [$name, $rest] = explode(':', $line, 2);
        $name = trim($name);
        $parts = preg_split('/\s+/', trim($rest)) ?: [];
        if (count($parts) < 16) {
            continue;
        }

        $out[] = [
            'name' => $name,
            'loopback' => $name === 'lo',
            'rx_bytes' => (int) $parts[0],
            'rx_packets' => (int) $parts[1],
            'rx_errors' => (int) $parts[2],
            'rx_dropped' => (int) $parts[3],
            'tx_bytes' => (int) $parts[8],
            'tx_packets' => (int) $parts[9],
            'tx_errors' => (int) $parts[10],
            'tx_dropped' => (int) $parts[11],
            'up' => interfaceIsUp($name),
            'speed_mbit' => interfaceSpeed($name),
        ];
    }

    usort(
        $out,
        fn(array $a, array $b): int => $a['loopback'] <=> $b['loopback'] ?:
        $b['rx_bytes'] + $b['tx_bytes'] <=> $a['rx_bytes'] + $a['tx_bytes'],
    );

    return $out;
}

function interfaceIsUp(string $name): ?bool
{
    $state = @file_get_contents('/sys/class/net/' . $name . '/operstate');
    if ($state === false) {
        return null;
    }
    return trim($state) === 'up';
}

/**
 * Link speed in megabits, where the driver reports one.
 *
 * Virtual interfaces and most containers report nothing, and a wrong number
 * here would make "we are saturating the link" a claim rather than a reading -
 * so absence is reported as absence.
 */
function interfaceSpeed(string $name): ?int
{
    $speed = @file_get_contents('/sys/class/net/' . $name . '/speed');
    if ($speed === false) {
        return null;
    }
    $value = (int) trim($speed);
    return $value > 0 ? $value : null;
}

/**
 * Every socket in a listening state, with whatever holds it.
 *
 * `ss` first because it names the process; the /proc/net files as a fallback
 * because they are always there. The fallback cannot attribute a socket to a
 * process without walking the fd directory of every process, which is expensive
 * and, from an unprivileged account, mostly refused - so it reports the socket
 * without an owner rather than guessing.
 *
 * @return array{rows: list<array>, source: string, reason: string}
 */
function listeningSockets(): array
{
    $out = shellRead('ss -lntupH 2>/dev/null');
    if ($out !== null) {
        return [
            'rows' => parseSsListening($out),
            'source' => 'ss',
            'reason' => '',
        ];
    }

    $rows = [];
    foreach (
        [
            'tcp' => '/proc/net/tcp',
            'tcp6' => '/proc/net/tcp6',
            'udp' => '/proc/net/udp',
            'udp6' => '/proc/net/udp6',
        ]
        as $kind => $file
    ) {
        foreach (parseProcNet($file, str_starts_with($kind, 'tcp')) as $row) {
            $rows[] = $row + [
                'protocol' => $kind,
                'process' => null,
                'pid' => null,
            ];
        }
    }

    return [
        'rows' => dedupePorts($rows),
        'source' => 'proc',
        'reason' =>
            $rows === []
                ? 'Neither ss nor /proc/net could be read here.'
                : 'Read from /proc/net, which does not say which process holds each socket. ' .
                    'Install iproute2, or run the host collector as root, for that.',
    ];
}

function parseSsListening(string $out): array
{
    $rows = [];

    foreach (preg_split('/\R/', trim($out)) ?: [] as $line) {
        $parts = preg_split('/\s+/', trim($line)) ?: [];
        if (count($parts) < 5) {
            continue;
        }

        // netid state recv-q send-q local peer [users:(("name",pid=N,fd=N))]
        $protocol = $parts[0];
        $local = $parts[4];

        /*
         * Split on the last colon, not the first.
         *
         * An IPv6 listener is written `[::]:443` or `*:443`, and splitting on
         * the first colon turns `::` into an address of `` and a port of `:`.
         */
        $at = strrpos($local, ':');
        if ($at === false) {
            continue;
        }
        $address = substr($local, 0, $at);
        $port = (int) substr($local, $at + 1);
        if ($port <= 0) {
            continue;
        }

        $process = null;
        $pid = null;
        if (preg_match('/users:\(\("([^"]+)",pid=(\d+)/', $line, $m)) {
            $process = $m[1];
            $pid = (int) $m[2];
        }

        $rows[] = [
            'protocol' => $protocol,
            'address' => trim($address, '[]'),
            'port' => $port,
            'process' => $process,
            'pid' => $pid,
        ];
    }

    return dedupePorts($rows);
}

/**
 * One row per protocol and port, with the addresses folded together.
 *
 * A daemon bound to both stacks appears four times in `ss` - v4 and v6, each
 * possibly on several addresses - and four identical rows for one service reads
 * as four services. What matters is "port 53 is open, held by named, on
 * everything", so that is what is reported.
 */
function dedupePorts(array $rows): array
{
    $byKey = [];

    foreach ($rows as $row) {
        /*
         * The family comes from the address, not from the protocol name.
         *
         * `ss` writes "tcp" for both stacks and only the address says which -
         * `[::]:22` against `0.0.0.0:22`. Reading it off the protocol worked
         * against /proc/net/tcp6, where the name carries it, and quietly
         * reported every IPv6 listener as IPv4 everywhere else.
         */
        $family = str_contains((string) $row['address'], ':') ? 'v6' : 'v4';
        $base = rtrim($row['protocol'], '6');
        $key = $base . ':' . $row['port'];

        if (!isset($byKey[$key])) {
            $byKey[$key] = [
                'protocol' => $base,
                'port' => $row['port'],
                'addresses' => [],
                'families' => [],
                'process' => $row['process'],
                'pid' => $row['pid'],
            ];
        }

        $byKey[$key]['addresses'][$row['address']] = true;
        $byKey[$key]['families'][$family] = true;
        $byKey[$key]['process'] ??= $row['process'];
        $byKey[$key]['pid'] ??= $row['pid'];
    }

    $out = [];
    foreach ($byKey as $entry) {
        $addresses = array_keys($entry['addresses']);
        $out[] = [
            'protocol' => $entry['protocol'],
            'port' => $entry['port'],
            'addresses' => $addresses,
            'families' => array_keys($entry['families']),
            /*
             * Whether it is reachable from off the machine.
             *
             * A service on 127.0.0.1 is a different thing from the same service
             * on 0.0.0.0, and the distinction is the whole of "should this be
             * open" - one is internal plumbing, the other is exposed to whoever
             * can route to this host.
             */
            'public' => portIsPublic($addresses),
            'process' => $entry['process'],
            'pid' => $entry['pid'],
            'service' => portService($entry['port'], $entry['protocol']),
        ];
    }

    usort($out, fn(array $a, array $b): int => $a['port'] <=> $b['port']);
    return $out;
}

function portIsPublic(array $addresses): bool
{
    foreach ($addresses as $address) {
        $address = trim($address, '[]');
        if (
            $address === '0.0.0.0' ||
            $address === '*' ||
            $address === '::' ||
            $address === ''
        ) {
            return true;
        }
        if (!str_starts_with($address, '127.') && $address !== '::1') {
            return true;
        }
    }
    return false;
}

/**
 * Two hex fields per line of /proc/net/tcp, little-endian.
 *
 * State 0A is LISTEN for TCP. UDP has no listening state - a bound socket is
 * simply state 07 - so for UDP anything bound with no peer is reported.
 */
function parseProcNet(string $file, bool $isTcp): array
{
    $raw = @file_get_contents($file);
    if ($raw === false) {
        return [];
    }

    $rows = [];
    foreach (array_slice(preg_split('/\R/', trim($raw)) ?: [], 1) as $line) {
        $parts = preg_split('/\s+/', trim($line)) ?: [];
        if (count($parts) < 4) {
            continue;
        }

        $state = strtoupper($parts[3]);
        if ($isTcp ? $state !== '0A' : $state !== '07') {
            continue;
        }

        [$hexAddress, $hexPort] = array_pad(explode(':', $parts[1]), 2, '');
        $port = (int) hexdec($hexPort);
        if ($port <= 0) {
            continue;
        }

        $rows[] = ['address' => hexToAddress($hexAddress), 'port' => $port];
    }

    return $rows;
}

function hexToAddress(string $hex): string
{
    if (strlen($hex) === 8) {
        // Little-endian: 0100007F is 127.0.0.1.
        $bytes = array_reverse(str_split($hex, 2));
        return implode('.', array_map('hexdec', $bytes));
    }

    if (strlen($hex) === 32) {
        // Each 32-bit word is little-endian; the words themselves are in order.
        $words = str_split($hex, 8);
        $parts = [];
        foreach ($words as $word) {
            $reversed = implode('', array_reverse(str_split($word, 2)));
            $parts[] = substr($reversed, 0, 4);
            $parts[] = substr($reversed, 4, 4);
        }
        $packed = @inet_pton(implode(':', $parts));
        return $packed === false
            ? implode(':', $parts)
            : (string) inet_ntop($packed);
    }

    return $hex;
}

/**
 * What usually answers on a port.
 *
 * Deliberately phrased as "usually" throughout. A port number is a convention,
 * not a fact about what is listening - anything may bind 443 - so this names the
 * expectation and the process column names the reality. Where they disagree,
 * that disagreement is the interesting thing, and stating the port's service as
 * though it were established would hide exactly that case.
 */
function portService(int $port, string $protocol = 'tcp'): array
{
    static $known = [
        20 => ['FTP data', 'The data channel for a classic FTP transfer.'],
        21 => ['FTP', 'Unencrypted by default. Usually replaced by SFTP.'],
        22 => [
            'SSH',
            'Shell access and SFTP. The most probed port on the internet.',
        ],
        25 => [
            'SMTP',
            'Mail between servers. An open one gets abused within hours.',
        ],
        53 => [
            'DNS',
            'Name service. Both UDP and TCP; UDP carries almost all of it, ' .
            'and an authoritative server here will see far more traffic than anything else.',
        ],
        80 => ['HTTP', 'Web, unencrypted. Usually a redirect to 443.'],
        110 => ['POP3', 'Mail collection, legacy.'],
        123 => [
            'NTP',
            'Clock synchronisation. Amplification-attack bait if open to the world.',
        ],
        143 => ['IMAP', 'Mail collection.'],
        443 => ['HTTPS', 'Web, encrypted. The busiest port on most web hosts.'],
        465 => ['SMTPS', 'Mail submission over TLS.'],
        587 => ['Mail submission', 'What mail clients use to send.'],
        993 => ['IMAPS', 'IMAP over TLS.'],
        995 => ['POP3S', 'POP3 over TLS.'],
        3306 => [
            'MySQL / MariaDB',
            'Should almost never be reachable from off the machine.',
        ],
        5432 => [
            'PostgreSQL',
            'Should almost never be reachable from off the machine.',
        ],
        6379 => [
            'Redis',
            'No authentication by default. Public exposure is a compromise.',
        ],
        8000 => [
            'HTTP alternate',
            'Often a development or application server.',
        ],
        8080 => [
            'HTTP alternate',
            'Often a proxy, an app server, or a control panel.',
        ],
        8443 => ['HTTPS alternate', 'Often a control panel.'],
        9000 => ['PHP-FPM or app server', 'FastCGI, usually local only.'],
        11211 => [
            'Memcached',
            'No authentication. A famous amplification vector when public.',
        ],
        27017 => [
            'MongoDB',
            'Should almost never be reachable from off the machine.',
        ],
        // cPanel and WHM, which is what this is most likely running on.
        2077 => ['cPanel WebDisk', ''],
        2078 => ['cPanel WebDisk (TLS)', ''],
        2082 => ['cPanel', 'Control panel, unencrypted.'],
        2083 => ['cPanel (TLS)', 'Control panel.'],
        2086 => ['WHM', 'Server administration, unencrypted.'],
        2087 => ['WHM (TLS)', 'Server administration.'],
        2095 => ['Webmail', ''],
        2096 => ['Webmail (TLS)', ''],
        3000 => [
            'Application server',
            'Node, Rails and Forgejo all default here.',
        ],
        // Common self-hosted things.
        8006 => ['Proxmox', ''],
        9090 => ['Cockpit or Prometheus', ''],
    ];

    if (isset($known[$port])) {
        return [
            'name' => $known[$port][0],
            'note' => $known[$port][1],
            'known' => true,
        ];
    }

    if ($port >= 49152) {
        return [
            'name' => 'ephemeral range',
            'note' =>
                'Ports above 49151 are handed out to outgoing connections. A listener ' .
                'here is unusual and worth identifying.',
            'known' => false,
        ];
    }

    return ['name' => 'unrecognised', 'note' => '', 'known' => false];
}

/**
 * Who is connected right now, grouped by peer.
 *
 * Grouped because the useful shape is "one address holds nine hundred sockets",
 * which a flat list of nine hundred rows states and does not show. Local
 * addresses are grouped by port instead, since that is what says which service
 * is carrying the load.
 *
 * @return array{peers: list<array>, ports: list<array>, total: int, states: array<string,int>, source: string, reason: string}
 */
function connectionSummary(): array
{
    $out = shellRead('ss -Htn state connected 2>/dev/null');
    if ($out === null) {
        return [
            'peers' => [],
            'ports' => [],
            'total' => 0,
            'states' => [],
            'source' => 'none',
            'reason' =>
                'ss could not be run here, so established connections cannot be listed. ' .
                '/proc/net/tcp has the same information but not the process behind it.',
        ];
    }

    $peers = [];
    $ports = [];
    $total = 0;

    foreach (preg_split('/\R/', trim($out)) ?: [] as $line) {
        $parts = preg_split('/\s+/', trim($line)) ?: [];
        if (count($parts) < 4) {
            continue;
        }

        $local = $parts[count($parts) - 2];
        $peer = $parts[count($parts) - 1];

        $localPort = (int) substr($local, (int) strrpos($local, ':') + 1);
        $peerAddress = trim(substr($peer, 0, (int) strrpos($peer, ':')), '[]');

        if ($localPort <= 0 || $peerAddress === '') {
            continue;
        }

        $total++;
        $peers[$peerAddress] = ($peers[$peerAddress] ?? 0) + 1;
        $ports[$localPort] = ($ports[$localPort] ?? 0) + 1;
    }

    arsort($peers);
    arsort($ports);

    return [
        'peers' => array_map(
            fn(string $address, int $count): array => [
                'address' => $address,
                'connections' => $count,
                'private' => addressIsPrivate($address),
            ],
            array_keys(array_slice($peers, 0, CAPTURE_KEEP, true)),
            array_values(array_slice($peers, 0, CAPTURE_KEEP, true)),
        ),
        'ports' => array_map(
            fn(int $port, int $count): array => [
                'port' => $port,
                'connections' => $count,
                'service' => portService($port),
            ],
            array_keys(array_slice($ports, 0, CAPTURE_KEEP, true)),
            array_values(array_slice($ports, 0, CAPTURE_KEEP, true)),
        ),
        'total' => $total,
        'states' => [],
        'source' => 'ss',
        'reason' => '',
    ];
}

/** Whether an address is one nobody routes, so a busy one is a neighbour. */
function addressIsPrivate(string $address): bool
{
    if (str_contains($address, ':')) {
        return str_starts_with($address, 'fe80') ||
            str_starts_with($address, 'fc') ||
            str_starts_with($address, 'fd') ||
            $address === '::1';
    }

    return str_starts_with($address, '10.') ||
        str_starts_with($address, '192.168.') ||
        str_starts_with($address, '127.') ||
        (bool) preg_match('/^172\.(1[6-9]|2\d|3[01])\./', $address);
}

/* ------------------------------------------------------- packet sampling -- */

/**
 * The addresses this machine answers on.
 *
 * Needed to tell inbound from outbound, which is the difference between a
 * useful capture report and a useless one. Without it the local address turns
 * up in ~100% of packets and sits at the top of "busiest addresses" telling you
 * nothing, while the shares add to well over 100% because both ends of every
 * packet are counted.
 *
 * @return list<string>
 */
function localAddresses(): array
{
    $out = [];

    $ip = whichBinary('ip');
    if ($ip !== null) {
        $lines = shellRead(escapeshellarg($ip) . ' -o addr show 2>/dev/null');
        foreach (preg_split('/\R/', (string) $lines) ?: [] as $line) {
            if (preg_match('/\binet6?\s+([0-9a-f.:]+)/i', $line, $m)) {
                $out[] = $m[1];
            }
        }
    }

    if ($out === []) {
        // Nothing exotic: the loopback addresses are always ours, and anything
        // else we cannot determine is handled by the fallback in the summariser.
        $out = ['127.0.0.1', '::1'];
    }

    return array_values(array_unique($out));
}

/**
 * A bounded look at what is actually on the wire.
 *
 * This is the tool of last resort and it is treated as one. Everything above is
 * free and continuous; this costs a real capture on a live interface, needs
 * root, and is therefore run only when somebody asks for it and only for as
 * long as they asked.
 *
 * Counters are kept as packets arrive rather than by collecting the output and
 * summarising it afterwards. That is what makes a sixty-second sample of a busy
 * link possible: an earlier version read everything into memory and needed a
 * twenty-thousand packet ceiling to stay safe, which a busy server hits in
 * fifteen seconds - so the "sample" was the first fifteen seconds of the minute
 * and the rest of the period was never looked at.
 *
 * What is kept is counts: how many packets and bytes, in which direction,
 * between which addresses, on which ports, with which TCP flags, and for DNS
 * the names asked for. No payload is stored. A monitor that quietly wrote other
 * people's traffic into a file on a shared host would be a far worse problem
 * than whatever it was pointed at.
 *
 * `$readAsked` relaxes that, and only that. With it on, tcpdump prints packet
 * contents and this reads three things out of them: the name in a TLS
 * handshake, and the Host and path of an HTTP request. Those are counted like
 * everything else and the packet is discarded - still no payload stored - but
 * it does mean the process has looked at the beginning of other people's
 * traffic, so it is off unless somebody asks for it on one capture, and the
 * page says so in those words. Query strings are dropped before counting.
 *
 * @return array<string, mixed>
 */
function captureTraffic(
    string $interface,
    int $seconds,
    string $filter = '',
    ?array $local = null,
    bool $readAsked = false,
): array {
    $seconds = max(5, min(CAPTURE_MAX_SECONDS, $seconds));

    if (!preg_match('/^[A-Za-z0-9._:-]{1,32}$/', $interface)) {
        return captureFailure('That is not a valid interface name.');
    }

    $expression = captureFilter($filter);
    if ($expression === null) {
        return captureFailure(
            'That capture filter is not one of the ones offered. Filters are chosen from a ' .
                'fixed list rather than typed, because a tcpdump expression is passed to a ' .
                'program running as root and there is no safe way to accept an arbitrary one.',
        );
    }

    $tcpdump = whichBinary('tcpdump');
    if ($tcpdump === null) {
        return captureFailure(
            'tcpdump is not installed, or is not on this PATH.',
        );
    }

    if (!function_exists('popen')) {
        return captureFailure(
            'popen is disabled in this PHP, so a capture cannot be read.',
        );
    }

    $timeout = whichBinary('timeout');
    /*
     * Two shapes of the same command.
     *
     * Without payload reading: quiet output and a 128-byte snaplen, which is
     * headers and nothing more. With it: `-A` to print contents and 512 bytes,
     * which is enough to reach a TLS server name and an HTTP Host header and
     * not much more. `-q` goes, because it suppresses the protocol detail the
     * ASCII sits beside.
     */
    $command = sprintf(
        '%s%s -nn %s -l -s %d -c %d -i %s %s 2>&1',
        $timeout === null
            ? ''
            : escapeshellarg($timeout) . ' ' . ($seconds + 3) . ' ',
        escapeshellarg($tcpdump),
        $readAsked ? '-A' : '-q',
        $readAsked ? 512 : 128,
        CAPTURE_MAX_PACKETS,
        escapeshellarg($interface),
        $expression['argument'] === ''
            ? ''
            : escapeshellarg($expression['argument']),
    );

    $local ??= localAddresses();
    $started = microtime(true);

    $handle = @popen($command, 'r');
    if ($handle === false) {
        return captureFailure('The capture could not be started.');
    }

    $state = newCaptureState($local, $readAsked);
    $complaints = [];

    while (($line = fgets($handle)) !== false) {
        $line = rtrim($line, "\r\n");
        if ($line === '') {
            continue;
        }

        if (
            str_starts_with($line, 'tcpdump:') ||
            str_starts_with($line, 'listening on')
        ) {
            $complaints[] = $line;
            continue;
        }

        countPacket($state, $line);

        // A second guard on the clock: `timeout` may be missing, and a filter
        // that matches nothing would otherwise block here for ever.
        if (
            $state['packets'] % 512 === 0 &&
            microtime(true) - $started > $seconds + 5
        ) {
            break;
        }
    }
    pclose($handle);

    $elapsed = round(microtime(true) - $started, 1);

    if ($state['packets'] === 0) {
        /*
         * tcpdump's own complaints look like an empty capture.
         *
         * "You don't have permission" and "No such device" arrive on stderr,
         * folded into the same stream - and an empty summary from a failed
         * command reads as "no traffic", which is the opposite of the truth.
         */
        $said = implode(' ', $complaints);
        foreach (
            [
                'permission' =>
                    'This needs root: tcpdump could not open the interface.',
                'No such device' =>
                    'There is no interface by that name on this machine.',
                'not found' => 'tcpdump is not installed here.',
            ]
            as $needle => $because
        ) {
            if (stripos($said, $needle) !== false) {
                return captureFailure($because);
            }
        }

        return captureFailure(
            sprintf(
                'Nothing matched in %ss. The interface is up and the filter caught no packets, ' .
                    'which for a narrow filter on a quiet link is an ordinary answer.',
                $elapsed,
            ),
        );
    }

    $summary = finishCapture($state);
    $summary['ok'] = true;
    $summary['reason'] = '';
    $summary['interface'] = $interface;
    $summary['seconds'] = $elapsed;
    $summary['requested_seconds'] = $seconds;
    $summary['filter'] = $expression['key'];
    $summary['filter_label'] = $expression['label'];
    $summary['hit_packet_limit'] = $summary['packets'] >= CAPTURE_MAX_PACKETS;
    $summary['local'] = $state['local'];

    return $summary;
}

function captureFailure(string $reason): array
{
    return [
        'ok' => false,
        'reason' => $reason,
        'packets' => 0,
        'bytes' => 0,
        'peers' => [],
        'ports' => [],
        'names' => [],
        'flags' => [],
        'asked' => [],
        'visited' => [],
        'read_asked' => false,
        'in_packets' => 0,
        'out_packets' => 0,
        'in_bytes' => 0,
        'out_bytes' => 0,
        'unparsed' => 0,
        'distinct_peers' => 0,
        'distinct_names' => 0,
        'seconds' => 0,
        'interface' => '',
        'filter' => '',
        'filter_label' => '',
        'hit_packet_limit' => false,
        'requested_seconds' => 0,
        'local' => [],
    ];
}

/** The counters a capture accumulates, one packet at a time. */
function newCaptureState(array $local, bool $readAsked = false): array
{
    return [
        'local' => array_flip($local),
        'read_asked' => $readAsked,
        'seen' => [],
        'peers' => [],
        'ports' => [],
        'names' => [],
        'flags' => [],
        // name => count, by DNS record type, and the hosts and pages read out
        // of TLS and HTTP when that was asked for.
        'asked' => [],
        'visited' => [],
        'packets' => 0,
        'bytes' => 0,
        'in_packets' => 0,
        'out_packets' => 0,
        'in_bytes' => 0,
        'out_bytes' => 0,
        'unparsed' => 0,
    ];
}

/**
 * One line of tcpdump output, folded into the counters.
 *
 * Parsed with deliberately loose patterns. tcpdump's format varies by version,
 * by protocol and by whether `-q` is in effect, and a strict parser that
 * silently matched nothing would report a quiet network on a machine falling
 * over. Anything unrecognised is counted as a packet and not attributed, and
 * that remainder is reported so the arithmetic can be checked.
 */
function countPacket(array &$state, string $line): void
{
    /*
     * A packet header, or the contents of the packet before it?
     *
     * With `-A` in effect tcpdump writes the header line and then the payload
     * on the lines after it, and the two need completely different treatment: a
     * payload line is not a packet and counting it as one inflates every total
     * on the page. It also cannot be parsed for addresses, so the first version
     * counted every one of them as an unparsed packet and returned before ever
     * looking at the contents - which is where the whole point of reading them
     * was supposed to happen.
     */
    if (!preg_match('/\s(\S+?)\.(\d+) > (\S+?)\.(\d+):/', $line, $m)) {
        if (!empty($state['read_asked'])) {
            countPayload($state, $line);
            return;
        }

        $state['packets']++;
        $state['unparsed']++;
        return;
    }

    $state['packets']++;

    [, $source, $sourcePort, $destination, $destinationPort] = $m;

    /*
     * Bytes: the length tcpdump reports, which is payload rather than frame.
     *
     * `-q` writes "tcp 517" for TCP and "(29)" for UDP and DNS. Neither counts
     * headers, so these totals are a floor on what crossed the wire - stated,
     * rather than quietly inflated by a guess at header size.
     */
    $bytes = 0;
    if (preg_match('/\blength (\d+)/', $line, $size)) {
        // The usual form, and the one TCP uses once flags are being printed.
        $bytes = (int) $size[1];
    } elseif (preg_match('/\b(?:tcp|UDP|udp)\s+(\d+)/', $line, $size)) {
        // `-q` without flags: "tcp 517".
        $bytes = (int) $size[1];
    } elseif (preg_match('/\((\d+)\)\s*$/', $line, $size)) {
        // DNS and other UDP, which carry the length in brackets at the end.
        $bytes = (int) $size[1];
    }
    $state['bytes'] += $bytes;

    /*
     * Which end is ours, and therefore which way this packet went.
     *
     * The whole point of the report: without this the local address appears in
     * every packet and tops the table telling you nothing, and the shares add
     * past 100% because both ends are counted. When neither end is recognised -
     * a machine whose addresses could not be read - the packet is counted and
     * left undirected rather than being attributed to a guess.
     */
    $sourceIsLocal = isset($state['local'][$source]);
    $destinationIsLocal = isset($state['local'][$destination]);

    if ($sourceIsLocal === $destinationIsLocal) {
        // Both or neither: local-to-local traffic, or addresses we do not know.
        $state['seen'][$source] = ($state['seen'][$source] ?? 0) + 1;
        $state['seen'][$destination] = ($state['seen'][$destination] ?? 0) + 1;
    } else {
        $remote = $sourceIsLocal ? $destination : $source;
        $inbound = $destinationIsLocal;

        if (!isset($state['peers'][$remote])) {
            $state['peers'][$remote] = [
                'in_packets' => 0,
                'out_packets' => 0,
                'in_bytes' => 0,
                'out_bytes' => 0,
                'opened' => 0,
            ];
        }

        if ($inbound) {
            $state['peers'][$remote]['in_packets']++;
            $state['peers'][$remote]['in_bytes'] += $bytes;
            $state['in_packets']++;
            $state['in_bytes'] += $bytes;
        } else {
            $state['peers'][$remote]['out_packets']++;
            $state['peers'][$remote]['out_bytes'] += $bytes;
            $state['out_packets']++;
            $state['out_bytes'] += $bytes;
        }
    }

    /*
     * The service port, which is the lower of the two.
     *
     * One side of every conversation is an ephemeral port picked at random, and
     * counting those produces thousands of ports each seen once.
     */
    $port = min((int) $sourcePort, (int) $destinationPort);
    if (!isset($state['ports'][$port])) {
        $state['ports'][$port] = ['packets' => 0, 'bytes' => 0];
    }
    $state['ports'][$port]['packets']++;
    $state['ports'][$port]['bytes'] += $bytes;

    /*
     * TCP flags, which say what *kind* of busy this is.
     *
     * Mostly ACK and PSH+ACK is established traffic doing work - a large clone,
     * a busy API. Mostly SYN from many changing addresses is something else
     * entirely: scanning, or a flood. The packet rate is identical in both
     * cases and the flags are the only thing that tells them apart.
     */
    if (preg_match('/Flags \[([^\]]+)\]/', $line, $f)) {
        $flag = captureFlagName($f[1]);
        $state['flags'][$flag] = ($state['flags'][$flag] ?? 0) + 1;
        if ($flag === 'SYN') {
            $remote = $sourceIsLocal ? $destination : $source;
            if (isset($state['peers'][$remote])) {
                $state['peers'][$remote]['opened']++;
            }
        }
    }

    /*
     * What was actually asked for, which is the only part anybody reads.
     *
     * "748 packets on 443" is true and answers nothing; the name in the
     * handshake answers the question that was being asked.
     */
    $question = captureDnsQuestion($line);
    if ($question !== null) {
        $state['names'][$question['name']] =
            ($state['names'][$question['name']] ?? 0) + 1;

        $key = $question['type'] . ' ' . $question['name'];
        $state['asked'][$key] = ($state['asked'][$key] ?? 0) + 1;
    }
}

/**
 * The contents of a packet, when contents are being read.
 *
 * Only ever called for lines that are not packet headers, which matters more
 * than it sounds: a DNS summary line contains a hostname, and running the TLS
 * matcher over it counted every DNS question a second time as a server name.
 * Eighty TLS connections to a host that had been looked up eighty times, and
 * the number was the number of lookups.
 */
function countPayload(array &$state, string $line): void
{
    /*
     * HTTP first, and if it is HTTP then it is not a TLS handshake.
     *
     * Both matchers looked at every payload line, so an HTTP request was
     * reported twice: once correctly as a page, and once as a TLS connection to
     * its own Host header. A request for a file was reported a third time, as a
     * connection to "thumb.png".
     */
    $http = captureHttpRequest($line);

    if ($http !== null) {
        if ($http['host'] !== null) {
            $key = 'http ' . $http['host'];
            $state['visited'][$key] = ($state['visited'][$key] ?? 0) + 1;
        }
        if ($http['path'] !== null) {
            $key = 'page ' . $http['path'];
            $state['visited'][$key] = ($state['visited'][$key] ?? 0) + 1;
        }
        return;
    }

    $tls = captureTlsName($line);
    if ($tls !== null) {
        $key = 'https ' . $tls;
        $state['visited'][$key] = ($state['visited'][$key] ?? 0) + 1;
    }
}

/* ------------------------------------------------ what it was asking for -- */

/**
 * Reading what the traffic was actually asking for.
 *
 * A capture without this counts packets and addresses: "748 packets on 443" is
 * true and answers almost nothing. What somebody wants is which name, which
 * page. Three things carry that in the clear, and only three:
 *
 *   DNS       the question is the name, and tcpdump prints it even in quiet
 *             mode. This is free and is on by default.
 *   TLS SNI   a ClientHello names the host it wants before any encryption
 *             starts. Needs the payload.
 *   HTTP      the request line and the Host header. Needs the payload.
 *
 * BE CLEAR ABOUT THE COST. The last two need tcpdump to print packet contents
 * (`-A`) with a snaplen big enough to reach them, and that means this reads the
 * first few hundred bytes of other people's traffic on a shared machine. That
 * is why it is off unless somebody turns it on for one capture, why the page
 * says so in those words, and why the query string is thrown away: a path says
 * which page, and a query string is where tokens, session ids and search terms
 * live. "Which page" is the question that was asked; the rest is not needed to
 * answer it and is not kept.
 *
 * Nothing here decrypts anything. An SNI is sent in the clear by design, and a
 * request to an https:// address shows only the name.
 */

/** Longest thing this will treat as a hostname or path. */
const CAPTURE_ASKED_MAX = 180;

/**
 * The DNS question on a line, if there is one.
 *
 * tcpdump writes `A? name.example.` and `AAAA? name.example.`; the record type
 * is worth keeping because a flood of AAAA lookups against a host with no IPv6
 * is a different fault from a flood of A lookups, and they are identical when
 * the type is discarded.
 *
 * @return array{name: string, type: string}|null
 */
function captureDnsQuestion(string $line): ?array
{
    if (
        !preg_match(
            '/\s(A|AAAA|NS|MX|TXT|SOA|PTR|CNAME|SRV|CAA|DS|DNSKEY|ANY)\?\s+([A-Za-z0-9._-]+)/',
            $line,
            $m,
        )
    ) {
        return null;
    }

    $name = strtolower(rtrim($m[2], '.'));

    return $name === '' ? null : ['name' => $name, 'type' => $m[1]];
}

/**
 * The host a TLS handshake asked for, from the ASCII of a ClientHello.
 *
 * Not a TLS parser. tcpdump's `-A` gives printable bytes with dots for the
 * rest, so the structure is gone and only the string survives - and the SNI is
 * a run of hostname characters that no other part of a handshake looks like.
 * Anchored on a plausible domain shape rather than on an offset, which is the
 * only thing that survives the mangling.
 *
 * Returns null far more often than it returns a name, and that is correct: only
 * the first packet of a connection carries one.
 */
function captureTlsName(string $line): ?string
{
    /*
     * A ClientHello starts with 0x16 0x03 - "handshake, TLS" - which prints as
     * an unprintable byte then a dot. Checking for the string instead: the
     * hostname is preceded by the SNI extension header, whose length bytes are
     * unprintable, so the name appears as a run bounded by dots.
     */
    if (!str_contains($line, '.') || strlen($line) < 16) {
        return null;
    }

    /*
     * A dot must be followed by a letter or digit to be part of a name.
     *
     * That one rule is what makes this work at all, and leaving it out is what
     * made the first version match nothing useful. tcpdump prints every
     * unprintable byte as a dot, so a hostname in this output is nearly always
     * surrounded by them - `..git.lewismoten.com..` - and a pattern that treats
     * a dot as a name character either swallows the padding or refuses to start
     * because the character before is a dot.
     */
    if (
        !preg_match_all(
            '/[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?){1,6}/i',
            $line,
            $matches,
        )
    ) {
        return null;
    }

    foreach ($matches[0] as $candidate) {
        $name = strtolower(rtrim($candidate, '.'));

        /*
         * The last label has to look like a top-level domain.
         *
         * Without this, version strings and file names - "1.2.3", "3.1" - come
         * through as hosts, and this output is full of both.
         */
        if (!preg_match('/\.[a-z]{2,24}$/', $name)) {
            continue;
        }

        /*
         * The protocol list, which sits right beside the name it is not.
         *
         * A ClientHello advertises ALPN - "h2", "http/1.1", "spdy/3.1" - a few
         * bytes from the server name, so "h2.http" reads as a perfectly
         * well-formed hostname and was being reported as one on every single
         * TLS connection. These are the tokens that actually appear there.
         */
        if (
            preg_match('/^(?:h2|h3|http|spdy|hq|quic)\b/', $name) ||
            str_contains($name, 'http/')
        ) {
            continue;
        }

        /*
         * A file name is not a host name.
         *
         * "thumb.png" and "app.min.js" are perfectly well-formed by every rule
         * above, and payload is full of them - so the last label is checked
         * against the extensions that turn up rather than against a list of
         * every real top-level domain, which would be a list to maintain.
         */
        if (
            preg_match(
                '/\.(?:png|jpe?g|gif|webp|svg|ico|css|js|mjs|json|xml|html?|txt|woff2?|' .
                    'ttf|eot|map|pdf|zip|gz|mp4|webm|mp3|wav|php|asp|aspx|jsp)$/',
                $name,
            )
        ) {
            continue;
        }

        /*
         * A dotted quad is an address, not a name.
         *
         * They turn up constantly in this output and would otherwise be
         * reported as the host somebody asked for.
         */
        if (filter_var($name, FILTER_VALIDATE_IP) !== false) {
            continue;
        }
        if (strlen($name) > CAPTURE_ASKED_MAX) {
            continue;
        }

        return $name;
    }

    return null;
}

/**
 * The page an HTTP request asked for, from the payload.
 *
 * The request line and the Host header arrive in the same packet often enough
 * to be worth pairing, and separately often enough that each is reported on its
 * own rather than waiting for both.
 *
 * The query string is cut off. A path answers "which page"; a query string is
 * where session ids, tokens and search terms live, and none of that is needed
 * to answer the question that was asked.
 *
 * @return array{host: ?string, path: ?string}|null
 */
function captureHttpRequest(string $line): ?array
{
    $out = ['host' => null, 'path' => null];

    if (
        preg_match(
            '#\b(GET|POST|HEAD|PUT|DELETE|PATCH|OPTIONS)\s+(/[^\s?]*)#',
            $line,
            $m,
        )
    ) {
        $out['path'] = $m[1] . ' ' . substr($m[2], 0, CAPTURE_ASKED_MAX);
    }

    /*
     * The same rule as the TLS name: a dot has to be followed by a character.
     *
     * tcpdump renders the CRLF between headers as "..", and a pattern treating
     * a dot as a name character ran straight through it and into the next
     * header - "radio.kognabo.com..user-agent" was reported as a hostname.
     */
    if (
        preg_match(
            '/\bHost:\s*([A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*)/i',
            $line,
            $m,
        )
    ) {
        $host = strtolower(rtrim($m[1], '.'));
        if ($host !== '' && strlen($host) <= CAPTURE_ASKED_MAX) {
            $out['host'] = $host;
        }
    }

    return $out['host'] === null && $out['path'] === null ? null : $out;
}

/** tcpdump's flag letters, named for what they mean. */
function captureFlagName(string $letters): string
{
    $syn = str_contains($letters, 'S');
    $ack = str_contains($letters, '.');
    $push = str_contains($letters, 'P');

    if (str_contains($letters, 'R')) {
        return 'RST';
    }
    if (str_contains($letters, 'F')) {
        return 'FIN';
    }
    if ($syn && $ack) {
        return 'SYN+ACK';
    }
    if ($syn) {
        return 'SYN';
    }
    if ($push) {
        return 'PSH+ACK';
    }
    if ($ack) {
        return 'ACK';
    }
    return 'other';
}

/**
 * The capture filters on offer, and what each is for.
 *
 * A fixed list rather than a text box. The expression is handed to a program
 * running as root, and while escapeshellarg would stop a shell escape,
 * tcpdump's own filter language can itself open files (`-F`) and is not
 * something to accept from a web form. Every one of these is a question
 * somebody actually asks when a machine is being hammered.
 *
 * @return array{key: string, label: string, argument: string, about: string}|null
 */
function captureFilter(string $key): ?array
{
    foreach (captureFilters() as $filter) {
        if (
            $filter['key'] === $key ||
            ($key === '' && $filter['key'] === 'all')
        ) {
            return $filter;
        }
    }
    return null;
}

/** @return list<array{key: string, label: string, argument: string, about: string}> */
function captureFilters(): array
{
    return [
        [
            'key' => 'all',
            'label' => 'Everything',
            'argument' => '',
            'about' =>
                'Every packet crossing the interface. The right place to start when ' .
                'you do not yet know what is busy - and the only one whose port breakdown ' .
                'is a share of the whole, rather than of whatever the filter let through.',
        ],
        [
            'key' => 'dns',
            'label' => 'DNS (port 53)',
            'argument' => 'port 53',
            'about' =>
                'Both UDP and TCP. Dramatic to watch scrolling past and very often a ' .
                'small fraction of the actual load - which is worth knowing before you go ' .
                'looking at the nameserver.',
        ],
        [
            'key' => 'dns-udp',
            'label' => 'DNS over UDP only',
            'argument' => 'udp port 53',
            'about' =>
                'Almost all real query traffic. Separating it from TCP tells a flood ' .
                'of ordinary queries apart from zone transfers.',
        ],
        [
            'key' => 'https',
            'label' => 'HTTPS (port 443)',
            'argument' => 'tcp port 443',
            'about' =>
                'Web traffic in both directions. On a machine hosting anything at all ' .
                'this is usually where the packets actually are.',
        ],
        [
            'key' => 'https-in',
            'label' => 'HTTPS arriving here',
            'argument' => 'tcp dst port 443',
            'about' =>
                'Only packets addressed to this machine\'s web port, so the counts are ' .
                'what clients are sending rather than what is being served back. This is the ' .
                'one that names who is generating the load.',
        ],
        [
            'key' => 'https-syn',
            'label' => 'New HTTPS connections only',
            'argument' => 'tcp dst port 443 and tcp[tcpflags] & tcp-syn != 0',
            'about' =>
                'Only the opening packet of each connection, so the count is "how many ' .
                'connections are being attempted" rather than "how much data". Many from one ' .
                'address is a busy client; a few each from hundreds of changing addresses is ' .
                'scanning or a flood.',
        ],
        [
            'key' => 'mail',
            'label' => 'Mail (25, 465, 587)',
            'argument' => 'port 25 or port 465 or port 587',
            'about' =>
                'A compromised account sending spam shows up here long before it shows ' .
                'up in the disk figures.',
        ],
        [
            'key' => 'web',
            'label' => 'Web (80 and 443)',
            'argument' => 'port 80 or port 443',
            'about' => 'Both web ports together.',
        ],
    ];
}

/** The accumulated counters, sorted and trimmed for storage. */
function finishCapture(array $state): array
{
    $peers = $state['peers'];

    /*
     * A fallback when no address could be recognised as ours.
     *
     * `ip addr` can be missing, and a capture on an interface whose addresses
     * we could not read would otherwise produce no peers at all. The address
     * appearing in nearly every packet is this machine, so it is treated as
     * local and everything else becomes a peer - stated in the result, because
     * it is an inference rather than a reading.
     */
    $inferred = null;
    if ($peers === [] && $state['seen'] !== []) {
        arsort($state['seen']);
        $top = array_key_first($state['seen']);
        if ($state['seen'][$top] >= $state['packets'] * 0.5) {
            $inferred = $top;
            foreach ($state['seen'] as $address => $count) {
                if ($address === $top) {
                    continue;
                }
                $peers[$address] = [
                    'in_packets' => $count,
                    'out_packets' => 0,
                    'in_bytes' => 0,
                    'out_bytes' => 0,
                    'opened' => 0,
                ];
            }
        }
    }

    uasort(
        $peers,
        fn(array $a, array $b): int => $b['in_bytes'] + $b['out_bytes'] <=>
        $a['in_bytes'] + $a['out_bytes'] ?:
        $b['in_packets'] + $b['out_packets'] <=>
            $a['in_packets'] + $a['out_packets'],
    );

    $peerRows = [];
    foreach (
        array_slice($peers, 0, CAPTURE_KEEP, true)
        as $address => $counts
    ) {
        $peerRows[] = [
            'address' => (string) $address,
            'packets' => $counts['in_packets'] + $counts['out_packets'],
            'in_packets' => $counts['in_packets'],
            'out_packets' => $counts['out_packets'],
            'in_bytes' => $counts['in_bytes'],
            'out_bytes' => $counts['out_bytes'],
            'opened' => $counts['opened'],
            'private' => addressIsPrivate((string) $address),
        ];
    }

    uasort(
        $state['ports'],
        fn(array $a, array $b): int => $b['packets'] <=> $a['packets'],
    );
    $portRows = [];
    foreach (
        array_slice($state['ports'], 0, CAPTURE_KEEP, true)
        as $port => $counts
    ) {
        $portRows[] = [
            'port' => (int) $port,
            'packets' => $counts['packets'],
            'bytes' => $counts['bytes'],
            'service' => portService((int) $port),
        ];
    }

    arsort($state['names']);
    $nameRows = [];
    foreach (
        array_slice($state['names'], 0, CAPTURE_KEEP, true)
        as $name => $count
    ) {
        $nameRows[] = ['name' => (string) $name, 'packets' => $count];
    }

    arsort($state['flags']);

    /*
     * The rollup: what was asked for, most first.
     *
     * Split into the DNS questions and what was read from the traffic itself,
     * because they are answers to different questions and have very different
     * costs - one is free and always on, the other means having looked at
     * packet contents.
     */
    arsort($state['asked']);
    $askedRows = [];
    foreach (
        array_slice($state['asked'], 0, CAPTURE_KEEP, true)
        as $key => $count
    ) {
        [$type, $name] = explode(' ', $key, 2);
        $askedRows[] = ['kind' => $type, 'name' => $name, 'packets' => $count];
    }

    arsort($state['visited']);
    $visitedRows = [];
    foreach (
        array_slice($state['visited'], 0, CAPTURE_KEEP, true)
        as $key => $count
    ) {
        [$how, $what] = explode(' ', $key, 2);
        $visitedRows[] = ['kind' => $how, 'name' => $what, 'packets' => $count];
    }

    return [
        'packets' => $state['packets'],
        'bytes' => $state['bytes'],
        'in_packets' => $state['in_packets'],
        'out_packets' => $state['out_packets'],
        'in_bytes' => $state['in_bytes'],
        'out_bytes' => $state['out_bytes'],
        'unparsed' => $state['unparsed'],
        'peers' => $peerRows,
        'ports' => $portRows,
        'names' => $nameRows,
        'asked' => $askedRows,
        'visited' => $visitedRows,
        'read_asked' => (bool) ($state['read_asked'] ?? false),
        'flags' => $state['flags'],
        'distinct_peers' => count($peers),
        'distinct_names' => count($state['names']),
        'inferred_local' => $inferred,
    ];
}

/**
 * An absolute path to a binary, searched rather than assumed.
 *
 * The standard sbin directories first, because tcpdump and ss live there and a
 * web server's PATH almost never includes them - which is the difference
 * between "not installed" and "not on the four directories Apache starts with".
 */
function whichBinary(string $name): ?string
{
    if (!preg_match('/^[a-z0-9_-]{1,32}$/i', $name)) {
        return null;
    }

    foreach (
        [
            '/usr/sbin/',
            '/sbin/',
            '/usr/bin/',
            '/bin/',
            '/usr/local/sbin/',
            '/usr/local/bin/',
            '/opt/homebrew/bin/',
        ]
        as $dir
    ) {
        if (@is_executable($dir . $name)) {
            return $dir . $name;
        }
    }

    $found = shellRead('command -v ' . escapeshellarg($name) . ' 2>/dev/null');
    $found = $found === null ? '' : trim($found);
    return $found !== '' && @is_executable($found) ? $found : null;
}

/* ------------------------------------------------------------------ DNS -- */

/**
 * Whether name service on this machine actually answers, and how fast.
 *
 * Worth having separately from the packet counts because they answer different
 * questions. The capture says how much DNS traffic there is; this says whether
 * any of it is being answered correctly - and a nameserver that is busy *and*
 * answering is a completely different situation from one that is busy and
 * timing out.
 *
 * Every query is bounded and asked once. This is a diagnostic, not a monitor:
 * running it on a schedule would add this machine's own traffic to the thing
 * being measured.
 *
 * @param list<array{name: string, type: string, server: ?string, why: string}> $queries
 * @return array{available: bool, reason: string, rows: list<array>}
 */
function dnsChecks(array $queries, ?callable $runner = null): array
{
    $dig = whichBinary('dig');
    if ($dig === null && $runner === null) {
        return [
            'available' => false,
            'rows' => [],
            'reason' =>
                'dig is not installed here. On a Red Hat system it is in bind-utils; ' .
                'on Debian, dnsutils. Without it this cannot ask a nameserver anything.',
        ];
    }

    $rows = [];
    foreach (array_slice($queries, 0, 12) as $query) {
        $rows[] = dnsQuery($dig ?? 'dig', $query, $runner);
    }

    return ['available' => true, 'rows' => $rows, 'reason' => ''];
}

/**
 * One lookup, timed, with the answer and the failure told apart.
 *
 * Four outcomes worth distinguishing, and they are frequently confused with
 * each other: an answer, a valid "there is no such name", a server that refused
 * to answer, and no reply at all. Only the last means the nameserver is down,
 * and reporting all four as "failed" is how somebody spends an afternoon
 * restarting a daemon that was working.
 */
function dnsQuery(string $dig, array $query, ?callable $runner = null): array
{
    $name = (string) ($query['name'] ?? '');
    $type = strtoupper((string) ($query['type'] ?? 'A'));
    $server = $query['server'] ?? null;

    $row = [
        'name' => $name,
        'type' => $type,
        'server' => $server,
        'why' => (string) ($query['why'] ?? ''),
        'ok' => false,
        'status' => 'unknown',
        'answers' => [],
        'ms' => null,
        'note' => '',
    ];

    if (
        !preg_match('/^[A-Za-z0-9._-]{1,253}$/', $name) ||
        !preg_match('/^[A-Z]{1,10}$/', $type) ||
        ($server !== null &&
            !preg_match('/^[A-Za-z0-9._:-]{1,253}$/', (string) $server))
    ) {
        $row['note'] = 'That is not a name this will look up.';
        return $row;
    }

    $command = sprintf(
        '%s +tries=1 +time=3 +noall +comments +answer %s %s %s 2>&1',
        escapeshellarg($dig),
        $server === null ? '' : escapeshellarg('@' . $server),
        escapeshellarg($name),
        escapeshellarg($type),
    );

    $started = microtime(true);
    $out = $runner === null ? shellRead($command) : $runner($command);
    $row['ms'] = (int) round((microtime(true) - $started) * 1000);

    if ($out === null || trim($out) === '') {
        $row['status'] = 'no reply';
        $row['note'] =
            'Nothing came back at all. The server is unreachable, or a firewall ' .
            'is dropping the query.';
        return $row;
    }

    if (preg_match('/status:\s*([A-Z]+)/', $out, $m)) {
        $row['status'] = strtolower($m[1]);
    } elseif (
        str_contains($out, 'connection timed out') ||
        str_contains($out, 'no servers could be reached')
    ) {
        $row['status'] = 'no reply';
    }

    foreach (preg_split('/\R/', trim($out)) ?: [] as $line) {
        if ($line === '' || str_starts_with($line, ';')) {
            continue;
        }
        $parts = preg_split('/\s+/', trim($line)) ?: [];
        if (count($parts) >= 5) {
            $row['answers'][] = [
                'ttl' => (int) $parts[1],
                'type' => $parts[3],
                'value' => implode(' ', array_slice($parts, 4)),
            ];
        }
    }

    $row['ok'] = $row['status'] === 'noerror' && $row['answers'] !== [];
    $row['note'] = match ($row['status']) {
        'noerror' => $row['answers'] === []
            ? 'The server answered and knows the name, but has no record of this type.'
            : '',
        'nxdomain'
            => 'The server answered: there is no such name. That is a working ' .
            'nameserver giving a correct negative answer, not a failure.',
        'refused'
            => 'The server refused. It is running and reachable, and does not consider ' .
            'itself responsible for this name — usually a zone that is not configured, or ' .
            'recursion turned off for outside queries.',
        'servfail'
            => 'The server tried and failed. For a recursive query that often means ' .
            'upstream is broken; for an authoritative one, the zone failed to load.',
        'no reply' => $row['note'],
        default => '',
    };

    return $row;
}

/**
 * The lookups worth making by default, built from what this machine claims.
 *
 * Its own hostname against the local resolver, and against itself if it is
 * listening on 53 - because "does my nameserver answer for my own zone" is the
 * first question, and the difference between the two answers is the second.
 *
 * @return list<array{name: string, type: string, server: ?string, why: string}>
 */
function defaultDnsQueries(array $config, array $listening = []): array
{
    $host = php_uname('n');
    $servesDns = false;
    foreach ($listening as $row) {
        if ((int) ($row['port'] ?? 0) === 53) {
            $servesDns = true;
            break;
        }
    }

    $queries = [
        [
            'name' => $host,
            'type' => 'A',
            'server' => null,
            'why' =>
                'This machine\'s own name, asked the way anything else on it would ask.',
        ],
        [
            'name' => 'example.com',
            'type' => 'A',
            'server' => null,
            'why' =>
                'A name that has nothing to do with this machine, so a failure here is ' .
                'the resolver rather than the zone.',
        ],
    ];

    if ($servesDns) {
        $queries[] = [
            'name' => $host,
            'type' => 'A',
            'server' => '127.0.0.1',
            'why' =>
                'The same question asked of the nameserver running here. If this differs ' .
                'from the first answer, this machine and the rest of the world disagree ' .
                'about where it is.',
        ];
        $queries[] = [
            'name' => 'example.com',
            'type' => 'A',
            'server' => '127.0.0.1',
            'why' =>
                'Whether the nameserver here will recurse for a name it is not ' .
                'authoritative for. An authoritative-only server refusing this is correct ' .
                'and is not a fault.',
        ];
    }

    foreach ((array) ($config['network']['dns_names'] ?? []) as $extra) {
        $queries[] = [
            'name' => (string) $extra,
            'type' => 'A',
            'server' => null,
            'why' => 'From network.dns_names in the config.',
        ];
    }

    return $queries;
}

/* -------------------------------------------------------------- services -- */

/**
 * Every service unit the machine knows about, running or not.
 *
 * Distinct from the watched-daemon panel, which asks "are these particular
 * things alive". This is the whole list, and it exists for the other question:
 * what is installed, what is enabled, and what has failed - including things
 * nobody thought to watch, which is where the answer usually is.
 *
 * `failed` and `masked` are called out because they are the two states that
 * are silently wrong. A failed unit uses no memory and no CPU, so it is absent
 * from every other panel here rather than standing out in one; a masked unit
 * cannot be started at all, which is a five-minute mystery if you do not know.
 *
 * @return array{available: bool, reason: string, rows: list<array>, summary: array<string,int>}
 */
function serviceUnits(): array
{
    $systemctl = whichBinary('systemctl');
    if ($systemctl === null) {
        return [
            'available' => false,
            'rows' => [],
            'summary' => [],
            'reason' =>
                'systemctl is not on this machine, so there are no systemd units to ' .
                'list. On a container or an older init this is expected.',
        ];
    }

    $listed = shellRead(
        escapeshellarg($systemctl) .
            ' list-units --type=service --all --no-pager --no-legend --plain 2>/dev/null',
    );
    $files = shellRead(
        escapeshellarg($systemctl) .
            ' list-unit-files --type=service --no-pager --no-legend --plain 2>/dev/null',
    );

    if ($listed === null && $files === null) {
        return [
            'available' => false,
            'rows' => [],
            'summary' => [],
            'reason' =>
                'systemctl is installed but returned nothing. Inside a container ' .
                'without a running systemd this is what happens.',
        ];
    }

    $rows = [];

    foreach (preg_split('/\R/', trim((string) $listed)) ?: [] as $line) {
        $parts = preg_split('/\s+/', trim($line), 5) ?: [];
        if (count($parts) < 4 || !str_ends_with($parts[0], '.service')) {
            continue;
        }
        $name = substr($parts[0], 0, -8);
        $rows[$name] = [
            'name' => $name,
            'loaded' => $parts[1],
            'active' => $parts[2],
            'sub' => $parts[3],
            'description' => $parts[4] ?? '',
            'enabled' => null,
        ];
    }

    /*
     * The enablement state comes from a different command.
     *
     * `list-units` says what is running now; `list-unit-files` says what is set
     * to start at boot. A service that is running but disabled will vanish at
     * the next reboot, and one that is enabled but dead failed to start - two
     * findings that neither list produces on its own.
     */
    foreach (preg_split('/\R/', trim((string) $files)) ?: [] as $line) {
        $parts = preg_split('/\s+/', trim($line)) ?: [];
        if (count($parts) < 2 || !str_ends_with($parts[0], '.service')) {
            continue;
        }
        $name = substr($parts[0], 0, -8);
        if (str_contains($name, '@')) {
            // A template, not a unit. It has no state of its own.
            continue;
        }
        if (!isset($rows[$name])) {
            $rows[$name] = [
                'name' => $name,
                'loaded' => 'not-loaded',
                'active' => 'inactive',
                'sub' => 'dead',
                'description' => '',
                'enabled' => $parts[1],
            ];
            continue;
        }
        $rows[$name]['enabled'] = $parts[1];
    }

    $out = array_values($rows);
    foreach ($out as &$row) {
        $row['state'] = serviceState($row);
    }
    unset($row);

    /*
     * Sorted by how much attention each state deserves.
     *
     * A list of two hundred services alphabetically is a list nobody reads to
     * the bottom, and the three that failed are somewhere in the middle of it.
     */
    $rank = [
        'failed' => 0,
        'masked' => 1,
        'running-not-enabled' => 2,
        'enabled-not-running' => 3,
        'running' => 4,
        'stopped' => 5,
    ];
    usort(
        $out,
        fn(array $a, array $b): int => ($rank[$a['state']] ?? 9) <=>
        ($rank[$b['state']] ?? 9) ?:
        strcmp($a['name'], $b['name']),
    );

    $summary = [];
    foreach ($out as $row) {
        $summary[$row['state']] = ($summary[$row['state']] ?? 0) + 1;
    }

    return [
        'available' => true,
        'reason' => '',
        'rows' => $out,
        'summary' => $summary,
    ];
}

/**
 * One word for what a unit's several state fields add up to.
 *
 * systemd reports loaded/active/sub/enabled separately and the interesting
 * conditions are combinations of them. Naming the combination is the whole
 * value: "running but not enabled" is a real finding and is invisible in any
 * one column.
 */
function serviceState(array $row): string
{
    $active = (string) ($row['active'] ?? '');
    $enabled = (string) ($row['enabled'] ?? '');

    if ($active === 'failed' || ($row['sub'] ?? '') === 'failed') {
        return 'failed';
    }
    if ($enabled === 'masked' || $row['loaded'] === 'masked') {
        return 'masked';
    }
    if ($active === 'active') {
        return $enabled === 'disabled' ? 'running-not-enabled' : 'running';
    }
    if ($enabled === 'enabled') {
        return 'enabled-not-running';
    }
    return 'stopped';
}

/** What each state means, for the interface to say rather than imply. */
function serviceStateNote(string $state): string
{
    return match ($state) {
        'failed'
            => 'Tried to run and could not. It uses no memory and no CPU, so it is ' .
            'absent from every other panel here rather than standing out in one.',
        'masked'
            => 'Deliberately blocked. It cannot be started at all until it is unmasked, ' .
            'which is a long mystery if you do not know it was done.',
        'running-not-enabled'
            => 'Running now, but not set to start at boot — it will be gone ' .
            'after the next restart.',
        'enabled-not-running'
            => 'Set to start at boot and is not running. Either it failed ' .
            'quietly or something stopped it.',
        'running' => 'Running, and set to start at boot.',
        'stopped' => 'Not running and not set to. Usually intentional.',
        default => '',
    };
}

/* --------------------------------------------------------------- storing -- */

/**
 * Writes one collection's network state beside the sample it belongs to.
 *
 * Everything here is cheap and free of side effects: reading /proc and asking
 * `ss` for a list. The capture is not part of this and never will be - it costs
 * a real measurement on a live interface and only happens when asked for.
 */
function storeNetwork(PDO $host, int $sampleId): array
{
    $counts = ['ports' => 0, 'interfaces' => 0, 'peers' => 0, 'units' => 0];

    $listening = listeningSockets();
    $insertPort = $host->prepare(
        'INSERT OR REPLACE INTO host_ports
            (sample_id, protocol, port, addresses, families, public, process, pid)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
    );
    foreach ($listening['rows'] as $row) {
        $insertPort->execute([
            $sampleId,
            $row['protocol'],
            $row['port'],
            implode(' ', $row['addresses']),
            implode(' ', $row['families']),
            $row['public'] ? 1 : 0,
            $row['process'],
            $row['pid'],
        ]);
        $counts['ports']++;
    }

    $insertInterface = $host->prepare(
        'INSERT OR REPLACE INTO host_interfaces
            (sample_id, name, rx_bytes, tx_bytes, rx_packets, tx_packets,
             rx_errors, tx_errors, rx_dropped, tx_dropped, up, speed_mbit)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    );
    foreach (networkInterfaces() as $row) {
        $insertInterface->execute([
            $sampleId,
            $row['name'],
            $row['rx_bytes'],
            $row['tx_bytes'],
            $row['rx_packets'],
            $row['tx_packets'],
            $row['rx_errors'],
            $row['tx_errors'],
            $row['rx_dropped'],
            $row['tx_dropped'],
            $row['up'] === null ? null : ($row['up'] ? 1 : 0),
            $row['speed_mbit'],
        ]);
        $counts['interfaces']++;
    }

    $connections = connectionSummary();
    $insertPeer = $host->prepare(
        'INSERT OR REPLACE INTO host_peers (sample_id, address, connections, private)
         VALUES (?, ?, ?, ?)',
    );
    foreach (array_slice($connections['peers'], 0, CAPTURE_KEEP) as $peer) {
        $insertPeer->execute([
            $sampleId,
            $peer['address'],
            $peer['connections'],
            $peer['private'] ? 1 : 0,
        ]);
        $counts['peers']++;
    }

    $units = serviceUnits();
    $insertUnit = $host->prepare(
        'INSERT OR REPLACE INTO host_units
            (sample_id, name, state, active, sub, enabled, description)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
    );
    foreach ($units['rows'] as $row) {
        $insertUnit->execute([
            $sampleId,
            $row['name'],
            $row['state'],
            $row['active'],
            $row['sub'],
            $row['enabled'],
            $row['description'],
        ]);
        $counts['units']++;
    }

    return $counts;
}

/**
 * Writes one capture and the counts it produced.
 *
 * Every capture is kept, not just the newest. Two samples an hour apart are the
 * only way to answer "is this getting worse", and a report you cannot compare
 * with anything is a snapshot of a number you had to be watching to catch.
 */
function storeCapture(PDO $host, array $capture): int
{
    $host
        ->prepare(
            'INSERT INTO host_captures
            (taken_at, interface, filter, seconds, packets, bytes, unparsed,
             distinct_peers, distinct_names, hit_limit, ok, reason,
             in_packets, out_packets, in_bytes, out_bytes, inferred_local)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        )
        ->execute([
            time(),
            $capture['interface'],
            $capture['filter'],
            $capture['seconds'],
            $capture['packets'],
            $capture['bytes'],
            $capture['unparsed'] ?? 0,
            $capture['distinct_peers'] ?? 0,
            $capture['distinct_names'] ?? 0,
            $capture['hit_packet_limit'] ? 1 : 0,
            $capture['ok'] ? 1 : 0,
            $capture['reason'],
            $capture['in_packets'] ?? 0,
            $capture['out_packets'] ?? 0,
            $capture['in_bytes'] ?? 0,
            $capture['out_bytes'] ?? 0,
            $capture['inferred_local'] ?? null,
        ]);

    $id = (int) $host->lastInsertId();

    $insert = $host->prepare(
        'INSERT OR REPLACE INTO host_capture_rows
            (capture_id, kind, label, packets, private,
             in_packets, out_packets, in_bytes, out_bytes, opened, bytes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    );
    /*
     * The two rollups are stored with their sub-kind in the label.
     *
     * "A git.example.com" rather than a column for the record type: this table
     * is a label and a count by design, and it already holds four unrelated
     * kinds that way. A column read by one of six kinds would be emptier than
     * the string it replaced.
     */
    foreach (['asked', 'visited'] as $kind) {
        foreach ($capture[$kind] ?? [] as $row) {
            $insert->execute([
                $id,
                $kind,
                $row['kind'] . ' ' . $row['name'],
                (int) $row['packets'],
                0,
                0,
                0,
                0,
                0,
                0,
                0,
            ]);
        }
    }

    foreach (
        ['peers' => 'address', 'ports' => 'port', 'names' => 'name']
        as $kind => $field
    ) {
        foreach ($capture[$kind] ?? [] as $row) {
            $insert->execute([
                $id,
                $kind,
                (string) $row[$field],
                (int) $row['packets'],
                (int) ($row['private'] ?? 0),
                (int) ($row['in_packets'] ?? 0),
                (int) ($row['out_packets'] ?? 0),
                (int) ($row['in_bytes'] ?? 0),
                (int) ($row['out_bytes'] ?? 0),
                (int) ($row['opened'] ?? 0),
                (int) ($row['bytes'] ?? 0),
            ]);
        }
    }

    // The flag tally, stored as rows of its own kind so it needs no new table.
    foreach ($capture['flags'] ?? [] as $flag => $count) {
        $insert->execute([
            $id,
            'flags',
            (string) $flag,
            (int) $count,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
        ]);
    }

    return $id;
}

/**
 * The capture task name for a given interface and filter.
 *
 * Encoded into the task key so two different requests are two different tasks -
 * asking for DNS on eth0 and then everything on eth1 should run both, not
 * overwrite one with the other.
 */
function captureTaskName(
    string $interface,
    string $filter,
    bool $readAsked = false,
): string {
    /*
     * The payload flag rides in the task name.
     *
     * The task table is one row per name with no argument column - that is the
     * whole protocol - so a capture that reads contents has to be a different
     * task from one that does not, or asking for one would silently satisfy a
     * request for the other. Two people asking for the same capture still get
     * one capture; two people asking for different ones get both.
     */
    return 'capture:' .
        $interface .
        ':' .
        $filter .
        ($readAsked ? ':asked' : '');
}

/** Reads a task name back into its parts, or null if it is not one. */
function parseCaptureTask(string $task): ?array
{
    if (!str_starts_with($task, 'capture:')) {
        return null;
    }

    $parts = explode(':', $task, 4);
    if (count($parts) < 3) {
        return null;
    }

    if (
        !preg_match('/^[A-Za-z0-9._-]{1,32}$/', $parts[1]) ||
        captureFilter($parts[2]) === null
    ) {
        return null;
    }

    return [
        'interface' => $parts[1],
        'filter' => $parts[2],
        'read_asked' => ($parts[3] ?? '') === 'asked',
    ];
}

/* ------------------------------------------------- commands root can run -- */

/**
 * The verbs the collector will run on the account's behalf.
 *
 * A fixed list, and each one takes exactly one argument validated as a domain
 * name. Nothing here accepts a command string: this runs as root, and the whole
 * point of the arrangement is that the account can ask a question without being
 * able to say how it is answered.
 *
 * @return array<string, array{binary: string, build: callable, about: string}>
 */
function hostVerbs(): array
{
    return [
        'whois' => [
            'binary' => 'whois',
            'build' => fn(
                string $binary,
                string $argument,
            ): string => escapeshellarg($binary) .
                ' ' .
                escapeshellarg($argument),
            'about' => 'the registry record for a domain',
        ],
        'dig-any' => [
            'binary' => 'dig',
            'build' => fn(
                string $binary,
                string $argument,
            ): string => escapeshellarg($binary) .
                ' +noall +answer +tries=1 +time=3 ' .
                escapeshellarg($argument) .
                ' ANY',
            'about' => 'every record type for a name',
        ],
        'dig-ns' => [
            'binary' => 'dig',
            'build' => fn(
                string $binary,
                string $argument,
            ): string => escapeshellarg($binary) .
                ' +noall +answer +tries=1 +time=3 ' .
                escapeshellarg($argument) .
                ' NS',
            'about' => 'which nameservers are authoritative',
        ],
        /*
         * The one verb here that changes something.
         *
         * Everything else asks a question. ldconfig rebuilds the dynamic
         * linker's cache from the directories in /etc/ld.so.conf, which is the
         * fix when a library symlink has been repaired or a package half
         * installed and programs are failing to start with "cannot open shared
         * object file".
         *
         * Safe and idempotent - it derives the cache from what is on disk, so
         * running it twice is running it once - but it is still root writing to
         * /etc, so it is marked as a change and the page says so before asking.
         */
        'ldconfig' => [
            'binary' => 'ldconfig',
            'build' => fn(
                string $binary,
                string $argument,
            ): string => escapeshellarg($binary) . ' -v',
            'about' => 'rebuilding the shared library cache',
            'takes_argument' => false,
            'changes_things' => true,
        ],
    ];
}

/** The task name for one question, so two different ones are two tasks. */
function hostAskTask(string $verb, string $argument): string
{
    return 'ask:' . $verb . ':' . $argument;
}

/** Reads a task name back into its parts, or null if it is not one. */
function parseAskTask(string $task): ?array
{
    if (!str_starts_with($task, 'ask:')) {
        return null;
    }

    $parts = explode(':', $task, 3);
    if (count($parts) !== 3 || !isset(hostVerbs()[$parts[1]])) {
        return null;
    }

    $verb = hostVerbs()[$parts[1]];

    /*
     * A verb that takes no argument must be given none.
     *
     * Not merely "the argument is ignored": the argument is the only part of
     * this string that did not come from a fixed list, so a verb that has no
     * use for one should refuse to carry one rather than have it sit unread in
     * a task the collector will run as root.
     */
    if (($verb['takes_argument'] ?? true) === false) {
        return $parts[2] === ''
            ? ['verb' => $parts[1], 'argument' => '']
            : null;
    }

    if (
        !preg_match(
            '/^(?!-)[A-Za-z0-9-]{1,63}(?<!-)(\.(?!-)[A-Za-z0-9-]{1,63}(?<!-))+$/',
            $parts[2],
        )
    ) {
        return null;
    }

    return ['verb' => $parts[1], 'argument' => $parts[2]];
}

/**
 * Runs one asked-for command and records the answer.
 *
 * Bounded by `timeout` and by the fixed command shape. The output is stored as
 * text for the account to parse - the collector deliberately does no parsing of
 * its own, so that improving the parsing never requires touching the thing that
 * runs as root.
 */
function runHostAsk(
    PDO $host,
    string $verb,
    string $argument,
    int $askedAt,
): array {
    $verbs = hostVerbs();
    if (!isset($verbs[$verb])) {
        return ['ok' => false, 'error' => 'not a verb this runs'];
    }

    $binary = whichBinary($verbs[$verb]['binary']);
    if ($binary === null) {
        $answer = [
            'ok' => false,
            'text' => null,
            'error' =>
                $verbs[$verb]['binary'] .
                ' is not installed on the host either',
        ];
    } else {
        $timeout = whichBinary('timeout');
        $command =
            ($timeout === null ? '' : escapeshellarg($timeout) . ' 25 ') .
            $verbs[$verb]['build']($binary, $argument) .
            ' 2>&1';

        $text = shellRead($command);
        $answer =
            $text === null || trim($text) === ''
                ? [
                    'ok' => false,
                    'text' => null,
                    'error' => 'the command produced no output',
                ]
                : [
                    'ok' => true,
                    'text' => substr($text, 0, 40000),
                    'error' => null,
                ];
    }

    $host
        ->prepare(
            'INSERT INTO host_answers (verb, argument, asked_at, answered_at, ok, text, error)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        )
        ->execute([
            $verb,
            $argument,
            $askedAt,
            time(),
            $answer['ok'] ? 1 : 0,
            $answer['text'],
            $answer['error'],
        ]);

    return $answer;
}

/**
 * The newest answer to one question, if there is one.
 *
 * @return array{ok: bool, text: ?string, error: ?string, answered_at: int}|null
 */
function hostAnswer(PDO $host, string $verb, string $argument): ?array
{
    try {
        $stmt = $host->prepare(
            'SELECT ok, text, error, answered_at FROM host_answers
              WHERE verb = ? AND argument = ? ORDER BY answered_at DESC LIMIT 1',
        );
        $stmt->execute([$verb, $argument]);
        $row = $stmt->fetch();
    } catch (Throwable) {
        return null;
    }

    if (!$row) {
        return null;
    }

    return [
        'ok' => (bool) $row['ok'],
        'text' => $row['text'],
        'error' => $row['error'],
        'answered_at' => (int) $row['answered_at'],
    ];
}
