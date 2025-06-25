<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("DELETE FROM transactions WHERE id = $id");

    if ($result) {
        $_SESSION['transaction_status'] = ['type' => 'success', 'message' => 'Transaction deleted successfully.'];
    } else {
        $_SESSION['transaction_status'] = ['type' => 'danger', 'message' => 'Failed to delete transaction.'];
    }
} else {
    $_SESSION['transaction_status'] = ['type' => 'warning', 'message' => 'No transaction ID provided.'];
}

header("Location: landlord_dashboard.php#transactions");
exit();
?>
