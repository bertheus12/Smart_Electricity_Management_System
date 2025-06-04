<?php
session_start();
include 'database.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM landlords WHERE id=$id";
    if ($conn->query($sql)) {
        $_SESSION['tenant_status'] = ['type' => 'success', 'message' => 'Landlord deleted successfully!'];
    } else {
        $_SESSION['tenant_status'] = ['type' => 'danger', 'message' => 'Error deleting landlord: ' . $conn->error];
    }
}
header("Location: landlord_dashboard.php"); // Update with your dashboard filename
exit();
?>
