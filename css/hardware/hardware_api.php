<?php
$conn = new mysqli("localhost", "username", "password", "database");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tenant_id'])) {
    $tenant_id = $_GET['tenant_id'];
    $sql = "SELECT balance FROM cashpower WHERE tenant_id = $tenant_id";
    $result = $conn->query($sql);

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["balance" => $row['balance']]);
    } else {
        echo json_encode(["error" => "Tenant not found"]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tenant_id'], $_POST['status'])) {
    $tenant_id = $_POST['tenant_id'];
    $status = $_POST['status']; // 0 = OFF, 1 = ON
    $sql = "UPDATE tenants SET power_status = $status WHERE id = $tenant_id";
    $conn->query($sql);
    echo json_encode(["success" => true]);
}
?>
