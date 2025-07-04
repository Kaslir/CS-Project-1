<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['role_name']) || !in_array($_SESSION['role_name'], ['Administrator','Clinic Operations Supervisor'], true)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$sql = "
  SELECT
    a.appointment_id,
    p.name            AS patient_name,
    a.scheduled_date,
    a.scheduled_time,
    a.category
  FROM Appointment a
  JOIN Patient     p ON a.patient_id = p.patient_id
  WHERE a.status = 'Missed'
  ORDER BY a.scheduled_date DESC, a.scheduled_time DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Missed Appointments</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .dashboard-container { display: flex; min-height: 100vh; }
    .sidebar {
      width: 220px;
      background: #2c3e50;
      color: #ecf0f1;
      padding: 20px 0;
    }
    .sidebar .logo {
      text-align: center;
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }
    .sidebar .nav { list-style: none; padding: 0; }
    .sidebar .nav li { margin: 0.5rem 0; }
    .sidebar .nav li a {
      color: #ecf0f1;
      text-decoration: none;
      padding: 0.5rem 1rem;
      display: block;
    }
    .sidebar .nav li.active a,
    .sidebar .nav li a:hover {
      background: #34495e;
    }
    .main-content {
      flex: 1;
      background: #ecf0f1;
      padding: 20px;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header h1 { margin: 0; }
    .header a.logout-btn {
      text-decoration: none;
      color: #e74c3c;
      font-weight: bold;
    }
    .panel {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background: #f0f0f0;
    }
  </style>
</head>
<body class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">Clinic</div>
    <ul class="nav">
      <li><a href="admin_dashboard.php">Dashboard</a></li>
      <li><a href="manage_users.php">Manage Users</a></li>
      <li class="active"><a href="reports.php">Reports</a></li>
      <li><a href="configure_settings.php">Configure Settings</a></li>
    </ul>
  </aside>

  <main class="main-content">
    <div class="header">
      <h1>Missed Appointments</h1>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="panel">
      <table>
        <thead>
          <tr>
            <th>Appointment ID</th>
            <th>Patient Name</th>
            <th>Scheduled Date</th>
            <th>Scheduled Time</th>
            <th>Category</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['appointment_id']) ?></td>
                <td><?= htmlspecialchars($row['patient_name']) ?></td>
                <td><?= htmlspecialchars($row['scheduled_date']) ?></td>
                <td><?= date('h:i A', strtotime($row['scheduled_time'])) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align:center;">No missed appointments found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>