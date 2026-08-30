<?php
declare(strict_types=1);
require_once __DIR__ . '/core-features.php';

/*
 * Talking to a model, and keeping the bill.
 *
 * The provider is configurable because there is no good reason for a monitoring
 * tool to have an opinion about which one somebody uses, and because the
 * request shape is the same everywhere that matters: OpenAI's chat completions
 * format with tool calling, which Anthropic, Google, Groq, Together, Mistral,
 * Ollama and llama.cpp all either speak natively or expose a compatible
 * endpoint for.
 *
 * Money is counted in millicents - thousandths of a cent, as integers. Prices
 * are quoted per million tokens and a session can be hundreds of calls, and a
 * budget enforced against a float that drifts by a fraction each turn is a
 * budget that stops working somewhere past the third decimal place.
 *
 * Everything sent and everything received is written down before anything is
 * done with it. This is a tool that reads a machine's logs and hands them to
 * somebody else's service, and "what exactly was sent" has to be answerable
 * afterwards without qualification.
 */

require_once __DIR__ . '/spans.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/aidb.php';
require_once __DIR__ . '/outbound.php';

/** How long one request to the model may take before it is abandoned. */
const AI_TIMEOUT = 120;

/** How many tool-calling rounds one user message may cause. */
const AI_MAX_STEPS = 12;

/**
 * The providers this knows how to talk to.
 *
 * `base` is the default endpoint; anything speaking the same protocol can be
 * pointed at by overriding it, which is how a local model is used.
 */
function aiProviders(): array
{
    return [
        'openai' => [
            'label' => 'OpenAI (chat completions)',
            'base' => 'https://api.openai.com/v1',
            'auth' => 'bearer',
            'note' =>
                'Also the shape used by Groq, Together, Fireworks, DeepSeek, ' .
                'OpenRouter and most others. Point the address at theirs. Newer ' .
                'reasoning models refuse function tools on this endpoint - use the ' .
                'Responses option below for those.',
            'models' => ['gpt-4o-mini', 'gpt-4o', 'gpt-4.1-mini', 'gpt-4.1'],
        ],
        /*
         * The newer endpoint, which is where reasoning models want tools.
         *
         * Chat completions answers a model like gpt-5.6-luna with "Function
         * tools with reasoning_effort are not supported ... use /v1/responses
         * or set reasoning_effort to 'none'" - and turning the reasoning off to
         * keep the old endpoint is throwing away the reason for using that
         * model at all. So the other endpoint is spoken instead.
         */
        'openai-responses' => [
            'label' => 'OpenAI (responses, for reasoning models)',
            'base' => 'https://api.openai.com/v1',
            'auth' => 'bearer',
            'note' =>
                'For gpt-5 and other reasoning models, which refuse function tools ' .
                'on the older chat-completions endpoint unless reasoning is turned off ' .
                'entirely. Same key, same address, different shape.',
            'models' => ['gpt-5.6-luna', 'gpt-5', 'gpt-5-mini', 'o4-mini'],
        ],
        'anthropic' => [
            'label' => 'Anthropic',
            'base' => 'https://api.anthropic.com/v1',
            'auth' => 'x-api-key',
            'note' =>
                'Uses its own message format, which this translates to and from.',
            'models' => [
                'claude-sonnet-4-5',
                'claude-haiku-4-5',
                'claude-opus-4-5',
            ],
        ],
        'compatible' => [
            'label' => 'Ollama and OpenAI-compatible',
            'base' => 'http://127.0.0.1:11434/v1',
            'auth' => 'bearer',
            'note' =>
                'Ollama, llama.cpp, LM Studio, vLLM, or a gateway of your own. ' .
                'No API key is required. Use loopback HTTP(S) for a local model, or a ' .
                'public HTTPS address (including a custom port such as :8443) for a ' .
                'remote Ollama-compatible server.',
            'models' => [],
        ],
    ];
}

/**
 * Validates a model endpoint and returns cURL host pinning for public providers.
 *
 * A configurable provider URL is an administrative setting, but it still must
 * not let a compromised administrator session make PHP fetch local metadata or
 * a private control panel. Public names are resolved here and pinned on the
 * outgoing request, closing the DNS-rebinding gap between validation and cURL.
 * Compatible models retain loopback HTTP support, but may also use a public
 * HTTPS endpoint without a credential. Public destinations are pinned just as
 * hosted providers are, so this does not turn the model URL into an SSRF path.
 *
 * @return array{url: string, resolve: list<string>, local: bool}
 */
