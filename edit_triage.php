<?php
// edit_triage.php
require_once 'db_connect.php';
require_once 'service.php';

// only triage nurses
if ($_SESSION['role_name'] !== 'Triage Nurse') {
    header('HTTP/1.1 403 Forbidden'); exit;
}

$today = date('Y-m-d');

// handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id   = intval($_POST['patient_id']);
    $level        = $conn->real_escape_string($_POST['triage_level']);
    $nurse_id     = intval($_SESSION['user_id']);
    // record a new triage assessment
    $conn->query("
      INSERT INTO Triage(patient_id, triage_level, time, nurse_id)
      VALUES($patient_id,'$level',NOW(),$nurse_id)
    ");
    reorderQueue();
    header('Location: triage_nurse.php');
    exit;
}

// fetch today's live queue
$res = $conn->query("
  SELECT 
    q.queue_id,
    q.position,
    p.patient_id,
    p.name,
    a.scheduled_time,
    COALESCE(
      (SELECT triage_level 
         FROM Triage t 
        WHERE t.patient_id = q.patient_id 
          AND DATE(t.time)= '$today'
        ORDER BY t.time DESC 
        LIMIT 1),
      'Normal'
    ) AS current_level
  FROM Queue q
  JOIN Appointment a ON q.appointment_id = a.appointment_id
  JOIN Patient p     ON q.patient_id     = p.patient_id
  WHERE a.scheduled_date = '$today'
  ORDER BY q.position
");
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Triage Nurse</title><link rel="stylesheet" href="style.css"></head>
<body>
  <div class="header">
    <h1>Clinic – Triage</h1>
    <a href="logout.php">Logout</a>
  </div>

  <table>
    <thead>
      <tr><th>Pos</th><th>Patient</th><th>Time</th><th>Current Priority</th><th>Set New Level</th></tr>
    </thead>
    <tbody>
      <?php while($row = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $row['position'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= date('h:i A',strtotime($row['scheduled_time'])) ?></td>
        <td><?= htmlspecialchars($row['current_level']) ?></td>
        <td>
          <form method="POST" style="margin:0">
            <input type="hidden" name="patient_id" value="<?= $row['patient_id'] ?>">
            <select name="triage_level" required>
              <option value="" disabled selected>--</option>
              <option value="Normal">Normal</option>
              <option value="High">High</option>
              <option value="Emergency">Emergency</option>
            </select>
            <button type="submit">Update</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
