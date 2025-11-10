<?php
session_start();
header('Content-Type: application/json');

// 🔒 Session Validation
if (!isset($_SESSION['trade_token'], $_SESSION['trade_sid'], $_SESSION['trade_base'])) {
    echo json_encode(['error' => 'Session expired']);
    exit;
}

$base = $_SESSION['trade_base'];
$token = $_SESSION['trade_token'];
$sid   = $_SESSION['trade_sid'];

// ✅ Kotak Endpoint
$url = "$base/quick/user/check-margin";
$headers = [
    "Auth: $token",
    "Sid: $sid",
    "neo-fin-key: neotradeapi",
    "Content-Type: application/x-www-form-urlencoded"
];

// ✅ Static mock order payload (used for margin lookup)
$jData = [
    "brkName" => "KOTAK",
    "brnchId" => "ONLINE",
    "exSeg" => "nse_cm",
    "prc" => "12500",
    "prcTp" => "L",
    "prod" => "CNC",
    "qty" => "1",
    "tok" => "11536",
    "trnsTp" => "B"
];

$postData = http_build_query(["jData" => json_encode($jData)]);

// ✅ Execute cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// 🧩 Error handling
if ($error) {
    echo json_encode(['error' => $error]);
    exit;
}

// ✅ Decode JSON
$data = json_decode($response, true);

// 🧾 Validate and output same structure
if (is_array($data) && isset($data['stat'])) {
    echo json_encode([
        'stat' => $data['stat'],
        'stCode' => $data['stCode'] ?? '',
        'avlCash' => $data['avlCash'] ?? '0.00',
        'totMrgnUsd' => $data['totMrgnUsd'] ?? '0.00',
        'mrgnUsd' => $data['mrgnUsd'] ?? '0.00',
        'ordMrgn' => $data['ordMrgn'] ?? '0.00',
        'rmsVldtd' => $data['rmsVldtd'] ?? '',
        'reqdMrgn' => $data['reqdMrgn'] ?? '0.00',
        'avlMrgn' => $data['avlMrgn'] ?? '0.00',
        'insufFund' => $data['insufFund'] ?? '0.00',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'error' => 'Invalid response from API',
        'raw' => $response
    ], JSON_PRETTY_PRINT);
}
