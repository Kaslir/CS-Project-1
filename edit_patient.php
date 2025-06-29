<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Receptionist') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$patient_id = isset($_GET['patient_id'])
    ? intval($_GET['patient_id'])
    : (isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0);

if (!$patient_id) {
    die("Invalid patient ID.");
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $phone     = $conn->real_escape_string(trim($_POST['phone'] ?? ''));
    $dob       = $conn->real_escape_string(trim($_POST['dob'] ?? ''));
    $id_number = $conn->real_escape_string(trim($_POST['id_number'] ?? ''));

    if (!$name || !$phone || !$dob || !$id_number) {
        $error = "All fields are required.";
    } else {
        $sql = "
          UPDATE Patient SET
            name          = '$name',
            phone_number  = '$phone',
            date_of_birth = '$dob',
            ID_Number     = '$id_number'
          WHERE patient_id = $patient_id
        ";
        if ($conn->query($sql)) {
            $success = "Patient details updated successfully.";
        } else {
            $error = "Update failed: " . htmlspecialchars($conn->error);
        }
    }
}

$res = $conn->query("
  SELECT
    name,
    phone_number,
    date_of_birth,
    ID_Number
  FROM Patient
  WHERE patient_id = $patient_id
  LIMIT 1
");
if (!$res || $res->num_rows !== 1) {
    die("Patient not found.");
}
$patient = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Patient</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px;
      background: #3498db;
      color: #fff;
    }
    .header a.logout-btn {
      color: #fff;
      text-decoration: none;
      font-weight: bold;
    }
    .form-container {
      max-width: 400px;
      margin: 40px auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      font-family: Arial, sans-serif;
    }
    .form-container h2 {
      margin-top: 0;
      text-align: center;
    }
    label {
      display: block;
      margin-top: 12px;
      font-weight: 600;
    }
    input {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }
    .btn {
      width: 100%;
      padding: 10px;
      margin-top: 20px;
      border: none;
      border-radius: 4px;
      background: #27ae60;
      color: #fff;
      font-size: 1rem;
      cursor: pointer;
    }
    .btn:hover {
      background: #219150;
    }
    .message {
      margin-top: 12px;
      padding: 10px;
      border-radius: 4px;
      text-align: center;
    }
    .message.error {
      background: #fdecea;
      color: #e74c3c;
    }
    .message.success {
      background: #e6f4ea;
      color: #27ae60;
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>Clinic</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="form-container">
    <h2>Edit Patient</h2>

    <?php if ($error): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="edit_patient.php">
      <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

      <label for="name">Full Name</label>
      <input
        type="text"
        id="name"
        name="name"
        value="<?= htmlspecialchars($patient['name']) ?>"
        required
      />

      <label for="phone">Phone Number</label>
      <input
        type="tel"
        id="phone"
        name="phone"
        value="<?= htmlspecialchars($patient['phone_number']) ?>"
        required
      />

      <label for="dob">Date of Birth</label>
      <input
        type="date"
        id="dob"
        name="dob"
        value="<?= htmlspecialchars($patient['date_of_birth']) ?>"
        required
      />

      <label for="id_number">ID Number</label>
      <input
        type="text"
        id="id_number"
        name="id_number"
        value="<?= htmlspecialchars($patient['ID_Number']) ?>"
        required
      />

      <button type="submit" class="btn">Save Changes</button>
    </form>
  </div>

</body>
</html>