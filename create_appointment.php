<?php
// create_appointment.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only receptionists can assign appointments
if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Receptionist') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Get the single doctor on file
$docRes = $conn->query("
  SELECT u.user_id
    FROM `User` u
    JOIN Role r ON u.role_id = r.role_id
   WHERE r.role_name = 'Doctor'
   LIMIT 1
");
if (!$docRes || $docRes->num_rows !== 1) {
    die("No doctor configured in the system.");
}
$doctor = $docRes->fetch_assoc();
$doctor_id = intval($doctor['user_id']);

// Get patient_id from query string
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
if (!$patient_id) {
    die("Invalid patient.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduled_date = $conn->real_escape_string($_POST['date']);
    $scheduled_time = $conn->real_escape_string($_POST['time']);
    $category       = $conn->real_escape_string(trim($_POST['category']));
    $receptionist   = intval($_SESSION['user_id']);

    // Insert appointment
    $sql = "
      INSERT INTO Appointment
        (patient_id, scheduled_date, scheduled_time, buffer_time, category, status, doctor_id)
      VALUES
        ($patient_id, '$scheduled_date', '$scheduled_time', 5, '$category', 'Scheduled', $doctor_id)
    ";
    if (!$conn->query($sql)) {
        $error = $conn->error;
    } else {
        $appointment_id = $conn->insert_id;

        // Compute next queue position for today
        $today  = date('Y-m-d');
        $posRes = $conn->query("
          SELECT COALESCE(MAX(position),0) + 1 AS next_pos
            FROM Queue q
            JOIN Appointment a ON q.appointment_id = a.appointment_id
           WHERE a.scheduled_date = '$today'
        ");
        $position = intval($posRes->fetch_assoc()['next_pos']);

        // Insert into queue
        $sql2 = "
          INSERT INTO Queue
            (appointment_id, patient_id, status, position, paused, managed_by)
          VALUES
            ($appointment_id, $patient_id, 'Waiting', $position, FALSE, $receptionist)
        ";
        if (!$conn->query($sql2)) {
            $error = $conn->error;
        } else {
            header("Location: receptionist_dashboard.php");
            exit();
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
  <!-- full-width header, outside of the centering wrapper -->
  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <!-- only this wrapper is flex-centered -->
  <div class="login-page">
    <div class="login-box">
      <h2>Assign Appointment Details</h2>

      <?php if ($error): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" action="">
        <label for="date">Date:</label>
        <input 
          type="date" 
          id="date" 
          name="date" 
          required 
          min="<?= date('Y-m-d') ?>" 
        />

        <label for="time">Time:</label>
        <input 
          type="time" 
          id="time" 
          name="time" 
          required 
        />

        <label for="category">Ailment Category:</label>
        <input 
          type="text" 
          id="category" 
          name="category" 
          required 
        />

        <button type="submit">Submit</button>
      </form>
    </div>
  </div>

  <script>
    const dateInput = document.getElementById('date');
    const timeInput = document.getElementById('time');

    function updateTimeMin() {
      const now = new Date();
      const todayStr = now.toISOString().split('T')[0];
      if (dateInput.value === todayStr) {
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        timeInput.min = `${hh}:${mm}`;
      } else {
        timeInput.min = '00:00';
      }
    }

    dateInput.addEventListener('change', updateTimeMin);
    // initialize on load
    dateInput.value = dateInput.value || new Date().toISOString().split('T')[0];
    updateTimeMin();
  </script>
</body>
</html>
