<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('Asia/Kolkata');

$config = require __DIR__ . "/secure.php";

$base = $_SESSION['trade_base'] ?? '';
$access = $config['access_token'];

if (!$base) {
    echo json_encode(["stat" => "Not_Ok", "emsg" => "Session expired"]);
    exit;
}

// incoming symbol = "INFY-EQ" / "TATASTEEL-EQ"
$userSymbol = strtoupper(trim($_GET['symbol'] ?? ''));

if (!$userSymbol) {
    echo json_encode(["stat" => "Not_Ok", "emsg" => "No symbol given"]);
    exit;
}

// === Find pSymbol in CSV ===
$csv = __DIR__ . "/nse_cm-v1.csv";

if (!file_exists($csv)) {
    echo json_encode(["stat"=>"Not_Ok", "emsg"=>"CSV missing"]);
    exit;
}

$fh = fopen($csv, "r");
$head = fgetcsv($fh);
$map = array_flip($head);

$colSym = $map["pTrdSymbol"];
$colPS  = $map["pSymbol"];

$pSymbol = "";

while (($row = fgetcsv($fh)) !== false) {
    if (trim($row[$colSym]) === $userSymbol) {
        $pSymbol = trim($row[$colPS]);
        break;
    }
}
fclose($fh);

if (!$pSymbol) {
    echo json_encode(["stat"=>"Not_Ok","emsg"=>"pSymbol not found in CSV"]);
    exit;
}

// === Call Quotes API ===
$url = "$base/script-details/1.0/quotes/neosymbol/nse_cm|$pSymbol/all";

$headers = [
    "Content-Type: application/json",
    "Authorization: $access",
    "neo-fin-key: neotradeapi"
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(["stat"=>"Not_Ok", "emsg"=>$err]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data[0]['ltp'])) {
    echo json_encode(["stat"=>"Not_Ok","raw"=>$data]);
    exit;
}

echo json_encode([
    "stat" => "Ok",
    "symbol" => $userSymbol,
    "pSymbol" => $pSymbol,
    "ltp" => $data[0]["ltp"],
    "change" => $data[0]["change"],
    "per_change" => $data[0]["per_change"],
    "high" => $data[0]["ohlc"]["high"],
    "low" => $data[0]["ohlc"]["low"],
    "open" => $data[0]["ohlc"]["open"],
    "close" => $data[0]["ohlc"]["close"],
    "year_high" => $data[0]["year_high"],
    "year_low" => $data[0]["year_low"],
], JSON_PRETTY_PRINT);
?>
