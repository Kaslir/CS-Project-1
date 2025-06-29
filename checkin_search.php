<?php
// checkin_search.php
require 'db_connect.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $conn->real_escape_string($_POST['phone']);
    $res = $conn->query("
      SELECT patient_id 
      FROM Patient 
      WHERE phone_number = '$phone' 
      LIMIT 1
    ");
    if ($res && $res->num_rows === 1) {
        $patient = $res->fetch_assoc();
        header("Location: patient_profile.php?patient_id=" . $patient['patient_id']);
        exit();
    } else {
        $error = "Patient not Found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Patient</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Sticky Header -->
  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <!-- Centered Search Form -->
  <div class="login-page">
    <div class="login-box">
      <h2>Search Patient</h2>

      <?php if ($error): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" action="">
        <label for="phone">Phone Number:</label>
        <input 
          type="tel" 
          id="phone" 
          name="phone" 
          placeholder="Enter phone number" 
          required 
        />
        <button type="submit">Search</button>
      </form>
    </div>
  </div>
  <div style="
      background: #3498db;
      color: #fff;
      padding: 16px 24px;
      text-align: center;
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      box-shadow: 0 -2px 6px rgba(0,0,0,0.1);
      z-index: 1000;
    ">
    &copy; <?= date('Y') ?> Clinic Operations System. All rights reserved.
    <div style="margin-top: 8px;">
      <a href="about.php" style="color: #fff; text-decoration: none; margin: 0 8px;">About</a> |
      <a href="contact.php" style="color: #fff; text-decoration: none; margin: 0 8px;">Contact</a> |
      <a href="privacy.php" style="color: #fff; text-decoration: none; margin: 0 8px;">Privacy Policy</a>
    </div>
  </div>

</body>
</html>
