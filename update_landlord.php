<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $password = $_POST['password'];

    if (!empty($password)) {
        // Hash password if provided
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE landlords SET name='$name', phone='$phone', address='$address', password='$hashed_password' WHERE id=$id";
    } else {
        // Update without password
        $sql = "UPDATE landlords SET name='$name', phone='$phone', address='$address' WHERE id=$id";
    }

    if ($conn->query($sql)) {
        $_SESSION['tenant_status'] = ['type' => 'success', 'message' => 'Landlord updated successfully!'];
    } else {
        $_SESSION['tenant_status'] = ['type' => 'danger', 'message' => 'Error updating landlord: ' . $conn->error];
    }
    header("Location: landlord_dashboard.php"); // Change to your actual dashboard filename
    exit();
}
?>
