<?php
session_start();

// ✅ Access control: only if Kotak session exists
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['trade_token']) || !isset($_SESSION['trade_sid']) || !isset($_SESSION['trade_base'])) {
    die("⚠️ Please login and validate MPIN first (index.php)");
}

$config = require __DIR__ . '/secure.php';

// ✅ Kotak credentials (from session)
$access_token = $config['access_token'];
$trade_token  = $_SESSION['trade_token'];
$trade_sid    = $_SESSION['trade_sid'];
$base_url     = rtrim($_SESSION['trade_base'], '/');

// ✅ API endpoint (official Kotak endpoint for live quotes)
$url = "$base_url/quote/ltp";

// ✅ NSE instruments — (Official Kotak IDs)
$stocks = [
    "INFY"      => 1594,
    "RELIANCE"  => 2885,
    "TCS"       => 11536,
    "HDFCBANK"  => 1333,
    "SBIN"      => 3045
];

// ✅ Prepare request body
$instrumentArray = [];
foreach ($stocks as $symbol => $id) {
    $instrumentArray[] = [
        "exchangeSegment" => "NSE_CM",
        "exchangeInstrumentID" => (string)$id
    ];
}

$payload = [
    "instrumentToken" => $instrumentArray
];

// ✅ Request headers
$headers = [
    "Authorization: $access_token",
    "neo-fin-key: neotradeapi",
    "sid: $trade_sid",
    "Auth: $trade_token",
    "Content-Type: application/json"
];

// ✅ Make API request
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10
]);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die("❌ CURL Error: " . $error);
}

$result = json_decode($response, true);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>📈 Kotak Official Quotes API</title>
<style>
    body {
        background: linear-gradient(135deg, #e8f5e9, #ffffff);
        font-family: 'Inter', sans-serif;
        padding: 40px;
        color: #222;
    }
    h1 {
        text-align: center;
        font-weight: 600;
        margin-bottom: 20px;
    }
    table {
        width: 80%;
        margin: 0 auto;
        border-collapse: collapse;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    th, td {
        padding: 12px 16px;
        border-bottom: 1px solid #eee;
    }
    th {
        background: #0aa70a;
        color: #fff;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    tr:hover td {
        background: #f9fff9;
    }
    .green { color: #0aa70a; font-weight: 600; }
    .red { color: #d32f2f; font-weight: 600; }
    pre {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        font-size: 12px;
        overflow-x: auto;
        margin-top: 30px;
    }
</style>
</head>
<body>
<h1>📊 Kotak NSE Live Prices (Official API)</h1>

<?php if (!isset($result['data'])): ?>
    <p style="text-align:center;color:red;">❌ Invalid response from Kotak API</p>
    <pre><?= htmlspecialchars($response) ?></pre>
<?php else: ?>
<table>
    <tr>
        <th>Symbol</th>
        <th>Last Price (₹)</th>
        <th>Change</th>
        <th>% Change</th>
        <th>Timestamp</th>
    </tr>
    <?php foreach ($result['data'] as $i => $stock): ?>
        <?php
            $symbol = array_keys($stocks)[$i];
            $ltp = $stock['lastTradedPrice'] ?? 0;
            $change = $stock['netChange'] ?? 0;
            $perChange = $stock['percentChange'] ?? 0;
            $time = $stock['quoteTime'] ?? '—';
        ?>
        <tr>
            <td><?= $symbol ?></td>
            <td><?= number_format($ltp, 2) ?></td>
            <td class="<?= $change >= 0 ? 'green':'red' ?>">
                <?= $change >= 0 ? '▲':'▼' ?> <?= number_format($change, 2) ?>
            </td>
            <td class="<?= $perChange >= 0 ? 'green':'red' ?>">
                <?= number_format($perChange, 2) ?>%
            </td>
            <td><?= htmlspecialchars($time) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- Optional Debug -->
<h3 style="text-align:center;margin-top:40px;">Raw API Response (Debug)</h3>
<pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
</body>
</html>
