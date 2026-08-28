<?php
declare(strict_types=1);

/*
 * Pictures the assistant can make, and how often it may.
 *
 * Two quite different things share this file because they share a bill.
 *
 * A reaction - a small animated image expressing a mood - costs a search
 * against somebody's catalogue and nothing else. An generated image costs real
 * money per call and takes seconds. Both are entirely optional, both are off
 * until somebody configures a key, and both are rate-limited by the hour
 * because a model that finds a thing amusing will keep doing it.
 *
 * The self-portrait deserves a note. Letting the assistant regenerate its own
 * avatar is a novelty, and novelties in a monitoring tool have to be cheap and
 * bounded or they become the reason somebody turns the whole feature off. So it
 * shares the same hourly budget as everything else here, it is resized to 256
 * pixels before it is stored, and it can be switched off on its own.
 */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/spans.php';
require_once __DIR__ . '/aidb.php';
require_once __DIR__ . '/ai.php';

/**
 * Every style the generator offers, read from data rather than written here.
 *
 * A hundred and twenty-three of them, and the list is somebody else's to
 * change. Hard-coding it meant a handful were supported and the rest did not
 * exist as far as this tool was concerned; as a file it can be replaced without
 * touching any code, and the one thing this needs to know about a style - that
 * its key is also its endpoint - is true of all of them.
 *
 * @return array<string, string> key => the name the provider gives it
 */
function aiImageStyles(): array
{
    static $styles = null;

    if ($styles !== null) {
        return $styles;
    }

    $file = __DIR__ . '/../data/image-styles.json';
    $raw = is_file($file) ? file_get_contents($file) : false;
    $found = $raw === false ? null : json_decode($raw, true);

    /*
     * A fallback that is not empty.
     *
     * If the file is missing or unreadable the picture tools would otherwise
     * offer nothing, which reads as "this install cannot generate pictures"
     * rather than "a file is missing". One style is enough to keep the feature
     * working and obvious enough to be noticed.
     */
    $styles = is_array($found) && $found !== []
        ? array_map('strval', $found)
        : ['text2img' => 'Plain (the style list could not be read)'];

    return $styles;
}



/** How large a picture may be, and the sizes worth asking for. */
function aiImageShapes(): array
{
    /*
     * Multiples of 32, between 128 and 1536, and 256-700 is where the
     * generator behaves. Past that it starts repeating limbs and horizons -
     * so the choices offered stay inside the range the provider recommends
     * rather than the range it accepts.
     */
    return [
        'square' => ['label' => 'Square', 'width' => 512, 'height' => 512],
        'classic' => ['label' => 'Classic', 'width' => 640, 'height' => 480],
        'wide' => ['label' => 'Wide', 'width' => 704, 'height' => 384],
        'tall' => ['label' => 'Tall', 'width' => 384, 'height' => 704],
        'small' => ['label' => 'Small square', 'width' => 256, 'height' => 256],
    ];
}


/** The four generator tiers, and what each is for. */
function aiImageQualities(): array
{
    return [
        'standard' => [
            'label' => 'Standard',
            'version' => 'standard',
            'what' => 'The ordinary generator. Fine for anything illustrative.',
        ],
        'hd' => [
            'label' => 'HD',
            'version' => 'hd',
            'what' => 'More detail, more time, and it counts against the same allowance.',
        ],
        'genius' => [
            'label' => 'Genius',
            'version' => 'genius',
            'what' => 'The expensive tier, with its own monthly allowance. Takes a '
                . 'preference for how it should look.',
        ],
        'super_genius' => [
            'label' => 'Super genius',
            'version' => 'super_genius',
            'what' => 'The most expensive, and the only one that will render at 2K or 4K.',
        ],
    ];
}



/**
 * The image settings in force.
 *
 * @return array<string, mixed>
 */
