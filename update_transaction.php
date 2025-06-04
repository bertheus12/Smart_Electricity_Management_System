<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $tenant_id = $conn->real_escape_string(trim($_POST['tenant_id']));
    $charge = $conn->real_escape_string(trim($_POST['charge']));
    $kwh = $conn->real_escape_string(trim($_POST['kw']));
    $date= intval($_POST['created_at']);
    if ($id && $name && $phone && $house_number) {
        $sql = "UPDATE transactions SET tenant_id='$tenant_id', charge='$charge', kw='$kwh' WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['transaction_status'] = [
                'type' => 'success',
                'message' => 'Transaction updated successfully.'
            ];
        } else {
            $_SESSION['transaction_status'] = [
                'type' => 'danger',
                'message' => 'Error updating transaction: ' . $conn->error
            ];
        }
    } else {
        $_SESSION['transaction_status'] = [
            'type' => 'danger',
            'message' => 'All fields are required.'
        ];
    }
} else {
    $_SESSION['transaction_status'] = [
        'type' => 'danger',
        'message' => 'Invalid request method.'
    ];
}

header("Location: landlord_dashboard.php");
exit();
