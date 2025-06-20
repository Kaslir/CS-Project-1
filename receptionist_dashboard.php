<?php
// receptionist_dashboard.php
require_once 'db_connect.php';
require_once 'service.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only receptionists allowed
if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Receptionist') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$today = date('Y-m-d');
$now   = date('H:i:s');

// ─── Auto‐mark missed appointments ───
$conn->query("
    UPDATE Appointment a
    LEFT JOIN Queue q ON q.appointment_id = a.appointment_id
       SET a.status = 'Missed',
           q.status = 'Missed'
     WHERE a.status = 'Scheduled'
       AND (
         a.scheduled_date < '$today'
         OR (a.scheduled_date = '$today' AND a.scheduled_time < '$now')
       )
");

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pause queue
    if (isset($_POST['pause_queue'])) {
        $conn->query("
            UPDATE Queue
               SET paused = TRUE
             WHERE DATE(created_at) = '$today'
        ");
    }
    // Resume queue
    if (isset($_POST['resume_queue'])) {
        $conn->query("
            UPDATE Queue
               SET paused = FALSE
             WHERE DATE(created_at) = '$today'
        ");
        reorderQueue();
    }
    // Cancel individual appointment
    if (isset($_POST['cancel_queue_id'])) {
        $qid = intval($_POST['cancel_queue_id']);
        // Mark queue entry cancelled
        $conn->query("
            UPDATE Queue
               SET status = 'Cancelled'
             WHERE queue_id = $qid
        ");
        // Mark appointment cancelled
        $conn->query("
            UPDATE Appointment a
            JOIN Queue q ON q.appointment_id = a.appointment_id
               SET a.status = 'Cancelled'
             WHERE q.queue_id = $qid
        ");
        reorderQueue();
    }

    header("Location: receptionist_dashboard.php");
    exit;
}

// Fetch today's live queue (exclude Cancelled & Missed)
$sql = "
  SELECT
    q.queue_id,
    q.position,
    p.name             AS patient_name,
    COALESCE(doc.name, '—') AS doctor_name,
    a.scheduled_time,
    q.status,
    q.paused
  FROM Queue q
  JOIN Appointment a ON q.appointment_id = a.appointment_id
  JOIN Patient     p ON q.patient_id     = p.patient_id
  LEFT JOIN User   doc ON a.doctor_id     = doc.user_id
  WHERE a.scheduled_date = '$today'
    AND q.status NOT IN ('Cancelled','Missed')
  ORDER BY q.position
";
$result = $conn->query($sql);

// Determine if queue is paused
$pausedRow = $conn->query("
  SELECT paused
    FROM Queue
   WHERE DATE(created_at) = '$today'
   LIMIT 1
")->fetch_assoc();
$isPaused = (bool)$pausedRow['paused'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Receptionist Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <meta http-equiv="refresh" content="30">
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .header { display: flex; justify-content: space-between; align-items: center; }
    .header a.logout-btn { text-decoration: none; color: #e74c3c; }
    .top-actions { margin: 16px 0; display: flex; gap: 12px; }
    .action-button { padding: 8px 16px; border-radius: 4px; text-decoration: none; color: #fff; }
    .action-button.red   { background: #e74c3c; }
    .action-button.green { background: #27ae60; }
    .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .header-controls { display: flex; gap: 8px; }
    .header-controls form { margin: 0; }
    .header-controls button { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
    .header-controls .pause  { background: #f39c12; color: #fff; }
    .header-controls .resume { background: #27ae60; color: #fff; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    .cancel-btn { background: #e74c3c; color: #fff; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; }
    .cancel-btn:hover { background: #c0392b; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Clinic Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="top-actions">
    <a href="register_patient.php" class="action-button red">Register Patient</a>
    <a href="checkin_search.php" class="action-button green">Search Patient</a>
  </div>

  <div class="table-header">
    <h2>Queue</h2>
    <div class="header-controls">
      <form method="POST">
        <?php if (!$isPaused): ?>
          <button name="pause_queue" class="pause">Pause Queue</button>
        <?php else: ?>
          <button name="resume_queue" class="resume">Resume Queue</button>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Position</th>
        <th>Patient Name</th>
        <th>Doctor</th>
        <th>Time</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['position'] ?></td>
            <td><?= htmlspecialchars($row['patient_name']) ?></td>
            <td><?= htmlspecialchars($row['doctor_name']) ?></td>
            <td><?= date('h:i A', strtotime($row['scheduled_time'])) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
              <?php if ($row['status'] === 'Waiting'): ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="cancel_queue_id" value="<?= $row['queue_id'] ?>">
                  <button type="submit" class="cancel-btn">Cancel</button>
                </form>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" style="text-align:center;">No patients in queue.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>