function aiImageSettings(array $config): array
{
    $set = $config['ai']['images'] ?? [];
    $styles = aiImageStyles();

    $allowed = array_values(array_filter(
        (array)($set['styles'] ?? ['text2img']),
        fn(string $key): bool => isset($styles[$key])
    ));

    return [
        'enabled' => (bool)($set['enabled'] ?? false),
        'provider' => 'deepai',
        'api_key' => (string)($set['api_key'] ?? ''),
        'styles' => $allowed === [] ? ['text2img'] : $allowed,
        'quality' => isset(aiImageQualities()[$set['quality'] ?? ''])
            ? (string)$set['quality'] : 'standard',

        /*
         * Three parameters the generator documents and this never sent.
         *
         * genius_preference and resolution are each read by exactly one tier;
         * negative_prompt is read by all of them and is the difference between
         * a picture with six fingers and one without.
         */
        'genius_preference' => isset(aiGeniusPreferences()[$set['genius_preference'] ?? ''])
            ? (string)($set['genius_preference'] ?? '') : '',
        'resolution' => isset(aiImageResolutions()[$set['resolution'] ?? ''])
            ? (string)($set['resolution'] ?? '') : '',
        'negative_prompt' => (string)($set['negative_prompt']
            ?? 'bad anatomy, blurry, cropped, deformed, disfigured, extra limbs, text, '
               . 'watermark'),
        'shape' => isset(aiImageShapes()[$set['shape'] ?? ''])
            ? (string)$set['shape'] : 'square',
        /*
         * Per hour, across everything in this file.
         *
         * A model that finds a thing amusing will keep doing it, and each of
         * these costs money. Ten is generous for illustrating a diagnosis and
         * tight enough that a loop cannot run up a bill before anybody notices.
         */
        'per_hour' => max(0, (int)($set['per_hour'] ?? 10)),
        'self_portrait' => (bool)($set['self_portrait'] ?? false),

        /*
         * Searching for an animation, which is a different thing from
         * generating a picture and is priced, limited and named differently.
         *
         * `search_key` was `giphy_key`. The old name is still read so nobody's
         * configuration breaks, but the field is not named after one company:
         * there are three services with the same shape, the endpoint is a
         * setting, and hard-coding a brand into a config key is how an install
         * ends up unable to switch.
         */
        'reactions' => (bool)($set['reactions'] ?? false),
        'search_service' => isset(aiReactionServices()[$set['search_service'] ?? ''])
            ? (string)$set['search_service']
            : 'tenor',
        'search_key' => (string)($set['search_key'] ?? $set['giphy_key'] ?? ''),
        'search_base' => (string)($set['search_base'] ?? $set['giphy_base'] ?? ''),
        /*
         * Searches an hour. A hundred is the free allowance these services
         * publish; it can be set lower and not higher, because a ceiling a
         * setting can lift past the provider's own protects nothing.
         */
        'searches_per_hour' => max(0, min(100, (int)($set['searches_per_hour'] ?? 100))),
        'rating' => in_array($set['rating'] ?? 'g', ['g', 'pg', 'pg-13'], true)
            ? (string)($set['rating'] ?? 'g') : 'g',

        /*
         * What generated pictures cost, for the running total.
         *
         * DeepAI sells a monthly plan with an allowance and then charges for
         * credits past it, so a per-image price is only true once the allowance
         * is gone. Both are recorded and the arithmetic is shown rather than
         * hidden: while there is allowance left the marginal cost is nothing,
         * and after it the per-image price applies. Guessing either would
         * produce a total that looks authoritative and is not.
         */
        'monthly_cents' => max(0, (float)($set['monthly_cents'] ?? 0)),
        'included_standard' => max(0, (int)($set['included_standard']
            ?? $set['included_per_month'] ?? 0)),
        'included_genius' => max(0, (int)($set['included_genius'] ?? 0)),
        'price_standard' => max(0, (float)($set['price_standard'] ?? 0)),
        'price_hd' => max(0, (float)($set['price_hd'] ?? 0)),
        'price_genius' => max(0, (float)($set['price_genius'] ?? 0)),

        // Overridable so the paths that leave this machine can be exercised
        // against a stub; there is one real address for each in practice.
        'base' => (string)($set['base'] ?? ''),

        // Kept so an install configured before the rename still works.
        'giphy_base' => (string)($set['giphy_base'] ?? ''),
    ];
}

/**
 * How many pictures have been made in the last hour, and whether another may be.
 *
 * Counted from the record rather than held in memory, so restarting PHP does
 * not reset the budget - which on a shared host happens constantly.
 *
 * @return array{used: int, allowed: int, ok: bool, why: string}
 */
