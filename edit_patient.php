<?php
// edit_patient.php
require_once 'db_connect.php';

$patient_id = intval($_GET['patient_id'] ?? 0);
if (!$patient_id) die("Invalid ID");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = $conn->real_escape_string($_POST['name']);
  $phone = $conn->real_escape_string($_POST['phone']);
  $dob   = $conn->real_escape_string($_POST['dob']);
  $gender= $conn->real_escape_string($_POST['gender']);
  $conn->query("
    UPDATE Patient
       SET name = '$name',
           phone_number = '$phone',
           date_of_birth = '$dob',
           gender = '$gender'
     WHERE patient_id = $patient_id
  ");
  header("Location: patient_profile.php?patient_id=$patient_id");
  exit;
}

$res = $conn->query("SELECT * FROM Patient WHERE patient_id = $patient_id");
$pt  = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Edit Patient</title><link rel="stylesheet" href="style.css"></head>
<body>
  <div class="header"><h1>Edit Patient</h1><a href="patient_profile.php?patient_id=<?= $patient_id ?>">← Back</a></div>
  <form method="POST">
    <label>Name:    <input name="name"  value="<?= htmlspecialchars($pt['name']) ?>"   required></label><br>
    <label>Phone:   <input name="phone" value="<?= htmlspecialchars($pt['phone_number']) ?>" required></label><br>
    <label>DOB:     <input type="date" name="dob" value="<?= $pt['date_of_birth'] ?>" required></label><br>
    <label>Gender:  
      <select name="gender" required>
        <option <?= $pt['gender']==='Male'   ? 'selected':'' ?>>Male</option>
        <option <?= $pt['gender']==='Female' ? 'selected':'' ?>>Female</option>
      </select>
    </label><br>
    <button type="submit">Save</button>
  </form>
</body>
</html>
