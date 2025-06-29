<?php
require 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$patient_id = isset($_GET['patient_id']) 
    ? intval($_GET['patient_id']) 
    : 0;
if (!$patient_id) {
    die("Invalid patient ID.");
}

$sql = "
  SELECT 
    patient_id,
    name,
    phone_number,
    date_of_birth,
    ID_Number AS id_number
  FROM Patient
  WHERE patient_id = $patient_id
";
$result = $conn->query($sql);
if (!$result || $result->num_rows !== 1) {
    die("Patient not found.");
}

$patient = $result->fetch_assoc();

$dob      = $patient['date_of_birth'];
$dobObj   = new DateTime($dob);
$todayObj = new DateTime();
$age      = $dobObj->diff($todayObj)->y;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Profile</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .profile-box table {
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    .profile-box th,
    .profile-box td {
      text-align: left;
      padding: 8px 12px;
    }
    .profile-box th {
      width: 30%;
      background: #f0f0f0;
      font-weight: 600;
    }
    .profile-box tr:nth-child(even) td {
      background: #fafafa;
    }
    .profile-buttons {
      display: flex;
      flex-direction: row;
      gap: 8px;
    }
    .profile-btn {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      background: #3498db;
      color: #fff;
      cursor: pointer;
    }
    .profile-btn:hover {
      background: #2980b9;
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="profile-box">
    <h2>Patient Profile</h2>

    <table>
      <tr>
        <th>Name:</th>
        <td><?= htmlspecialchars($patient['name']) ?></td>
      </tr>
      <tr>
        <th>Phone Number:</th>
        <td><?= htmlspecialchars($patient['phone_number']) ?></td>
      </tr>
      <tr>
        <th>Age:</th>
        <td><?= $age ?></td>
      </tr>
      <tr>
        <th>ID Number:</th>
        <td><?= htmlspecialchars($patient['id_number']) ?></td>
      </tr>
    </table>

    <div class="profile-buttons">
      <button
        class="profile-btn"
        type="button"
        onclick="window.location.href='create_appointment.php?patient_id=<?= $patient_id ?>'">
        Create Appointment
      </button>

      <button
        class="profile-btn"
        type="button"
        onclick="window.location.href='edit_patient.php?patient_id=<?= $patient_id ?>'">
        Edit Details
      </button>

      <button
        class="profile-btn"
        type="button"
        onclick="window.location.href='appointment_history.php?patient_id=<?= $patient_id ?>'">
        View Appointment History
      </button>
    </div>
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
      <a href="about.php" style="color: #fff; text-decoration: none; margin: 0 8px;">About</a> |
      <a href="contact.php" style="color: #fff; text-decoration: none; margin: 0 8px;">Contact</a> |
      <a href="privacy.php" style="color: #fff; text-decoration: none; margin: 0 8px;">Privacy Policy</a>
    </div>
  </div>

</body>
</html>