function aiImageBudget(PDO $ai, array $settings): array
{
    $allowed = (int)$settings['per_hour'];

    /*
     * Generated pictures only.
     *
     * Searching for an animation and generating an image share nothing except
     * that both produce a picture: one is a read against a free quota, the
     * other is a bill. Counting them together meant a handful of reactions
     * could use up the budget for the thing that actually costs money.
     */
    try {
        $stmt = $ai->prepare(
            "SELECT COUNT(*) FROM ai_images WHERE at >= ? AND kind != 'reaction'"
        );
        $stmt->execute([time() - SPAN_HOUR]);
        $used = (int)$stmt->fetchColumn();
    } catch (Throwable) {
        $used = 0;
    }

    if ($allowed <= 0) {
        return ['used' => $used, 'allowed' => 0, 'ok' => false,
                'why' => 'picture-making is switched off'];
    }

    if ($used >= $allowed) {
        return ['used' => $used, 'allowed' => $allowed, 'ok' => false, 'why' => sprintf(
            'that is %d pictures this hour, which is the limit. It resets as the hour '
            . 'rolls forward.',
            $used
        )];
    }

    return ['used' => $used, 'allowed' => $allowed, 'ok' => true, 'why' => ''];
}

/**
 * Whether another animation search may be made this hour.
 *
 * Its own budget, far larger than the picture one, because the two cost
 * different things. A hundred an hour is what these services publish as a free
 * allowance; going past it gets the key throttled, which breaks the feature for
 * everybody using this install rather than costing anybody money.
 *
 * @return array{used: int, allowed: int, ok: bool, why: string}
 */
function aiSearchBudget(PDO $ai, array $settings): array
{
    $allowed = (int)$settings['searches_per_hour'];

    try {
        $stmt = $ai->prepare(
            "SELECT COUNT(*) FROM ai_images WHERE at >= ? AND kind = 'reaction'"
        );
        $stmt->execute([time() - SPAN_HOUR]);
        $used = (int)$stmt->fetchColumn();
    } catch (Throwable) {
        $used = 0;
    }

    if ($allowed <= 0) {
        return ['used' => $used, 'allowed' => 0, 'ok' => false,
                'why' => 'reaction searches are switched off'];
    }

    if ($used >= $allowed) {
        return ['used' => $used, 'allowed' => $allowed, 'ok' => false, 'why' => sprintf(
            'that is %d searches this hour, which is the limit. It resets as the hour '
            . 'rolls forward.',
            $used
        )];
    }

    return ['used' => $used, 'allowed' => $allowed, 'ok' => true, 'why' => ''];
}

/**
 * What one generated picture costs, in millicents.
 *
 * The plan is a monthly fee with an allowance, so a picture inside the
 * allowance has no marginal cost at all - and reporting zero would be true and
 * useless, because the fee was paid for exactly this. What is reported instead
 * is the fee divided by the allowance: four hundred standard images on a $4.95
 * plan is 1.2375 cents each, which is the number somebody actually wants when
 * they ask what a conversation cost.
 *
 * Be clear about what that number is, because the UI has to be: it is an
 * ATTRIBUTION of a fixed cost, not a marginal one. Nothing is saved by
 * generating one fewer, and the two tiers divide the same fee, so adding up a
 * full month of both would come to more than the fee. It is the right figure
 * for "what did this conversation cost" and the wrong one for "what will one
 * more cost".
 *
 * Past the allowance the plan charges for credits, and there the per-image
 * price is a real marginal cost.
 */
function aiImageCost(PDO $ai, array $settings, string $quality): int
{
    $genius = $quality === 'genius';

    $allowance = $genius
        ? (int)$settings['included_genius']
        : (int)$settings['included_standard'];

    $used = aiImagesThisMonth($ai, $genius);

    if ($allowance > 0 && $used < $allowance) {
        $monthly = (float)$settings['monthly_cents'];
        return $monthly > 0 ? (int)round(($monthly / $allowance) * 1000) : 0;
    }

    /*
     * Past the allowance, or no allowance configured.
     *
     * The overage price if one was given; otherwise nothing, because a guessed
     * price is worse than an absent one and this is where guessing would be
     * least visible.
     */
    $price = match ($quality) {
        'hd' => (float)$settings['price_hd'],
        'genius' => (float)$settings['price_genius'],
        default => (float)$settings['price_standard'],
    };

    return $price > 0 ? (int)round($price * 1000) : 0;
}

