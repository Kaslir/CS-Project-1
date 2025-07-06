<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Administrator') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if (!$user_id) {
    die('Invalid user ID.');
}

$roles = [];
$rRes = $conn->query("SELECT role_id, role_name FROM Role");
if ($rRes) {
    $roles = $rRes->fetch_all(MYSQLI_ASSOC);
}

$stmt = $conn->prepare("
  SELECT name, email, phone, date_of_birth, id_number, role_id
    FROM `User`
   WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows !== 1) {
    die('User not found.');
}
$user = $result->fetch_assoc();
$stmt->close();

$msg      = '';
$error    = '';
$msg_pwd  = '';
$error_pwd = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $name      = $conn->real_escape_string($_POST['name'] ?? '');
    $email     = $conn->real_escape_string($_POST['email'] ?? '');
    $phone     = $conn->real_escape_string($_POST['phone'] ?? '');
    $dob       = $conn->real_escape_string($_POST['dob'] ?? '');
    $id_number = $conn->real_escape_string($_POST['id_number'] ?? '');
    $role_id   = intval($_POST['role_id'] ?? 0);

    if (!$name || !$email || !$role_id) {
        $error = 'Name, email and role are required.';
    } else {
        $uStmt = $conn->prepare("
          UPDATE `User`
             SET name = ?, email = ?, phone = ?, date_of_birth = ?, id_number = ?, role_id = ?
           WHERE user_id = ?
        ");
        $uStmt->bind_param("ssssiii",
            $name, $email, $phone, $dob, $id_number, $role_id, $user_id
        );
        if ($uStmt->execute()) {
            $msg = 'User details updated.';
            $user = ['name'=>$name,'email'=>$email,'phone'=>$phone,'date_of_birth'=>$dob,'id_number'=>$id_number,'role_id'=>$role_id];
        } else {
            $error = 'Update error: ' . $uStmt->error;
        }
        $uStmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pwd'])) {
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password === '' || $confirm_password === '') {
        $error_pwd = 'Both password fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error_pwd = 'Passwords do not match.';
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $pStmt = $conn->prepare("UPDATE `User` SET password = ? WHERE user_id = ?");
        $pStmt->bind_param("si", $hash, $user_id);
        if ($pStmt->execute()) {
            $msg_pwd = 'Password updated successfully.';
        } else {
            $error_pwd = 'Password update error: ' . $pStmt->error;
        }
        $pStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .dashboard-container { display: flex; min-height: 100vh; }
    .sidebar { width: 220px; background: #2c3e50; color: #ecf0f1; padding: 20px 0; }
    .sidebar .logo { text-align: center; font-size: 1.5rem; margin-bottom: 1rem; }
    .sidebar .nav { list-style: none; padding: 0; }
    .sidebar .nav li { margin: 0.5rem 0; }
    .sidebar .nav li a { color: #ecf0f1; text-decoration: none; padding: 0.5rem 1rem; display: block; }
    .sidebar .nav li.active a, .sidebar .nav li a:hover { background: #34495e; }
    .main-content { flex: 1; background: #ecf0f1; padding: 20px; }
    .header { display: flex; justify-content: space-between; align-items: center; }
    .header h1 { margin: 0; }
    .header a.logout-btn { text-decoration: none; color: #e74c3c; font-weight: bold; }
    .panel { background: #fff; border-radius: 8px; padding: 20px; margin-top: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    label { display: block; margin: 10px 0 5px; font-weight: 600; }
    input, select { width: 100%; padding: 8px; box-sizing: border-box; }
    .btn { margin-top: 12px; padding: 10px 20px; background: #3498db; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .btn:hover { background: #2980b9; }
    .message { color: green; margin-top: 10px; }
    .error   { color: red; margin-top: 10px; }
  </style>
</head>
<body class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">Clinic</div>
    <ul class="nav">
      <li><a href="admin_dashboard.php">Dashboard</a></li>
      <li class="active"><a href="manage_users.php">Manage Users</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="configure_settings.php">Settings</a></li>
    </ul>
  </aside>

  <main class="main-content">
    <div class="header">
      <h1>Edit User</h1>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <section class="panel">
      <h2>Update Profile</h2>
      <?php if ($msg): ?><p class="message"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

      <form method="POST">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Phone</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

        <label>Date of Birth</label>
        <input type="date" name="dob" value="<?= htmlspecialchars($user['date_of_birth']) ?>">

        <label>ID Number</label>
        <input type="text" name="id_number" value="<?= htmlspecialchars($user['id_number']) ?>">

        <label>Role</label>
        <select name="role_id" required>
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['role_id'] ?>"
              <?= $r['role_id']==$user['role_id']?'selected':'' ?>>
              <?= htmlspecialchars($r['role_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button type="submit" name="update_user" class="btn">Save Changes</button>
      </form>
    </section>

    <section class="panel">
      <h2>Change Password</h2>
      <?php if ($msg_pwd): ?><p class="message"><?= htmlspecialchars($msg_pwd) ?></p><?php endif; ?>
      <?php if ($error_pwd): ?><p class="error"><?= htmlspecialchars($error_pwd) ?></p><?php endif; ?>

      <form method="POST">
        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" name="update_pwd" class="btn">Update Password</button>
      </form>
    </section>
  </main>
</body>
</html>
