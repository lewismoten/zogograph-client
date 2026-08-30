<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/ai.php';

function aiErrorExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$secret = 'sk-super-secret-provider-token-123456789';
$text = aiRedactErrorText(
    "Authorization: Bearer {$secret}; api_key={$secret}; token={$secret}",
);
aiErrorExpect(
    !str_contains($text, $secret) && substr_count($text, '[hidden]') >= 3,
    'provider error text retained a credential',
);

$error = aiErrorText(['error' => ['message' => "api_key={$secret}"]], 401);
aiErrorExpect(
    !str_contains($error, $secret) && str_contains($error, 'HTTP 401'),
    'structured provider error retained a credential',
);

$payload = aiRedactErrorPayload([
    'error' => ['message' => "token={$secret}", 'request_token' => $secret],
    'api_key' => $secret,
    'safe' => 'rate limit',
]);
aiErrorExpect(
    $payload['api_key'] === '[hidden]' &&
        $payload['error']['request_token'] === '[hidden]' &&
        !str_contains($payload['error']['message'], $secret) &&
        $payload['safe'] === 'rate limit',
    'provider failure payload was not selectively redacted',
);

echo "AI error redaction checks passed\n";
