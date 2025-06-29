<?php
// register_patient.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = $conn->real_escape_string($_POST['name']);
    $phone     = $conn->real_escape_string($_POST['phone']);
    $dob       = $conn->real_escape_string($_POST['dob']);
    $id_number = $conn->real_escape_string($_POST['id_number']);

    $sql = "INSERT INTO Patient 
              (name, phone_number, date_of_birth, ID_Number)
            VALUES
              ('$name', '$phone', '$dob', '$id_number')";

    if ($conn->query($sql)) {
        $patient_id = $conn->insert_id;
        header("Location: patient_profile.php?patient_id={$patient_id}");
        exit;
    } else {
        $error = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Patient</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="login-page">
    <div class="login-box">
      <h2>Register Patient</h2>

      <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" action="">
        <label for="name">Full Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="phone">Phone Number:</label>
        <input type="tel" name="phone" id="phone" required>

        <label for="dob">Date of Birth:</label>
        <input type="date" name="dob" id="dob" required>

        <label for="id_number">ID Number:</label>
        <input type="text" name="id_number" id="id_number" required>

        <button type="submit">Register Patient</button>
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
