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

// Fetch data for distributed section
$tenants = [];
$stmtTenants = $conn->prepare("SELECT id, name FROM tenants WHERE landlord_id = ?");
$stmtTenants->bind_param("i", $landlord_id);
$stmtTenants->execute();
$resultTenants = $stmtTenants->get_result();
while ($row = $resultTenants->fetch_assoc()) {
    $tenants[] = $row;
}
$stmtTenants->close();

$cashpowers = [];
$stmtCashpower = $conn->prepare("SELECT id, amount, unit, balance FROM cashpower WHERE balance > 0");
$stmtCashpower->execute();
$resultCashpower = $stmtCashpower->get_result();
while ($row = $resultCashpower->fetch_assoc()) {
    $cashpowers[] = $row;
}
$stmtCashpower->close();
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" type="text/css" href="css/style.css">
<style>
    /* Add this to your CSS */
    .form-section {
        display: none;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    #welcome {
        display: block;
    }
    
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

    #tenant-charge-container > div {
        background: #fff;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }

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
            <!-- <a href="#welcome"><i class="fas fa-home"></i> Dashboard</a> -->
            <a href="#addTenant"><i class="fas fa-user-plus"></i> Add Tenant</a>
            <a href="#tenants"><i class="fas fa-list"></i> Tenants</a>
            <a href="#recharge"><i class="fas fa-sync"></i> Recharge</a>
            <a href="#recharge_list"><i class="fas fa-eye"></i> Recharge List</a>
            <a href="#distributed"><i class="fas fa-bolt"></i> Distribute Power</a>
            <a href="#transactions"><i class="fas fa-exchange-alt"></i> Transactions</a>
            <a href="#powerStatus"><i class="fas fa-home"></i> powerStatus</a>
            <a href="#Comments"><i class="fas fa-comments"></i> View Comments</a> 
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Welcome Section -->
            <div id="welcome" class="form-section">
                <h1>Welcome to Landlord Dashboard</h1>
                <p>Select an option from the navigation menu.</p>
                <div class="rectangle-boxes">
                    <?php
                        $count = $conn->query("SELECT COUNT(*) AS total_active FROM tenant_power WHERE current_kw > 0")->fetch_assoc();
                        $counts = $conn->query("SELECT COUNT(*) AS total_inactive FROM tenant_power WHERE current_kw = 0")->fetch_assoc();
                        $com = $conn->query("SELECT COUNT(*) AS comment FROM comments")->fetch_assoc();
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
                        <h5>Comments</h5>
                        <span><?php echo $com['comment']; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Status Messages -->
            <?php

                // session_start();
                if (isset($_SESSION['distribute_status'])) {
                    echo $_SESSION['distribute_status'];
                    unset($_SESSION['distribute_status']);
                }


                if (isset($_SESSION['recharge_status'])) {
                    $status = $_SESSION['recharge_status'];
                    echo "<div class='alert alert-{$status['type']}' role='alert'>{$status['message']}</div>";
                    unset($_SESSION['recharge_status']);
                }
                if (isset($_SESSION['tenant_status'])): ?>
                <div class="alert alert-<?php echo $_SESSION['tenant_status']['type']; ?> alert-dismissible fade show mt-3" role="alert">
                    <?php echo $_SESSION['tenant_status']['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['tenant_status']); ?>
            <?php endif; ?>
            <?php
                if (isset($_SESSION['transaction_status'])) {
                    $status = $_SESSION['transaction_status'];
                    $alertClass = 'alert-' . $status['type'];
                    echo "<div class='alert $alertClass'>{$status['message']}</div>";
                    unset($_SESSION['transaction_status']);
                }
                if (isset($_SESSION['cashpower_status'])) {
                    $status = $_SESSION['cashpower_status'];
                    $alertClass = 'alert-' . $status['type'];
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
                        <input type="number" name="amount" step="0.01" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Unit (kWh):</label>
                        <input type="number" name="unit" step="0.01" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success"><i class="fas fa-sync"></i> Recharge</button>
                    </div>
                </form>
            </div>

            <!-- Distributed Power -->
            <div id="distributed" class="form-section">
                <h4 class="section-header"><i class="fas fa-bolt"></i> Distribute Power from Cashpower</h4>
                <form id="distributeForm" class="distribute-form" method="POST" action="distribute_action.php">
                    <div class="form-group">
                        <label>Select Cashpower:</label>
                        <select name="cashpower_id" class="form-control" required>
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
                                <select name="tenant_ids[]" class="form-control" required>
                                    <option value="">-- Select Tenant --</option>
                                    <?php foreach ($tenants as $t): ?>
                                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (ID <?= $t['id'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Charge (RWF):</label>
                                <input type="number" name="charges[]" step="0.01" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="button" class="btn btn-primary" onclick="addTenantCharge()">
                            <i class="fas fa-plus"></i> Add Another Tenant
                        </button>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-bolt"></i> Distribute
                        </button>
                    </div>
                </form>
            </div>

            <!-- View tenants -->
            <div id="tenants" class="form-section">
                <h4 class="section-header"><i class="fas fa-users"></i> View Tenants</h4>
                <button class="btn btn-secondary mb-3" onclick="exportTableToCSV('tenantsTable', 'tenants.csv')">
                    <i class="fas fa-download"></i> Export Tenants
                </button>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="tenantsTable">
                        <thead class="table-secondary">
                            <tr>
                                <th>ID</th><th>Name</th><th>Phone</th><th>House Number</th><th>Action</th>
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
                                        <a href="delete_tenant.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this tenant?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recharge List -->
            <div id="recharge_list" class="form-section">
                <h4 class="section-header"><i class="fas fa-eye"></i> View Recharge</h4>
                <button class="btn btn-secondary mb-3" onclick="exportTableToCSV('rechargeTable', 'recharge.csv')">
                    <i class="fas fa-download"></i> Export Recharge
                </button>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="rechargeTable">
                        <thead class="table-secondary">
                            <tr>
                                <th>ID</th><th>Amount</th><th>Unit</th><th>Remaining</th><th>Date</th><th>Action</th>
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
                                        <td>
                                            <a href="update_recharge.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
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
                <button class="btn btn-secondary mb-3" onclick="exportTableToCSV('transactionTable', 'transactions.csv')">
                    <i class="fas fa-download"></i> Export Transactions
                </button>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="transactionTable">
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
            <!-- Add under any section -->
            <div id="powerStatus" class="form-section">
                <h4 class="section-header"><i class="fas fa-battery-three-quarters"></i> Tenant Power Usage</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Tenant ID</th><th>Current Power (kWh)</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    $res = $conn->query("SELECT t.name, p.tenant_id, p.current_kw, p.status FROM tenant_power p JOIN tenants t ON t.id = p.tenant_id WHERE t.landlord_id = $landlord_id");
                    while ($row = $res->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['tenant_id']) ?> - <?= htmlspecialchars($row['name']) ?></td>
                        <td><?= round($row['current_kw'], 2) ?></td>
                        <td><span class="badge bg-<?= $row['status'] === 'connected' ? 'success' : 'danger' ?>"><?= ucfirst($row['status']) ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Comments -->
            <div id="Comments" class="form-section">
                <h4 class="section-header"><i class="fas fa-comments"></i> Comments from Tenants</h4>
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
            // Function to show/hide sections
            function showSection(id) {
                document.querySelectorAll('.form-section').forEach(sec => {
                    sec.style.display = 'none';
                });
                const section = document.getElementById(id.substring(1)); // Remove # from id
                if (section) {
                    section.style.display = 'block';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }

            // Show welcome section on load
            showSection('welcome');

            // Handle navigation clicks
            document.querySelectorAll('.sidebar a, .navbar a').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href.startsWith("#")) {
                        e.preventDefault();
                        showSection(href);
                    }
                });
            });

            // Handle form submission for distribute power
            // document.getElementById('distributeForm').addEventListener('submit', function(e) {
            //     e.preventDefault();
            //     const form = e.target;
            //     const formData = new FormData(form);

            //     fetch(form.action, {
            //         method: 'POST',
            //         body: formData
            //     })
            //     .then(response => response.text())
            //     .then(data => {
            //         // Show success message
            //         alert('Power distributed successfully!');
            //         // Reload the page to see changes
            //         window.location.reload();
            //     })
            //     .catch(error => {
            //         console.error('Error:', error);
            //         alert('An error occurred while distributing power.');
            //     });
            // });
