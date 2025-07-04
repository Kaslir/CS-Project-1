<?php
// login_form.php
session_start();
$loginMessage = $_SESSION['loginMessage'] ?? '';
unset($_SESSION['loginMessage']);
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
    <?php if ($loginMessage): ?>
      <div class="message"><?= htmlspecialchars($loginMessage) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
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
    const toggle = document.getElementById('show_password');
    toggle.addEventListener('change', () => {
      pwdInput.type = toggle.checked ? 'text' : 'password';
    });
  </script>
</body>
</html>