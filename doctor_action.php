<?php
// doctor_action.php
require 'db_connect.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only doctors may perform these actions
if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Doctor') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$queue_id = intval($_POST['queue_id'] ?? 0);
$action   = $_POST['action']      ?? '';

if (!$queue_id || !in_array($action, ['start','end'], true)) {
    header('Location: doctor_dashboard.php');
    exit;
}

// Look up the corresponding appointment
$row = $conn->query("
    SELECT appointment_id 
      FROM Queue 
     WHERE queue_id = $queue_id
    LIMIT 1
")->fetch_assoc();

if (!$row) {
    header('Location: doctor_dashboard.php');
    exit;
}

$aid = intval($row['appointment_id']);

if ($action === 'start') {
    // Record the actual consultation start
    $conn->query("
      UPDATE Appointment
         SET start_time = NOW()
       WHERE appointment_id = $aid
    ");
    $conn->query("
      UPDATE Queue
         SET status = 'In Progress'
       WHERE queue_id = $queue_id
    ");
}
elseif ($action === 'end') {
    // Record the consultation end
    $conn->query("
      UPDATE Appointment
         SET end_time = NOW(),
             status   = 'Completed'
       WHERE appointment_id = $aid
    ");
    $conn->query("
      UPDATE Queue
         SET status = 'Completed'
       WHERE queue_id = $queue_id
    ");
}

// Redirect back to the dashboard
header("Location: doctor_dashboard.php");
exit;
