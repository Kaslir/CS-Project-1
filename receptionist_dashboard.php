<?php
// receptionist_dashboard.php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_queue'])) {
    $conn->query("DELETE FROM Queue");
    header("Location: receptionist_dashboard.php");
    exit();
}

$today = date('Y-m-d');
$sql = "
  SELECT 
    q.position,
    p.name      AS patient_name,
    COALESCE(doc.name, '—') AS doctor_name,
    a.scheduled_time,
    q.status
  FROM Queue q
  JOIN Appointment a 
    ON q.appointment_id = a.appointment_id
  JOIN Patient p 
    ON q.patient_id = p.patient_id
  LEFT JOIN User doc 
    ON a.doctor_id = doc.user_id
  WHERE a.scheduled_date = '$today'
  ORDER BY q.position
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Receptionist Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <meta http-equiv="refresh" content="30">
  <style>
    .table-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
    }
    .header-controls {
      display: flex;
      align-items: center;
    }
    .header-controls a,
    .header-controls form {
      margin-left: 8px;
    }
    .header-controls form {
      margin: 0;
    }
    .header-controls form button {
      font: inherit;
      cursor: pointer;
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      background-color: #e74c3c;
      color: white;
    }
    .header-controls form button:hover {
      background-color: #c0392b;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Clinic Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="button-container">
    <a href="register_patient.php" class="action-button red">Register Patient</a>
    <a href="checkin_search.php" class="action-button green">Search Patient</a>
  </div>

  <div class="table-container">
    <div class="table-header">
      <h2>Live Queue (<?= htmlspecialchars($today) ?>)</h2>
      <div class="header-controls">
        <a href="manage_queue.php" class="small-button">Manage Queue</a>
        <form method="POST">
          <button type="submit" name="clear_queue">Clear Queue</button>
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
              <td>
                <span class="status <?= strtolower($row['status']) ?>">
                  <?= htmlspecialchars($row['status']) ?>
                </span>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" style="text-align:center;">No patients in queue.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
