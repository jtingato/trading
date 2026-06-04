<?php
declare(strict_types=1);

header('Content-Type: application/json');

// Accept any ticker; default to ^XSP
$raw = trim($_GET['ticker'] ?? '^XSP');

// Validate: letters, digits, ^, ., -, = only (covers stocks, indices, crypto, futures)
if (!preg_match('/^[\^A-Z0-9.\-=]{1,15}$/i', $raw)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid ticker']);
    exit;
}

$ticker = urlencode(strtoupper($raw));
$url    = "https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}";

$ctx = stream_context_create([
    'http' => [
        'timeout' => 5,
        'header'  => "User-Agent: Mozilla/5.0\r\n",
    ],
]);

$body = @file_get_contents($url, false, $ctx);

if ($body === false) {
    http_response_code(502);
    echo json_encode(['error' => 'fetch failed']);
    exit;
}

$data  = json_decode($body, true);
$price = $data['chart']['result'][0]['meta']['regularMarketPrice'] ?? null;

if ($price === null) {
    http_response_code(502);
    echo json_encode(['error' => 'price not found']);
    exit;
}

echo json_encode(['price' => $price]);
