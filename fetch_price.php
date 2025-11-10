<?php
// Step 3: Fetch Live Price
$trading_token = $_GET['token'] ?? '';
$trading_sid = $_GET['sid'] ?? '';
$baseUrl = $_GET['base'] ?? 'https://cis.kotaksecurities.com';
$symbol = "RELIANCE"; // test symbol

$url = $baseUrl . "/quote/ltp";  // LTP API endpoint

$data = [
    "instrument_token" => $symbol,
    "exchange_segment" => "NSE_CM"
];

$headers = [
    "Auth: $trading_token",
    "Sid: $trading_sid",
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
curl_close($ch);

$result = json_decode($response, true);

echo "<h2>📊 Step 3: Live Price for RELIANCE</h2>";

if (isset($result['data']['last_traded_price'])) {
    echo "<b>Symbol:</b> RELIANCE<br>";
    echo "<b>Last Traded Price:</b> ₹" . $result['data']['last_traded_price'] . "<br>";
    echo "<b>Time:</b> " . date("Y-m-d H:i:s") . "<br>";
} else {
    echo "<pre>Price not found or invalid response:</pre>";
    echo "<pre>"; print_r($result); echo "</pre>";
}
?>
