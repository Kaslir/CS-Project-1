<?php
// db_connect.php
session_start();

$host     = "localhost";
$user     = "root";
$password = "12345678";           // your MySQL password
$database = "clinic_system";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// For simplicity, we assume the receptionist is already logged in
// and $_SESSION['user_id'] holds their user_id.