/**
 * How many pictures of one tier were generated this calendar month.
 *
 * The calendar month because that is how the plan is sold, and the two tiers
 * are counted apart because they have separate allowances. Failed generations
 * do not count: nobody's allowance is spent on an error.
 */
function aiImagesThisMonth(PDO $ai, bool $genius): int
{
    $from = (int)mktime(0, 0, 0, (int)date('n'), 1, (int)date('Y'));

    try {
        $stmt = $ai->prepare(
            "SELECT COUNT(*) FROM ai_images
              WHERE at >= ? AND kind != 'reaction' AND error IS NULL
                AND (quality = ?) "
        );
        $stmt->execute([$from, $genius ? 'genius' : 'standard']);
        return (int)$stmt->fetchColumn();
    } catch (Throwable) {
        /*
         * An install from before the quality column existed.
         *
         * Everything in it was standard, which is what the default was, so the
         * standard count is the whole count and the genius count is zero.
         */
        if ($genius) {
            return 0;
        }

        try {
            $stmt = $ai->prepare(
                "SELECT COUNT(*) FROM ai_images
                  WHERE at >= ? AND kind != 'reaction' AND error IS NULL"
            );
            $stmt->execute([$from]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }
}

/**
 * What the picture plan is costing, and how much of it is used.
 *
 * For the settings page, so the arithmetic is visible rather than implied.
 *
 * @return array<string, mixed>
 */
function aiImageAllowance(PDO $ai, array $settings): array
{
    $out = ['monthly_cents' => $settings['monthly_cents'], 'tiers' => []];

    foreach ([['standard', 'included_standard'], ['genius', 'included_genius']] as [$tier, $key]) {
        $allowance = (int)$settings[$key];
        $used = aiImagesThisMonth($ai, $tier === 'genius');

        $out['tiers'][$tier] = [
            'allowance' => $allowance,
            'used' => $used,
            'left' => max(0, $allowance - $used),
            'each' => $allowance > 0 && $settings['monthly_cents'] > 0
                ? round($settings['monthly_cents'] / $allowance, 6)
                : null,
        ];
    }

    return $out;
}

/**
 * Keeps the bytes of a generated picture.
 *
 * Only generated ones. An animation found by a search is somebody else's file
 * sitting on their service, and copying it here would be storing their content
 * rather than ours; the link is enough for something nobody paid for.
 *
 * The reason to keep the bytes at all is that a provider URL expires. A picture
 * that was paid for and is a 404 a fortnight later is a record of nothing, and
 * "review them later in full size" is not possible from a dead link.
 *
 * @return array{ok: bool, bytes?: int, why?: string}
 */
function aiKeepAsset(PDO $ai, int $imageId, string $url, ?callable $fetch = null): array
{
    if ($url === '') {
        return ['ok' => false, 'why' => 'there was no picture to keep'];
    }

    $got = $fetch !== null ? $fetch($url) : aiFetchBytes($url);

    if (!$got['ok']) {
        return ['ok' => false, 'why' => $got['why']];
    }

    $size = @getimagesizefromstring($got['body']);
    if ($size === false) {
        return ['ok' => false, 'why' => 'what came back was not an image'];
    }

    $ai->prepare(
        'INSERT INTO ai_assets (image_id, at, mime, width, height, bytes, body)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $imageId, time(), (string)($size['mime'] ?? 'application/octet-stream'),
        (int)$size[0], (int)$size[1], strlen($got['body']), $got['body'],
    ]);

    return ['ok' => true, 'bytes' => strlen($got['body'])];
}

/** Largest generated image kept. Past this the link is all there is. */
const AI_ASSET_MAX = 12 * 1024 * 1024;

/** Fetches an image, refusing anything that is not one or is too large. */
function aiFetchBytes(string $url): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'why' => 'this PHP has no curl'];
    }
    try {
        $endpoint = aiEndpointPolicy('openai', $url, true);
    } catch (ActionRefused $e) {
        return ['ok' => false, 'why' => $e->getMessage()];
    }

    $handle = curl_init($endpoint['url']);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_RESOLVE => $endpoint['resolve'],
        /*
         * Stopped mid-download rather than checked afterwards.
         *
         * A Content-Length is a claim by somebody else's server; the only limit
         * that holds is one that counts the bytes as they arrive.
         */
        CURLOPT_NOPROGRESS => false,
        CURLOPT_PROGRESSFUNCTION => fn($handle, $expect, $got): int =>
            $got > AI_ASSET_MAX ? 1 : 0,
    ]);

    $body = curl_exec($handle);
    $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $error = curl_error($handle);
    /*
     * No curl_close: it has done nothing since PHP 8.0 and is deprecated from
     * 8.5, where it writes a notice to the problem log on every call. The
     * handle is released when it goes out of scope. lib/urlwatch.php learned
     * this first; these three did not get the message until somebody's 8.5
     * host filled its log with it.
     */

    if (!is_string($body) || $body === '') {
        return ['ok' => false, 'why' => $error !== '' ? $error : 'nothing came back'];
    }
    if ($status >= 400) {
        return ['ok' => false, 'why' => 'the picture host answered ' . $status];
    }
    if (strlen($body) > AI_ASSET_MAX) {
        return ['ok' => false, 'why' => 'that picture is larger than this keeps'];
    }

    return ['ok' => true, 'body' => $body];
}

