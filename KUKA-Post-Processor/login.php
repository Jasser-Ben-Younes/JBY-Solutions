<?php
session_start();

define('ALLOWED_IPS', [
    '176.2.90.255',
    '2a0d:3341:b908:c908:2d72:c17c:8684:c2dc',       // ← Replace with your allowed IP address(es)
    // '1.2.3.4',    // ← Add more IPs here if needed
]);

define('AUTH_USER', 'admin');
define('AUTH_HASH', 'ce7090661cdc99eb0037b763c01367ca38cfdf55cf2824fea1632d967f447aac'); // SHA-256

$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$client_ip = trim(explode(',', $client_ip)[0]);
$ip_allowed = in_array($client_ip, ALLOWED_IPS, true);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ip_allowed) {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $hash = hash('sha256', $pass);

    if ($user === AUTH_USER && hash_equals(AUTH_HASH, $hash)) {
        $_SESSION['kuka_auth'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JBY Solutions - Login</title>
  <link rel="stylesheet" href="/style.css" />
  <style>
    .login-wrap {
      max-width: 380px;
      margin: 6rem auto;
      background: #fff;
      border-radius: 14px;
      padding: 2.5rem 2rem;
      box-shadow: 0 4px 24px rgba(0,0,0,.08);
    }
    .login-wrap h1 {
      font-size: 1.4rem;
      margin-bottom: 0.3rem;
      color: #111827;
    }
    .login-wrap .sub {
      color: #64748b;
      font-size: 0.88rem;
      margin-bottom: 1.75rem;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .form-group label {
      display: block;
      font-size: 0.83rem;
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.3rem;
    }
    .form-group input {
      width: 100%;
      padding: 0.55rem 0.85rem;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 0.93rem;
      color: #111827;
    }
    .form-group input:focus {
      outline: none;
      border-color: #2563eb;
    }
    .btn-login {
      width: 100%;
      padding: 0.65rem;
      background: #2563eb;
      color: #fff;
      border: none;
      border-radius: 999px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 0.5rem;
      transition: background 0.2s;
    }
    .btn-login:hover { background: #1d4ed8; }
    .error-msg {
      background: #fef2f2;
      border: 1px solid #fca5a5;
      color: #dc2626;
      border-radius: 8px;
      padding: 0.6rem 0.85rem;
      font-size: 0.88rem;
      margin-bottom: 1rem;
    }
    .blocked-msg {
      background: #fef2f2;
      border: 1px solid #fca5a5;
      color: #dc2626;
      border-radius: 8px;
      padding: 0.85rem;
      font-size: 0.9rem;
      text-align: center;
    }
  </style>
</head>
<body>
  <header></header>
  <main>
    <div class="login-wrap">
      <h1>Restricted Access</h1>
      <p class="sub">KUKA Post-Processor — authorised users only.</p>

      <?php if (!$ip_allowed): ?>
        <div class="blocked-msg">
          Access denied.<br>Your IP address (<strong><?= htmlspecialchars($client_ip) ?></strong>) is not authorised.
        </div>
      <?php else: ?>
        <?php if ($error): ?>
          <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autocomplete="off" required />
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required />
          </div>
          <button type="submit" class="btn-login">Sign In</button>
        </form>
      <?php endif; ?>
    </div>
  </main>
  <footer></footer>
  <script src="inject-shared.js"></script>
</body>
</html>
