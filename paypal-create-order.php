<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$product  = trim($_POST['product'] ?? '');
$priceRaw = trim($_POST['price']   ?? '');

// Strip currency symbol → numeric string
$amount = preg_replace('/[^0-9.]/', '', $priceRaw);

if (!$product || !is_numeric($amount) || (float)$amount <= 0) {
    echo json_encode(['error' => 'Invalid product or price']);
    exit;
}

// ── PayPal credentials ────────────────────────────────────────────
// Get these from https://developer.paypal.com → Apps & Credentials
// Use sandbox credentials while testing, live credentials when ready
define('PAYPAL_CLIENT_ID',     'AW8PCsFtYESP3HkqGB98edHizOtCl0osJogJtNPDIlZ0nrR5NINFpTvjIsYB0HUOnqBXXVpyhMHYGNj2');
define('PAYPAL_CLIENT_SECRET', 'EP3HkqGB98edHizOtCl0osJogJtNPDIlZ0nrR5NINFpTvjIsYB0HUOnqBXXVpyhMHYGNj2');

// Sandbox:  https://api-m.sandbox.paypal.com
// Live:     https://api-m.paypal.com
define('PAYPAL_BASE', 'https://api-m.sandbox.paypal.com');
// ─────────────────────────────────────────────────────────────────

function paypalAccessToken() {
    $ch = curl_init(PAYPAL_BASE . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $res  = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $res['access_token'] ?? null;
}

$token = paypalAccessToken();
if (!$token) {
    echo json_encode(['error' => 'PayPal authentication failed']);
    exit;
}

$payload = json_encode([
    'intent'         => 'CAPTURE',
    'purchase_units' => [[
        'description' => $product,
        'amount'      => [
            'currency_code' => 'EUR',
            'value'         => number_format((float)$amount, 2, '.', ''),
        ],
    ]],
]);

$ch = curl_init(PAYPAL_BASE . '/v2/checkout/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
        'PayPal-Request-Id: ' . uniqid('jby-order-', true),
    ],
]);
$order = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($order['id'])) {
    echo json_encode(['error' => 'Failed to create PayPal order']);
    exit;
}

echo json_encode(['orderID' => $order['id']]);
?>
