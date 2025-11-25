<?php
error_reporting(0);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/db.php';

if ($_SESSION['active_profile_id']) {
  header("Location: index.php");
}

// 🔹 Load all profiles
$profiles = [];
$res = $conn->query("SELECT * FROM profiles ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
  $profiles[] = $row;
}

// 🧠 Handle Add Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_profile'])) {
  $stmt = $conn->prepare("INSERT INTO profiles (name, access_token, ucc, mobile, mpin) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $_POST['name'], $_POST['access_token'], $_POST['ucc'], $_POST['mobile'], $_POST['mpin']);
  $stmt->execute();
  $stmt->close();
  header("Location: kotak_login.php");
  exit;
}

// 🗑 Handle Delete
if (isset($_GET['del'])) {
  $id = intval($_GET['del']);
  $conn->query("DELETE FROM profiles WHERE id = $id");
  if (isset($_SESSION['active_profile_id']) && $_SESSION['active_profile_id'] == $id) {
    unset($_SESSION['active_profile_id']);
  }
  header("Location: kotak_login.php");
  exit;
}

// ✅ Handle TOTP submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['totp'])) {
  $active_id = intval($_POST['profile_id']);
  $_SESSION['active_profile_id'] = $active_id;

  $stmt = $conn->prepare("SELECT * FROM profiles WHERE id = ?");
  $stmt->bind_param("i", $active_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $profile = $result->fetch_assoc();
  $stmt->close();

  if (!$profile) {
    die("<p style='color:red;text-align:center;margin-top:30px;'>⚠️ Profile not found.</p>");
  }

  $totp = trim($_POST['totp']);
  $url = "https://mis.kotaksecurities.com/login/1.0/tradeApiLogin";

  $data = [
    "mobileNumber" => $profile['mobile'],
    "ucc" => $profile['ucc'],
    "totp" => $totp
  ];

  $headers = [
    "Authorization: {$profile['access_token']}",
    "neo-fin-key: neotradeapi",
    "Content-Type: application/json"
  ];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => $headers
  ]);
  $response = curl_exec($ch);
  $error = curl_error($ch);
  curl_close($ch);

  if ($error) {
    die("<p style='color:red;text-align:center;margin-top:30px;'>⚠️ cURL Error: $error</p>");
  }

  $result = json_decode($response, true);

  if (isset($result['data']['token']) && isset($result['data']['sid'])) {
    $view_token = $result['data']['token'];
    $view_sid   = $result['data']['sid'];
    header("Location: validate.php?token=" . urlencode($view_token) . "&sid=" . urlencode($view_sid));
    exit;
  } else {
    echo "<main class='card' style='max-width:560px;margin:40px auto;font-family:Inter,sans-serif'>";
    echo "<h2>❌ TOTP Login Failed</h2>";
    echo "<pre style='background:#fafafa;border:1px solid #eee;padding:12px;border-radius:8px;overflow:auto'>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "</pre>";
    echo "<p><a href='kotak_login.php'>↩️ Try again</a></p>";
    echo "</main>";
    exit;
  }
}
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Kotak Multi-Profile Login</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      background: radial-gradient(circle at center,
          rgba(173, 216, 230, 0.3) 0%,
          rgba(152, 251, 152, 0.2) 40%,
          rgba(255, 255, 255, 0) 85%);
      font-family: "Inter", sans-serif;
      margin: 0;
      padding: 40px;
      color: #111;
    }

    /* ⭐⭐ ONLY CHANGE: GRID → FLEX ⭐⭐ */
    .container {
      display: flex;
      gap: 30px;
      max-width: 1100px;
      margin: auto;
    }

    .container > div {
      flex: 1;
    }

    @media(max-width: 1200px) {
      .container {
        flex-direction: column;
      }
      .card{
        width: 800px;
      }
    }
    /* ⭐⭐ END FLEXBOX UPDATE ⭐⭐ */


    .card {
      background: rgba(255, 255, 255, 0.78);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
      padding: 26px 28px;
    }

    h1 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    label {
      font-weight: 600;
      font-size: 14px;
    }

    select,
    input {
      width: 100%;
      padding: 11px;
      margin-top: 6px;
      margin-bottom: 14px;
      border: 1px solid #ddd;
      border-radius: 10px;
      font-size: 14px;
      outline: none;
    }

    input:focus,
    select:focus {
      border-color: #0aa70a;
      box-shadow: 0 0 6px rgba(10, 167, 10, 0.25);
    }

    .btn {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: linear-gradient(90deg, #0aa70a, #17c317);
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn:hover {
      box-shadow: 0 0 10px rgba(10, 167, 10, 0.4);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
      margin-top: 10px;
    }

    th,
    td {
      padding: 8px 10px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }

    th {
      background: #f9fafb;
    }

    .del {
      color: #d32f2f;
      text-decoration: none;
      font-weight: 600;
    }

    .add-box {
      border-top: 1px solid #eee;
      margin-top: 20px;
      padding-top: 10px;
    }

    .add-box h3 {
      margin-top: 0;
      font-size: 15px;
    }

    .logout-btn {
      display: inline-block;
      background: linear-gradient(135deg, #ef4444, #b91c1c);
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      padding: 8px 18px;
      border-radius: 8px;
      text-decoration: none;
      box-shadow: 0 0 16px rgba(239, 68, 68, 0.7);
      transition: all 0.3s ease;
    }

    .logout-btn:hover {
      background: linear-gradient(135deg, #dc2626, #7f1d1d);
      box-shadow: 0 0 16px rgba(239, 68, 68, 0.7);
      transform: scale(1.05);
    }

    .logout-btn:active {
      transform: scale(0.96);
      box-shadow: 0 0 10px rgba(239, 68, 68, 0.8) inset;
    }
  </style>
</head>

<body>
  <div class="container">

    <!-- Left Side -->
    <div>
      <div class="card">
        <h1>🔑 Login with TOTP</h1>
        <form method="POST">
          <label>Select Profile</label>
          <select name="profile_id" required>
            <option value="">— Choose Profile —</option>
            <?php foreach ($profiles as $p): ?>
              <option value="<?= $p['id'] ?>" <?= ($_SESSION['active_profile_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                <?= $p['name'] ?> (<?= $p['ucc'] ?>)
              </option>
            <?php endforeach; ?>
          </select>

          <label>Enter TOTP</label>
          <input type="text" name="totp" maxlength="6" placeholder="123456" required>
          <button type="submit" class="btn">Continue Login</button>
        </form>
      </div>

      <div class="card" style="margin-top: 10px;">
        <div style="display: flex; justify-content: space-between; align-items:center">
          <h3>👤 Manage Profiles</h3>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <table>
          <tr>
            <th>Name</th>
            <th>UCC</th>
            <th>Delete</th>
          </tr>
          <?php if (count($profiles) > 0): ?>
            <?php foreach ($profiles as $p): ?>
              <tr>
                <td><?= $p['name'] ?></td>
                <td><?= $p['ucc'] ?></td>
                <td>
                  <a href="?del=<?= $p['id'] ?>" class="del" onclick="return confirm('Delete this profile?')">🗑</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" style="text-align:center;">No profiles yet</td>
            </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- Right Side -->
    <div class="card">
      <div class="add-box">
        <h2>➕ Add New Profile</h2>
        <form method="POST">
          <input type="hidden" name="add_profile" value="1">
          <input type="text" name="name" placeholder="Profile Name" required>
          <input type="text" name="access_token" placeholder="Access Token" required>
          <input type="text" name="ucc" placeholder="UCC Code" required>
          <input type="text" name="mobile" placeholder="Mobile Number" required>
          <input type="text" name="mpin" placeholder="MPIN" required>
          <button type="submit" class="btn" style="margin-top:6px;">Add Profile</button>
        </form>
      </div>
    </div>

  </div>
</body>

</html>
