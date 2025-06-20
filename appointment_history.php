<?php
// appointment_history.php
require_once 'db_connect.php';

$pid = intval($_GET['patient_id'] ?? 0);
if (!$pid) die("Invalid ID");

$res = $conn->query("
  SELECT scheduled_date, scheduled_time, category, status
  FROM Appointment
  WHERE patient_id = $pid
  ORDER BY scheduled_date DESC, scheduled_time DESC
");
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>History</title><link rel="stylesheet" href="style.css"></head>
<body>
  <div class="header">
    <h1>Appointment History</h1>
    <a href="patient_profile.php?patient_id=<?= $pid ?>">← Back</a>
  </div>
  <table>
    <thead>
      <tr><th>Date</th><th>Time</th><th>Category</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php if ($res->num_rows): while($r = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $r['scheduled_date'] ?></td>
        <td><?= date('h:i A',strtotime($r['scheduled_time'])) ?></td>
        <td><?= htmlspecialchars($r['category']) ?></td>
        <td><?= htmlspecialchars($r['status']) ?></td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="4" style="text-align:center">No history.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
