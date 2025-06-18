<?php
require 'db_connect.php';

$patient_id = isset($_GET['patient_id']) 
    ? intval($_GET['patient_id']) 
    : 0;
if (!$patient_id) die("Invalid patient ID.");

$sql    = "SELECT * FROM Patient WHERE patient_id = $patient_id";
$result = $conn->query($sql);
if (!$result || $result->num_rows !== 1) die("Patient not found.");

$patient = $result->fetch_assoc();
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
      flex-direction: column;
      gap: 15px;
    }
    .profile-btn {
  width:20%; 
}
.profile-box .profile-buttons {
  flex-direction: row;    
  gap: 8px;               
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
        <th>Date of Birth:</th>
        <td><?= htmlspecialchars($patient['date_of_birth']) ?></td>
      </tr>
      <tr>
        <th>Gender:</th>
        <td><?= htmlspecialchars($patient['gender']) ?></td>
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

</body>
</html>
