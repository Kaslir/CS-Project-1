<?php
// edit_user.php

// Show all errors for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session if none
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only Administrators may access
if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Administrator') {
    header('HTTP/1.1 403 Forbidden');
    echo "<h1>403 Forbidden</h1><p>You do not have permission to view this page.</p>";
    exit;
}

require_once 'db_connect.php';

$error   = '';
$success = '';
$user_id = intval($_GET['user_id'] ?? ($_POST['user_id'] ?? 0));

if (!$user_id) {
    die("Invalid user ID.");
}

// Fetch list of roles for dropdown
$rolesRes = $conn->query("SELECT role_id, role_name FROM Role");
if (!$rolesRes) {
    die("Error loading roles: " . htmlspecialchars($conn->error));
}
$roles = $rolesRes->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = $conn->real_escape_string(trim($_POST['name']      ?? ''));
    $email     = $conn->real_escape_string(trim($_POST['email']     ?? ''));
    $phone     = $conn->real_escape_string(trim($_POST['phone']     ?? ''));
    $dob       = $conn->real_escape_string(trim($_POST['dob']       ?? ''));
    $id_number = $conn->real_escape_string(trim($_POST['id_number'] ?? ''));
    $role_id   = intval($_POST['role_id'] ?? 0);

    // Basic validation
    if (!$name || !$email || !$role_id) {
        $error = "Name, Email, and Role are required.";
    } else {
        // Update the user record
        $sql = "
          UPDATE User SET
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

// Fetch the user’s current data
$res = $conn->query("
  SELECT
    user_id,
    name,
    email,
    phone,
    date_of_birth,
    id_number,
    role_id
  FROM User
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
  <link rel="stylesheet" href="style.css">
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .header a { text-decoration: none; color: #007bff; }
    .form-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); max-width: 500px; margin: auto; }
    label { display: block; margin-top: 12px; font-weight: 600; }
    input, select { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 4px; }
    .btn { margin-top: 20px; padding: 10px 16px; border: none; border-radius: 4px; background: #28a745; color: #fff; cursor: pointer; }
    .btn:hover { background: #218838; }
    .message { margin-top: 12px; padding: 10px; border-radius: 4px; }
    .error { background: #fdecea; color: #e74c3c; }
    .success { background: #e6f4ea; color: #28a745; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Edit User</h1>
    <a href="manage_users.php">← Back to Manage Users</a>
  </div>

  <div class="form-container">
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
</body>
</html>