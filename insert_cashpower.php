<?php
session_start();
include "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']);
    $unit = floatval($_POST['unit']);

    if ($unit <= 0) {
        $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Unit must be greater than zero.'];
        header("Location: landlord_dashboard.php");
        exit();
    }

    if ($unit > $amount) {
        $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Recharge failed: Unit (kWh) cannot be greater than Amount (RWF).'];
        header("Location: landlord_dashboard.php");
        exit();
    }

    $price = $amount / $unit;
    $balance = $amount;
    $created_at = date("Y-m-d H:i:s");

    $sql = "INSERT INTO cashpower (amount, unit, price, balance, created_at) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Database error: ' . $conn->error];
        header("Location: landlord_dashboard.php");
        exit();
    }

    $stmt->bind_param("dddds", $amount, $unit, $price, $balance, $created_at);

    if ($stmt->execute()) {
        $_SESSION['recharge_status'] = ['type' => 'success', 'message' => "Recharge successful: RWF $amount for $unit kWh"];
    } else {
        $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Recharge failed: ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();

    header("Location: landlord_dashboard.php");
    exit();
} else {
    header("Location: landlord_dashboard.php");
    exit();
}
