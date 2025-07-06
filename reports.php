<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['role_name']) || !in_array($_SESSION['role_name'], ['Administrator','Clinic Operations Supervisor'], true)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$today       = date('Y-m-d');
$week_start  = date('Y-m-d', strtotime('-6 days'));
$month_start = date('Y-m-d', strtotime('-1 month'));

$reg = $conn->query("
    SELECT 
      SUM(DATE(created_at) = '$today')                             AS today,
      SUM(DATE(created_at) BETWEEN '$week_start' AND '$today')     AS week,
      SUM(DATE(created_at) BETWEEN '$month_start' AND '$today')    AS month
    FROM Patient
")->fetch_assoc();

$tri = $conn->query("
    SELECT 
      SUM(DATE(time) = '$today')                                  AS today,
      SUM(DATE(time) BETWEEN '$week_start' AND '$today')          AS week,
      SUM(DATE(time) BETWEEN '$month_start' AND '$today')         AS month
    FROM Triage
")->fetch_assoc();

$avgRow = $conn->query("
    SELECT 
      AVG(
        GREATEST(
          TIMESTAMPDIFF(
            MINUTE,
            CONCAT(scheduled_date,' ',scheduled_time),
            start_time
          ),
          0
        )
      ) AS avg_wait
    FROM Appointment
   WHERE start_time IS NOT NULL
")->fetch_assoc();
$avg_wait = round($avgRow['avg_wait'] ?? 0, 1);

$adRow = $conn->query("
    SELECT 
      SUM(status = 'Missed') AS missed,
      COUNT(*)               AS total
    FROM Appointment
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
    .panel h2 { margin-top: 0; }
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
    th { background: #f0f0f0; }
        th {
      background: #3498db;   
      color: #fff;           
    }
    .btn {
      margin-top: 16px;
      padding: 10px 20px;
      background: #3498db;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1rem;
    }
    .btn:hover {
      background: #2980b9;
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
      <h1>Reports & Analytics</h1>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="panel">
      <h2>Appointment Metrics</h2>
      <table>
        <thead>
          <tr><th>Report</th><th>Value</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>New Patient Registrations</td>
            <td>
              Today: <?= $reg['today'] ?>,
              This Week: <?= $reg['week'] ?>,
              This Month: <?= $reg['month'] ?>
            </td>
          </tr>
          <tr>
            <td>Triage Assessments</td>
            <td>
              Today: <?= $tri['today'] ?>,
              This Week: <?= $tri['week'] ?>,
              This Month: <?= $tri['month'] ?>
            </td>
          </tr>
          <tr>
            <td>Average Wait Time</td>
            <td><?= $avg_wait ?> minutes</td>
          </tr>
          <tr>
            <td>Appointment Adherence</td>
            <td><?= $adherence ?>% showed up</td>
          </tr>
        </tbody>
      </table>

      <button class="btn" onclick="window.location.href='missed_appointments.php'">
        View Missed Appointments
      </button>
    </div>

  </main>
</body>
</html>
