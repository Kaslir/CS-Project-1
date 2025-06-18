<?php
require 'db_connect.php';

$patient_id = isset($_GET['patient_id'])
    ? intval($_GET['patient_id'])
    : 0;
if (!$patient_id) {
    die("Invalid patient.");
}

$docRes = $conn->query("
  SELECT u.user_id, u.name
    FROM User u
    JOIN Role r ON u.role_id = r.role_id
   WHERE r.role_name = 'Doctor'
");
$doctors = $docRes->fetch_all(MYSQLI_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduled_date = $conn->real_escape_string($_POST['date']);
    $scheduled_time = $conn->real_escape_string($_POST['time']);
    $category       = $conn->real_escape_string($_POST['category']);
    $doctor_id      = intval($_POST['doctor_id']);
    $receptionist   = intval($_SESSION['user_id']);

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
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
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

        <label for="doctor_id">Doctor:</label>
        <select name="doctor_id" id="doctor_id" required>
          <option value="" disabled selected>Select a doctor</option>
          <?php foreach ($doctors as $doc): ?>
            <option value="<?= $doc['user_id'] ?>">
              <?= htmlspecialchars($doc['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

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
