<?php
declare(strict_types=1);

/*
 * Hiding the names while showing the numbers.
 *
 * The masking happens here, on the server, and the real path never enters a
 * response for a signed-out visitor. Masking in the browser would be a costume:
 * the data would still be in the payload, one View Source away.
 *
 * A mask has to be *stable* - the same file has to carry the same label across
 * requests, or a chart of one file's growth is meaningless and the table
 * reshuffles its own labels every poll. So it is a keyed hash of the path
 * rather than a counter. Keyed, because an unkeyed hash of a path is a
 * confirmation oracle: anyone who guesses "/home/account/logs/error.log" can
 * hash it and check.
 */

/**
 * What a signed-out visitor sees instead of a path.
 *
 * The shape survives: a directory still looks like a directory, a file keeps
 * its extension, and the depth is preserved. That is enough to reason about
 * ("something four levels down, a .log, growing fast") without naming anything.
 */
function maskPath(array $config, string $path, string $kind): string
{
    $secret = (string) ($config['mask_secret'] ?? '');
    $tag = strtoupper(substr(hash_hmac('sha256', $path, $secret), 0, 6));

    $depth = max(0, substr_count(trim($path, '/'), '/'));
    $extension =
        $kind === 'file' ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';

    $label = $kind === 'dir' ? 'directory' : 'file';
    if ($extension !== '' && preg_match('/^[a-z0-9]{1,8}$/', $extension)) {
        $label = '.' . $extension . ' file';
    }

    return sprintf('%s %s (depth %d)', $label, $tag, $depth);
}

/**
 * Applies masking to a row that carries a path.
 *
 * One function so that no endpoint can forget: every response that mentions a
 * path goes through here, and when signed out the real one is *removed* rather
 * than merely overwritten in the rendered output.
 */
function presentPath(array $config, array $row, bool $signedIn): array
{
    $path = (string) ($row['path'] ?? '');
    $kind = (string) ($row['kind'] ?? 'file');

    if ($signedIn) {
        $row['display'] = $path;
        $row['masked'] = false;
        return $row;
    }

    unset($row['path']);
    $row['display'] = maskPath($config, $path, $kind);
    $row['masked'] = true;
    return $row;
}

/**
 * The same, for free text that may quote a path - alert details, mostly.
 *
 * An alert says "/home/x/logs/error.log grew by 4 GiB", which would hand over
 * in a notification exactly what the masking withholds in the table.
 */
function presentAlert(array $config, array $row, bool $signedIn): array
{
    if ($signedIn) {
        $row['masked'] = false;
        return $row;
    }

    $path = (string) ($row['path'] ?? '');
    if ($path !== '') {
        $mask = maskPath($config, $path, (string) ($row['kind'] ?? 'file'));
        $row['subject'] = str_replace(
            [$path, basename($path)],
            $mask,
            (string) $row['subject'],
        );
        $row['detail'] = str_replace(
            [$path, basename($path)],
            $mask,
            (string) $row['detail'],
        );
    }

    /*
     * Belt and braces: strip anything that still looks like an absolute path.
     *
     * The rules above only know about the one path an alert is linked to, and a
     * detail line is free text that a future rule might fill in differently.
     * Leaking a path here would be silent, so the fallback is unconditional.
     */
    $row['subject'] = redactPaths((string) $row['subject']);
    $row['detail'] = redactPaths((string) $row['detail']);

    /*
     * The link to the evidence goes too.
     *
     * The endpoint refuses anonymous readers outright, so this is not what
     * stops the record being read - but an id is still a fact about the
     * install, and handing out a run of them says how many times this server
     * has been in trouble. There is nothing a signed-out visitor can do with
     * it, so there is no reason to send it.
     */
    unset($row['path'], $row['incident_id']);

    /*
     * The assistant's note is free text about this machine.
     *
     * It is written from tool results that name paths, processes and log lines,
     * and no amount of redacting a sentence afterwards makes that safe to hand
     * to the open internet. A signed-out visitor sees that something was said
     * and not what.
     */
    foreach (['ai_summary', 'ai_reason', 'ai_advice'] as $field) {
        if (($row[$field] ?? null) !== null) {
            $row[$field] = '[hidden]';
        }
    }

    $row['masked'] = true;
    return $row;
}

function redactPaths(string $text): string
{
    return (string) preg_replace('#(/[^\s/]+){2,}/?#', '[path hidden]', $text);
}
