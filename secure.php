<?php
include("db.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🧠 Load user credentials (assuming single admin or logged-in user)
$userResult = $conn->query("SELECT * FROM users LIMIT 1");
$user = $userResult->fetch_assoc();

// 🧠 Load selected Kotak profile
$active_id = $_SESSION['active_profile_id'] ?? 0;

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

// ❌ Handle no data cases
if (!$user) {
    die("<p style='color:red;text-align:center;margin-top:30px;'>⚠️ No user found in database.</p>");
}
if (!$profile) {
    die("<p style='color:red;text-align:center;margin-top:30px;'>⚠️ No profile found. Please add one first.</p>");
}

// ✅ Return array (compatible with your entire system)
return [
    'username' => $user['username'],
    'password' => $user['password'],
    'pin'      => $user['pin'],

    'name'     => $profile['name'],
    'access_token' => $profile['access_token'],
    'ucc'          => $profile['ucc'],
    'mobile'       => $profile['mobile'],
    'mpin'         => $profile['mpin']
];
?>
