<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($conn->query("DELETE FROM cashpower WHERE id = $id")) {
        $_SESSION['cashpower_status'] = [
            'type' => 'success',
            'message' => 'Cashpower record deleted successfully.'
        ];
    } else {
        $_SESSION['cashpower_status'] = [
            'type' => 'danger',
            'message' => 'Failed to delete cashpower record.'
        ];
    }
} else {
    $_SESSION['cashpower_status'] = [
        'type' => 'warning',
        'message' => 'No cashpower ID provided.'
    ];
}

header("Location: landlord_dashboard.php#transactions");
exit();
?>

