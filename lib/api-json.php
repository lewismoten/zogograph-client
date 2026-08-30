<?php
declare(strict_types=1);

/* The browser and AI tools do not benefit from binary floating-point noise.
 * Keep the wire contract compact; byte counters and integer identifiers stay
 * integers, while measurements use at most five decimal places. */

const API_FLOAT_DECIMALS = 5;

function apiCompactValue(mixed $value): mixed
{
    if (is_float($value)) {
        $rounded = round($value, API_FLOAT_DECIMALS);
        return $rounded == 0.0 ? 0 : $rounded;
    }
    if (!is_array($value)) {
        return $value;
    }
    foreach ($value as $key => $item) {
        $value[$key] = apiCompactValue($item);
    }
    return $value;
}

function apiJson(mixed $value): string
{
    /* Some hosts set serialize_precision=53. Without this, round(23.45, 5)
     * is emitted as the binary representation of 23.45 rather than 23.45. */
    $previous = ini_get('serialize_precision');
    ini_set('serialize_precision', '-1');
    try {
        return json_encode(
            apiCompactValue($value),
            JSON_UNESCAPED_SLASHES |
                JSON_INVALID_UTF8_SUBSTITUTE |
                JSON_THROW_ON_ERROR,
        );
    } finally {
        ini_set('serialize_precision', (string) $previous);
    }
}

function apiAcceptsGzip(string $header): bool
{
    foreach (explode(',', strtolower($header)) as $part) {
        if (
            !preg_match(
                '/^\s*gzip(?:\s*;\s*q\s*=\s*([0-9.]+))?\s*$/',
                $part,
                $match,
            )
        ) {
            continue;
        }
        return !isset($match[1]) || (float) $match[1] > 0.0;
    }
    return false;
}

/** Apache compression is preferred; this is the shared-host fallback. */
function apiRespond(mixed $value): void
{
    $json = apiJson($value);
    $accept = (string) ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '');
    if (
        apiAcceptsGzip($accept) &&
        function_exists('gzencode') &&
        !headers_sent()
    ) {
        header('Content-Encoding: gzip');
        header('Vary: Accept-Encoding', false);
        echo gzencode($json, 6);
        return;
    }
    header('Vary: Accept-Encoding', false);
    echo $json;
}
