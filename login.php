<?php
// login.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

// If already logged in, redirect based on role
if (!empty($_SESSION['role_name'])) {
    switch ($_SESSION['role_name']) {
        case 'Receptionist':
            header('Location: receptionist_dashboard.php'); break;
        case 'Administrator':
            header('Location: admin_dashboard.php'); break;
        case 'Triage Nurse':
            header('Location: edit_triage.php'); break;
        case 'Doctor':
            header('Location: doctor_dashboard.php'); break;
        default:
            session_unset();
            session_destroy();
            header('Location: login.php'); break;
    }
    exit;
}

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $sql = "
      SELECT u.user_id, u.name, u.password, r.role_name
        FROM `User` u
   LEFT JOIN Role      r ON u.role_id = r.role_id
       WHERE u.email = '$email'
       LIMIT 1
    ";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['name']      = $user['name'];
            $_SESSION['role_name'] = $user['role_name'];

            switch ($user['role_name']) {
                case 'Receptionist':
                    header("Location: receptionist_dashboard.php"); break;
                case 'Administrator':
                    header("Location: admin_dashboard.php"); break;
                case 'Triage Nurse':
                    header("Location: edit_triage.php"); break;
                case 'Doctor':
                    header("Location: doctor_dashboard.php"); break;
                default:
                    $loginError = "Unknown role.";
                    session_unset();
                    session_destroy();
                    break;
            }
            exit;
        } else {
            $loginError = "Incorrect password.";
        }
    } else {
        $loginError = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #ecf0f1;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      background: #fff;
      padding: 24px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      width: 320px;
    }
    .login-box h2 {
      margin: 0 0 16px;
      text-align: center;
    }
    label {
      display: block;
      margin: 12px 0 4px;
      font-weight: 600;
    }
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }
    .checkbox-container {
      margin: 12px 0;
      display: flex;
      align-items: center;
    }
    .checkbox-container input {
      margin-right: 8px;
    }
    .message {
      color: #e74c3c;
      text-align: center;
      margin-bottom: 12px;
    }
    button {
      width: 100%;
      padding: 10px;
      background: #3498db;
      color: #fff;
      border: none;
      border-radius: 4px;
      font-size: 1rem;
      cursor: pointer;
    }
    button:hover {
      background: #2980b9;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Login</h2>
    <?php if ($loginError): ?>
      <div class="message"><?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required autofocus>
      
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
      
      <div class="checkbox-container">
        <input type="checkbox" id="show_password">
        <label for="show_password">Show Password</label>
      </div>
      
      <button type="submit">Login</button>
    </form>
  </div>

  <script>
    const pwdInput = document.getElementById('password');
    const toggle   = document.getElementById('show_password');
    toggle.addEventListener('change', () => {
      pwdInput.type = toggle.checked ? 'text' : 'password';
    });
  </script>
</body>
</html>
