<?php
session_start();

// Ensure user is logged in & validated
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['trade_token'])) {
    header("Location: login.php");
    exit;
}

$config = require __DIR__ . '/secure.php';

// Required API params
$base_url     = rtrim($_SESSION['trade_base'] ?? 'https://mis.kotaksecurities.com', '/');
$trade_token  = $_SESSION['trade_token'];
$consumer_key = $config['access_token'];

// API endpoint (URL-encoded symbols)
$symbols = rawurlencode('nse_cm|Nifty 50,nse_cm|Nifty Bank/all');
$url     = "{$base_url}/script-details/1.0/quotes/neosymbol/{$symbols}";

// Prepare cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: {$consumer_key}",  // Consumer key (from secure.php)
        "Auth: {$trade_token}"             // Session trade token (from validate.php)
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// Handle error
if ($error) {
    die("<p style='color:red;text-align:center;margin-top:30px;'>❌ cURL Error: $error</p>");
}

$data = json_decode($response, true);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>📈 Market Live | Kotak Trade</title>
<style>
body {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #e8f5e9, #ffffff);
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  margin: 0;
}
.card {
  background: #fff;
  padding: 28px;
  width: 480px;
  border-radius: 14px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}
h1 {
  font-size: 22px;
  margin-bottom: 20px;
  text-align: center;
  color: #111;
}
.market-box {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}
.market-item {
  flex: 1;
  background: #f8fdf8;
  border-radius: 10px;
  padding: 14px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.04);
  text-align: center;
  transition: 0.2s;
}
.market-item:hover { transform: translateY(-2px); }
.symbol {
  font-weight: 700;
  color: #0a7a0a;
  font-size: 16px;
}
.price {
  font-size: 20px;
  margin-top: 6px;
  font-weight: 600;
}
.change {
  font-size: 14px;
  margin-top: 4px;
}
.green { color: #0a8a0a; }
.red { color: #d32f2f; }
.refresh {
  display: block;
  text-align: center;
  margin-top: 20px;
  background: linear-gradient(90deg, #0aa70a, #17c317);
  color: #fff;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
}
.refresh:hover { box-shadow: 0 0 12px rgba(10,167,10,0.6); }
.logout {
  display: block;
  text-align: center;
  margin-top: 10px;
  font-size: 13px;
  color: #d32f2f;
  text-decoration: none;
}
.logout:hover { color: #b71c1c; }
</style>
</head>
<body>
<div class="card">
  <h1>📊 Kotak Market Snapshot</h1>

  <?php if (isset($data[0]['exchange_token'])): ?>
  <div class="market-box">
    <?php foreach ($data as $s): 
      $ltp = number_format($s['ltp'], 2);
      $chg = $s['change'];
      $per = $s['per_change'];
      $color = ($chg >= 0) ? 'green' : 'red';
    ?>
    <div class="market-item">
      <div class="symbol"><?= htmlspecialchars($s['exchange_token']) ?></div>
      <div class="price">₹<?= $ltp ?></div>
      <div class="change <?= $color ?>">
        <?= $chg ?> (<?= $per ?>%)
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <p style="text-align:center;color:#d32f2f;">⚠️ Invalid or expired token. Please re-login.</p>
  <?php endif; ?>

  <a href="market.php" class="refresh">🔄 Refresh</a>
  <a href="logout.php" class="logout">Logout</a>
</div>
</body>
</html>
