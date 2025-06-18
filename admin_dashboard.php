<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <!-- Make sure this path actually points to your CSS file! -->
  <link rel="stylesheet" href="admin.css">
</head>
<!-- ⚠️ Note the class="dashboard-container" on the body -->
<body class="dashboard-container">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="logo">Clinic</div>
    <ul class="nav">
      <li class="active"><a href="admin_dashboard.php">Dashboard</a></li>
      <li><a href="manage_users.php">Manage Users</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="configure_settings.php">Configure Settings</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <h1>Dashboard</h1>

    <section class="panel health-report-panel">
      <h2>Health Report</h2>
      <!-- blank -->
    </section>

    <section class="panel heart-rate-panel">
      <h2>Heart Rate Diagram</h2>
      <!-- blank -->
    </section>

    <section class="panel recent-appointments-panel">
      <h2>Recent Appointments</h2>
      <ul class="recent-appointments-list">
        <li>
          <div class="avatar"><img src="avatars/emily.jpg" alt="Dr. Emily"></div>
          <div class="details">
            <p>Dr. Emily Johnson – Main Clinic</p>
            <small>April 12, 2024 | 10:00 AM – 11:00 AM</small>
          </div>
          <span class="badge completed">Completed</span>
        </li>
        <!-- …more items… -->
      </ul>
    </section>
  </main>

</body>
</html>
