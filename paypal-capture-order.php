<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$orderID = trim($_POST['orderID'] ?? '');
$product = trim($_POST['product'] ?? '');
$price   = trim($_POST['price']   ?? '');
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');

if (!$orderID || !$product || strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Must match paypal-create-order.php
define('PAYPAL_CLIENT_ID',     'YOUR_PAYPAL_CLIENT_ID');
define('PAYPAL_CLIENT_SECRET', 'YOUR_PAYPAL_CLIENT_SECRET');
define('PAYPAL_BASE',          'https://api-m.sandbox.paypal.com');

function paypalAccessToken() {
    $ch = curl_init(PAYPAL_BASE . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $res['access_token'] ?? null;
}

$token = paypalAccessToken();
if (!$token) {
    echo json_encode(['error' => 'PayPal authentication failed']);
    exit;
}

// Capture the payment
$ch = curl_init(PAYPAL_BASE . "/v2/checkout/orders/{$orderID}/capture");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => '{}',
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ],
]);
$capture = json_decode(curl_exec($ch), true);
curl_close($ch);

if (($capture['status'] ?? '') !== 'COMPLETED') {
    echo json_encode(['error' => 'Payment capture failed']);
    exit;
}

// Save order to DB
$hostname = "localhost";
$username = "cazzyfi8v_databasejbysolutions";
$password = "Engineering#FTW123";
$database = "cazzyfi8v_databasejbysolutions";

$conn = new mysqli($hostname, $username, $password, $database);
if (!$conn->connect_error) {
    $conn->query("CREATE TABLE IF NOT EXISTS orders (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        product    VARCHAR(255) NOT NULL,
        price      VARCHAR(50),
        name       VARCHAR(255) NOT NULL,
        email      VARCHAR(255) NOT NULL,
        paypal_id  VARCHAR(100),
        ip         VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $conn->prepare(
        "INSERT INTO orders (product, price, name, email, paypal_id, ip) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssss", $product, $price, $name, $email, $orderID, $_SERVER['REMOTE_ADDR']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Send emails
$from    = "noreply@jby-solutions.com";
$headers = "From: JBY Solutions <{$from}>\r\nReply-To: {$email}\r\nX-Mailer: PHP/" . phpversion();

mail(
    "info@jby-solutions.com,jasserben@gmail.com",
    "New sale: $product — $price ($name)",
    "Payment confirmed via PayPal.\n\n"
    . "Order ID: $orderID\n"
    . "Product:  $product\n"
    . "Price:    $price\n"
    . "Name:     $name\n"
    . "Email:    $email\n"
    . "IP:       " . $_SERVER['REMOTE_ADDR'] . "\n"
    . "Time:     " . date('Y-m-d H:i:s') . "\n",
    $headers
);

mail(
    $email,
    "Your JBY Solutions purchase — $product",
    "Hi $name,\n\n"
    . "Your payment has been confirmed. Thank you for your purchase!\n\n"
    . "  Product:  $product\n"
    . "  Price:    $price\n"
    . "  Order ID: $orderID\n\n"
    . "We'll send your download link to this address shortly.\n\n"
    . "Regards,\nJBY Solutions\n",
    $headers
);

echo json_encode(['success' => true]);
?>
