<?php
// triage_nurse.php
// (Add your session/login checks here)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Triage Nurse Queue</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Full-width header identical to receptionist/doctor -->
  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <!-- Live Queue Table -->
  <div class="table-container">
    <div class="table-header">
      <h2>Live Queue</h2>
    </div>
    <table>
      <thead>
        <tr>
          <th>Patient Name</th>
          <th>Scheduled Time</th>
          <th>Triage Level</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Shubham Kumar</td>
          <td>09:00 AM</td>
          <td>High</td>
          <td>
            <a href="edit_triage.php?patient_id=1" class="small-button">
              Edit Triage Level
            </a>
          </td>
        </tr>
        <tr>
          <td>Ram Kumar</td>
          <td>09:30 AM</td>
          <td>Medium</td>
          <td>
            <a href="edit_triage.php?patient_id=2" class="small-button">
              Edit Triage Level
            </a>
          </td>
        </tr>
        <tr>
          <td>Mona Kumari</td>
          <td>10:00 AM</td>
          <td>Low</td>
          <td>
            <a href="edit_triage.php?patient_id=3" class="small-button">
              Edit Triage Level
            </a>
          </td>
        </tr>
        <!-- …more rows… -->
      </tbody>
    </table>
  </div>
</body>
</html>
