<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Administrator') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$recentSql = "
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
$recent = $conn->query($recentSql);

$statusSql = "
  SELECT status, COUNT(*) AS cnt
    FROM Appointment
   GROUP BY status
";
$statusRes = $conn->query($statusSql);
$statusLabels = [];
$statusData   = [];
while ($row = $statusRes->fetch_assoc()) {
    $statusLabels[] = $row['status'];
    $statusData[]   = $row['cnt'];
}

$regSql = "
  SELECT 
    DATE(created_at) AS reg_date,
    COUNT(*)         AS cnt
  FROM Patient
  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY reg_date
  ORDER BY reg_date
";
$regRes = $conn->query($regSql);
$regDates = [];
$regCounts = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $regDates[] = $d;
    $regCounts[$d] = 0;
}
while ($row = $regRes->fetch_assoc()) {
    $regCounts[$row['reg_date']] = (int)$row['cnt'];
}
$regData = array_values($regCounts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin.css">
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
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .panel h2 { margin-top: 0; }
    .recent-appointments-list { list-style: none; padding: 0; margin: 0; }
    .recent-appointments-list li {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #eee;
    }
    .recent-appointments-list li:last-child { border-bottom: none; }
    .details p { margin: 0; font-weight: 500; }
    .details small { color: #666; }
    .badge {
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
      text-transform: capitalize;
    }
    .badge.scheduled { background: #3498db; color: #fff; }
    .badge.completed { background: #2ecc71; color: #fff; }
    .badge.cancelled { background: #e74c3c; color: #fff; }
    .badge.missed    { background: #95a5a6; color: #fff; }
    canvas { max-width: 100%; display: block; margin: 0 auto; }
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
    <div class="header">
      <h1>Dashboard</h1>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <section class="panel">
      <h2>Appointment Status Breakdown</h2>
      <canvas id="statusChart"></canvas>
    </section>

    <section class="panel">
      <h2>New Patient Registrations (Last 7 Days)</h2>
      <canvas id="regChart"></canvas>
    </section>

    <section class="panel">
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

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    new Chart(document.getElementById('statusChart'), {
      type: 'bar',
      data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{
          label: 'Appointments',
          data: <?= json_encode($statusData) ?>,
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
      }
    });

    new Chart(document.getElementById('regChart'), {
      type: 'line',
      data: {
        labels: <?= json_encode($regDates) ?>,
        datasets: [{
          label: 'Registrations',
          data: <?= json_encode($regData) ?>,
          fill: false,
          tension: 0.1
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
      }
    });
  </script>
</body>
</html>
