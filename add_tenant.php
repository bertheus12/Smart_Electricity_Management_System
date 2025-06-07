<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $house_number = trim($_POST['house_number']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $landlord_id = $_POST['landlord_id'];

    if (empty($name) || empty($phone) || empty($house_number) || empty($password)) {
        $_SESSION['tenant_status'] = ['type' => 'danger', 'message' => 'Please fill in all fields.'];
        header("Location: landlord_dashboard.php");
        exit();
    }
// Validate phone number
if (!preg_match('/^07[2839]\d{7}$/', $phone)) {
    $_SESSION['tenant_status'] = ['type' => 'danger', 'message' => 'Invalid phone number. It must be 10 digits and start with 078, 079, 072, or 073.'];
    header("Location: landlord_dashboard.php");
    exit();
}

    // 🔍 Check if house_number is already occupied for this landlord
    $check = $conn->prepare("SELECT id FROM tenants WHERE landlord_id = ? AND house_number = ?");
    $check->bind_param("is", $landlord_id, $house_number);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['tenant_status'] = ['type' => 'danger', 'message' => 'The house number is already occupied.'];
        $check->close();
        header("Location: landlord_dashboard.php");
        exit();
    }
    $check->close();

    // Insert tenant
    $stmt = $conn->prepare("INSERT INTO tenants (name, phone, house_number, password, landlord_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $phone, $house_number, $password, $landlord_id);

    if ($stmt->execute()) {
        $tenant_id = $stmt->insert_id;
        $stmt->close();

        // Create cashpower record
        $stmt2 = $conn->prepare("INSERT INTO cashpower (tenant_id, balance) VALUES (?, 0)");
        $stmt2->bind_param("i", $tenant_id);

        if ($stmt2->execute()) {
            $_SESSION['tenant_status'] = ['type' => 'success', 'message' => 'Tenant added successfully.'];
        } else {
            $_SESSION['tenant_status'] = ['type' => 'warning', 'message' => 'Tenant added, but failed to initialize cashpower.'];
        }
        $stmt2->close();
    } else {
        $_SESSION['tenant_status'] = ['type' => 'danger', 'message' => 'Failed to add tenant. Try again.'];
        $stmt->close();
    }

    $conn->close();
    header("Location: landlord_dashboard.php");
    exit();
} else {
    header("Location: landlord_dashboard.php");
    exit();
}
