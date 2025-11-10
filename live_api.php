<?php
header('Content-Type: application/json');
$config = require __DIR__ . '/secure.php';
$access_token = $config['access_token'] ?? '';

$neosymbol = $_GET['neosymbol'] ?? '';
if (!$neosymbol) {
    echo json_encode(['error' => 'Missing neosymbol']);
    exit;
}

$url = "https://mis.kotaksecurities.com/script-details/1.0/quotes/neosymbol/" . rawurlencode($neosymbol) . "/all";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: $access_token",
        "Content-Type: application/json"
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['error' => $error]);
    exit;
}

$data = json_decode($response, true);
if (isset($data[0])) {
    $d = $data[0];
} elseif (isset($data['data'][0])) {
    $d = $data['data'][0];
} else {
    echo json_encode(['error' => 'Invalid response', 'raw' => $data]);
    exit;
}

echo json_encode([
    'ltp' => $d['ltp'] ?? '',
    'change' => $d['change'] ?? '',
    'per_change' => $d['per_change'] ?? ''
]);
?>
