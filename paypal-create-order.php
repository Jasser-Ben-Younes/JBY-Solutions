<?php
// Suppress PHP warnings/notices so they don't corrupt the JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Catch any fatal crash and return JSON instead of an empty body
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode(['error' => 'PHP fatal: ' . $err['message'] . ' in ' . basename($err['file']) . ':' . $err['line']]);
    }
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$product = trim($_POST['product'] ?? '');

$catalogue = require __DIR__ . '/products.php';
if (!$product || !isset($catalogue[$product])) {
    echo json_encode(['error' => 'Unknown product']);
    exit;
}

$amount   = $catalogue[$product]['price'];
$currency = $catalogue[$product]['currency'];

// ── PayPal credentials ────────────────────────────────────────────
define('PAYPAL_CLIENT_ID',     'AezzTHzS_1xMbOkSrdrQlQW7haRJz_Q2j1ZOtZqXy4ycN-2a7u1Es0hY8cj_lVhSMW8aRdzDIryWNmDC');
define('PAYPAL_CLIENT_SECRET', 'EBRaNlN1NZRxbokWfWuUhMecStj1e65j-HIaCy4gFqG12Vki8xYjjswrJUb2-dB9qb9uokiaeEn5qtIn');
define('PAYPAL_BASE',          'https://api-m.paypal.com');
// ─────────────────────────────────────────────────────────────────

if (!function_exists('curl_init')) {
    echo json_encode(['error' => 'curl is not available on this server']);
    exit;
}

function paypalRequest($url, $opts) {
    $ch = curl_init($url);
    curl_setopt_array($ch, $opts + [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $raw      = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'error' => 'curl error: ' . $curlErr, 'data' => null];
    }
    $data = json_decode($raw, true);
    if ($data === null) {
        return ['ok' => false, 'error' => 'bad JSON from PayPal (HTTP ' . $httpCode . '): ' . substr($raw, 0, 300), 'data' => null];
    }
    return ['ok' => true, 'data' => $data, 'http' => $httpCode];
}

// 1. Get access token
$authRes = paypalRequest(PAYPAL_BASE . '/v1/oauth2/token', [
    CURLOPT_POST       => true,
    CURLOPT_USERPWD    => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
    CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
]);

if (!$authRes['ok']) {
    echo json_encode(['error' => 'Auth request failed: ' . $authRes['error']]);
    exit;
}

$token = $authRes['data']['access_token'] ?? null;
if (!$token) {
    $detail = $authRes['data']['error_description'] ?? $authRes['data']['error'] ?? json_encode($authRes['data']);
    echo json_encode(['error' => 'Auth denied by PayPal: ' . $detail]);
    exit;
}

// 2. Create order
$orderRes = paypalRequest(PAYPAL_BASE . '/v2/checkout/orders', [
    CURLOPT_POST       => true,
    CURLOPT_POSTFIELDS => json_encode([
        'intent'         => 'CAPTURE',
        'purchase_units' => [[
            'description' => $product,
            'amount'      => [
                'currency_code' => $currency,
                'value'         => number_format((float)$amount, 2, '.', ''),
            ],
        ]],
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
        'PayPal-Request-Id: ' . uniqid('jby-', true),
    ],
]);

if (!$orderRes['ok']) {
    echo json_encode(['error' => 'Order request failed: ' . $orderRes['error']]);
    exit;
}

$orderID = $orderRes['data']['id'] ?? null;
if (!$orderID) {
    $detail = $orderRes['data']['message'] ?? json_encode($orderRes['data']);
    echo json_encode(['error' => 'Order not created: ' . $detail]);
    exit;
}

echo json_encode(['orderID' => $orderID]);
?>
