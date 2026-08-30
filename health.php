<?php
declare(strict_types=1);

/*
 * A diagnostic page that depends on nothing.
 *
 * The health check inside api.php is more useful than this one, and completely
 * unreachable in the situation that matters: if api.php cannot load, neither
 * can the endpoint that would tell you why. So this file requires nothing,
 * includes nothing, and does not touch the database or the config. It will run
 * on an install that is otherwise entirely broken.
 *
 * It is readable without signing in, because a broken install has to be
 * diagnosable by whoever is looking at it, and there is no session to check
 * when the code that manages sessions is the thing that will not load. So it
 * reports *states* and never paths, never the config, never the contents of
 * anything - "the data directory is not writable", not where it is.
 *
 * You may remove this health page if you would rather not have it there.
 */

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$root = __DIR__;
require_once $root . '/lib/branding.php';
$brand = brandName([]);

/** Each file the app needs, and whether PHP can actually parse it. */
$required = [
    'api.php',
    'cron.php',
    'index.html',
    'index.css',
    'index.js',
    'lib/boot.php',
    'lib/db.php',
    'lib/collect.php',
    'lib/alerts.php',
    'lib/authrecovery.php',
    'lib/anomalies.php',
    'lib/incident.php',
    'lib/daemons.php',
    'lib/growth.php',
    'lib/retention.php',
    'lib/logwatch.php',
    'lib/treemap.php',
    'lib/hostdisk.php',
    'lib/capabilities.php',
    'lib/backups.php',
    'lib/mountinfo.php',
    'lib/auth.php',
    'lib/mask.php',
    'lib/actions.php',
    'lib/logs.php',
    'lib/schedule.php',
    'lib/configure.php',
    'lib/processes.php',
    'lib/symlinks.php',
    'lib/signals.php',
    'lib/host.php',
    'lib/uptime.php',
    'lib/limits.php',
    'lib/inspect.php',
    'thumb.php',
    'lib/network.php',
    'lib/logstream.php',
    'lib/diagnose.php',
    'lib/labels.php',
    'lib/urlwatch.php',
    'lib/domains.php',
    'lib/events.php',
    'lib/whoami.php',
    'lib/dnsrecords.php',
    'lib/external.php',
    'lib/hostdb.php',
    'host-cron.php',
    'lib/config.sample.php',
    'lib/headers.php',
    'lib/pagebits.php',
    'preview.php',
    'lib/spans.php',
    'lib/storekeep.php',
    'lib/notes.php',
    'lib/software.php',
    'lib/notify.php',
    'lib/rdns.php',
    'lib/ai.php',
    'lib/aidb.php',
    'lib/aitools.php',
    'lib/airun.php',
    'lib/aisession.php',
    'lib/aicharts.php',
    'lib/scans.php',
    'lib/aivoice.php',
    'lib/aiimages.php',
    'lib/aimemory.php',
    'lib/aicompact.php',
    'lib/terminal.php',
    'lib/aialerts.php',
    'lib/whatis.php',
    'scripts/reset-password.php',
    'scripts/restore-account.php',
];

$files = [];
$missing = 0;
$broken = 0;

/*
 * Whether the files agree with each other.
 *
 * A file present, readable and parsing perfectly can still be the wrong
 * version. Uploading by hand over FTP or File Manager means uploading one file
 * at a time, and the moment one lands without another that it needs, the site
 * dies with "Call to undefined function" - which reads like a code bug and is
 * actually half a deployment.
 *
 * Checked statically, by reading. Every file is already being tokenised for the
 * parse check above, so collecting the functions each one defines and the
 * functions each one calls costs almost nothing extra, and nothing has to be
 * executed or loaded to do it. That matters: this page has to work on the
 * install that is too broken to run.
 */
$defined = [];
$called = [];
$optional = [];

$scan = function (string $source, string $where) use (
    &$defined,
    &$called,
    &$optional,
): void {
    try {
        $tokens = token_get_all($source, TOKEN_PARSE);
    } catch (Throwable) {
        return;
    }

    $simple = [];
    foreach ($tokens as $token) {
        // Whitespace and comments never matter to what follows what.
        if (
            is_array($token) &&
            in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)
        ) {
            continue;
        }
        $simple[] = $token;
    }

    foreach ($simple as $i => $token) {
        if (!is_array($token) || $token[0] !== T_STRING) {
            continue;
        }

        $name = $token[1];
        $before = $simple[$i - 1] ?? null;
        $after = $simple[$i + 1] ?? null;

        // A definition: `function name(`.
        if (is_array($before) && $before[0] === T_FUNCTION) {
            $defined[strtolower($name)] = true;
            continue;
        }

        // Only a call if a bracket follows it.
        if ($after !== '(') {
            continue;
        }

        /*
         * Not a plain function call if it is a method, a static call or a
         * constructor - those resolve against a class, not the global function
         * table, and flagging them would bury the real answer in noise.
         */
        if (
            is_array($before) &&
            in_array(
                $before[0],
                [
                    T_OBJECT_OPERATOR,
                    T_NULLSAFE_OBJECT_OPERATOR,
                    T_DOUBLE_COLON,
                    T_NEW,
                    T_FUNCTION,
                ],
                true,
            )
        ) {
            continue;
        }

        /*
         * `function_exists('posix_kill')` marks a name as allowed to be absent.
         *
         * Half this codebase is written to survive a host without posix, or
         * without shell_exec. Those guarded calls are deliberate, and reporting
         * them as broken would make this check worthless on exactly the
         * restricted hosting it is written for.
         */
        if (strtolower($name) === 'function_exists') {
            $argument = $simple[$i + 2] ?? null;
            if (
                is_array($argument) &&
                $argument[0] === T_CONSTANT_ENCAPSED_STRING
            ) {
                $optional[strtolower(trim($argument[1], "'\""))] = true;
            }
            continue;
        }

        // Keyed lowercase because PHP resolves function names case-insensitively,
        // but the original spelling is kept for display - a report that renames
        // the thing it is complaining about is harder to act on.
        $called[strtolower($name)] ??= ['name' => $name, 'where' => $where];
    }
};

