<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Appointment Details</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- full-width header, outside of the centering wrapper -->
  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <!-- only this wrapper is flex-centered -->
  <div class="login-page">
    <div class="login-box">
      <h2>Appointment Details</h2>

      <?php
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $date     = $_POST['date'] ?? '';
          $time     = $_POST['time'] ?? '';
          $category = trim($_POST['category'] ?? '');

          echo "<p style='color:green; text-align:center;'>
                  Appointment set for <strong>$date</strong> at <strong>$time</strong><br>
                  Ailment Category: <strong>$category</strong>
                </p>";
      }
      ?>

      <form action="appointment_details.php" method="POST">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="time">Time:</label>
        <input type="time" id="time" name="time" required>

        <label for="category">Ailment Category:</label>
        <input type="text" id="category" name="category" placeholder="Enter category" required>

        <button type="submit">Submit</button>
      </form>
    </div>
  </div>
</body>
</html>
