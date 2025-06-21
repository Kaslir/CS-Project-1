<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Administrator') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$error   = '';
$success = '';
$user_id = intval($_GET['user_id'] ?? ($_POST['user_id'] ?? 0));
if (!$user_id) {
    die("Invalid user ID.");
}

$rolesRes = $conn->query("SELECT role_id, role_name FROM Role");
if (!$rolesRes) {
    die("Error loading roles: " . htmlspecialchars($conn->error));
}
$roles = $rolesRes->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = $conn->real_escape_string(trim($_POST['name']      ?? ''));
    $email     = $conn->real_escape_string(trim($_POST['email']     ?? ''));
    $phone     = $conn->real_escape_string(trim($_POST['phone']     ?? ''));
    $dob       = $conn->real_escape_string(trim($_POST['dob']       ?? ''));
    $id_number = $conn->real_escape_string(trim($_POST['id_number'] ?? ''));
    $role_id   = intval($_POST['role_id'] ?? 0);

    if (!$name || !$email || !$role_id) {
        $error = "Name, Email, and Role are required.";
    } else {
        $sql = "
          UPDATE `User` SET
            name          = '$name',
            email         = '$email',
            phone         = '$phone',
            date_of_birth = '$dob',
            id_number     = '$id_number',
            role_id       = $role_id
          WHERE user_id = $user_id
        ";
        if ($conn->query($sql)) {
            $success = "User updated successfully.";
        } else {
            $error = "Update failed: " . htmlspecialchars($conn->error);
        }
    }
}

$res = $conn->query("
  SELECT
    user_id, name, email, phone,
    date_of_birth, id_number, role_id
  FROM `User`
  WHERE user_id = $user_id
  LIMIT 1
");
if (!$res || $res->num_rows !== 1) {
    die("User not found.");
}
$user = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .dashboard-container { display: flex; min-height: 100vh; }
    .sidebar {
      width: 220px;
      background: #2c3e50;
      color: #ecf0f1;
      padding: 20px 0;
    }
    .sidebar .logo {
      text-align: center;
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }
    .sidebar .nav { list-style: none; padding: 0; }
    .sidebar .nav li { margin: 0.5rem 0; }
    .sidebar .nav li a {
      color: #ecf0f1;
      text-decoration: none;
      padding: 0.5rem 1rem;
      display: block;
    }
    .sidebar .nav li.active a,
    .sidebar .nav li a:hover {
      background: #34495e;
    }
    .main-content {
      flex: 1;
      background: #ecf0f1;
      padding: 20px;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header h1 { margin: 0; }
    .header a.logout-btn {
      text-decoration: none;
      color: #e74c3c;
      font-weight: bold;
    }
    .panel {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }
    .panel label {
      display: block;
      margin-top: 12px;
      font-weight: 600;
    }
    .panel input, .panel select {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .panel .btn {
      margin-top: 20px;
      padding: 10px 16px;
      border: none;
      border-radius: 4px;
      background: #28a745;
      color: #fff;
      cursor: pointer;
    }
    .panel .btn:hover { background: #218838; }
    .message {
      margin-top: 12px;
      padding: 10px;
      border-radius: 4px;
    }
    .message.error { background: #fdecea; color: #e74c3c; }
    .message.success { background: #e6f4ea; color: #28a745; }
  </style>
</head>
<body class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">Clinic</div>
    <ul class="nav">
      <li><a href="admin_dashboard.php">Dashboard</a></li>
      <li class="active"><a href="manage_users.php">Manage Users</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="configure_settings.php">Configure Settings</a></li>
    </ul>
  </aside>

  <main class="main-content">
    <div class="header">
      <h1>Edit User</h1>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="panel">
      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php elseif ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="edit_user.php">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">

        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($user['date_of_birth']) ?>">

        <label for="id_number">ID Number</label>
        <input type="text" id="id_number" name="id_number" value="<?= htmlspecialchars($user['id_number']) ?>">

        <label for="role_id">Role</label>
        <select id="role_id" name="role_id" required>
          <option value="" disabled>-- Select Role --</option>
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['role_id'] ?>"
              <?= $r['role_id'] === intval($user['role_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($r['role_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Save Changes</button>
      </form>
    </div>
  </main>
</body>
</html>