function aiEndpointPolicy(
    string $provider,
    string $base,
    bool $allowQuery = false,
): array {
    $url = rtrim(trim($base), '/');
    $parts = parse_url($url);
    if (
        $url === '' ||
        $parts === false ||
        empty($parts['host']) ||
        isset($parts['user']) ||
        isset($parts['pass']) ||
        (!$allowQuery && isset($parts['query'])) ||
        isset($parts['fragment'])
    ) {
        throw new ActionRefused('the model endpoint must be a plain URL');
    }
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    $host = trim((string) $parts['host'], '[]');
    $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));

    if ($provider === 'compatible') {
        if (
            in_array($scheme, ['http', 'https'], true) &&
            outboundLoopbackHost($host)
        ) {
            return ['url' => $url, 'resolve' => [], 'local' => true];
        }
        if ($scheme !== 'https') {
            throw new ActionRefused(
                'a remote compatible model must use HTTPS; HTTP is allowed only on loopback',
            );
        }
    }

    if ($scheme !== 'https') {
        throw new ActionRefused('hosted model endpoints must use HTTPS');
    }
    $addresses = outboundHostAddresses($host);
    if (
        $addresses === [] ||
        array_filter($addresses, 'outboundPublicAddress') !== $addresses
    ) {
        throw new ActionRefused(
            'the model endpoint must resolve only to public addresses',
        );
    }
    return [
        'url' => $url,
        'resolve' => array_map(
            fn(string $address): string => $host . ':' . $port . ':' . $address,
            $addresses,
        ),
        'local' => false,
    ];
}

/**
 * The assistant's settings, with the defaults filled in.
 *
 * @return array<string, mixed>
 */
function aiSettings(array $config): array
{
    $set = $config['ai'] ?? [];
    $provider = (string) ($set['provider'] ?? 'openai');
    $providers = aiProviders();

    if (!isset($providers[$provider])) {
        $provider = 'openai';
    }
    $connections = is_array($set['providers'] ?? null) ? $set['providers'] : [];
    $connection = is_array($connections[$provider] ?? null)
        ? $connections[$provider]
        : [];
    /*
     * Older installs have one endpoint and key at the top level.  Keep using
     * that pair for their active provider until it is saved on the Providers
     * page; newly saved connections are isolated by provider.
     */
    $legacy = $provider === (string) ($set['provider'] ?? '');
    $base = trim((string) ($connection['base_url'] ?? ''));
    if ($base === '' && $legacy) {
        $base = trim((string) ($set['base_url'] ?? ''));
    }
    $key = (string) ($connection['api_key'] ?? '');
    if ($key === '' && $legacy) {
        $key = (string) ($set['api_key'] ?? '');
    }
    $model =
        trim((string) ($set['model'] ?? '')) ?:
        $providers[$provider]['models'][0] ?? '';
    $activePrice = aiKnownPrice(
        [
            'price_book' => $set['price_book'] ?? [],
            'known_prices' => $set['known_prices'] ?? [],
        ],
        $provider,
        $model,
    );

    return [
        'enabled' =>
            coreFeatureEnabled($config, 'assistant') &&
            (bool) ($set['enabled'] ?? false),
        'provider' => $provider,
        'base_url' => $base ?: $providers[$provider]['base'],
        'model' => $model,
        // Never returned to the browser; see aiSettingsView.
        'api_key' => $key,
        'connections' => array_map(
            static fn($one): array => [
                'base_url' => is_array($one)
                    ? trim((string) ($one['base_url'] ?? ''))
                    : '',
                'has_key' =>
                    is_array($one) && (string) ($one['api_key'] ?? '') !== '',
            ],
            $connections,
        ),

        /*
         * Prices per million tokens, in cents.
         *
         * Optional: without them everything still works and the session simply
         * reports token counts rather than money. A wrong price is worse than
         * no price, so nothing is guessed from the model name.
         */
        'price_in' => $activePrice['in'] ?? (float) ($set['price_in'] ?? 0),
        'price_out' => $activePrice['out'] ?? (float) ($set['price_out'] ?? 0),
        'price_cached' =>
            $activePrice['cached'] ?? (float) ($set['price_cached'] ?? 0),

        // Limits on one session. Zero means no limit of that kind.
        'max_seconds' => max(0, (int) ($set['max_seconds'] ?? 3600)),
        'max_cents' => max(0, (float) ($set['max_cents'] ?? 50)),
        'max_turns' => max(0, (int) ($set['max_turns'] ?? 40)),
        'daily_cents' => max(0, (float) ($set['daily_cents'] ?? 0)),
        'monthly_cents' => max(0, (float) ($set['monthly_cents'] ?? 0)),

        /*
         * How hard a reasoning model should think, where it has the option.
         *
         * Only the Responses endpoint takes this. On chat completions the same
         * models refuse function tools whenever it is set to anything but
         * none, which is the error that led to this endpoint being spoken at
         * all.
         */
        'reasoning_effort' => in_array(
            (string) ($set['reasoning_effort'] ?? ''),
            array_merge([''], aiEffortLevels()),
            true,
        )
            ? (string) ($set['reasoning_effort'] ?? '')
            : '',

        /*
         * How much the model can hold, and when to start summarising.
         *
         * The window is not discoverable from the API - it is a fact about the
         * model that changes when the model does - so it is typed in. The
         * default is deliberately conservative: guessing high means the
         * provider rejects the request, which is the failure this exists to
         * avoid.
         */
        'context_tokens' => max(4000, (int) ($set['context_tokens'] ?? 128000)),
        'compact_at' => min(85, max(30, (int) ($set['compact_at'] ?? 70))),
        'compact_automatically' =>
            (bool) ($set['compact_automatically'] ?? true),

        /*
         * Whether recommended commands come with their explanation.
         *
         * On by default. Somebody pasting a command they do not understand into
         * a shell is the thing this is meant to prevent rather than a step on
         * the way to it - but it is a preference, and somebody who reads shell
         * fluently should be able to turn the commentary off.
         */
        'explain_commands' => (bool) ($set['explain_commands'] ?? true),

        /*
         * Whether it may see what the host has that this account cannot.
         *
         * Off by default, because it changes the kind of advice given: with it
         * on, the assistant can say "ask your host to run this as root", which
         * means the operator opening a support ticket. That is often exactly
         * the right move and is nobody's decision but theirs to enable.
         */
        'host_commands' => (bool) ($set['host_commands'] ?? false),

        /*
         * Prices remembered per provider and model.
         *
         * Keyed "provider/model". The problem is small and constant: changing
         * the model leaves the price boxes holding the previous model's
         * numbers, which are now wrong and look right, and nobody notices until
         * a bill arrives. Remembering them means switching models and switching
         * back does not silently corrupt every cost figure in between.
         */
        'known_prices' => is_array($set['known_prices'] ?? null)
            ? $set['known_prices']
            : [],
        'price_book' => aiPriceBook($set),

        'tools' => $set['tools'] ?? [],
    ];
}

