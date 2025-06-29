<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['role_name'])) {
    switch ($_SESSION['role_name']) {
        case 'Receptionist':
            header('Location: receptionist_dashboard.php'); break;
        case 'Administrator':
            header('Location: admin_dashboard.php'); break;
        case 'Triage Nurse':
            header('Location: triage_nurse.php'); break;
        case 'Doctor':
            header('Location: doctor_dashboard.php'); break;
        default:
            session_unset();
            session_destroy();
            header('Location: login.php'); break;
    }
    exit;
}

require 'db_connect.php';

$loginError = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $sql = "
      SELECT u.user_id,
             u.name,
             u.password,
             r.role_name
        FROM User u
   LEFT JOIN Role   r ON u.role_id = r.role_id
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
                    header("Location: triage_nurse.php"); break;
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
  <title>Clinic Login</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body.login-page {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f5f5f5;
    }
    .login-box {
      background: #fff;
      padding: 24px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      width: 320px;
    }
    .login-box h2 {
      margin-top: 0;
      text-align: center;
    }
    .login-box label {
      display: block;
      margin-top: 12px;
      font-size: 0.9rem;
    }
    .login-box input {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 1rem;
    }
    .login-box button {
      width: 100%;
      padding: 10px;
      margin-top: 20px;
      border: none;
      border-radius: 4px;
      background: #007bff;
      color: #fff;
      font-size: 1rem;
      cursor: pointer;
    }
    .login-box button:hover {
      background: #0056b3;
    }
    .login-box .error {
      color: #e74c3c;
      text-align: center;
      margin-top: 12px;
    }
  </style>
</head>
<body class="login-page">
  <div class="login-box">
    <h2>Login</h2>

    <?php if ($loginError): ?>
      <div class="error"><?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <label for="email">Email:</label>
      <input
        type="email"
        id="email"
        name="email"
        placeholder="Enter your email"
        required
      />

      <label for="password">Password:</label>
      <input
        type="password"
        id="password"
        name="password"
        placeholder="Enter your password"
        required
      />

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>