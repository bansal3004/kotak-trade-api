<?php
session_start();

// 🧹 Destroy everything
$_SESSION = [];
unset($_SESSION['logged_in']);
unset($_SESSION['trade_token']);
unset($_SESSION['trade_sid']);
unset($_SESSION['trade_base']);
unset($_SESSION['validated']);

if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}

session_destroy();

// 🔁 Redirect to login
header("Location: login.php");
exit;
?>