/** Shared spend ceilings: UTC day and calendar month, across every AI session. */
function aiSharedBudget(PDO $ai, array $settings, ?int $now = null): array
{
    $now ??= time();
    $windows = [
        'daily' => gmmktime(
            0,
            0,
            0,
            (int) gmdate('n', $now),
            (int) gmdate('j', $now),
            (int) gmdate('Y', $now),
        ),
        'monthly' => gmmktime(
            0,
            0,
            0,
            (int) gmdate('n', $now),
            1,
            (int) gmdate('Y', $now),
        ),
    ];
    $limits = [
        'daily' => (int) round((float) ($settings['daily_cents'] ?? 0) * 1000),
        'monthly' => (int) round(
            (float) ($settings['monthly_cents'] ?? 0) * 1000,
        ),
    ];
    $spent = $ai->prepare(
        'SELECT COALESCE(SUM(millicents), 0) FROM ai_calls WHERE at >= ?',
    );
    $out = [];
    foreach ($windows as $name => $from) {
        $spent->execute([$from]);
        $used = (int) $spent->fetchColumn();
        $limit = $limits[$name];
        $out[$name] = [
            'used_millicents' => $used,
            'limit_millicents' => $limit,
            'remaining_millicents' =>
                $limit === 0 ? null : max(0, $limit - $used),
            'ok' => $limit === 0 || $used < $limit,
        ];
    }
    return $out;
}

/** @return array{ok: bool, which: ?string, why: string} */
function aiWithinSharedBudget(PDO $ai, array $settings): array
{
    foreach (aiSharedBudget($ai, $settings) as $name => $budget) {
        if (!$budget['ok']) {
            return [
                'ok' => false,
                'which' => $name . '_spend',
                'why' => sprintf(
                    'The shared %s AI budget has spent about %s, which is the limit set in Settings.',
                    $name,
                    aiMoney((int) $budget['used_millicents']),
                ),
            ];
        }
    }
    return ['ok' => true, 'which' => null, 'why' => ''];
}

/**
 * The prices for one provider and model, if this install has been told them.
 *
 * @return array{in: float, out: float, cached: float}|null
 */
