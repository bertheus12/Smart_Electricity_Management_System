<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

include 'database.php';

if (isset($_GET['id'])) {
    $tenant_id = intval($_GET['id']);

    // Ensure the tenant belongs to the logged-in landlord
    $landlord_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id FROM tenants WHERE id = ? AND landlord_id = ?");
    $stmt->bind_param("ii", $tenant_id, $landlord_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete from cashpower first to maintain foreign key constraint
        $conn->query("DELETE FROM cashpower WHERE tenant_id = $tenant_id");

        // Then delete tenant
        if ($conn->query("DELETE FROM tenants WHERE id = $tenant_id") === TRUE) {
            $_SESSION['tenant_status'] = [
                'type' => 'success',
                'message' => 'Tenant successfully deleted.'
            ];
        } else {
            $_SESSION['tenant_status'] = [
                'type' => 'danger',
                'message' => 'Error deleting tenant: ' . $conn->error
            ];
        }
    } else {
        $_SESSION['tenant_status'] = [
            'type' => 'warning',
            'message' => 'Tenant not found or not authorized.'
        ];
    }
}

header("Location: landlord_dashboard.php");
exit();
