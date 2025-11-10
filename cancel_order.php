<?php
session_start(); header('Content-Type: application/json');
if(empty($_SESSION['trade_token'])||empty($_SESSION['trade_sid'])||empty($_SESSION['trade_base'])){ echo json_encode(['stat'=>'Not_Ok']); exit; }
$base=$_SESSION['trade_base']; $auth=$_SESSION['trade_token']; $sid=$_SESSION['trade_sid'];

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$on = $in['on'] ?? '';
if(!$on){ echo json_encode(['stat'=>'Not_Ok','emsg'=>'order no missing']); exit; }

$ch=curl_init($base.'/quick/order/cancel');
curl_setopt_array($ch,[
  CURLOPT_RETURNTRANSFER=>true,
  CURLOPT_POST=>true,
  CURLOPT_HTTPHEADER=>["Auth: {$auth}","Sid: {$sid}","neo-fin-key: neotradeapi","Content-Type: application/x-www-form-urlencoded"],
  CURLOPT_POSTFIELDS=> http_build_query(['jData'=>json_encode(['on'=>$on,'am'=>'NO'])])
]);
echo curl_exec($ch) ?: json_encode(['stat'=>'Not_Ok']);
curl_close($ch);