function aiKnownPrice(array $settings, string $provider, string $model): ?array
{
    $today = gmdate('Y-m-d');
    $book = aiPriceBook($settings);
    $matches = array_values(
        array_filter(
            $book,
            fn(array $row): bool => $row['provider'] === $provider &&
                $row['model'] === $model &&
                $row['active_from'] <= $today &&
                ($row['retired_on'] === null || $row['retired_on'] >= $today),
        ),
    );
    usort(
        $matches,
        fn(array $a, array $b): int => strcmp(
            $b['active_from'],
            $a['active_from'],
        ),
    );
    if ($matches !== []) {
        return [
            'in' => $matches[0]['in'],
            'out' => $matches[0]['out'],
            'cached' => $matches[0]['cached'],
        ];
    }
    $found = $settings['known_prices'][$provider . '/' . $model] ?? null;

    return is_array($found)
        ? [
            'in' => (float) ($found['in'] ?? 0),
            'out' => (float) ($found['out'] ?? 0),
            'cached' => (float) ($found['cached'] ?? 0),
        ]
        : null;
}

/** Dated price records, with legacy provider/model map converted on read. */
function aiPriceBook(array $settings): array
{
    $rows = is_array($settings['price_book'] ?? null)
        ? $settings['price_book']
        : [];
    foreach ((array) ($settings['known_prices'] ?? []) as $key => $price) {
        [$provider, $model] = array_pad(explode('/', (string) $key, 2), 2, '');
        if ($provider === '' || $model === '' || !is_array($price)) {
            continue;
        }
        $found = array_filter(
            $rows,
            fn(mixed $row): bool => is_array($row) &&
                ($row['provider'] ?? '') === $provider &&
                ($row['model'] ?? '') === $model,
        );
        if ($found === []) {
            $rows[] =
                [
                    'id' => sha1($key . '|legacy'),
                    'provider' => $provider,
                    'model' => $model,
                    'active_from' => '1970-01-01',
                    'retired_on' => null,
                ] + $price;
        }
    }
    return array_values(
        array_map(
            fn(array $row): array => [
                'id' => (string) ($row['id'] ?? sha1(json_encode($row))),
                'provider' => (string) ($row['provider'] ?? ''),
                'model' => (string) ($row['model'] ?? ''),
                'in' => (float) ($row['in'] ?? 0),
                'out' => (float) ($row['out'] ?? 0),
                'cached' => (float) ($row['cached'] ?? 0),
                'cache_write_5m' => (float) ($row['cache_write_5m'] ?? 0),
                'cache_write_1h' => (float) ($row['cache_write_1h'] ?? 0),
                'cache_refresh' => (float) ($row['cache_refresh'] ?? 0),
                'active_from' =>
                    (string) ($row['active_from'] ?? gmdate('Y-m-d')),
                'retired_on' => $row['retired_on'] ?? null ?: null,
            ],
            array_filter($rows, 'is_array'),
        ),
    );
}

/** The effort levels, cheapest first. The empty string means "do not send one". */
function aiEffortLevels(): array
{
    return ['minimal', 'low', 'medium', 'high'];
}

/**
 * The lower of two effort levels.
 *
 * A ceiling has to be enforced where the request is built, not where it is
 * chosen - anything else is a limit that holds until somebody finds the other
 * route to the same field.
 */
function aiEffortAtMost(string $wanted, string $ceiling): string
{
    $levels = aiEffortLevels();

    $wantedAt = array_search($wanted, $levels, true);
    if ($wantedAt === false) {
        return $ceiling;
    }

    $ceilingAt = array_search($ceiling, $levels, true);
    if ($ceilingAt === false) {
        // No ceiling configured means the provider's own default, and a request
        // that names an effort where none was configured is not a raise.
        return $wanted;
    }

    return $levels[min($wantedAt, $ceilingAt)];
}

/** Whether there is enough configured to try. */
function aiReady(array $config): array
{
    $set = aiSettings($config);

    if (!$set['enabled']) {
        return [
            'ok' => false,
            'why' =>
                aiPersona($config)['name'] . ' is switched off in Settings.',
        ];
    }
    if ($set['model'] === '') {
        return [
            'ok' => false,
            'why' => 'No model has been chosen in Settings.',
        ];
    }
    if ($set['api_key'] === '' && $set['provider'] !== 'compatible') {
        return ['ok' => false, 'why' => 'No API key has been set in Settings.'];
    }
    if (!function_exists('curl_init')) {
        return [
            'ok' => false,
            'why' => 'This PHP has no curl, so it cannot reach a model.',
        ];
    }
    try {
        aiEndpointPolicy($set['provider'], $set['base_url']);
    } catch (ActionRefused $e) {
        return ['ok' => false, 'why' => $e->getMessage()];
    }

    return ['ok' => true, 'why' => ''];
}