document.getElementById('distributeForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    // Clear previous messages
    const oldMessage = document.getElementById('distribute-status-message');
    if (oldMessage) oldMessage.remove();

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Network response was not OK");
        }
        return response.json();
    })
    .then(data => {
        const messageDiv = document.createElement('div');
        messageDiv.id = 'distribute-status-message';
        messageDiv.innerHTML = data.html;
        document.getElementById('distributed').prepend(messageDiv);

        if (data.success) {
            form.reset();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const messageDiv = document.createElement('div');
        messageDiv.id = 'distribute-status-message';
        messageDiv.innerHTML = `
            <div class="alert alert-danger">
                <strong>❌ Server error:</strong> Unable to process the request. Please try again.
            </div>`;
        document.getElementById('distributed').prepend(messageDiv);
    });
});


        });

        // Function to add more tenant charge fields
        function addTenantCharge() {
            const container = document.getElementById('tenant-charge-container');
            const div = document.createElement('div');
            div.innerHTML = `
                <label>Tenant:</label>
                <select name="tenant_ids[]" class="form-control" required>
                    <option value="">-- Select Tenant --</option>
                    <?php foreach ($tenants as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (ID <?= $t['id'] ?>)</option>
                    <?php endforeach; ?>
                </select>
                <label>Charge (RWF):</label>
                <input type="number" name="charges[]" step="0.01" class="form-control" required>
                <button type="button" class="btn btn-danger btn-sm remove-btn mt-2" onclick="this.parentNode.remove()">
                    <i class="fas fa-trash"></i> Remove
                </button>
            `;
            container.appendChild(div);
        }

        // Export to CSV function
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