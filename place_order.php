<?php
session_start();
header('Content-Type: application/json');

// 🧠 Step 1: Validate session
if (empty($_SESSION['trade_token']) || empty($_SESSION['trade_sid']) || empty($_SESSION['trade_base'])) {
    echo json_encode(['stat' => 'Not_Ok', 'emsg' => 'Login/Validate first']);
    exit;
}

// 🗝️ Step 2: Load config
$config = require __DIR__ . '/secure.php';
$base = $_SESSION['trade_base'];
$auth = $_SESSION['trade_token'];
$sid  = $_SESSION['trade_sid'];

// 🧾 Step 3: Get incoming data (support JSON + form)
$input = file_get_contents('php://input');
parse_str($input, $form);
$payload = isset($form['jData']) ? json_decode($form['jData'], true) : json_decode($input, true);

// 🧩 Step 4: Sanitize and map order type (API format)
$userOrderType = strtoupper(trim($payload['pt'] ?? 'MARKET'));
switch ($userOrderType) {
    case 'LIMIT':
    case 'L':
        $pt = 'L';  // Limit
        break;
    case 'MARKET':
    case 'MKT':
        $pt = 'MKT'; // Market
        break;
    case 'SL':
        $pt = 'SL';
        break;
    case 'SL-M':
    case 'SLM':
        $pt = 'SL-M';
        break;
    default:
        $pt = 'L';
        break;
}

// 🧠 Step 5: Price handling
$price = ($pt === 'MKT') ? "0" : strval($payload['pr'] ?? '0');
$triggerPrice = $payload['tp'] ?? "0";

// 🏗️ Step 6: Construct jData (from docs)
$jData = [
    'am' => $payload['am'] ?? 'NO',          // After market
    'dq' => $payload['dq'] ?? '0',           // Disclosed qty
    'es' => $payload['es'] ?? 'nse_cm',      // Exchange segment
    'mp' => $payload['mp'] ?? '0',           // Market protection
    'pc' => $payload['pc'] ?? 'CNC',         // Product
    'pf' => 'N',                             // Portfolio flag
    'pr' => $price,                          // Price
    'pt' => $pt,                             // Order type (L/MKT/SL/SL-M)
    'qt' => strval($payload['qt'] ?? '1'),   // Quantity
    'rt' => $payload['rt'] ?? 'DAY',         // Validity
    'tp' => $triggerPrice,                   // Trigger price
    'ts' => $payload['ts'] ?? '',            // Symbol
    'tt' => $payload['tt'] ?? 'B',           // Buy/Sell
];

// Optional BO/CO fields if provided
$extraFields = ['sot','slt','slv','sov','tlt','tsv'];
foreach ($extraFields as $f) {
    if (isset($payload[$f])) $jData[$f] = $payload[$f];
}

// 🔗 Step 7: Setup API request
$url = $base . '/quick/order/rule/ms/place';
$headers = [
    "Auth: $auth",
    "Sid: $sid",
    "neo-fin-key: neotradeapi",
    "Content-Type: application/x-www-form-urlencoded"
];

// ⚙️ Step 8: Execute cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => http_build_query(['jData' => json_encode($jData)]),
]);
$response = curl_exec($ch);
$error = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 🧩 Step 9: Error handling
if ($error) {
    echo json_encode(['stat' => 'Not_Ok', 'curl_error' => $error]);
    exit;
}

$decoded = json_decode($response, true);

// 🧠 Step 10: Extract important fields
$api = $decoded['data'][0] ?? $decoded ?? [];

$orderStatus = $api['ordSt'] ?? $decoded['stat'] ?? 'Unknown';
$rejReason   = $api['rejRsn'] ?? $decoded['errMsg'] ?? $decoded['emsg'] ?? '';
$orderNo     = $api['nOrdNo'] ?? '';
$symbol      = $api['trdSym'] ?? ($jData['ts'] ?? '');
$price       = $api['prc'] ?? $jData['pr'];
$qty         = $api['qty'] ?? $jData['qt'];
$type        = $api['pt'] ?? $jData['pt'];
$product     = $api['pc'] ?? $jData['pc'];

// 🧾 Step 11: Final structured JSON response
echo json_encode([
    'http_code'    => $httpcode,
    'request_sent' => $jData,
    'order_no'     => $orderNo,
    'symbol'       => $symbol,
    'price'        => $price,
    'qty'          => $qty,
    'type'         => $type,
    'product'      => $product,
    'status'       => $orderStatus,
    'reason'       => $rejReason,
    'raw_response' => $decoded
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