/**
 * Turns tokens into money, in millicents.
 *
 * Cached input is billed separately by every provider that offers it, usually
 * at a tenth of the normal rate, and on a conversation that re-sends a long
 * system prompt every turn that is most of the bill. Counted separately or the
 * estimate is wrong by a factor.
 */
function aiCost(array $settings, int $in, int $out, int $cached): int
{
    $perMillionToMillicents = fn(float $cents): float => ($cents * 1000) /
        1000000;

    $total =
        $in * $perMillionToMillicents($settings['price_in']) +
        $out * $perMillionToMillicents($settings['price_out']) +
        $cached * $perMillionToMillicents($settings['price_cached']);

    return (int) round($total);
}

/** Millicents as something to put on screen. */
function aiMoney(int $millicents, string $symbol = '$'): string
{
    if ($millicents === 0) {
        return $symbol . '0.00';
    }

    $cents = $millicents / 1000;

    /*
     * Sub-cent totals said as such rather than rounded to zero.
     *
     * A session that cost four tenths of a cent showing "$0.00" reads as "this
     * is free", which is the wrong impression to leave somebody with when they
     * are about to run fifty of them.
     */
    if ($cents < 1) {
        return sprintf('under %s0.01', $symbol);
    }

    return $symbol . number_format($cents / 100, 2);
}

/**
 * One request to the model.
 *
 * Returns the raw decoded response alongside what it cost, with errors as
 * values rather than exceptions - a provider being down is something the
 * conversation should say out loud, not something that ends the request with a
 * stack trace.
 *
 * @return array<string, mixed>
 */
function aiAsk(
    array $settings,
    array $messages,
    array $tools,
    ?string $system = null,
    string $effort = '',
): array {
    $started = microtime(true);
    $provider = $settings['provider'];

    try {
        $endpoint = aiEndpointPolicy($provider, (string) $settings['base_url']);
    } catch (ActionRefused $e) {
        return [
            'ok' => false,
            'error' => $e->getMessage(),
            'status' => 0,
            'ms' => 0,
            'request' => null,
            'response' => null,
        ];
    }
    $settings['base_url'] = $endpoint['url'];

    /*
     * A per-conversation effort, never above the configured one.
     *
     * The setting is a ceiling, not a default that anything may raise: turning
     * thinking down for a quick question should be free, and turning it up
     * should be a decision made once, in settings, by whoever pays the bill.
     */
    if ($effort !== '') {
        $settings['reasoning_effort'] = aiEffortAtMost(
            $effort,
            $settings['reasoning_effort'],
        );
    }

    [$url, $headers, $body] = match ($provider) {
        'anthropic' => aiAnthropicRequest(
            $settings,
            $messages,
            $tools,
            $system,
        ),
        'openai-responses' => aiResponsesRequest(
            $settings,
            $messages,
            $tools,
            $system,
        ),
        default => aiOpenAiRequest($settings, $messages, $tools, $system),
    };

    $handle = curl_init($url);
    $protocols = $endpoint['local']
        ? CURLPROTO_HTTP | CURLPROTO_HTTPS
        : CURLPROTO_HTTPS;
    curl_setopt_array($handle, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode(
            $body,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ),
        CURLOPT_TIMEOUT => AI_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_PROTOCOLS => $protocols,
        CURLOPT_REDIR_PROTOCOLS => $protocols,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_RESOLVE => $endpoint['resolve'],
        // A model endpoint is somebody else's service over the internet; the
        // certificate check is the only thing standing between the API key and
        // whoever is on the path.
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $raw = curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $problem = curl_error($handle);
    /*
     * No curl_close: it has done nothing since PHP 8.0 and is deprecated from
     * 8.5, where it writes a notice to the problem log on every call. The
     * handle is released when it goes out of scope. lib/urlwatch.php learned
     * this first; these three did not get the message until somebody's 8.5
     * host filled its log with it.
     */

    $ms = (int) round((microtime(true) - $started) * 1000);

    if ($raw === false) {
        return [
            'ok' => false,
            'error' =>
                $problem !== ''
                    ? aiRedactErrorText($problem)
                    : 'the request failed',
            'status' => $status,
            'ms' => $ms,
            'request' => $body,
            'response' => null,
        ];
    }

    $decoded = json_decode((string) $raw, true);

    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'error' => 'the model answered with something that is not JSON',
            'status' => $status,
            'ms' => $ms,
            'request' => $body,
            'response' => [
                'raw' => mb_substr(aiRedactErrorText((string) $raw), 0, 2000),
            ],
        ];
    }

    if ($status < 200 || $status >= 300) {
        return [
            'ok' => false,
            'error' => aiErrorText($decoded, $status),
            'status' => $status,
            'ms' => $ms,
            'request' => $body,
            'response' => aiRedactErrorPayload($decoded),
        ];
    }

    $parsed = match ($provider) {
        'anthropic' => aiAnthropicReply($decoded),
        'openai-responses' => aiResponsesReply($decoded),
        default => aiOpenAiReply($decoded),
    };

    return $parsed + [
        'ok' => true,
        'error' => null,
        'status' => $status,
        'ms' => $ms,
        'request' => $body,
        'response' => $decoded,
    ];
}

