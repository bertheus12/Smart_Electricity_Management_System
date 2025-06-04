<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenant_id = intval($_POST['tenant_id']);
    $charge = floatval($_POST['charge']);

    // Define rate (example: 500 RWF per kWh)
    $rate_per_kwh = 500;

    if ($tenant_id <= 0 || $charge <= 0) {
        $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Invalid input values.'];
        header("Location: landlord_dashboard.php");
        exit();
    }

    $kw = $charge / $rate_per_kwh;

    // Validate tenant belongs to landlord
    $landlord_id = $_SESSION['user_id'];
    $stmtCheck = $conn->prepare("SELECT id FROM tenants WHERE id = ? AND landlord_id = ?");
    $stmtCheck->bind_param("ii", $tenant_id, $landlord_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $stmtCheck->close();

    if ($resultCheck->num_rows === 0) {
        $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Tenant not found or not associated with you.'];
        header("Location: landlord_dashboard.php");
        exit();
    }

    // Update cashpower: add balance + kwh
    $stmtUpdate = $conn->prepare("UPDATE cashpower SET balance = balance + ?, kwh = kwh + ? WHERE tenant_id = ?");
    $stmtUpdate->bind_param("ddi", $charge, $kw, $tenant_id);
    if (!$stmtUpdate->execute()) {
        $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Failed to update balance: ' . $stmtUpdate->error];
        $stmtUpdate->close();
        header("Location: landlord_dashboard.php");
        exit();
    }
    $stmtUpdate->close();

    // Check new kwh value to determine power status
    $checkKwh = $conn->prepare("SELECT kwh FROM cashpower WHERE tenant_id = ?");
    $checkKwh->bind_param("i", $tenant_id);
    $checkKwh->execute();
    $resKwh = $checkKwh->get_result()->fetch_assoc();
    $checkKwh->close();

    $new_kwh = $resKwh['kwh'];
    $status = $new_kwh > 0 ? 'power_on' : 'power_off';

    // Update status
    $stmtStatus = $conn->prepare("UPDATE cashpower SET status = ? WHERE tenant_id = ?");
    $stmtStatus->bind_param("si", $status, $tenant_id);
    $stmtStatus->execute();
    $stmtStatus->close();

    // Insert transaction
    $stmtInsert = $conn->prepare("INSERT INTO transactions (tenant_id, charge, kw, created_at) VALUES (?, ?, ?, NOW())");
    $stmtInsert->bind_param("idd", $tenant_id, $charge, $kw);
    if ($stmtInsert->execute()) {
        $_SESSION['recharge_status'] = ['type' => 'success', 'message' => 'Recharge successful. ' . round($kw, 2) . ' kWh added.'];
    } else {
        $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Failed to insert transaction: ' . $stmtInsert->error];
    }
    $stmtInsert->close();

    $conn->close();
    header("Location: landlord_dashboard.php");
    exit();
}
?>
