<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Administrator') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$sql = "
  SELECT 
    a.appointment_id,
    p.name         AS patient_name,
    COALESCE(doc.name, '—') AS doctor_name,
    a.scheduled_date,
    a.scheduled_time,
    a.status
  FROM Appointment a
  JOIN Patient p ON a.patient_id = p.patient_id
  LEFT JOIN `User` doc ON a.doctor_id = doc.user_id
  ORDER BY a.created_at DESC
  LIMIT 4
";
$recent = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .recent-appointments-list { list-style:none; padding:0; margin:0; }
    .recent-appointments-list li {
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:12px 0;
      border-bottom:1px solid #eee;
    }
    .recent-appointments-list li:last-child { border-bottom:none; }
    .details p { margin:0; font-weight:500; }
    .details small { color:#666; }
    .badge {
      padding:4px 8px;
      border-radius:4px;
      font-size:0.8rem;
      text-transform:capitalize;
    }
    .badge.scheduled { background:#3498db; color:#fff; }
    .badge.completed { background:#2ecc71; color:#fff; }
    .badge.cancelled { background:#e74c3c; color:#fff; }
    .badge.missed    { background:#95a5a6; color:#fff; }
  </style>
</head>
<body class="dashboard-container">

  <aside class="sidebar">
    <div class="logo">Clinic</div>
    <ul class="nav">
      <li class="active"><a href="admin_dashboard.php">Dashboard</a></li>
      <li><a href="manage_users.php">Manage Users</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="configure_settings.php">Configure Settings</a></li>
    </ul>
  </aside>

  <main class="main-content">
    <h1>Dashboard</h1>

    <section class="panel health-report-panel">
      <h2>Health Report</h2>
    </section>

    <section class="panel heart-rate-panel">
      <h2>Heart Rate Diagram</h2>
    </section>

    <section class="panel recent-appointments-panel">
      <h2>Recent Appointments</h2>
      <ul class="recent-appointments-list">
        <?php if ($recent && $recent->num_rows > 0): ?>
          <?php while ($row = $recent->fetch_assoc()): ?>
            <li>
              <div class="details">
                <p><?= htmlspecialchars($row['patient_name']) ?> with Dr. <?= htmlspecialchars($row['doctor_name']) ?></p>
                <small>
                  <?= date('F j, Y', strtotime($row['scheduled_date'])) ?> |
                  <?= date('g:i A', strtotime($row['scheduled_time'])) ?>
                </small>
              </div>
              <span class="badge <?= strtolower($row['status']) ?>">
                <?= htmlspecialchars($row['status']) ?>
              </span>
            </li>
          <?php endwhile; ?>
        <?php else: ?>
          <li style="text-align:center; color:#666;">No recent appointments.</li>
        <?php endif; ?>
      </ul>
    </section>
  </main>

</body>
</html>
