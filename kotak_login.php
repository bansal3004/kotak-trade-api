<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$config = require __DIR__ . '/secure.php';

// If a TOTP is submitted, call Step 1 API and redirect with token+sid
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['totp'])) {
    $totp = trim($_GET['totp']);

    // Step 1: Login with TOTP (View token + sid)
    $url = "https://mis.kotaksecurities.com/login/1.0/tradeApiLogin";

    $data = [
        "mobileNumber" => $config['mobile'],
        "ucc"          => $config['ucc'],
        "totp"         => $totp
    ];

    $headers = [
        "Authorization: {$config['access_token']}", // consumer key
        "neo-fin-key: neotradeapi",
        "Content-Type: application/json"
    ];

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

    if (isset($result['data']['token']) && isset($result['data']['sid'])) {
        $view_token = $result['data']['token'];
        $view_sid   = $result['data']['sid'];
        // ✅ go to Step 2 (MPIN validate) with view token+sid
        header("Location: validate.php?token=" . urlencode($view_token) . "&sid=" . urlencode($view_sid));
        exit;
    } else {
        echo "<main class='card' style='max-width:560px;margin:40px auto;font-family:Inter,sans-serif'>";
        echo "<h2>❌ TOTP Login Failed</h2>";
        echo "<pre style='background:#fafafa;border:1px solid #eee;padding:12px;border-radius:8px;overflow:auto'>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "</pre>";
        echo "<p><a href='kotak_login.php'>↩️ Try again</a></p>";
        echo "</main>";
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Kotak TOTP Login</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body{background:linear-gradient(135deg,#e8f5e9,#ffffff);display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:'Inter',sans-serif}
    .card{background:#fff;padding:26px;width:340px;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.08);text-align:center}
    h1{font-size:20px;margin-bottom:18px;font-weight:600}
    input{width:100%;padding:11px;margin-bottom:14px;border:1px solid #e0e0e0;border-radius:8px;font-size:14px;outline:none;text-align:center;letter-spacing:3px;font-weight:600}
    input:focus{border-color:#0aa70a;box-shadow:0 0 6px rgba(10,167,10,0.3)}
    .btn{width:100%;padding:12px;border:none;border-radius:8px;background:linear-gradient(90deg,#0aa70a,#17c317);color:#fff;font-size:15px;font-weight:600;cursor:pointer}
    .btn:hover{box-shadow:0 0 12px rgba(10,167,10,0.6)}
    .logout{display:inline-block;margin-top:14px;font-size:13px;color:#d32f2f;text-decoration:none}
  </style>
</head>
<body>
  <main class="card">
    <h1>🔑 Enter 6-digit TOTP</h1>
    <form method="GET">
      <input type="text" name="totp" maxlength="6" placeholder="123456" required>
      <button type="submit" class="btn">Continue Login</button>
    </form>
    <a href="logout.php" class="logout">Logout</a>
  </main>
</body>
</html>
