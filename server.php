<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$filePath = __DIR__ . "/EQUITY_L.csv";
$config = require __DIR__ . '/secure.php';

// --- CSV Reader (unchanged) ---
function readCsv($file) {
    $rows = array_map('str_getcsv', file($file));
    array_shift($rows);
    $data = [];
    foreach ($rows as $r) {
        if (isset($r[0]) && isset($r[1])) {
            $data[] = [
                "symbol" => trim($r[0]),
                "name"   => trim($r[1])
            ];
        }
    }
    return $data;
}

// --- 1. Scrip Master (unchanged) ---
if ($action === 'scrip_master') {
    if (!file_exists($filePath)) {
        echo json_encode(["error" => "EQUITY_L.csv not found"]);
        exit;
    }
    echo json_encode(readCsv($filePath));
    exit;
}

// --- 2. Quote (Official Kotak API LTP Endpoint) ---
if ($action === 'quote') {
    // ✅ Ensure session is validated
    if (!isset($_SESSION['trade_token']) || !isset($_SESSION['trade_sid'])) {
        echo json_encode(["error" => "Session not validated. Please login again."]);
        exit;
    }

    $symbol = strtoupper(trim($_GET['symbol'] ?? ''));
    if ($symbol === '') {
        echo json_encode(["error" => "No symbol provided"]);
        exit;
    }

    // Kotak API LTP endpoint
    $baseUrl = $_SESSION['trade_base'] ?? 'https://cis.kotaksecurities.com';
    $url = $baseUrl . "/quote/ltp";

    $data = [
        "instrument_token" => $symbol,
        "exchange_segment" => "NSE_CM"
    ];

    $headers = [
        "Auth: {$_SESSION['trade_token']}",
        "Sid: {$_SESSION['trade_sid']}",
        "neo-fin-key: neotradeapi",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 10
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo json_encode(["error" => "Curl error: $error"]);
        exit;
    }

    $result = json_decode($response, true);

    // ✅ Proper Kotak format check
    if (isset($result['data']['last_traded_price'])) {
        echo json_encode([
            "data" => [
                "symbol" => $symbol,
                "last_traded_price" => $result['data']['last_traded_price'],
                "exchange_segment" => "NSE_CM",
                "source" => "Kotak API"
            ],
            "timestamp" => date("Y-m-d H:i:s")
        ]);
    } else {
        echo json_encode([
            "error" => "Price not found in Kotak API response",
            "raw" => $result
        ]);
    }
    exit;
}

// --- 3. Mock Order Placement (unchanged) ---
if ($action === 'place_order') {
    $json = json_decode(file_get_contents('php://input'), true);
    if (!$json) {
        echo json_encode(["error" => "Invalid JSON payload"]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "message" => "✅ Order placed (mock) for {$json['trading_symbol']} ({$json['transaction_type']}) @ ₹{$json['price']} x {$json['quantity']}"
    ]);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
?>
