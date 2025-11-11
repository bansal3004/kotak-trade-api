<?php
// db.php — Database connection for Kotak API panel

// localhost

// $host = 'localhost';
// $user = 'root';      
// $pass = '';          
// $dbname = 'kotakapi';

// server

$host = 'localhost';
$user = 'u451884548_Kotakapi';      
$pass = 'Kotakapi@3004';          
$dbname = 'u451884548_Kotakapi';


$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
