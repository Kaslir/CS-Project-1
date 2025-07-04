<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Triage Nurse Queue</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

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
      </tbody>
    </table>
  </div>
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
    </div>
  </div>
</body>
</html>
