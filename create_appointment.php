<?php
require 'db_connect.php';  

$patient_id = isset($_GET['patient_id'])
    ? intval($_GET['patient_id'])
    : 0;
if (!$patient_id) {
    die("Invalid patient.");
}

$docRes = $conn->query("
  SELECT u.user_id
    FROM `User` u
    JOIN Role r ON u.role_id = r.role_id
   WHERE r.role_name = 'Doctor'
   LIMIT 1
");
if (!$docRes || $docRes->num_rows !== 1) {
    die("No doctor available.");
}
$onlyDoctor = $docRes->fetch_assoc()['user_id'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduled_date = $conn->real_escape_string($_POST['date']);
    $scheduled_time = $conn->real_escape_string($_POST['time']);
    $category       = $conn->real_escape_string($_POST['category']);
    $doctor_id      = $onlyDoctor;
    $receptionist   = intval($_SESSION['user_id']);

    $dayName = date('l', strtotime($scheduled_date)); 
    $hStmt = $conn->prepare("
      SELECT open_time, close_time
        FROM clinic_hours
       WHERE day_of_week = ?
    ");
    $hStmt->bind_param("s", $dayName);
    $hStmt->execute();
    $hStmt->bind_result($open_time, $close_time);
    if ($hStmt->fetch()) {
        if ($scheduled_time < $open_time || $scheduled_time > $close_time) {
            $error = "Appointments only between {$open_time} and {$close_time}.";
        }
    } else {
        $error = "Clinic is closed on {$dayName}.";
    }
    $hStmt->close();

    if (!$error) {
        $sStmt = $conn->prepare("
          SELECT max_daily_appointments
            FROM appointment_settings
           WHERE id = 1
        ");
        $sStmt->execute();
        $sStmt->bind_result($max_daily);
        $sStmt->fetch();
        $sStmt->close();

        $cStmt = $conn->prepare("
          SELECT COUNT(*) 
            FROM Appointment 
           WHERE scheduled_date = ?
        ");
        $cStmt->bind_param("s", $scheduled_date);
        $cStmt->execute();
        $cStmt->bind_result($count_today);
        $cStmt->fetch();
        $cStmt->close();

        if ($count_today >= $max_daily) {
            $error = "Daily limit of {$max_daily} appointments reached for {$scheduled_date}.";
        }
    }

    if (!$error) {
        $sql = "INSERT INTO Appointment 
                (patient_id, scheduled_date, scheduled_time, buffer_time, category, status, doctor_id)
                VALUES
                ($patient_id, '$scheduled_date', '$scheduled_time', 5, '$category', 'Scheduled', $doctor_id)";
        if (!$conn->query($sql)) {
            $error = $conn->error;
        } else {
            $appointment_id = $conn->insert_id;

            $today = date('Y-m-d');
            $posRes = $conn->query("
              SELECT COALESCE(MAX(position),0) + 1 AS next_pos
                FROM Queue q
                JOIN Appointment a ON q.appointment_id = a.appointment_id
               WHERE a.scheduled_date = '$today'
            ");
            $position = intval($posRes->fetch_assoc()['next_pos']);

            $sql2 = "INSERT INTO Queue
                     (appointment_id, patient_id, status, position, paused, managed_by)
                     VALUES
                     ($appointment_id, $patient_id, 'Waiting', $position, FALSE, $receptionist)";
            if (!$conn->query($sql2)) {
                $error = $conn->error;
            } else {
                header("Location: receptionist_dashboard.php");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Appointment Details</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="login-page">
    <div class="login-box">
      <h2>Assign Appointment Details</h2>

      <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" action="">
        <label for="date">Date:</label>
        <input 
          type="date" 
          id="date" 
          name="date" 
          required 
          min="<?= date('Y-m-d') ?>" 
          value="<?= htmlspecialchars($_POST['date'] ?? '') ?>"
        />

        <label for="time">Time:</label>
        <input 
          type="time" 
          id="time" 
          name="time" 
          required 
          value="<?= htmlspecialchars($_POST['time'] ?? '') ?>"
        />

        <label for="category">Ailment Category:</label>
        <input 
          type="text" 
          id="category" 
          name="category" 
          required 
          value="<?= htmlspecialchars($_POST['category'] ?? '') ?>"
        />

        <input type="hidden" name="doctor_id" value="<?= $onlyDoctor ?>">

        <button type="submit">Submit</button>
      </form>

      <script>
        const dateInput = document.getElementById('date');
        const timeInput = document.getElementById('time');
        function updateTimeMin() {
          const now = new Date();
          const todayStr = now.toISOString().split('T')[0];
          if (dateInput.value === todayStr) {
            const hh = String(now.getHours()).padStart(2,'0');
            const mm = String(now.getMinutes()).padStart(2,'0');
            timeInput.min = `${hh}:${mm}`;
          } else {
            timeInput.min = '00:00';
          }
        }
        dateInput.addEventListener('change', updateTimeMin);
        dateInput.value = dateInput.value || new Date().toISOString().split('T')[0];
        updateTimeMin();
      </script>
    </div>
  </div>
</body>
</html>
