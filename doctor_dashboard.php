<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Doctor Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Header -->
  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <!-- Live Queue Section -->
  <div class="table-container">
    <div class="table-header">
      <h2>Live Queue</h2>
    </div>
    <table>
      <thead>
        <tr>
          <th>Patient Name</th>
          <th>Scheduled Time</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Shubham Kumar</td>
          <td>09:00 AM</td>
          <td><span class="status onhold">Waiting</span></td>
          <td>
            <a href="#" class="small-button">Start Consultation</a>
            <a href="#" class="small-button">End Consultation</a>
            <a href="#" class="small-button">Add Notes</a>
          </td>
        </tr>
        <tr>
          <td>Ram Kumar</td>
          <td>09:30 AM</td>
          <td><span class="status onhold">Waiting</span></td>
          <td>
            <a href="#" class="small-button">Start Consultation</a>
            <a href="#" class="small-button">End Consultation</a>
            <a href="#" class="small-button">Add Notes</a>
          </td>
        </tr>
        <!-- Repeat rows as needed -->
      </tbody>
    </table>
  </div>
</body>
</html>
