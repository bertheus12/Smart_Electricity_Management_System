
<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

include 'database.php';
$landlord_id = $_SESSION['user_id'];
$tenant = $conn->query("SELECT * FROM landlords WHERE id = $landlord_id")->fetch_assoc();
$tenantss = $conn->query("SELECT * FROM tenants ");
$transactions = $conn->query("SELECT * FROM transactions");
$cashpower = $conn->query("SELECT * FROM cashpower");
$power = $conn->query("SELECT * FROM tenant_power");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Landlord Dashboard</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome (for icons) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>


    <nav class="navbar navbar-expand-lg fixed-top bg-dark px-3">
        <div class="container-fluid d-flex justify-content-between align-items-center w-100">
            <div class="flex-grow-1 text-center">
                <h1 class="text-white m-0">Smart Electricity Management System</h1>
            </div>
            <a href="landlord_profile.php" class="nav-link text-warning ms-3">
                <i class="fas fa-user-circle" style="font-size: 20px; color: #4f46e5;"></i> Profile 
            </a>
            <a href="logout.php" class="nav-link text-warning ms-3">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>



    <!-- Sidebar -->
    <div class="sidebar d-none d-lg-block">
        <div class="d-flex flex-column p-3" style="height:80vh;">
            <h4 class="text-white mb-4"><i class="fas fa-user-shield me-2"></i> Welcome, <?php echo $tenant['name']; ?> </h4>
            <a href="#addTenant"><i class="fas fa-user-plus"></i> Add Tenant</a>
            <a href="#tenants"><i class="fas fa-list"></i> Tenants</a>
            <a href="#recharge"><i class="fas fa-sync"></i> recharge</a>
            <a href="#recharge_list"><i class="fas fa-sync"></i> recharge list</a>
            <a href="#distributed"><i class="fas fa-bolt"></i> distributed </a> 
            <a href="#transactions"><i class="fas fa-exchange-alt"></i> Transactions</a>
            <a href="#Comments"><i class="fas fa-comments"></i> View Comments </a> 
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">

            <!-- Welcome Section -->
            <div id="welcome" class="form-section">
            <h1>Welcome to Landlord Dashboard </h1>
            <p>Select an option from the navigation menu.</p>
                <div class="rectangle-boxes">
                    <?php
                        // Count number of tenants with balance > 0
                        $count = $conn->query("SELECT COUNT(*) AS total_active FROM tenant_power WHERE current_kw > 0")->fetch_assoc();
                            
                        $counts = $conn->query("SELECT COUNT(*) AS total_inactive FROM tenant_power WHERE current_kw = 0")->fetch_assoc();
                        $com = $conn->query("SELECT COUNT(*) AS comment FROM comments ")->fetch_assoc();
                    ?>

                    <div class="rectangle-box">
                        <h5>Active Tenants</h5>
                        <span><?php echo $count['total_active']; ?></span>
                    </div>
                    
                    <div class="rectangle-box">
                        <h5>InActive Tenants</h5>
                        <span><?php echo $counts['total_inactive']; ?></span>
                    </div>
                    
                    <div class="rectangle-box">
                        <h5>comment</h5>
                        <span><?php echo $com['comment']; ?></span>
                    </div>
                </div>
            </div>
            
        </div>
    

        <!-- Status Messages -->
        <?php
            if (isset($_SESSION['distribute_status'])) {
                echo $_SESSION['distribute_status'];
                unset($_SESSION['distribute_status']); // remove after showing once
            }

        ?>

        <?php
            if (isset($_SESSION['recharge_status'])) {
                $status = $_SESSION['recharge_status'];
                echo "<div class='alert alert-{$status['type']}' role='alert'>{$status['message']}</div>";
                unset($_SESSION['recharge_status']);
            }
        ?>

        <?php if (isset($_SESSION['tenant_status'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tenant_status']['type']; ?> alert-dismissible fade show mt-3" role="alert">
            <?php echo $_SESSION['tenant_status']['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['tenant_status']); ?>
        <?php endif; ?>
        <?php
            if (isset($_SESSION['transaction_status'])) {
                $status = $_SESSION['transaction_status'];

                // Set bootstrap-style or custom alert class
                $alertClass = 'alert-' . $status['type'];

                echo "<div class='alert $alertClass'>{$status['message']}</div>";

                unset($_SESSION['transaction_status']);
            }
        ?>

        <?php
            if (isset($_SESSION['cashpower_status'])) {
                $status = $_SESSION['cashpower_status'];
                $alertClass = 'alert-' . $status['type']; // e.g., alert-success

                echo "<div class='alert $alertClass'>{$status['message']}</div>";

                unset($_SESSION['cashpower_status']);
            }
        ?>

        <!-- Add Tenant -->
        <div id="addTenant" class="form-section">
            <h4 class="section-header"><i class="fas fa-user-plus"></i> Add New Tenant</h4>
            <form method="POST" action="add_tenant.php" class="row g-3">
                <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Full Name" required></div>
                <div class="col-md-3"><input type="text" name="phone" class="form-control" placeholder="Phone Number" required></div>
                <div class="col-md-3"><input type="text" name="house_number" class="form-control" placeholder="House Number" required></div>
                <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                <input type="hidden" name="landlord_id" value="<?php echo $landlord_id; ?>">
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Tenant</button>
                </div>
            </form>
        </div>

        <!-- Recharge -->
        <div id="recharge" class="form-section">
            <h4 class="section-header"><i class="fas fa-sync"></i> Recharge power</h4>
            <form method="POST" action="insert_cashpower.php" class="row g-3">
            
                <div class="col-md-3">
                    <label>Amount (RWF):</label>
                    <input type="number" name="amount" step="0.01" required><br>
                </div>
                <div class="col-md-3"><label>Unit (kWh):</label>
                    <input type="number" name="unit" step="0.01" required><br>
                </div>
                <div class="col-md-4">
                    <button type="submit"class="btn btn-success"><i class="fas fa-sync"></i>Recharge</button>
                </div>
            </form>

        </div>

    
        <!-- distributed power-->
        <div id="distributed" class="form-section">
            <?php

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


                        <h2>Distribute Power from Cashpower</h2>
                        <?php if (!empty($message)) echo $message; ?>

                        <!-- 👇 Place this style inside your <head> or before the form -->
                        <style>
                        /* Main form styling */
                        .distribute-form {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 20px;
                            padding: 20px;
                            background: #f4f6f9;
                            border-radius: 10px;
                            border: 1px solid #ccc;
                            font-family: Arial, sans-serif;
                            margin-top: 20px;
                        }

                        /* Form group layout */
                        .distribute-form .form-group {
                            flex: 1 1 45%;
                            min-width: 250px;
                        }

                        .distribute-form label {
                            font-weight: bold;
                            display: block;
                            margin-bottom: 5px;
                            color: #333;
                        }

                        .distribute-form select,
                        .distribute-form input[type="number"] {
                            width: 100%;
                            padding: 8px;
                            border-radius: 6px;
                            border: 1px solid #bbb;
                            background: #fff;
                            margin-bottom: 10px;
                            box-sizing: border-box;
                        }

                        /* Button styling */
                        .distribute-form button {
                            padding: 10px 16px;
                            border: none;
                            border-radius: 6px;
                            font-weight: bold;
                            cursor: pointer;
                            margin-right: 10px;
                        }

                        .distribute-form button[type="submit"] {
                            background-color: #28a745;
                            color: #fff;
                        }

                        .distribute-form button[type="submit"]:hover {
                            background-color: #218838;
                        }

                        .distribute-form button[type="button"] {
                            background-color: #007bff;
                            color: #fff;
                        }

                        .distribute-form button[type="button"]:hover {
                            background-color: #0056b3;
                        }

                        /* Added tenant charge block */
                        #tenant-charge-container > div {
                            background: #fff;
                            border: 1px solid #ddd;
                            padding: 15px;
                            border-radius: 8px;
                            margin-bottom: 15px;
                        }

                        /* Remove button inside added blocks */
                        button.remove-btn {
                            background-color: #dc3545;
                            color: #fff;
                            margin-top: 10px;
                            border: none;
                            padding: 8px 12px;
                            border-radius: 6px;
                            cursor: pointer;
                        }

                        button.remove-btn:hover {
                            background-color: #c82333;
                        }
                        </style>

                        <!-- 👇 Your actual form starts here -->
                        <form method="POST" class="distribute-form">
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

                        <!-- 👇 JavaScript to add tenant rows -->
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

        </div>

        <!-- View tenants -->
        <div id="tenants" class="form-section">
            <h4 class="section-header"><i class="fas fa-users"></i> View tenants</h4>
            <button class="export-button" onclick="exportTableToCSV('tenantTable', 'tenant.csv')">Export tenants</button>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th><th>Name</th><th>Phone</th><th>house number</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($tenantss && $tenantss->num_rows > 0): ?>
                        <?php while ($row = $tenantss->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['house_number']); ?></td>           
                                <td>
                                    <a href="update_tenant.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    </button>
                                    <a href="delete_tenant.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this tenant?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    
                    <?php endif; ?>
                </tbody>
                </table>
            </div>
        </div>


        <!-- recharge list  -->
        <div id="recharge_list" class="form-section">
                <h4 class="section-header"><i class="fas fa-eye"></i> View recharge</h4>
                <button class="export-button" onclick="exportTableToCSV('rechargeTable', 'recharge.csv')">Export recharge</button>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>ID</th><th>amount</th><th>unit</th><th>remaining amount</th><th>date</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($cashpower && $cashpower->num_rows > 0): ?>
                                <?php while ($row = $cashpower->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['amount']); ?></td>
                                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                                        <td><?php echo htmlspecialchars($row['balance']); ?></td>
                                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td><a href="update_recharge.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="delete_recharge.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this recharge?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                            <?php endwhile; ?>
                    
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
        </div>
            

        <!-- Transactions -->
        <div id="transactions" class="form-section">
            <h4 class="section-header"><i class="fas fa-exchange-alt"></i> Recharge Transactions</h4>
            <button class="export-button" onclick="exportTableToCSV('transactionTable', 'transaction.csv')">Export transaction</button>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" id="transaction" >
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th><th>Tenant ID</th><th>Charge</th><th>kWh</th><th>Date</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $transactions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['tenant_id']; ?></td>
                                <td><?php echo number_format($row['charge'], 0); ?></td>
                                <td><?php echo $row['kw']; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <a href="update_transaction.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="delete_transaction.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this transaction?');">
                                    <i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- new coment -->
        <div id="Comments" class="form-section">
            <h4 class="section-header"><i class="fas fa-comments"></i> Comment from Tenants</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th><th>Tenant ID</th><th>Comment</th><th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $comments = $conn->query("SELECT * FROM comments ORDER BY created_at DESC");
                        if ($comments && $comments->num_rows > 0):
                            while ($row = $comments->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['tenant_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['comment']); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" class="text-center">No comments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            function showSection(id) {
                document.querySelectorAll('.form-section, #welcome').forEach(sec => {
                    sec.style.display = 'none';
                });
                const section = document.querySelector(id);
                if (section) {
                    section.style.display = 'block';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }

            // Show welcome section on load
            showSection('#welcome');

            // Toggle sections on sidebar link click
            document.querySelectorAll('.navbar a, .sidebar a').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href.startsWith("#")) {
                        e.preventDefault();
                        showSection(href);
                    }
                });
            });
        });
    </script>
    <script>
        function downloadCSV(csv, filename) {
            let csvFile = new Blob([csv], { type: "text/csv" });
            let downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }

        function exportTableToCSV(tableId, filename) {
            let csv = [];
            let rows = document.querySelectorAll(`#${tableId} tr`);
            
            for (let row of rows) {
                let cols = row.querySelectorAll("td, th");
                let rowData = Array.from(cols).map(col => `"${col.innerText.replace(/"/g, '""')}"`);
                csv.push(rowData.join(","));
            }

            downloadCSV(csv.join("\n"), filename);
        }
    </script>

</body>
</html>
