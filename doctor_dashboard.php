<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Doctor') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

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
  <title>Clinic – Doctor Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #ecf0f1;
    }
    .header {
      background: #3498db;
      color: #fff;
      padding: 16px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header h1 {
      margin: 0;
      font-size: 1.5rem;
    }
    .logout-btn {
      color: #fff;
      text-decoration: none;
      font-weight: bold;
    }
    .content {
      padding: 20px 24px;
    }
    .panel {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
    }
    th, td {
      padding: 8px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background: #3498db;
      color: #fff;
    }
    .small-button {
      padding: 4px 8px;
      border: none;
      border-radius: 4px;
      background: #3498db;
      color: #fff;
      cursor: pointer;
    }
    .small-button:disabled {
      background: #aaa;
      cursor: not-allowed;
    }
    .small-button:hover:not(:disabled) {
      background: #2980b9;
    }
  </style>
</head>
<body>

  <header class="header">
    <h1>Clinic – Doctor Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </header>

  <main class="content">
    <section class="panel">
      <h2>Queue</h2>
      <table>
        <thead>
          <tr>
            <th>Patient</th>
            <th>Scheduled Time</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['patient_name']) ?></td>
                <td><?= date('h:i A', strtotime($row['scheduled_time'])) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                  <?php if ($row['status'] === 'Waiting'): ?>
                    <form method="POST" action="doctor_action.php" style="display:inline">
                      <input type="hidden" name="queue_id" value="<?= $row['queue_id'] ?>">
                      <input type="hidden" name="action" value="start">
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
                      <input type="hidden" name="action" value="end">
                      <button type="submit" class="small-button">End Consultation</button>
                    </form>
                  <?php else: ?>
                    &mdash;
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" style="text-align:center;">No patients waiting.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>

  <div style="
      background: #3498db;
      color: #fff;
      padding: 16px 24px;
      text-align: center;
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      box-shadow: 0 -2px 6px rgba(0,0,0,0.1);
      z-index: 1000;
    ">
    &copy; <?= date('Y') ?> Clinic Operations System. All rights reserved.
    <div style="margin-top: 8px;">
      <a href="about.php" style="color: #fff; text-decoration: none; margin: 0 8px;">About</a> |
      <a href="contact.php" style="color: #fff; text-decoration: none; margin: 0 8px;">Contact</a> |
      <a href="privacy.php" style="color: #fff; text-decoration: none; margin: 0 8px;">Privacy Policy</a>
    </div>
  </div>

</body>
</html>