foreach ($required as $name) {
    $path = $root . '/' . $name;
    $state = 'ok';

    if (!is_file($path)) {
        $state = 'missing';
        $missing++;
    } elseif (!is_readable($path)) {
        $state = 'unreadable';
        $broken++;
    } elseif (str_ends_with($name, '.php')) {
        /*
         * Parsed, not just counted.
         *
         * A file present but truncated by a half-finished upload is the failure
         * this page exists to catch, and it looks identical to a healthy one
         * until PHP tries to compile it.
         *
         * In a try/catch because TOKEN_PARSE *throws* ParseError rather than
         * returning false, and `@` does not suppress an exception. Without
         * this, the page died on exactly the broken file it was written to
         * find - which is the least useful moment for a diagnostic to fail.
         */
        $source = file_get_contents($path) ?: '';
        try {
            token_get_all($source, TOKEN_PARSE);
            $scan($source, $name);
        } catch (Throwable) {
            $state = 'will not parse';
            $broken++;
        }
    }

    $files[$name] = $state;
}

/*
 * Anything called but defined nowhere, and not a PHP built-in.
 *
 * The built-in list comes from this PHP, which is the right one to ask: a call
 * to an extension function the server does not have is a genuine problem, and
 * the ones this code knowingly probes for are excluded by the function_exists
 * rule above.
 */
$internal = array_flip(
    array_map('strtolower', get_defined_functions()['internal']),
);
$undefined = [];
foreach ($called as $key => $call) {
    if (
        isset($defined[$key]) ||
        isset($internal[$key]) ||
        isset($optional[$key])
    ) {
        continue;
    }
    $undefined[$call['name']] = $call['where'];
}

/*
 * Whether the dashboard and the API agree about what exists.
 *
 * The function check above catches PHP that is out of step with other PHP. It
 * cannot see across the language boundary, and that is where the other half of
 * a partial upload shows up: index.js asks for an action that this api.php has
 * never heard of, and the only symptom is a 403 and "unknown action" in a
 * console the person diagnosing it may never open.
 *
 * Read as text rather than executed, like everything else on this page, so it
 * still works on the install that is too broken to run.
 *
 * Deliberately one-directional. Actions the API serves but nothing calls are
 * not reported: an endpoint can exist for a cron job, for a bookmark, or for a
 * future page, and flagging those would fill this list with things that are
 * fine. Only the calls that cannot be answered are faults.
 */
$actionsWanted = [];
$actionsServed = [];

$apiSource = @file_get_contents($root . '/api.php') ?: '';
if ($apiSource !== '') {
    // Over-collects: any 'key' => in the file, not only the dispatch arms.
    // That is the safe direction - a stray key can only mask a fault that is
    // not there, never invent one.
    preg_match_all("/'([a-z0-9][a-z0-9-]*)'\s*=>/", $apiSource, $found);
    $actionsServed = array_flip($found[1]);
}

$jsSource = @file_get_contents($root . '/index.js') ?: '';
if ($jsSource !== '') {
    preg_match_all(
        '/\b(?:api|post)\(\s*"([a-z0-9][a-z0-9-]*)"/',
        $jsSource,
        $found,
    );
    foreach ($found[1] as $name) {
        $actionsWanted[$name] = true;
    }
}

$actionsMissing = [];
if ($actionsServed && $actionsWanted) {
    $actionsMissing = array_keys(
        array_diff_key($actionsWanted, $actionsServed),
    );
    sort($actionsMissing);
}

$configured = is_file($root . '/lib/config.php');
$dataDir = $root . '/data';

$environment = [
    'PHP version' => PHP_VERSION,
    'PHP is 8.1 or newer' => PHP_VERSION_ID >= 80100,
    'PDO SQLite driver' => in_array('sqlite', PDO::getAvailableDrivers(), true),
    'lib/config.php exists' => $configured,
    'data directory exists' => is_dir($dataDir),
    'data directory writable' => is_dir($dataDir) && is_writable($dataDir),
    'problem log written' => is_file($dataDir . '/error.log'),
    'PHP has an error log configured' => (string) ini_get('error_log') !== '',
    'mail() available' => function_exists('mail'),
    'shell_exec available' =>
        function_exists('shell_exec') &&
        !in_array(
            'shell_exec',
            array_map(
                'trim',
                explode(',', (string) ini_get('disable_functions')),
            ),
            true,
        ),
];

