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

// 1) Email(s) to notify YOU (comma-separated)
$adminRecipients = "info@jby-solutions.com,jasserben@gmail.com";

// Common data
$subjectAdmin = "New contact form submission from $name";
$subjectUser  = "Thanks for contacting JBY Solution";
$fromAddress  = "noreply@jby-solutions.com";  // must be a domain you own
$replyTo      = $email;                       // user email for replies

// Build plain-text bodies
$bodyAdmin = "You have a new contact form submission:\n\n"
  . "Name: $name\n"
  . "Email: $email\n"
  . "IP: " . $_SERVER['REMOTE_ADDR'] . "\n"
  . "Sent at: " . date('Y-m-d H:i:s') . "\n\n"
  . "Message:\n$message\n";

$bodyUser = "Hi $name,\n\n"
  . "Thanks for reaching out to JBY Solution. This is an automatic confirmation "
  . "that I received your message and will get back to you as soon as possible.\n\n"
  . "Here is a copy of what you sent:\n\n"
  . "-----------------------------\n"
  . "$message\n"
  . "-----------------------------\n\n"
  . "Regards,\n"
  . "JBY Solutions\n";

// Build headers
$headers = [];
$headers[] = "From: JBY Solution <{$fromAddress}>";
$headers[] = "Reply-To: {$replyTo}";
$headers[] = "X-Mailer: PHP/" . phpversion();
$headersStr = implode("\r\n", $headers);

// 2) Send mail to you (multiple recipients allowed, comma-separated)
$mailAdminOk = mail($adminRecipients, $subjectAdmin, $bodyAdmin, $headersStr);

// 3) Send auto-reply to user
$mailUserOk = mail($email, $subjectUser, $bodyUser, $headersStr);

// Optional: check if both sends succeeded
if (!$mailAdminOk || !$mailUserOk) {
    // don't fail the whole request, just log or include a warning in JSON
    // error_log("Mail sending failed for contact form");
}
echo json_encode(["success" => "Message saved + emailed!"]);
?>