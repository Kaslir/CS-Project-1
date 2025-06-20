<?php
// reports.php
require 'db_connect.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only admins or supervisors can view reports
if (!in_array($_SESSION['role_name'] ?? '', ['Administrator','Clinic Operations Supervisor'], true)) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$today      = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('-6 days'));
$month_start= date('Y-m-d', strtotime('-1 month'));

// 1) Patient registrations
$reg = $conn->query("
  SELECT 
    SUM(DATE(created_at)    = '$today')       AS today,
    SUM(DATE(created_at) BETWEEN '$week_start' AND '$today') AS week,
    SUM(DATE(created_at) BETWEEN '$month_start' AND '$today') AS month
  FROM Patient
")->fetch_assoc();

// 2) Triage assessments
$tri = $conn->query("
  SELECT 
    SUM(DATE(time)    = '$today')       AS today,
    SUM(DATE(time) BETWEEN '$week_start' AND '$today') AS week,
    SUM(DATE(time) BETWEEN '$month_start' AND '$today') AS month
  FROM Triage
")->fetch_assoc();

// 3) Average real wait time (in minutes)
$avgRow = $conn->query("
  SELECT 
    AVG(
      TIMESTAMPDIFF(
        MINUTE,
        CONCAT(scheduled_date,' ',scheduled_time),
        start_time
      )
    ) AS avg_wait
  FROM Appointment
 WHERE DATE(scheduled_date) = '$today'
   AND start_time IS NOT NULL
")->fetch_assoc();
$avg_wait = round($avgRow['avg_wait'] ?? 0, 1);

// 4) Appointment adherence (% showed up)
$adRow = $conn->query("
  SELECT 
    SUM(status = 'Missed') AS missed,
    COUNT(*)               AS total
  FROM Appointment
 WHERE scheduled_date = '$today'
")->fetch_assoc();
$adherence = $adRow['total']
           ? round((($adRow['total'] - $adRow['missed']) / $adRow['total']) * 100, 1)
           : 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports & Analytics</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { padding:20px; font-family:Arial,sans-serif; }
    section { margin-bottom:24px; }
    h2 { margin-bottom:8px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Analytics & Reports</h1>
    <a href="admin_dashboard.php">← Back to Dashboard</a>
  </div>

  <section>
    <h2>New Patient Registrations</h2>
    <p>
      Today: <?= $reg['today'] ?> |
      This Week: <?= $reg['week'] ?> |
      This Month: <?= $reg['month'] ?>
    </p>
  </section>

  <section>
    <h2>Triage Assessments</h2>
    <p>
      Today: <?= $tri['today'] ?> |
      This Week: <?= $tri['week'] ?> |
      This Month: <?= $tri['month'] ?>
    </p>
  </section>

  <section>
    <h2>Average Wait Time (Actual)</h2>
    <p><?= $avg_wait ?> minutes</p>
  </section>

  <section>
    <h2>Appointment Adherence</h2>
    <p><?= $adherence ?>% of patients showed up</p>
  </section>
</body>
</html>