<?php
session_start();
$config = require __DIR__ . '/secure.php';

// Check session
if (!isset($_SESSION['trade_token']) || !isset($_SESSION['trade_base'])) {
    die("⚠️ Login first, session expired.");
}

$base_url    = $_SESSION['trade_base'];
echo $base_url;
echo "<br>";
echo "<br>";
$trade_token  = $_SESSION['trade_token'];
echo $trade_token;
echo "<br>";
echo "<br>";
$consumer_key = $config['access_token'];
echo $consumer_key;

// If user passes ?symbol=INFY-EQ
$symbol = $_GET['symbol'] ?? 'INFY-EQ';  // default: Infosys

$url = "{$base_url}/script-details/1.0/quote/ts/{$symbol}/nse_cm";

$headers = [
    "Content-Type: application/json",
    "Authorization: {$consumer_key}",
    "Auth: {$trade_token}"
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => $headers
]);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die("❌ cURL Error: $error");
}

$data = json_decode($response, true);

// Output formatted
echo "<h2>📈 Live Quote: {$symbol}</h2>";
if (isset($data['data'])) {
    $quote = $data['data'];
    echo "Last Traded Price (₹): " . $quote['ltp'] . "<br>";
    echo "Change: " . $quote['change'] . "<br>";
    echo "Percent Change: " . $quote['per_change'] . "%<br>";
} else {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
