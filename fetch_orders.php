<?php
session_start(); header('Content-Type: application/json');
if(empty($_SESSION['trade_token'])||empty($_SESSION['trade_sid'])||empty($_SESSION['trade_base'])){ echo json_encode(['stat'=>'Not_Ok']); exit; }
$base=$_SESSION['trade_base']; $auth=$_SESSION['trade_token']; $sid=$_SESSION['trade_sid'];

$ch=curl_init($base.'/quick/user/orders');
curl_setopt_array($ch,[
  CURLOPT_RETURNTRANSFER=>true,
  CURLOPT_HTTPHEADER=>["Auth: {$auth}","Sid: {$sid}","neo-fin-key: neotradeapi"]
]);
echo curl_exec($ch) ?: json_encode(['stat'=>'Not_Ok']);
curl_close($ch);