$verdict =
    $missing > 0
        ? "$missing file(s) missing — the upload is incomplete."
        : ($broken > 0
            ? "$broken file(s) present but unreadable or truncated — upload them again."
            : ($undefined
                ? count($undefined) .
                    ' function(s) are called but defined nowhere — some ' .
                    'files are older than the ones calling them. Upload the whole set again.'
                : ($actionsMissing
                    ? count($actionsMissing) .
                        ' action(s) the dashboard calls do not exist in ' .
                        'api.php — index.js is newer than api.php. Upload the whole set again.'
                    : (!$configured
                        ? 'The code is fine. Nothing is configured yet — open setup.php.'
                        : 'Everything this page can check looks right.'))));

function mark(bool $ok): string
{
    return $ok
        ? '<span style="color:#75d58a">&#10003;</span>'
        : '<span style="color:#ff7f7f">&#10007;</span>';
}

function esc(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($brand) ?> — health</title>
<style>
:root { color-scheme: dark; }
body {
  font-family: system-ui, -apple-system, sans-serif;
  background: #111; color: #eee; padding: 24px;
  max-width: 780px; margin-inline: auto; line-height: 1.5;
}
h1 { font-size: 1.25rem; }
h2 { font-size: 1rem; margin-top: 28px; }
table { width: 100%; border-collapse: collapse; font-size: .9rem; }
td { padding: 5px 8px; border-bottom: 1px solid #333; }
td:first-child { width: 2ch; }
.verdict { border: 1px solid #7a5a30; background: #2b2114; border-radius: 10px; padding: 12px 14px; }
.verdict.ok { border-color: #2f6f3a; background: #152b1c; }
.muted { color: #9a9a9a; font-size: .85rem; }
code { background: #000; padding: 1px 5px; border-radius: 4px; }
</style>
</head>
<body>

<h1><?= esc($brand) ?> — health</h1>

<p class="verdict <?= $missing === 0 && $broken === 0 ? 'ok' : '' ?>"><?= esc(
    $verdict,
) ?></p>

<p class="muted">
  This page loads nothing and reads nothing, so it works even when the rest does
  not. It shows states rather than paths, because it answers to anyone who can
  reach it.
</p>

<h2>Environment</h2>
<table>
<?php foreach ($environment as $label => $value): ?>
  <tr>
    <td><?= is_bool($value) ? mark($value) : '&#183;' ?></td>
    <td><?= esc($label) ?></td>
    <td class="muted"><?= is_bool($value) ? '' : esc((string) $value) ?></td>
  </tr>
<?php endforeach; ?>
</table>

<?php if ($undefined): ?>
<h2>Files that disagree</h2>
<p class="muted">
  These are called but defined nowhere in the install. That almost always means
  a partial upload: the file doing the calling is newer than the file that
  should define it. Uploading everything again fixes it.
</p>
<table>
<?php foreach ($undefined as $name => $where): ?>
  <tr>
    <td><?= mark(false) ?></td>
    <td><code><?= esc($name) ?>()</code></td>
    <td class="muted">called in <?= esc($where) ?></td>
  </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ($actionsMissing): ?>
<h2>Actions the dashboard cannot reach</h2>
<p class="muted">
  <code>index.js</code> asks for these and <code>api.php</code> does not define
  them, which means the two files are from different versions. In the browser
  this looks like <code>403</code> and <code>unknown action</code> in the
  console, and like a panel that never fills in on the page. Uploading
  everything again fixes it.
</p>
<table>
<?php foreach ($actionsMissing as $name): ?>
  <tr>
    <td><?= mark(false) ?></td>
    <td><code>action=<?= esc($name) ?></code></td>
    <td class="muted">called in index.js</td>
  </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<h2>Files</h2>
<table>
<?php foreach ($files as $name => $state): ?>
  <tr>
    <td><?= mark($state === 'ok') ?></td>
    <td><code><?= esc($name) ?></code></td>
    <td class="muted"><?= $state === 'ok' ? '' : esc($state) ?></td>
  </tr>
<?php endforeach; ?>
</table>

<h2>If the dashboard is returning errors</h2>
<ol class="muted">
  <li>Any red mark above is the cause. Fix that first.</li>
  <li><strong>Files that disagree</strong> means a partial upload — one file is
      newer than another it depends on. This is the usual cause of
      <code>Call to undefined function</code>. Upload everything again.</li>
  <li>If everything here is green, the failure is at runtime. The dashboard
      shows a reference when a request fails; find that reference in
      <code>data/error.log</code>, or under <strong>Recent problems</strong> on
      the dashboard once you are signed in.</li>
  <li>If <em>PHP has an error log configured</em> is red, that is why you cannot
      find one anywhere on the server — this project writes its own to
      <code>data/error.log</code> regardless.</li>
</ol>

</body>
</html>