/** Whatever the provider called the problem, in one line. */
function aiErrorText(array $decoded, int $status): string
{
    foreach (
        [['error', 'message'], ['error', 'type'], ['message'], ['detail']]
        as $path
    ) {
        $at = $decoded;
        foreach ($path as $key) {
            if (!is_array($at) || !isset($at[$key])) {
                $at = null;
                break;
            }
            $at = $at[$key];
        }
        if (is_string($at) && $at !== '') {
            return sprintf('%s (HTTP %d)', aiRedactErrorText($at), $status);
        }
    }

    return sprintf('the model refused the request (HTTP %d)', $status);
}

/** Redacts credential-shaped text before a provider failure becomes evidence. */
function aiRedactErrorText(string $text): string
{
    $patterns = [
        '/(authorization\s*:\s*bearer\s+)\S+/i' => '$1[hidden]',
        '/((?:api[-_]?key|access[-_]?key|token|secret|password)\s*[=:]\s*["\']?)\S+/i' =>
            '$1[hidden]',
        '/\b(?:sk|rk|AIza)[-_A-Za-z0-9]{16,}\b/' => '[hidden]',
    ];
    foreach ($patterns as $pattern => $replacement) {
        $text = (string) preg_replace($pattern, $replacement, $text);
    }
    return mb_substr(trim($text), 0, 800);
}

/** Removes explicit credential fields from a structured provider error payload. */
function aiRedactErrorPayload(array $payload): array
{
    $clean = [];
    foreach ($payload as $key => $value) {
        $name = strtolower((string) $key);
        if (
            preg_match(
                '/(?:api[-_]?key|access[-_]?key|token|secret|password|authorization)/',
                $name,
            )
        ) {
            $clean[$key] = '[hidden]';
        } elseif (is_array($value)) {
            $clean[$key] = aiRedactErrorPayload($value);
        } elseif (is_string($value)) {
            $clean[$key] = aiRedactErrorText($value);
        } else {
            $clean[$key] = $value;
        }
    }
    return $clean;
}

/** @return array{0: string, 1: list<string>, 2: array} */
function aiOpenAiRequest(
    array $settings,
    array $messages,
    array $tools,
    ?string $system,
): array {
    $out = [];

    foreach ($messages as $message) {
        // An image is a content block, not a string; see aiTranscript.
        if (isset($message['image'])) {
            $out[] = [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image_url',
                        'image_url' => ['url' => (string) $message['image']],
                    ],
                ],
            ];
            continue;
        }
        $out[] = $message;
    }

    $body = [
        'model' => $settings['model'],
        'messages' =>
            $system === null
                ? $out
                : array_merge(
                    [['role' => 'system', 'content' => $system]],
                    $out,
                ),
    ];

    if ($tools !== []) {
        $body['tools'] = $tools;
        $body['tool_choice'] = 'auto';
    }

    $headers = ['Content-Type: application/json'];
    if ($settings['api_key'] !== '') {
        $headers[] = 'Authorization: Bearer ' . $settings['api_key'];
    }

    return [
        rtrim($settings['base_url'], '/') . '/chat/completions',
        $headers,
        $body,
    ];
}

/**
 * The reply, in the shape the rest of this code works in.
 *
 * @return array<string, mixed>
 */
function aiOpenAiReply(array $decoded): array
{
    $choice = $decoded['choices'][0]['message'] ?? [];
    $usage = $decoded['usage'] ?? [];

    $calls = [];
    foreach ($choice['tool_calls'] ?? [] as $call) {
        $arguments = $call['function']['arguments'] ?? '{}';
        $calls[] = [
            'id' => (string) ($call['id'] ?? uniqid('call_', true)),
            'name' => (string) ($call['function']['name'] ?? ''),
            /*
             * Arguments arrive as a JSON string, and sometimes an invalid one.
             *
             * A model that produced malformed JSON has made a mistake worth
             * telling it about rather than crashing over - so it becomes an
             * empty set here and the checker refuses it with a message the
             * model can act on.
             */
            'arguments' => is_array($arguments)
                ? $arguments
                : (json_decode((string) $arguments, true) ?:
                []),
        ];
    }

    return [
        'text' => (string) ($choice['content'] ?? ''),
        'tool_calls' => $calls,
        'finish' => (string) ($decoded['choices'][0]['finish_reason'] ?? ''),
        'tokens_in' => (int) ($usage['prompt_tokens'] ?? 0),
        'tokens_out' => (int) ($usage['completion_tokens'] ?? 0),
        'tokens_cached' =>
            (int) ($usage['prompt_tokens_details']['cached_tokens'] ?? 0),
    ];
}

