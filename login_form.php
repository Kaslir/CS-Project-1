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

    <?php include 'login.php'; echo $loginMessage ?? ''; ?>

    <form method="POST" action="">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required placeholder="Enter your email">

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required placeholder="Enter your password">

      <button type="submit">Login</button>
    </form>

    <div class="register-link">
      <p>Forgot Password? <a href="register.php">Click here</a></p>
    </div>
  </div>

</body>
</html>
