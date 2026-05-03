<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

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

$orderID = trim($_POST['orderID'] ?? '');
$product = trim($_POST['product'] ?? '');
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');

$catalogue = require __DIR__ . '/products.php';
if (!$orderID || !$product || !isset($catalogue[$product]) || strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

$price = $catalogue[$product]['display'];

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

// 2. Capture the order
$captureRes = paypalRequest(PAYPAL_BASE . "/v2/checkout/orders/{$orderID}/capture", [
    CURLOPT_POST       => true,
    CURLOPT_POSTFIELDS => '{}',
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ],
]);

if (!$captureRes['ok']) {
    echo json_encode(['error' => 'Capture request failed: ' . $captureRes['error']]);
    exit;
}

$status = $captureRes['data']['status'] ?? 'unknown';
if ($status !== 'COMPLETED') {
    $detail = $captureRes['data']['message'] ?? json_encode($captureRes['data']);
    echo json_encode(['error' => 'Capture status "' . $status . '": ' . $detail]);
    exit;
}

// 3. Save to DB
$conn = new mysqli('localhost', 'cazzyfi8v_databasejbysolutions', 'Engineering#FTW123', 'cazzyfi8v_databasejbysolutions');
if (!$conn->connect_error) {
    $conn->query("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product VARCHAR(255) NOT NULL,
        price VARCHAR(50),
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        paypal_id VARCHAR(100),
        ip VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $stmt = $conn->prepare("INSERT INTO orders (product, price, name, email, paypal_id, ip) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $product, $price, $name, $email, $orderID, $_SERVER['REMOTE_ADDR']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// 4. Send emails
$from    = "noreply@jby-solutions.com";
$headers = "From: JBY Solutions <{$from}>\r\nReply-To: {$email}\r\nX-Mailer: PHP/" . phpversion();

mail("info@jby-solutions.com,jasserben@gmail.com",
    "New sale: $product — $price ($name)",
    "Payment confirmed via PayPal.\n\nOrder ID: $orderID\nProduct: $product\nPrice: $price\nName: $name\nEmail: $email\nIP: {$_SERVER['REMOTE_ADDR']}\nTime: " . date('Y-m-d H:i:s'),
    $headers);

mail($email,
    "Your JBY Solutions purchase — $product",
    "Hi $name,\n\nYour payment has been confirmed. Thank you for your purchase!\n\n  Product: $product\n  Price: $price\n  Order ID: $orderID\n\nWe'll send your download link to this address shortly.\n\nRegards,\nJBY Solutions",
    $headers);

echo json_encode(['success' => true]);
?>
