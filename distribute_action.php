<?php
ob_start(); // Prevents accidental output
session_start();
include 'database.php';

header('Content-Type: application/json');

// Enable detailed error reporting (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = [
    'success' => false,
    'html' => ''
];

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['html'] = '<div class="alert alert-danger">❌ Invalid request method.</div>';
    echo json_encode($response);
    exit;
}

// Input validation
$cashpower_id = intval($_POST['cashpower_id'] ?? 0);
$tenant_ids = $_POST['tenant_ids'] ?? [];
$charges = $_POST['charges'] ?? [];

if ($cashpower_id <= 0 || empty($tenant_ids) || empty($charges) || count($tenant_ids) !== count($charges)) {
    $response['html'] = '<div class="alert alert-danger">⚠️ Invalid input. Please select valid tenants and charges.</div>';
    echo json_encode($response);
    exit;
}

// Fetch cashpower info
$stmt = $conn->prepare("SELECT amount, unit, balance FROM cashpower WHERE id = ?");
$stmt->bind_param("i", $cashpower_id);
$stmt->execute();
$cashpower = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cashpower) {
    $response['html'] = '<div class="alert alert-danger">❌ Cashpower not found.</div>';
    echo json_encode($response);
    exit;
}

$balance = floatval($cashpower['balance']);
$total_charge = array_sum(array_map('floatval', $charges));

// Check if enough balance
if ($total_charge > $balance) {
    $response['html'] = '<div class="alert alert-danger">
        ❌ <strong>Insufficient Balance:</strong><br>
        Required: <strong>' . number_format($total_charge, 2) . ' RWF</strong><br>
        Available: <strong>' . number_format($balance, 2) . ' RWF</strong>
    </div>';
    echo json_encode($response);
    exit;
}

// Prepare queries
$stmtInsert = $conn->prepare("INSERT INTO transactions (tenant_id, charge, kw, created_at) VALUES (?, ?, ?, NOW())");
$stmtUpdateBalance = $conn->prepare("UPDATE cashpower SET balance = balance - ? WHERE id = ?");
$stmtUpdatePower = $conn->prepare("
    INSERT INTO tenant_power (tenant_id, current_kw, status) 
    VALUES (?, ?, ?) 
    ON DUPLICATE KEY UPDATE 
        current_kw = current_kw + VALUES(current_kw),
        status = VALUES(status)
");

$successCount = 0;
$errors = [];

for ($i = 0; $i < count($tenant_ids); $i++) {
    $tenant_id = intval($tenant_ids[$i]);
    $charge = floatval($charges[$i]);
    $kw = ($cashpower['unit'] * $charge) / $cashpower['amount'];
    $status = $kw > 0 ? 'connected' : 'disconnected';

    try {
        $conn->begin_transaction();

        // Insert into transactions
        $stmtInsert->bind_param("idd", $tenant_id, $charge, $kw);
        if (!$stmtInsert->execute()) {
            throw new Exception("Insert failed for tenant ID: $tenant_id");
        }

        // Update balance
        $stmtUpdateBalance->bind_param("di", $charge, $cashpower_id);
        if (!$stmtUpdateBalance->execute()) {
            throw new Exception("Balance update failed");
        }

        // Update tenant power
        $stmtUpdatePower->bind_param("ids", $tenant_id, $kw, $status);
        if (!$stmtUpdatePower->execute()) {
            throw new Exception("Tenant power update failed");
        }

        $conn->commit();
        $successCount++;

    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = $e->getMessage();
    }
}

$stmtInsert->close();
$stmtUpdateBalance->close();
$stmtUpdatePower->close();

// Generate HTML response
if ($successCount > 0) {
    $response['success'] = true;
    $response['html'] .= '<div class="alert alert-success">✅ ' . $successCount . ' tenant(s) updated. Total: ' . number_format($total_charge, 2) . ' RWF.</div>';
}

if (!empty($errors)) {
    $response['html'] .= '<div class="alert alert-danger"><strong>❌ Errors:</strong><ul><li>' . implode('</li><li>', $errors) . '</li></ul></div>';
}

echo json_encode($response);
exit;
