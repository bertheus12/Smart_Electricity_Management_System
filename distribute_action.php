<?php
ob_start();
session_start();
include 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

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
$stmtCashpower->execute();
$resultCashpower = $stmtCashpower->get_result();
while ($row = $resultCashpower->fetch_assoc()) {
    $cashpowers[] = $row;
}
$stmtCashpower->close();
?>

<h2>Distribute Power from Cashpower</h2>
<div id="distributeMessage">
<?php
if (isset($_SESSION['distribute_status'])) {
    $status = $_SESSION['distribute_status'];
    echo "<div class='alert alert-{$status['type']}' role='alert'>{$status['message']}</div>";
    unset($_SESSION['distribute_status']);
}
?>
</div>

<form id="distributeForm" class="distribute-form" method="POST">
    <div class="form-group">
        <label>Select Cashpower:</label>
        <select name="cashpower_id" required>
            <option value="">-- Select Cashpower --</option>
            <?php foreach ($cashpowers as $cp): ?>
                <option value="<?= $cp['id'] ?>">
                    ID <?= $cp['id'] ?> - <?= $cp['unit'] ?> kWh - <?= $cp['balance'] ?> RWF remaining
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group" style="flex: 1 1 100%;">
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
    </div>

    <div class="form-group">
        <button type="button" onclick="addTenantCharge()">➕ Add Another Tenant</button>
    </div>

    <div class="form-group">
        <button type="submit">🚀 Distribute</button>
    </div>
</form>

<style>
/* [Styles remain unchanged] */
</style>

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
        <button type="button" class="remove-btn" onclick="this.parentNode.remove()">❌ Remove</button>
    `;
    container.appendChild(div);
}
</script>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cashpower_id = intval($_POST['cashpower_id']);
    $tenant_ids = $_POST['tenant_ids'] ?? [];
    $charges = $_POST['charges'] ?? [];

    if ($cashpower_id <= 0 || empty($tenant_ids) || empty($charges) || count($tenant_ids) !== count($charges)) {
        $_SESSION['distribute_status'] = ['type' => 'danger', 'message' => 'Invalid input. Please select valid tenants and charges.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $stmt = $conn->prepare("SELECT amount, unit, balance FROM cashpower WHERE id = ?");
    $stmt->bind_param("i", $cashpower_id);
    $stmt->execute();
    $cashpower = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$cashpower) {
        $_SESSION['distribute_status'] = ['type' => 'danger', 'message' => 'Cashpower not found.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $amount = $cashpower['amount'];
    $unit = $cashpower['unit'];
    $balance = $cashpower['balance'];

    $total_charge = array_sum(array_map('floatval', $charges));
    if ($total_charge > $balance) {
        $_SESSION['distribute_status'] = ['type' => 'danger', 'message' => 'Total charge exceeds available balance in cashpower.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $stmtInsert = $conn->prepare("INSERT INTO transactions (tenant_id, charge, kw, created_at) VALUES (?, ?, ?, NOW())");
    $stmtUpdateBalance = $conn->prepare("UPDATE cashpower SET balance = balance - ? WHERE id = ?");
    $stmtUpdatePower = $conn->prepare("INSERT INTO tenant_power (tenant_id, current_kw, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE current_kw = current_kw + VALUES(current_kw), status = VALUES(status)");

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

    $message = "$successCount transaction(s) successful.";
    if ($errors) {
        $message .= " Errors: " . implode(" | ", $errors);
        $_SESSION['distribute_status'] = ['type' => 'danger', 'message' => $message];
    } else {
        $_SESSION['distribute_status'] = ['type' => 'success', 'message' => $message];
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

ob_end_flush();
?>