/** @return array{0: string, 1: list<string>, 2: array} */
function aiAnthropicRequest(
    array $settings,
    array $messages,
    array $tools,
    ?string $system,
): array {
    /*
     * Anthropic keeps the system prompt out of the message list and names tool
     * results differently. Translated here rather than storing two shapes.
     */
    $out = [];
    foreach ($messages as $message) {
        if (isset($message['image'])) {
            /*
             * Anthropic wants the bytes and the type separately, not a data URL.
             */
            $url = (string) $message['image'];
            if (preg_match('#^data:(image/[a-z]+);base64,(.*)$#s', $url, $m)) {
                $out[] = [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $m[1],
                                'data' => $m[2],
                            ],
                        ],
                    ],
                ];
            }
            continue;
        }

        if (($message['role'] ?? '') === 'tool') {
            $out[] = [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' =>
                            (string) ($message['tool_call_id'] ?? ''),
                        'content' => (string) ($message['content'] ?? ''),
                    ],
                ],
            ];
            continue;
        }

        if (
            ($message['role'] ?? '') === 'assistant' &&
            !empty($message['tool_calls'])
        ) {
            $blocks = [];
            if (($message['content'] ?? '') !== '') {
                $blocks[] = [
                    'type' => 'text',
                    'text' => (string) $message['content'],
                ];
            }
            foreach ($message['tool_calls'] as $call) {
                $blocks[] = [
                    'type' => 'tool_use',
                    'id' => (string) $call['id'],
                    'name' => (string) $call['function']['name'],
                    'input' =>
                        json_decode(
                            (string) $call['function']['arguments'],
                            true,
                        ) ?:
                        (object) [],
                ];
            }
            $out[] = ['role' => 'assistant', 'content' => $blocks];
            continue;
        }

        $out[] = [
            'role' => $message['role'],
            'content' => (string) ($message['content'] ?? ''),
        ];
    }

    $body = [
        'model' => $settings['model'],
        'max_tokens' => 4096,
        'messages' => $out,
    ];

    if ($system !== null) {
        $body['system'] = $system;
    }

    if ($tools !== []) {
        $body['tools'] = array_map(
            fn(array $tool): array => [
                'name' => $tool['function']['name'],
                'description' => $tool['function']['description'],
                'input_schema' => $tool['function']['parameters'],
            ],
            $tools,
        );
    }

    return [
        rtrim($settings['base_url'], '/') . '/messages',
        [
            'Content-Type: application/json',
            'x-api-key: ' . $settings['api_key'],
            'anthropic-version: 2023-06-01',
        ],
        $body,
    ];
}

/** @return array<string, mixed> */
function aiAnthropicReply(array $decoded): array
{
    $text = '';
    $calls = [];

    foreach ($decoded['content'] ?? [] as $block) {
        if (($block['type'] ?? '') === 'text') {
            $text .= (string) ($block['text'] ?? '');
        }
        if (($block['type'] ?? '') === 'tool_use') {
            $calls[] = [
                'id' => (string) ($block['id'] ?? uniqid('call_', true)),
                'name' => (string) ($block['name'] ?? ''),
                'arguments' => is_array($block['input'] ?? null)
                    ? $block['input']
                    : [],
            ];
        }
    }

    $usage = $decoded['usage'] ?? [];

    return [
        'text' => $text,
        'tool_calls' => $calls,
        'finish' => (string) ($decoded['stop_reason'] ?? ''),
        'tokens_in' => (int) ($usage['input_tokens'] ?? 0),
        'tokens_out' => (int) ($usage['output_tokens'] ?? 0),
        'tokens_cached' => (int) ($usage['cache_read_input_tokens'] ?? 0),
    ];
}

/**
 * The Responses shape, for models that refuse tools on chat completions.
 *
 * Flatter than chat completions in one way and stranger in another: the
 * conversation is a list of "input items" rather than messages, tools are
 * described at the top level rather than wrapped in a `function` object, and a
 * tool result is its own item type instead of a message with a role.
 *
 * @return array{0: string, 1: list<string>, 2: array}
 */
