<?php
session_start();

// ✅ Ensure login session exists
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$config = require __DIR__ . '/secure.php';

// ✅ Step 2: Get token and sid from Step 1 (TOTP login)
$view_token = $_GET['token'] ?? '';
$view_sid   = $_GET['sid'] ?? '';

if (!$view_token || !$view_sid) {
    die("<p style='color:red;font-family:sans-serif;text-align:center;margin-top:30px;'>❌ Missing token or SID. Please go back and retry Step 1.</p>");
    header("Location: login.php");
}

// ✅ Kotak API endpoint
$url = "https://mis.kotaksecurities.com/login/1.0/tradeApiValidate";

// ✅ Request body (with MPIN)
$data = [
    "mpin" => $config['mpin']
];

// ✅ Headers
$headers = [
    "Authorization: {$config['access_token']}",
    "neo-fin-key: neotradeapi",
    "sid: $view_sid",
    "Auth: $view_token",
    "Content-Type: application/json"
];

// ✅ cURL request
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => $headers
]);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die("<p style='color:red;text-align:center;margin-top:30px;'>⚠️ cURL Error: $error</p>");
}

$result = json_decode($response, true);

// ✅ If success — save tokens and redirect
if (isset($result['data']['status']) && strtolower($result['data']['status']) === 'success') {
    $_SESSION['trade_token'] = $result['data']['token'] ?? '';
    $_SESSION['trade_sid']   = $result['data']['sid'] ?? '';
    $_SESSION['trade_base']  = $result['data']['baseUrl'] ?? '';
    $_SESSION['validated']   = true;

    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Kotak API | MPIN Validation Failed</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {background:linear-gradient(135deg,#fff5f5,#ffffff);display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:'Inter',sans-serif}
    .error-card {background:#fff;width:520px;padding:30px;border-radius:14px;box-shadow:0 8px 28px rgba(0,0,0,0.08);text-align:left}
    h2 {font-size:20px;margin-bottom:16px;font-weight:600;color:#d32f2f}
    .section {background:#fef4f4;border:1px solid #f2c4c4;border-radius:10px;padding:16px;font-size:14px;white-space:pre-wrap;word-break:break-word;color:#444}
    .btn {display:inline-block;background:linear-gradient(90deg,#d32f2f,#ef5350);color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:600;font-size:14px;margin-top:16px}
    .btn:hover {box-shadow:0 0 12px rgba(239,83,80,0.6);transform:translateY(-1px)}
  </style>
</head>
<body>
  <div class="error-card">
    <h2>⚠️ MPIN Validation Failed</h2>
    <div class="section">
      <?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?>
    </div>
    <a href="kotak_login.php" class="btn">↩️ Re-enter TOTP</a>
  </div>
</body>
</html>
