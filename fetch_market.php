<?php
session_start();
$config = require __DIR__ . '/secure.php';

$base  = $_SESSION['trade_base'] ?? 'https://mis.kotaksecurities.com';
$acc   = $config['access_token'];  // ONLY Authorization required for quotes
$symbols = rawurlencode('nse_cm|Nifty 50,nse_cm|Nifty Bank/all');

$url = $base . '/script-details/1.0/quotes/neosymbol/' . $symbols;
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER=>true,
  CURLOPT_HTTPHEADER=>["Authorization: {$acc}","Content-Type: application/json"],
  CURLOPT_SSL_VERIFYPEER=>false
]);
$out = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $out ?: '[]';
