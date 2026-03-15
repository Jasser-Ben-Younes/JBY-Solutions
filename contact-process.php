<?php
header('Content-Type: application/json'); // For JS fetch

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Server validation
if (strlen($name) < 2 || strlen($email) < 5 || strlen($message) < 10) {
    echo json_encode(["error" => "Fields too short"]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email"]);
    exit;
}

// YOUR one.com DB DETAILS HERE
$hostname = "localhost";  // e.g. u123456-mysql.private.one.com
$username = "cazzyfi8v_databasejbysolutions";
$password = "Engineering#FTW123";
$database = "cazzyfi8v_databasejbysolutions";

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO contacts (name, email, message, ip) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $message, $_SERVER['REMOTE_ADDR']);
$success = $stmt->execute();

if (!$success) {
    echo json_encode(["error" => "Save failed"]);
    $conn->close();
    exit;
}

$stmt->close();
$conn->close();

// Email you
$to = "your-email@jby-solutions.com";  // YOUR EMAIL
$subject = "New contact: $name";
$headers = "From: noreply@jby-solutions.com\r\n";
mail($to, $subject, "Name: $name\nEmail: $email\nIP: {$_SERVER['REMOTE_ADDR']}\n\n$message", $headers);

echo json_encode(["success" => "Message saved + emailed!"]);
?>