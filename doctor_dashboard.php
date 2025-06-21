<?php
require 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Doctor') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$errorMsg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['error_msg']);

$inProgCount = intval($conn->query("
  SELECT COUNT(*) AS cnt
    FROM Queue
   WHERE status = 'In Progress'
")->fetch_assoc()['cnt']);

$today = date('Y-m-d');
$sql = "
  SELECT
    q.queue_id,
    p.name            AS patient_name,
    a.scheduled_time,
    q.created_at      AS queued_at,
    q.status
  FROM Queue q
  JOIN Appointment a ON q.appointment_id = a.appointment_id
  JOIN Patient     p ON q.patient_id     = p.patient_id
  WHERE a.scheduled_date = '$today'
    AND q.status IN ('Waiting','In Progress')
  ORDER BY q.position
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Doctor Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .header { display:flex; justify-content:space-between; align-items:center; }
    .logout-btn { text-decoration:none; color:#e74c3c; }
    .error { margin:12px 0; padding:8px; background:#fdecea; color:#e74c3c; border-radius:4px; }
    table { width:100%; border-collapse:collapse; margin-top:16px; font-family:Arial,sans-serif; }
    th, td { padding:8px; border:1px solid #ddd; text-align:left; }
    .small-button { padding:4px 8px; border:none; border-radius:4px; background:#3498db; color:#fff; cursor:pointer; }
    .small-button:disabled { background:#aaa; cursor:not-allowed; }
    .small-button:hover:not(:disabled) { background:#2980b9; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Clinic – Doctor Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <?php if ($errorMsg): ?>
    <div class="error"><?= htmlspecialchars($errorMsg) ?></div>
  <?php endif; ?>

  <h2>Queue</h2>

  <table>
    <thead>
      <tr>
        <th>Patient</th>
        <th>Scheduled Time</th>
        <th>Wait (min)</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            $queuedTs = strtotime($row['queued_at']);
            if ($row['status'] === 'In Progress') {
                $endTs = strtotime($row['queued_at']); 
            } elseif ($row['status'] === 'Waiting') {
                $endTs = time();
            }
            $waitMins = max(0, round(($endTs - $queuedTs) / 60));
          ?>
          <tr>
            <td><?= htmlspecialchars($row['patient_name']) ?></td>
            <td><?= date('h:i A', strtotime($row['scheduled_time'])) ?></td>
            <td><?= $waitMins ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
              <?php if ($row['status'] === 'Waiting'): ?>
                <form method="POST" action="doctor_action.php" style="display:inline">
                  <input type="hidden" name="queue_id" value="<?= $row['queue_id'] ?>">
                  <input type="hidden" name="action"   value="start">
                  <button
                    type="submit"
                    class="small-button"
                    <?= $inProgCount > 0 ? 'disabled' : '' ?>>
                    Start Consultation
                  </button>
                </form>
              <?php elseif ($row['status'] === 'In Progress'): ?>
                <form method="POST" action="doctor_action.php" style="display:inline">
                  <input type="hidden" name="queue_id" value="<?= $row['queue_id'] ?>">
                  <input type="hidden" name="action"   value="end">
                  <button type="submit" class="small-button">End Consultation</button>
                </form>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" style="text-align:center;">No patients waiting.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
