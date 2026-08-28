<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/api-json.php';

function apiJsonExpect(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }

$value = apiCompactValue([
    'integer' => 922337203685477580,
    'normal' => 0.9999999123,
    'nested' => ['small' => 0.00000099123, 'negative' => -12.123456789],
]);
apiJsonExpect($value['integer'] === 922337203685477580, 'integer counter changed at the API boundary');
apiJsonExpect($value['normal'] === 1.0, 'float was not rounded to five decimal places');
apiJsonExpect($value['nested']['small'] === 0, 'sub-five-decimal float was not compacted');
apiJsonExpect($value['nested']['negative'] === -12.12346, 'nested float was not rounded');
$priorPrecision = ini_get('serialize_precision'); ini_set('serialize_precision', '53');
$json = apiJson(['number' => 0.123456789, 'binary' => 23.460000000000085]);
apiJsonExpect($json === '{"number":0.12346,"binary":23.46}', 'wire JSON retained excess floating-point precision');
apiJsonExpect(ini_get('serialize_precision') === '53', 'API JSON encoder did not restore PHP precision');
ini_set('serialize_precision', (string)$priorPrecision);
$invalid = apiJson(['text' => "bad\xB1text"]);
apiJsonExpect(str_contains($invalid, '\\ufffd'), 'invalid UTF-8 invalidated the API response');
apiJsonExpect(apiAcceptsGzip('br, gzip;q=0.8'), 'gzip support was not recognized');
apiJsonExpect(!apiAcceptsGzip('gzip;q=0, br'), 'explicitly refused gzip was accepted');
apiJsonExpect(!apiAcceptsGzip('br, deflate'), 'unsupported encoding was accepted');
echo "API JSON checks passed\n";
