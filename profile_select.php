<?php
session_start();
require_once __DIR__ . '/db.php';

// Handle Add Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $stmt = $conn->prepare("INSERT INTO profiles (name, access_token, ucc, mobile, mpin) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $_POST['name'], $_POST['access_token'], $_POST['ucc'], $_POST['mobile'], $_POST['mpin']);
    $stmt->execute();
    $stmt->close();
    header("Location: profile_select.php");
    exit;
}

// Handle Delete
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM profiles WHERE id = $id");
    if (isset($_SESSION['active_profile_id']) && $_SESSION['active_profile_id'] == $id) {
        unset($_SESSION['active_profile_id']);
    }
    header("Location: profile_select.php");
    exit;
}

// Handle Set Active
if (isset($_GET['set'])) {
    $_SESSION['active_profile_id'] = intval($_GET['set']);
    header("Location: index.php");
    exit;
}

$result = $conn->query("SELECT * FROM profiles ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile Selector | Kotak API</title>
<style>
body {font-family:Inter,sans-serif;background:#f9fafb;margin:0;padding:40px;}
.card {background:#fff;padding:20px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,0.08);max-width:640px;margin:auto;}
h2 {margin-bottom:12px;}
table {width:100%;border-collapse:collapse;margin-bottom:20px;}
th,td {padding:8px 10px;border-bottom:1px solid #eee;text-align:left;}
button,a.btn {background:#2563eb;color:#fff;padding:6px 12px;border:none;border-radius:6px;text-decoration:none;cursor:pointer;}
a.btn.del {background:#d32f2f;}
form input {padding:8px;margin:4px;width:calc(100% - 16px);}
</style>
</head>
<body>
<div class="card">
<h2>👤 Select Active Profile</h2>
<table>
<tr><th>Name</th><th>UCC</th><th>Mobile</th><th>Actions</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?=htmlspecialchars($row['name'])?></td>
<td><?=$row['ucc']?></td>
<td><?=$row['mobile']?></td>
<td>
<a href="?set=<?=$row['id']?>" class="btn">Use</a>
<a href="?del=<?=$row['id']?>" class="btn del" onclick="return confirm('Delete this profile?')">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<h3>Add New Profile</h3>
<form method="POST">
<input type="text" name="name" placeholder="Profile Name" required><br>
<input type="text" name="access_token" placeholder="Access Token" required><br>
<input type="text" name="ucc" placeholder="UCC Code" required><br>
<input type="text" name="mobile" placeholder="Mobile Number" required><br>
<input type="text" name="mpin" placeholder="MPIN" required><br>
<button type="submit">➕ Add Profile</button>
</form>
</div>
</body>
</html>
