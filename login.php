<?php
// 🧠 Show PHP errors (for debugging only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Start session (first thing)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ✅ Include DB connection
require_once __DIR__ . '/db.php';

// ✅ Load secure credentials (from database via secure.php)
$config = require __DIR__ . '/secure.php';

// 🔁 If already logged in, redirect to kotak_login.php
if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
  header("Location: kotak_login.php");
  exit;
}

$error = "";

// 🧩 Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($username === $config['username'] && $password === $config['password']) {
    $_SESSION['logged_in'] = true;

    // Clear previous Kotak session tokens
    unset($_SESSION['trade_token'], $_SESSION['trade_sid'], $_SESSION['trade_base'], $_SESSION['validated']);

    header("Location: kotak_login.php");
    exit;
  } else {
    $error = "❌ Invalid credentials.";
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Kotak API Login</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
       background: radial-gradient(
    circle at center,
    rgba(173, 216, 230, 0.3) 0%,    /* light sky */
    rgba(152, 251, 152, 0.2) 40%,   /* mint green */
    rgba(255, 255, 255, 0) 85%
  );
  font-family: "Inter", sans-serif;
  margin: 0;
  padding: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      font-family: 'Inter', sans-serif;
    }

    .card {
      background: rgba(255, 255, 255, 0.7); /* soft translucent white */
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
      padding: 26px;
      width: 440px;
      border-radius: 14px;
      
      text-align: center
    }

    @media(max-width: 1200px) {
     
      .card{
        width: 700px;
      }
    }

    h1 {
      font-size: 20px;
      margin-bottom: 18px;
      font-weight: 600
    }

    input {
      width: 100%;
      padding: 11px;
      margin-bottom: 14px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      font-size: 14px;
      outline: none
    }

    input:focus {
      border-color: #0aa70a;
      box-shadow: 0 0 6px rgba(10, 167, 10, 0.3)
    }

    .btn {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: linear-gradient(90deg, #0aa70a, #17c317);
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer
    }

    .btn:hover {
      box-shadow: 0 0 12px rgba(10, 167, 10, 0.6)
    }

    .err {
      color: #d32f2f;
      font-size: 13px;
      margin-bottom: 8px
    }
  </style>
</head>

<body>
  <main class="card">
    <h1>📈 Trade Login</h1>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" class="btn">Login</button>
    </form>
  </main>
</body>

</html>