<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string(trim($_POST['name']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $house_number = $conn->real_escape_string(trim($_POST['house_number']));

    if ($id && $name && $phone && $house_number) {
        $sql = "UPDATE tenants SET name='$name', phone='$phone', house_number='$house_number' WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['tenant_status'] = [
                'type' => 'success',
                'message' => 'Tenant updated successfully.'
            ];
        } else {
            $_SESSION['tenant_status'] = [
                'type' => 'danger',
                'message' => 'Error updating tenant: ' . $conn->error
            ];
        }
    } else {
        $_SESSION['tenant_status'] = [
            'type' => 'danger',
            'message' => 'All fields are required.'
        ];
    }
} else {
    $_SESSION['tenant_status'] = [
        'type' => 'danger',
        'message' => 'Invalid request method.'
    ];
}

header("Location: landlord_dashboard.php");
exit();
