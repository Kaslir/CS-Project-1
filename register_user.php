<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';

$error = '';

$roles = [];
if ($res = $conn->query("SELECT role_id, role_name FROM Role")) {
    $roles = $res->fetch_all(MYSQLI_ASSOC);
} else {
    die("Could not load roles: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = $conn->real_escape_string($_POST['name']);
    $email      = $conn->real_escape_string($_POST['email']);
    $phone      = $conn->real_escape_string($_POST['phone']);
    $dob        = $conn->real_escape_string($_POST['dob']);
    $id_number  = $conn->real_escape_string($_POST['id_number']);
    $role_id    = intval($_POST['role_id']);
    $password   = $_POST['password'] ?? '';

    $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

    $sql = "
      INSERT INTO `User`
        (name, email, phone, date_of_birth, id_number, password, role_id)
      VALUES
        ('$name', '$email', '$phone', '$dob', '$id_number', '$hashedPwd', $role_id)
    ";

    if (!$conn->query($sql)) {
        $error = "Database error: " . $conn->error;
    } else {
        header("Location: manage_users.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register User</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="login-page">
    <div class="login-box">
      <h2>Register User</h2>

      <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" action="">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone Number:</label>
        <input type="tel" id="phone" name="phone" required>

        <label for="dob">Date of Birth:</label>
        <input type="date" id="dob" name="dob" required>

        <label for="id_number">ID Number:</label>
        <input type="text" id="id_number" name="id_number" required>

        <label for="role_id">Role:</label>
        <select id="role_id" name="role_id" required>
          <option value="" disabled selected>Select role</option>
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['role_id'] ?>">
              <?= htmlspecialchars($r['role_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Register User</button>
      </form>
    </div>
  </div>
</body>
</html>
