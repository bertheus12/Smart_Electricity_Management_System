<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];
$message = "";

// Fetch tenants for the landlord
$tenants = [];
$stmtTenants = $conn->prepare("SELECT id, name FROM tenants WHERE landlord_id = ?");
$stmtTenants->bind_param("i", $landlord_id);
$stmtTenants->execute();
$resultTenants = $stmtTenants->get_result();
while ($row = $resultTenants->fetch_assoc()) {
    $tenants[] = $row;
}
$stmtTenants->close();

// Fetch available (unused) cashpower
$cashpowers = [];
$stmtCashpower = $conn->prepare("SELECT id, amount, unit, balance FROM cashpower WHERE balance > 0");
if (!$stmtCashpower) die("Prepare failed (cashpower): " . $conn->error);
$stmtCashpower->execute();
$resultCashpower = $stmtCashpower->get_result();
while ($row = $resultCashpower->fetch_assoc()) {
    $cashpowers[] = $row;
}
$stmtCashpower->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cashpower_id = intval($_POST['cashpower_id']);
    $tenant_ids = $_POST['tenant_ids'] ?? [];
    $charges = $_POST['charges'] ?? [];

    if ($cashpower_id <= 0 || empty($tenant_ids) || empty($charges) || count($tenant_ids) !== count($charges)) {
        $message = '<div style="color:red;">Invalid input. Please select valid tenants and charges.</div>';
    } else {
        // Get cashpower data
        $stmt = $conn->prepare("SELECT amount, unit, balance FROM cashpower WHERE id = ?");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("i", $cashpower_id);
        $stmt->execute();
        $cashpower = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$cashpower) {
            $message = '<div style="color:red;">Cashpower not found.</div>';
        } else {
            $amount = $cashpower['amount'];
            $unit = $cashpower['unit'];
            $balance = $cashpower['balance'];

            $total_charge = array_sum(array_map('floatval', $charges));
            if ($total_charge > $balance) {
                $message = '<div style="color:red;">Total charge exceeds available balance in cashpower.</div>';
            } else {
                $stmtInsert = $conn->prepare("INSERT INTO transactions (tenant_id, charge, kw, created_at) VALUES (?, ?, ?, NOW())");
                if (!$stmtInsert) die("Prepare failed (transaction insert): " . $conn->error);

                $stmtUpdateBalance = $conn->prepare("UPDATE cashpower SET balance = balance - ? WHERE id = ?");
                if (!$stmtUpdateBalance) die("Prepare failed (balance update): " . $conn->error);

                $stmtUpdatePower = $conn->prepare("INSERT INTO tenant_power (tenant_id, current_kw, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE current_kw = current_kw + VALUES(current_kw), status = VALUES(status)");
                if (!$stmtUpdatePower) die("Prepare failed (tenant_power): " . $conn->error);

                $successCount = 0;
                $errors = [];

                for ($i = 0; $i < count($tenant_ids); $i++) {
                    $tenant_id = intval($tenant_ids[$i]);
                    $charge = floatval($charges[$i]);
                    $kw = ($unit * $charge) / $amount;

                    $stmtInsert->bind_param("idd", $tenant_id, $charge, $kw);
                    if ($stmtInsert->execute()) {
                        $stmtUpdateBalance->bind_param("di", $charge, $cashpower_id);
                        $stmtUpdateBalance->execute();

                        $status = $kw > 0 ? 'connected' : 'disconnected';
                        $stmtUpdatePower->bind_param("ids", $tenant_id, $kw, $status);
                        $stmtUpdatePower->execute();

                        $successCount++;
                    } else {
                        $errors[] = "Error for tenant $tenant_id: " . htmlspecialchars($stmtInsert->error);
                    }
                }

                $stmtInsert->close();
                $stmtUpdateBalance->close();
                $stmtUpdatePower->close();

                $message = "<div style='color:green;'>$successCount transaction(s) successful.</div>";
                if ($errors) {
                    $message .= "<div style='color:red;'><ul><li>" . implode("</li><li>", $errors) . "</li></ul></div>";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Distribute Power</title>
</head>
<body>
<h2>Distribute Power from Cashpower</h2>
<?php if (!empty($message)) echo $message; ?>

<form method="POST">
    <label>Select Cashpower:</label>
    <select name="cashpower_id" required>
        <option value="">-- Select Cashpower --</option>
        <?php foreach ($cashpowers as $cp): ?>
            <option value="<?= $cp['id'] ?>">
                ID <?= $cp['id'] ?> - <?= $cp['unit'] ?> kWh - <?= $cp['balance'] ?> RWF remaining
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <div id="tenant-charge-container">
        <div>
            <label>Tenant:</label>
            <select name="tenant_ids[]" required>
                <option value="">-- Select Tenant --</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?= $t['id'] ?>"> <?= htmlspecialchars($t['name']) ?> (ID <?= $t['id'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <label>Charge (RWF):</label>
            <input type="number" name="charges[]" step="0.01" required>
        </div>
    </div>

    <button type="button" onclick="addTenantCharge()">Add Another Tenant</button><br><br>
    <button type="submit">Distribute</button>
</form>

<script>
function addTenantCharge() {
    const container = document.getElementById('tenant-charge-container');
    const div = document.createElement('div');
    div.innerHTML = `
        <label>Tenant:</label>
        <select name="tenant_ids[]" required>
            <option value="">-- Select Tenant --</option>
            <?php foreach ($tenants as $t): ?>
                <option value="<?= $t['id'] ?>"> <?= htmlspecialchars($t['name']) ?> (ID <?= $t['id'] ?>)</option>
            <?php endforeach; ?>
        </select>
        <label>Charge (RWF):</label>
        <input type="number" name="charges[]" step="0.01" required>
        <button type="button" onclick="this.parentNode.remove()">Remove</button>
    `;
    container.appendChild(div);
}
</script>
</body>
</html>
