<?php
require_once 'db_connect.php';
require_once 'service.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Triage Nurse') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$today = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id   = intval($_POST['patient_id']);
    $level        = $conn->real_escape_string($_POST['triage_level']);
    $nurse_id     = intval($_SESSION['user_id']);

    $conn->query("
      INSERT INTO Triage(patient_id, triage_level, time, nurse_id)
      VALUES($patient_id,'$level',NOW(),$nurse_id)
    ");
    reorderQueue();
    header('Location: triage_nurse.php');
    exit;
}

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
  JOIN Patient   p ON q.patient_id     = p.patient_id
  WHERE a.scheduled_date = '$today'
    AND q.status = 'Waiting'
  ORDER BY q.position
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Triage Nurse Queue</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="table-container">
    <div class="table-header">
      <h2>Live Queue (Triage)</h2>
    </div>
    <table>
      <thead>
        <tr>
          <th>Position</th>
          <th>Patient Name</th>
          <th>Scheduled Time</th>
          <th>Current Priority</th>
          <th>Set New Level</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($res && $res->num_rows > 0): ?>
          <?php while($row = $res->fetch_assoc()): ?>
            <tr>
              <td><?= $row['position'] ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= date('h:i A', strtotime($row['scheduled_time'])) ?></td>
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
        <?php else: ?>
          <tr>
            <td colspan="5" style="text-align:center;">No patients waiting for triage.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