/**
 * Asks the generator for a picture.
 *
 * Returns the URL it produced rather than the bytes: the image lives on their
 * CDN, and the bytes are fetched separately and kept - see aiKeepAsset, which
 * exists because that URL expires.
 *
 * The request is a multipart form with the key in a header, which is what their
 * own documentation shows:
 *
 *     curl -F 'text=...' -H 'api-key: ...' https://api.deepai.org/api/text2img
 *
 * @return array<string, mixed>
 */
function aiMakeImage(array $settings, string $prompt, string $style, ?callable $ask = null): array
{
    $styles = aiImageStyles();

    if (!isset($styles[$style]) || !in_array($style, $settings['styles'], true)) {
        return ['ok' => false, 'error' => 'that style is not one this install allows'];
    }

    if ($settings['api_key'] === '') {
        return ['ok' => false, 'error' => 'no key is set for the image generator'];
    }

    $shape = aiImageShapes()[$settings['shape']] ?? aiImageShapes()['square'];
    $quality = aiImageQualities()[$settings['quality']] ?? aiImageQualities()['standard'];

    $fields = [
        'text' => mb_substr($prompt, 0, 1000),
        'width' => (string)aiImageDimension((int)$shape['width']),
        'height' => (string)aiImageDimension((int)$shape['height']),
        'image_generator_version' => $quality['version'],
    ];

    /*
     * Two parameters that only one tier each will accept.
     *
     * Sent unconditionally they are either ignored or rejected depending on the
     * generator, and "rejected" is a failed picture with a message about a
     * parameter nobody chose. Gated on the tier that documents them.
     */
    if ($quality['version'] === 'genius' && $settings['genius_preference'] !== '') {
        $fields['genius_preference'] = $settings['genius_preference'];
    }

    if ($quality['version'] === 'super_genius' && $settings['resolution'] !== '') {
        $fields['resolution'] = $settings['resolution'];
    }

    if (trim((string)$settings['negative_prompt']) !== '') {
        $fields['negative_prompt'] = mb_substr(trim((string)$settings['negative_prompt']), 0, 500);
    }

    /*
     * The address, with a real default.
     *
     * `?? 'https://...'` was wrong here and produced a request to "/text2img":
     * the setting is an empty string when nothing is configured, and `??` only
     * falls back on null. curl called that "URL using bad/illegal format or
     * missing URL", which is exactly what it is and gives no hint where it came
     * from. `?:` falls back on empty as well.
     */
    $base = rtrim(((string)($settings['base'] ?? '')) ?: 'https://api.deepai.org/api', '/');
    $url = $base . '/' . $style;

    if ($ask !== null) {
        return $ask($url, $fields, $settings['api_key']);
    }

    return aiPostForm($url, $fields, ['api-key: ' . $settings['api_key']], 90);
}



