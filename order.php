<?php
session_start();
if (!isset($_SESSION['trade_token'], $_SESSION['trade_sid'], $_SESSION['trade_base'])) {
    die("⚠️ Session expired or not validated. Please login again.");
}

$baseUrl = $_SESSION['trade_base'];
$token   = $_SESSION['trade_token'];
$sid     = $_SESSION['trade_sid'];

// === Common Headers for all APIs ===
$headers = [
    "Auth: $token",
    "Sid: $sid",
    "neo-fin-key: neotradeapi",
    "Content-Type: application/json"
];

function call_api($url, $headers, $method = 'GET', $body = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST' && $body) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    return $error ? ["error" => $error] : json_decode($response, true);
}

// === 1️⃣ Order Book ===
$orderBookUrl = "$baseUrl/quick/user/orders";
$orderBook = call_api($orderBookUrl, $headers);

// === 2️⃣ Trade Book ===
$tradeBookUrl = "$baseUrl/quick/user/trades";
$tradeBook = call_api($tradeBookUrl, $headers);

// === 3️⃣ Position Book ===
$positionUrl = "$baseUrl/quick/user/positions";
$positions = call_api($positionUrl, $headers);

// === 4️⃣ Holdings ===
$holdingsUrl = "$baseUrl/portfolio/v1/holdings";
$holdings = call_api($holdingsUrl, $headers);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>📊 Kotak Trade Reports</title>
  <style>
    body {
      font-family: "Inter", sans-serif;
      background: #f5f9ff;
      padding: 30px;
      color: #111;
    }
    h1 { margin-bottom: 10px; }
    h2 { color: #0a7; margin-top: 30px; }
    pre {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 12px;
      overflow-x: auto;
      font-size: 13px;
    }
    .card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      margin-bottom: 20px;
    }
    a.logout {
      display: inline-block;
      background: #d32f2f;
      color: white;
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <h1>📑 Kotak API Reports Dashboard</h1>
  <p><b>Base URL:</b> <?= htmlspecialchars($baseUrl) ?></p>
  <p><b>Token:</b> <?= htmlspecialchars($token) ?></p>
  <p><b>SID:</b> <?= htmlspecialchars($sid) ?></p>

  <div class="card">
    <h2>🧾 Order Book</h2>
    <pre><?php print_r($orderBook); ?></pre>
  </div>

  <div class="card">
    <h2>💹 Trade Book</h2>
    <pre><?php print_r($tradeBook); ?></pre>
  </div>

  <div class="card">
    <h2>📦 Positions</h2>
    <pre><?php print_r($positions); ?></pre>
  </div>

  <div class="card">
    <h2>💼 Portfolio Holdings</h2>
    <pre><?php print_r($holdings); ?></pre>
  </div>

  <a href="logout.php" class="logout">Logout</a>
</body>
</html>
