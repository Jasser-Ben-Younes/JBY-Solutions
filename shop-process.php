<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

$product = trim($_POST['product'] ?? '');
$price   = trim($_POST['price']   ?? '');
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');

if (strlen($product) < 2 || strlen($name) < 2) {
    echo json_encode(["error" => "Invalid data"]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email"]);
    exit;
}

$hostname = "localhost";
$username = "cazzyfi8v_databasejbysolutions";
$password = "Engineering#FTW123";
$database = "cazzyfi8v_databasejbysolutions";

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    product    VARCHAR(255) NOT NULL,
    price      VARCHAR(50),
    name       VARCHAR(255) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    ip         VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$stmt = $conn->prepare("INSERT INTO orders (product, price, name, email, ip) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $product, $price, $name, $email, $_SERVER['REMOTE_ADDR']);

if (!$stmt->execute()) {
    echo json_encode(["error" => "Save failed"]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();
$conn->close();

$fromAddress     = "noreply@jby-solutions.com";
$adminRecipients = "info@jby-solutions.com,jasserben@gmail.com";
$headers         = "From: JBY Solutions <{$fromAddress}>\r\nReply-To: {$email}\r\nX-Mailer: PHP/" . phpversion();

mail(
    $adminRecipients,
    "New order: $product from $name",
    "New shop order received:\n\n"
    . "Product: $product\n"
    . "Price:   $price\n"
    . "Name:    $name\n"
    . "Email:   $email\n"
    . "IP:      " . $_SERVER['REMOTE_ADDR'] . "\n"
    . "Time:    " . date('Y-m-d H:i:s') . "\n",
    $headers
);

mail(
    $email,
    "Your JBY Solutions order — $product",
    "Hi $name,\n\n"
    . "Thank you for your order! We've received it and will send your download link "
    . "and invoice to this address shortly.\n\n"
    . "Order summary:\n"
    . "  Product: $product\n"
    . "  Price:   $price\n\n"
    . "Regards,\n"
    . "JBY Solutions\n",
    $headers
);

echo json_encode(["success" => true]);
?>
