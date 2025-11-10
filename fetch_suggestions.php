<?php
header('Content-Type: application/json; charset=utf-8');
$csv = __DIR__ . '/nse_cm-v1.csv';
$q = strtoupper(trim($_GET['q'] ?? ''));
if (!$q || !file_exists($csv)) { echo '[]'; exit; }

$fh = fopen($csv,'r'); $head = fgetcsv($fh); $map = array_flip($head);
$colSym = $map['pTrdSymbol'] ?? $map['SYMBOL'] ?? 0;
$colName= $map['pCompanyName'] ?? $map['pInstName'] ?? $map['pName'] ?? 1;

$res=[]; $max=40;
while(($r=fgetcsv($fh))!==false){
  $sym = strtoupper(trim($r[$colSym] ?? ''));
  $name= trim($r[$colName] ?? '');
  if(!$sym) continue;
  if(strpos($sym,$q)!==false || strpos(strtoupper($name),$q)!==false){
    $res[] = ['symbol'=>$sym,'name'=>$name];
    if(count($res)>=$max) break;
  }
}
fclose($fh);
echo json_encode($res);
