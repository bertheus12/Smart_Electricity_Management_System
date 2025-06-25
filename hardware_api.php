<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = $_POST['tenant_id'] ?? null;
    $used_kw = $_POST['used_kw'] ?? null;

    if ($tenant_id && $used_kw) {
        $stmt = $conn->prepare("UPDATE tenant_power SET current_kw = current_kw - ? WHERE tenant_id = ?");
        $stmt->bind_param("di", $used_kw, $tenant_id);
        $stmt->execute();
        $stmt->close();

        echo "OK";
    } else {
        http_response_code(400);
        echo "Missing tenant_id or used_kw";
    }
} else {
    echo "Hardware API endpoint";
}
?>