function aiResponsesRequest(
    array $settings,
    array $messages,
    array $tools,
    ?string $system,
): array {
    $input = [];

    foreach ($messages as $message) {
        if (isset($message['image'])) {
            $input[] = [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_image',
                        'image_url' => (string) $message['image'],
                    ],
                ],
            ];
            continue;
        }

        $role = (string) ($message['role'] ?? 'user');

        if ($role === 'tool') {
            $input[] = [
                'type' => 'function_call_output',
                'call_id' => (string) ($message['tool_call_id'] ?? ''),
                'output' => (string) ($message['content'] ?? ''),
            ];
            continue;
        }

        if ($role === 'assistant' && !empty($message['tool_calls'])) {
            /*
             * The text and the calls are separate items here.
             *
             * Chat completions carries both on one message; Responses wants the
             * message and then each call as its own item, in order.
             */
            if (($message['content'] ?? '') !== '') {
                $input[] = [
                    'role' => 'assistant',
                    'content' => [
                        [
                            'type' => 'output_text',
                            'text' => (string) $message['content'],
                        ],
                    ],
                ];
            }

            foreach ($message['tool_calls'] as $call) {
                $input[] = [
                    'type' => 'function_call',
                    'call_id' => (string) $call['id'],
                    'name' => (string) $call['function']['name'],
                    'arguments' => (string) $call['function']['arguments'],
                ];
            }
            continue;
        }

        $input[] = [
            'role' => $role,
            'content' => [
                [
                    'type' =>
                        $role === 'assistant' ? 'output_text' : 'input_text',
                    'text' => (string) ($message['content'] ?? ''),
                ],
            ],
        ];
    }

    $body = [
        'model' => $settings['model'],
        'input' => $input,
    ];

    if ($system !== null) {
        // Called `instructions` here rather than a message with a system role.
        $body['instructions'] = $system;
    }

    if ($tools !== []) {
        $body['tools'] = array_map(
            fn(array $tool): array => [
                'type' => 'function',
                'name' => $tool['function']['name'],
                'description' => $tool['function']['description'],
                'parameters' => $tool['function']['parameters'],
                /*
                 * Strict mode, since this endpoint offers it.
                 *
                 * The schemas here already say additionalProperties: false and list
                 * every required field, which is what strict mode needs - so the
                 * model is constrained to produce valid arguments rather than being
                 * told off for invalid ones afterwards.
                 */
                'strict' => true,
            ],
            $tools,
        );
        $body['tool_choice'] = 'auto';
    }

    if (($settings['reasoning_effort'] ?? '') !== '') {
        $body['reasoning'] = ['effort' => $settings['reasoning_effort']];
    }

    $headers = ['Content-Type: application/json'];
    if ($settings['api_key'] !== '') {
        $headers[] = 'Authorization: Bearer ' . $settings['api_key'];
    }

    return [rtrim($settings['base_url'], '/') . '/responses', $headers, $body];
}

/** @return array<string, mixed> */
function aiResponsesReply(array $decoded): array
{
    $text = '';
    $calls = [];

    foreach ($decoded['output'] ?? [] as $item) {
        $type = (string) ($item['type'] ?? '');

        if ($type === 'message') {
            foreach ($item['content'] ?? [] as $block) {
                if (($block['type'] ?? '') === 'output_text') {
                    $text .= (string) ($block['text'] ?? '');
                }
            }
            continue;
        }

        if ($type === 'function_call') {
            $arguments = $item['arguments'] ?? '{}';
            $calls[] = [
                'id' =>
                    (string) ($item['call_id'] ??
                        ($item['id'] ?? uniqid('call_', true))),
                'name' => (string) ($item['name'] ?? ''),
                'arguments' => is_array($arguments)
                    ? $arguments
                    : (json_decode((string) $arguments, true) ?:
                    []),
            ];
        }
    }

    $usage = $decoded['usage'] ?? [];

    return [
        'text' => $text,
        'tool_calls' => $calls,
        'finish' => (string) ($decoded['status'] ?? ''),
        'tokens_in' => (int) ($usage['input_tokens'] ?? 0),
        'tokens_out' => (int) ($usage['output_tokens'] ?? 0),
        /*
         * Reasoning tokens are billed as output and are invisible otherwise.
         *
         * On a reasoning model they can be most of the bill, so an estimate
         * that ignored them would be wrong by a large factor on exactly the
         * models this endpoint exists for. They are already inside
         * output_tokens, so this is reported rather than added.
         */
        'tokens_reasoning' =>
            (int) ($usage['output_tokens_details']['reasoning_tokens'] ?? 0),
        'tokens_cached' =>
            (int) ($usage['input_tokens_details']['cached_tokens'] ?? 0),
    ];
}
