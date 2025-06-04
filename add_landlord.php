<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Escape user inputs
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    
    // Hash password securely
    $password_raw = $_POST['password'];
    if (empty($password_raw)) {
        $_SESSION['landlord_status'] = ['type' => 'danger', 'message' => 'Password cannot be empty.'];
        header("Location: landlord_dashboard.php");
        exit();
    }
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

    // Use prepared statement for safety
    $stmt = $conn->prepare("INSERT INTO landlords (name, phone, address, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $address, $password_hashed);

    if ($stmt->execute()) {
        $_SESSION['landlord_status'] = ['type' => 'success', 'message' => 'Landlord added successfully.'];
    } else {
        $_SESSION['landlord_status'] = ['type' => 'danger', 'message' => 'Failed to add landlord.'];
    }
    $stmt->close();
    $conn->close();

    header("Location: landlord_dashboard.php");
    exit();
}
?>