/**
 * A width or height the generator will actually accept.
 *
 * Rounded to a multiple of 32 and clamped, rather than sent as typed and
 * refused: the failure at the far end is a rejected request with a message
 * about dimensions, which is a slow way to learn a rule that can be applied
 * here.
 */
function aiImageDimension(int $wanted): int
{
    $stepped = (int)(round($wanted / AI_IMAGE_STEP) * AI_IMAGE_STEP);

    return max(AI_IMAGE_MIN, min(AI_IMAGE_MAX, $stepped));
}


/** What a genius-tier picture should look like. Only that tier reads it. */
function aiGeniusPreferences(): array
{
    return [
        '' => 'no preference',
        'anime' => 'anime',
        'photography' => 'photography',
        'graphic' => 'graphic',
        'cinematic' => 'cinematic',
    ];
}


/** The two resolutions, which only the top tier accepts. */
function aiImageResolutions(): array
{
    return ['' => 'whatever the size asks for', '2k' => '2K', '4k' => '4K'];
}


/** Least and most the generator will accept, for anything typed in by hand. */
const AI_IMAGE_MIN = 128;
const AI_IMAGE_MAX = 1536;
const AI_IMAGE_STEP = 32;

/**
 * A form post that returns decoded JSON, used by both services here.
 *
 * @return array<string, mixed>
 */
function aiPostForm(string $url, array $fields, array $headers, int $timeout = 30): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'this PHP has no curl'];
    }

    try {
        $endpoint = aiEndpointPolicy('openai', $url);
    } catch (ActionRefused $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }

    $handle = curl_init($endpoint['url']);
    curl_setopt_array($handle, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $fields,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_RESOLVE => $endpoint['resolve'],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $raw = curl_exec($handle);
    $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $problem = curl_error($handle);
    // No curl_close; see the note above.

    if ($raw === false) {
        return ['ok' => false, 'error' => $problem !== '' ? $problem : 'the request failed'];
    }

    $decoded = json_decode((string)$raw, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'error' => 'the service answered with something that is not JSON'];
    }

    if ($status < 200 || $status >= 300) {
        return [
            'ok' => false,
            'error' => (string)($decoded['err'] ?? $decoded['error'] ?? "HTTP {$status}"),
            'status' => $status,
        ];
    }

    return ['ok' => true, 'body' => $decoded, 'status' => $status];
}

/**
 * The animation services this can search, and how each one answers.
 *
 * Three of them, and the endpoint is a field rather than a constant, because
 * which one an install can use is not something this code can know: they have
 * different terms, different regions, and one of them may be the only one an
 * employer will approve. The differences between their responses are small
 * enough to describe in a table and large enough that guessing would break.
 *
 * `path` is appended to the base. `pick` is told the decoded body and returns
 * the first usable animation, or null.
 */
function aiReactionServices(): array
{
    return [
        'tenor' => [
            'label' => 'Tenor',
            'base' => 'https://tenor.googleapis.com/v2',
            'path' => '/search',
            'note' => 'Google runs it, and both Google and the service it grew out of '
                . 'support it. A key comes from the Google Cloud console.',
            'query' => fn(array $set, string $mood): array => [
                'key' => $set['search_key'],
                'q' => $mood,
                'limit' => 1,
                'media_filter' => 'tinygif',
                'contentfilter' => $set['rating'] === 'g' ? 'high' : 'medium',
            ],
            'pick' => function (array $body): ?array {
                $first = $body['results'][0] ?? null;
                if (!is_array($first)) {
                    return null;
                }
                $media = $first['media_formats'] ?? [];
                $url = $media['tinygif']['url'] ?? $media['gif']['url'] ?? '';
                return $url === '' ? null : [
                    'url' => (string)$url,
                    'title' => (string)($first['content_description'] ?? ''),
                ];
            },
        ],

        'klipy' => [
            'label' => 'Klipy',
            'base' => 'https://api.klipy.com/api/v1',
            'path' => '/gifs/search',
            'note' => 'A smaller service with its own catalogue.',
            'query' => fn(array $set, string $mood): array => [
                'q' => $mood,
                'per_page' => 1,
                'content_filter' => $set['rating'],
            ],
            'pick' => function (array $body): ?array {
                $first = $body['data'][0] ?? $body['result'][0] ?? null;
                if (!is_array($first)) {
                    return null;
                }
                $url = $first['file']['sm']['gif']['url']
                    ?? $first['url']
                    ?? '';
                return $url === '' ? null : [
                    'url' => (string)$url,
                    'title' => (string)($first['title'] ?? ''),
                ];
            },
        ],

        'legacy' => [
            'label' => 'The older search service',
            'base' => 'https://api.giphy.com/v1',
            'path' => '/gifs/search',
            'note' => 'The one most keys are already for.',
            'query' => fn(array $set, string $mood): array => [
                'api_key' => $set['search_key'],
                'q' => $mood,
                'limit' => 1,
                'rating' => $set['rating'],
                'lang' => 'en',
            ],
            'pick' => function (array $body): ?array {
                $first = $body['data'][0] ?? null;
                if (!is_array($first)) {
                    return null;
                }
                $url = $first['images']['downsized']['url']
                    ?? $first['images']['original']['url']
                    ?? '';
                return $url === '' ? null : [
                    'url' => (string)$url,
                    'title' => (string)($first['title'] ?? ''),
                ];
            },
        ],
    ];
}

