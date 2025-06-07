<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['reset_phone'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_SESSION['reset_phone'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Try updating landlord first
    $stmt = $conn->prepare("UPDATE landlords SET password = ? WHERE phone = ?");
    $stmt->bind_param("ss", $new_password, $phone);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        // Try tenant if not a landlord
        $stmt = $conn->prepare("UPDATE tenants SET password = ? WHERE phone = ?");
        $stmt->bind_param("ss", $new_password, $phone);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();
    unset($_SESSION['reset_phone']);
    echo "<script>alert('Password updated successfully. You can now log in.'); window.location.href='login.php';</script>";
}
?>
