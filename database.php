<?php
// database.php
$host = 'localhost';
$db = 'electricity_system';
$user = 'root';
$pass = ''; // your DB password, if any

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
