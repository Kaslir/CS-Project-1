<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Administrator') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}


if (isset($_POST['buffer_submit'])) {
    $buffer = intval($_POST['buffer_duration']);
    $maxDaily = intval($_POST['max_daily']);
    $conn->query("
      INSERT INTO appointment_settings (id, buffer_duration, max_daily_appointments)
      VALUES (1, $buffer, $maxDaily)
      ON DUPLICATE KEY UPDATE
        buffer_duration = $buffer,
        max_daily_appointments = $maxDaily
    ");
}

if (isset($_POST['hours_submit']) && isset($_POST['open']) && isset($_POST['close'])) {
    foreach ($_POST['open'] as $day => $openTime) {
        $closeTime = $conn->real_escape_string($_POST['close'][$day]);
        $d = $conn->real_escape_string($day);
        $conn->query("
          INSERT INTO clinic_hours (day_of_week, open_time, close_time)
          VALUES ('$d', '$openTime', '$closeTime')
          ON DUPLICATE KEY UPDATE
            open_time = '$openTime',
            close_time = '$closeTime'
        ");
    }
}

if (isset($_POST['add_holiday'])) {
    $hdate = $conn->real_escape_string($_POST['holiday_date']);
    $desc  = $conn->real_escape_string($_POST['holiday_desc']);
    if ($hdate) {
        $conn->query("
          INSERT IGNORE INTO holidays (holiday_date, description)
          VALUES ('$hdate', '$desc')
        ");
    }
}

if (isset($_POST['delete_holiday'])) {
    $hdate = $conn->real_escape_string($_POST['del_date']);
    $conn->query("
      DELETE FROM holidays
       WHERE holiday_date = '$hdate'
    ");
}


$setRes = $conn->query("SELECT buffer_duration, max_daily_appointments FROM appointment_settings WHERE id = 1");
$settings = $setRes && $setRes->num_rows ? $setRes->fetch_assoc() : ['buffer_duration'=>5,'max_daily_appointments'=>20];

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$hours = [];
$hrRes = $conn->query("SELECT * FROM clinic_hours");
while ($row = $hrRes->fetch_assoc()) {
    $hours[$row['day_of_week']] = $row;
}

$holidays = [];
$holRes = $conn->query("SELECT * FROM holidays ORDER BY holiday_date");
while ($row = $holRes->fetch_assoc()) {
    $holidays[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Configure Settings</title>
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
    label { display: block; margin: 10px 0 5px; }
    input[type="number"], select, input[type="time"], input[type="date"], input[type="text"] {
      padding: 6px; width: 200px;
    }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    button { margin-top: 10px; padding: 8px 16px; background: #3498db; border: none; color: #fff; border-radius: 4px; cursor: pointer; }
    button:hover { background: #2980b9; }
    .holiday-form { margin-top: 10px; }
    .del-btn { background: #e74c3c; }
    .del-btn:hover { background: #c0392b; }
  </style>
</head>
<body class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">Clinic</div>
    <ul class="nav">
      <li><a href="admin_dashboard.php">Dashboard</a></li>
      <li><a href="manage_users.php">Manage Users</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li class="active"><a href="configure_settings.php">Configure Settings</a></li>
    </ul>
  </aside>

  <main class="main-content">
    <div class="header">
      <h1>Configure Settings</h1>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <section class="panel">
      <h2>Appointment Settings</h2>
      <form method="POST">
        <label for="buffer_duration">Buffer Duration (min):</label>
        <select id="buffer_duration" name="buffer_duration">
          <?php foreach ([5,10,15] as $b): ?>
            <option value="<?= $b ?>" <?= $settings['buffer_duration']==$b?'selected':'' ?>>
              <?= $b ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label for="max_daily">Max Daily Appointments:</label>
        <input type="number" id="max_daily" name="max_daily"
               value="<?= htmlspecialchars($settings['max_daily_appointments']) ?>" min="1" />

        <button type="submit" name="buffer_submit">Save</button>
      </form>
    </section>

    <section class="panel">
      <h2>Clinic Hours</h2>
      <form method="POST">
        <table>
          <thead>
            <tr><th>Day</th><th>Open</th><th>Close</th></tr>
          </thead>
          <tbody>
            <?php foreach ($days as $day): 
              $o = $hours[$day]['open_time']   ?? '08:00';
              $c = $hours[$day]['close_time']  ?? '17:00';
            ?>
              <tr>
                <td><?= $day ?></td>
                <td><input type="time" name="open[<?= $day ?>]" value="<?= $o ?>"></td>
                <td><input type="time" name="close[<?= $day ?>]" value="<?= $c ?>"></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <button type="submit" name="hours_submit">Save Hours</button>
      </form>
    </section>

    <section class="panel">
      <h2>Holiday &amp; Black-Out Dates</h2>
      <table>
        <thead>
          <tr><th>Date</th><th>Description</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php if ($holidays): ?>
            <?php foreach ($holidays as $h): ?>
              <tr>
                <td><?= htmlspecialchars($h['holiday_date']) ?></td>
                <td><?= htmlspecialchars($h['description']) ?></td>
                <td>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="del_date" value="<?= $h['holiday_date'] ?>">
                    <button type="submit" name="delete_holiday" class="del-btn">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="3" style="text-align:center;">No holidays defined.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <form method="POST" class="holiday-form">
        <label for="holiday_date">Add Holiday:</label>
        <input type="date" id="holiday_date" name="holiday_date" required>
        <input type="text" name="holiday_desc" placeholder="Description" required>
        <button type="submit" name="add_holiday">Add</button>
      </form>
    </section>

  </main>
</body>
</html>
