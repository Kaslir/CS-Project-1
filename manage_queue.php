<?php
// manage_queue.php
require_once 'db_connect.php';
require_once 'service.php';

// only receptionists
if ($_SESSION['role_name'] !== 'Receptionist') {
  header('HTTP/1.1 403 Forbidden'); exit;
}

$today = date('Y-m-d');

// pause/resume handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['pause'])) {
    $conn->query("UPDATE Queue SET paused = TRUE WHERE DATE(created_at) = '$today'");
  }
  if (isset($_POST['resume'])) {
    $conn->query("UPDATE Queue SET paused = FALSE WHERE DATE(created_at) = '$today'");
    reorderQueue();
  }
  header('Location: manage_queue.php');
  exit;
}

// fetch queue
$res = $conn->query("
  SELECT q.position,
         p.name,
         a.scheduled_time,
         q.status,
         q.paused
    FROM Queue q
    JOIN Appointment a ON q.appointment_id = a.appointment_id
    JOIN Patient p     ON q.patient_id     = p.patient_id
   WHERE a.scheduled_date = '$today'
   ORDER BY q.position
");
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Manage Queue</title><link rel="stylesheet" href="style.css"></head>
<body>
  <div class="header">
    <h1>Manage Queue</h1>
    <a href="receptionist_dashboard.php">← Back</a>
  </div>

  <form method="POST">
    <button name="pause"  <?= $res->fetch_assoc()['paused'] ? 'disabled' : '' ?>>Pause Queue</button>
    <button name="resume" <?= $res->fetch_assoc()['paused'] ? '' : 'disabled' ?>>Resume Queue</button>
  </form>

  <table>
    <thead>
      <tr><th>Pos</th><th>Patient</th><th>Time</th><th>Status</th><th>Paused?</th></tr>
    </thead>
    <tbody>
      <?php 
      mysqli_data_seek($res, 0);
      while($r = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $r['position'] ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= date('h:i A',strtotime($r['scheduled_time'])) ?></td>
        <td><?= htmlspecialchars($r['status']) ?></td>
        <td><?= $r['paused'] ? 'Yes' : 'No' ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
