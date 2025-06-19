<?php
// login.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

$loginMessage = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Fetch user with their role name
    $sql = "
      SELECT u.user_id,
             u.name,
             u.password,
             r.role_name
        FROM `User` u
   LEFT JOIN Role    r ON u.role_id = r.role_id
       WHERE u.email = '$email'
       LIMIT 1
    ";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Store session data
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['name']      = $user['name'];
            $_SESSION['role_name'] = $user['role_name'];

            // Redirect based on role
            switch ($user['role_name']) {
                case 'Receptionist':
                    header("Location: receptionist_dashboard.php");
                    break;
                case 'Administrator':
                    header("Location: admin_dashboard.php");
                    break;
                case 'Triage Nurse':
                    header("Location: edit_triage.php");
                    break;
                case 'Doctor':
                    header("Location: doctor_dashboard.php");
                    break;
                default:
                    $loginMessage = "<p style='color:red;'>Unknown role.</p>";
                    session_unset();
                    session_destroy();
                    break;
            }
            exit;
        } else {
            $loginMessage = "<p style='color:red;'>Incorrect password.</p>";
        }
    } else {
        $loginMessage = "<p style='color:red;'>User not found.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
  <div class="login-box">
    <h2>Login</h2>

    <?= $loginMessage ?>

    <form method="POST" action="">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
