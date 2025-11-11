<?php
// secure.php — dynamic multi-profile system
include("db.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// require_once __DIR__ . '/db.php';

// 🧠 Load selected profile (from session or fallback to first)
$active_id = $_SESSION['active_profile_id'] ?? 0;

// Query for profile
if ($active_id) {
    $stmt = $conn->prepare("SELECT * FROM profiles WHERE id = ?");
    $stmt->bind_param("i", $active_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM profiles ORDER BY id ASC LIMIT 1");
}
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if (!$profile) {
    die("<p style='color:red;text-align:center;margin-top:30px;'>⚠️ No profile found. Please add one first.</p>");
}

// Return structure (no change for rest of system)
return [
    'username' => 'admin',
    'password' => '1234',
    'pin'      => '1234',
    'access_token' => $profile['access_token'],
    'ucc'          => $profile['ucc'],
    'mobile'       => $profile['mobile'],
    'mpin'         => $profile['mpin']
];
?>
