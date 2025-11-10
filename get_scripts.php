<?php
header('Content-Type: application/json');

// Temporary mock data — later you can load real scrip master file
$stocks = [
  ["symbol" => "INFY", "name" => "Infosys Ltd", "price" => 1632.50],
  ["symbol" => "TCS", "name" => "Tata Consultancy Services", "price" => 3741.20],
  ["symbol" => "RELIANCE", "name" => "Reliance Industries Ltd", "price" => 2898.30],
  ["symbol" => "HDFCBANK", "name" => "HDFC Bank Ltd", "price" => 1567.80],
  ["symbol" => "ICICIBANK", "name" => "ICICI Bank Ltd", "price" => 1042.40],
  ["symbol" => "SBIN", "name" => "State Bank of India", "price" => 818.90],
  ["symbol" => "WIPRO", "name" => "Wipro Ltd", "price" => 488.30],
];

echo json_encode(["scrips" => $stocks]);
?>
