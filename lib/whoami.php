<?php
declare(strict_types=1);

/*
 * Who is asking, and who is answering.
 *
 * Two identities that get confused constantly and are the root of a whole class
 * of "why can't it read that file" confusion:
 *
 *   you        the browser at the other end - an address, whatever the proxy
 *              chain claims about it, and whether it is one you have named
 *   this       the account PHP is running as, which is not necessarily the
 *              account that owns the files, and on a cPanel host is not
 *              necessarily the one whose name is on the domain
 *
 * The second is the one that answers permission questions. "The host database
 * exists but this account cannot read it" is a sentence that means nothing
 * until you know which account that is, and the answer is here rather than
 * inferred from a path.
 *
 * Everything a proxy header claims is reported as a claim. `X-Forwarded-For` is
 * set by whatever spoke to the web server last and can say anything at all; the
 * only address this process actually observed is REMOTE_ADDR.
 */

require_once __DIR__ . '/labels.php';

/**
 * Everything knowable about the request and the process serving it.
 *
 * @return array<string, mixed>
 */
function whoAmI(array $config, PDO $pdo): array
{
    $labels = loadLabels($pdo);
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

    $out = [
        'you' => [
            'address' => $remote === '' ? null : $remote,
            'label' => null,
            'private' => $remote !== '' && addressIsPrivate($remote),
            'reverse' => $remote === '' ? null : reverseName($remote),
            'agent' => substr(
                (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                0,
                300,
            ),
            'secure' => requestIsSecure(),
            'claims' => forwardedClaims(),
        ],
        'server' => serverIdentity($config),
        'session' => [
            'signed_in' => isSignedIn(),
            'started_at' => isset($_SESSION['signed_in_at'])
                ? (int) $_SESSION['signed_in_at']
                : null,
        ],
    ];

    if ($remote !== '') {
        $found = labelForAddress($labels, $remote);
        if ($found !== null) {
            $out['you']['label'] = $found['label'];
            $out['you']['label_note'] = $found['note'];
            $out['you']['label_match'] = $found['match'];
        }
    }

    return $out;
}

/**
 * What the proxy chain says about where the request came from.
 *
 * Reported as claims, never as facts. Anything can set these headers, and a
 * dashboard that presented `X-Forwarded-For` as "your address" would be quoting
 * whatever the client felt like typing. They are still worth showing: behind
 * Cloudflare or a load balancer the real address is only in here, and knowing
 * which header carries it is half of configuring anything.
 *
 * @return list<array{header: string, value: string}>
 */
function forwardedClaims(): array
{
    $out = [];

    foreach (
        [
            'HTTP_X_FORWARDED_FOR' => 'X-Forwarded-For',
            'HTTP_X_REAL_IP' => 'X-Real-IP',
            'HTTP_CF_CONNECTING_IP' => 'CF-Connecting-IP',
            'HTTP_TRUE_CLIENT_IP' => 'True-Client-IP',
            'HTTP_X_FORWARDED_PROTO' => 'X-Forwarded-Proto',
            'HTTP_X_FORWARDED_HOST' => 'X-Forwarded-Host',
        ]
        as $key => $name
    ) {
        if (!isset($_SERVER[$key]) || trim((string) $_SERVER[$key]) === '') {
            continue;
        }
        $out[] = [
            'header' => $name,
            'value' => substr((string) $_SERVER[$key], 0, 200),
        ];
    }

    return $out;
}

/** Whether this request arrived over TLS, by any of the ways that can say so. */
function requestIsSecure(): bool
{
    if (
        ($_SERVER['HTTPS'] ?? '') !== '' &&
        strtolower((string) $_SERVER['HTTPS']) !== 'off'
    ) {
        return true;
    }
    if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }
    // A claim rather than a fact - see forwardedClaims - but the honest answer
    // behind a terminating proxy, where this process never sees the TLS.
    return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) ===
        'https';
}

/**
 * A name for an address, where the resolver has one.
 *
 * Bounded by the resolver's own timeout, which PHP does not let us set for
 * gethostbyaddr - so this is only called for the one address of the person
 * looking at the page, never in a loop.
 */
function reverseName(string $address): ?string
{
    $name = @gethostbyaddr($address);
    return $name === false || $name === $address ? null : $name;
}

/**
 * Which account this process is, and which account owns its files.
 *
 * These differ more often than people expect. On a cPanel host with suPHP or
 * FPM-per-user they match; behind mod_php they do not, and every "permission
 * denied" on the site traces back to that difference. Both are reported, with
 * the mismatch called out rather than left to be noticed.
 */
function serverIdentity(array $config): array
{
    $uid = function_exists('posix_geteuid') ? posix_geteuid() : null;
    $running = $uid === null ? null : accountNameFor($uid);

    $file = __DIR__ . '/../index.php';
    if (!is_file($file)) {
        $file = __DIR__ . '/../index.html';
    }
    $ownerId = is_file($file) ? @fileowner($file) : false;
    $owner = $ownerId === false ? null : accountNameFor((int) $ownerId);

    $dataDir = dirname((string) ($config['database'] ?? ''));

    return [
        'hostname' => php_uname('n'),
        'kernel' => php_uname('r'),
        'php' => PHP_VERSION,
        'sapi' => PHP_SAPI,
        'running_as' => $running,
        'running_uid' => $uid,
        'files_owned_by' => $owner,
        'same_user' =>
            $running !== null && $owner !== null && $running === $owner,
        'home' => getenv('HOME') ?: null,
        'scan_root' => (string) ($config['scan_root'] ?? ''),
        'data_dir' => $dataDir,
        'data_writable' => $dataDir !== '' && is_writable($dataDir),
        'host_owner' => (string) ($config['host']['owner'] ?? ''),
        'document_root' => (string) ($_SERVER['DOCUMENT_ROOT'] ?? ''),
        'server_software' => (string) ($_SERVER['SERVER_SOFTWARE'] ?? ''),
        'server_addresses' => localAddresses(),
    ];
}

/**
 * A username for a uid, and the real name beside it where the system has one.
 *
 * The GECOS field is where a display name lives on a Unix system. It is often
 * empty and often contains the account name again, both of which are reported
 * as "no display name" rather than as a name.
 */
function accountNameFor(int $uid): array
{
    $out = ['uid' => $uid, 'name' => (string) $uid, 'display' => null];

    if (!function_exists('posix_getpwuid')) {
        return $out;
    }

    $entry = @posix_getpwuid($uid);
    if (!is_array($entry)) {
        return $out;
    }

    $out['name'] = (string) ($entry['name'] ?? $uid);
    $out['shell'] = $entry['shell'] ?? null;
    $out['home'] = $entry['dir'] ?? null;

    // GECOS is comma-separated; the first field is the real name.
    $gecos = trim((string) ($entry['gecos'] ?? ''));
    $display = trim(explode(',', $gecos)[0]);

    if ($display !== '' && strcasecmp($display, $out['name']) !== 0) {
        $out['display'] = $display;
    }

    return $out;
}