/**
 * Finds one animation for a mood.
 *
 * Rate limited separately from the picture budget, and much more loosely: a
 * search is a cheap read against somebody else's quota, where generating an
 * image is a bill. The default ceiling is the free tier most of these publish;
 * it can be set lower and not higher, because a limit that a setting can raise
 * past the service's own is not protecting anything.
 *
 * @return array{ok: bool, url?: string, title?: string, source?: string, error?: string}
 */
function aiFindReaction(array $settings, string $mood, ?callable $ask = null): array
{
    if (!$settings['reactions'] || $settings['search_key'] === '') {
        return ['ok' => false, 'error' => 'reactions are not set up'];
    }

    $services = aiReactionServices();
    $key = $settings['search_service'];

    if (!isset($services[$key])) {
        return ['ok' => false, 'error' => 'no search service is chosen'];
    }

    $service = $services[$key];
    $base = rtrim($settings['search_base'] ?: $service['base'], '/');
    $url = $base . $service['path'] . '?' . http_build_query(
        ($service['query'])($settings, mb_substr($mood, 0, 60))
    );

    if ($ask !== null) {
        return $ask($url);
    }

    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'this PHP has no curl'];
    }
    try {
        $endpoint = aiEndpointPolicy('openai', $url, true);
    } catch (ActionRefused $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }

    $handle = curl_init($endpoint['url']);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_RESOLVE => $endpoint['resolve'],
    ]);
    $raw = curl_exec($handle);
    // No curl_close; see the note above.

    $decoded = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($decoded)) {
        return ['ok' => false, 'error' => 'the search did not answer'];
    }

    $found = ($service['pick'])($decoded);
    if ($found === null) {
        return ['ok' => false, 'error' => 'nothing matched that'];
    }

    return [
        'ok' => true,
        'url' => $found['url'],
        'title' => $found['title'],
        'source' => $service['label'],
    ];
}

/** Records that a picture was made, for the budget and for the record. */
function aiRecordImage(
    PDO $ai,
    int $session,
    string $kind,
    string $prompt,
    string $style,
    ?string $url,
    ?string $error,
    int $millicents = 0,
    string $quality = 'standard'
): int {
    $ai->prepare(
        'INSERT INTO ai_images
            (session_id, at, kind, prompt, style, url, error, millicents, quality)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $session, time(), $kind, mb_substr($prompt, 0, 1000), $style, $url, $error,
        $millicents, $quality,
    ]);

    return (int)$ai->lastInsertId();
}

/** The assistant's current portrait, if it has made one. */
function aiPortrait(PDO $ai): ?array
{
    try {
        $row = $ai->query(
            "SELECT url, prompt, at FROM ai_images
              WHERE kind = 'portrait' AND url IS NOT NULL
              ORDER BY id DESC LIMIT 1"
        )->fetch();
    } catch (Throwable) {
        return null;
    }

    return $row === false ? null : [
        'url' => (string)$row['url'],
        'as' => (string)$row['prompt'],
        'at' => (int)$row['at'],
    ];
}
