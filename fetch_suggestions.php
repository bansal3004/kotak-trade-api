<?php
header('Content-Type: application/json; charset=utf-8');

$csv = __DIR__ . '/nse_cm-v1.csv';
$q = strtoupper(trim($_GET['q'] ?? ''));

if (!$q || !file_exists($csv)) {
    echo '[]';
    exit;
}

$fh = fopen($csv, 'r');
$head = fgetcsv($fh);
$map = array_flip($head);

// 🧩 Identify CSV columns safely
$colSym  = $map['pTrdSymbol'] ?? $map['SYMBOL'] ?? 0;
$colName = $map['pCompanyName'] ?? $map['pInstName'] ?? $map['pName'] ?? 1;
$colDesc = $map['pDesc'] ?? $map['DESCRIPTION'] ?? $map['pDescName'] ?? null;

$res = [];
$max = 40;

while (($r = fgetcsv($fh)) !== false) {
    $sym = strtoupper(trim($r[$colSym] ?? ''));
    $name = trim($r[$colName] ?? '');
    $desc = $colDesc !== null ? trim($r[$colDesc] ?? '') : '';

    if (!$sym) continue;

    // 🔍 Match query in symbol or name
    if (strpos($sym, $q) !== false || strpos(strtoupper($name), $q) !== false) {
        $res[] = [
            'symbol' => $sym,
            'name'   => $name,
            'desc'   => $desc
        ];
        if (count($res) >= $max) break;
    }
}

fclose($fh);

// ✅ Send JSON response
echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
