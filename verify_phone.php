<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);

    // Check both landlords and tenants tables
    $stmt = $conn->prepare("
        SELECT 'landlord' AS user_type FROM landlords WHERE phone = ?
        UNION
        SELECT 'tenant' AS user_type FROM tenants WHERE phone = ?
    ");
    $stmt->bind_param("ss", $phone, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['reset_phone'] = $phone;
        header("Location: reset_password_form.php");
        exit();
    } else {
        echo "<script>alert('Phone number not found.'); window.location.href='forgot_password.php';</script>";
    }
}
?>
