
<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

include '../database.php';
$landlord_id = $_SESSION['user_id'];
$tenant = $conn->query("SELECT * FROM landlords WHERE id = $landlord_id")->fetch_assoc();
$tenants = $conn->query("SELECT t.id, t.name, t.phone, t.house_number, c.balance FROM tenants t JOIN cashpower c ON t.id = c.tenant_id WHERE t.landlord_id = $landlord_id");
$transactions = $conn->query("SELECT * FROM transactions");
$landlords = $conn->query("SELECT id, name, phone, address FROM landlords");
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
<link rel="stylesheet" type="text/css" href="../css/style.css">
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
    <a href="#Tenantstatus"><i class="fas fa-user"></i> Tenant status</a>
    <a href="recharge.php"><i class="fas fa-bolt"></i> recharge</a>
    <a href="insert_cashpower.php"><i class="fas fa-comments"></i> distributed </a>
    <a href="#tenants"><i class="fas fa-list"></i> Tenants</a>
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
    <?php if (isset($_SESSION['recharge_status'])): ?>
        <div class="alert alert-<?php echo $_SESSION['recharge_status']['type']; ?> alert-dismissible fade show mt-3" role="alert">
            <?php echo $_SESSION['recharge_status']['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['recharge_status']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['tenant_status'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tenant_status']['type']; ?> alert-dismissible fade show mt-3" role="alert">
            <?php echo $_SESSION['tenant_status']['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['tenant_status']); ?>
    <?php endif; ?>

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
        <h4 class="section-header"><i class="fas fa-bolt"></i> Recharge Balance</h4>
        <form method="POST" action="recharge.php" class="row g-3">
            <!-- Tenant ID or dropdown -->
            <div class="col-md-4">
                <input type="number" name="tenant_id" class="form-control" placeholder="Tenant ID" required>
            </div>

            <!-- Amount in RWF -->
            <div class="col-md-4">
                <input type="number" name="charge" class="form-control" placeholder="Amount (RWF)" required step="0.01" min="0">
            </div>

            <!-- Submit -->
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Recharge</button>
            </div>
        </form>
    </div>

         <!-- View status -->
        <div id="Tenantstatus"  class="form-section">
            <h3><i class="fas fa-user"></i>Tenant Status </h3> 
            <button class="export-button" onclick="exportTableToCSV('TenantstatusTable', 'Tenantstatus.csv')">Export Tenantstatus</button>
            <table class="table table-bordered table-hover align-middle mb-0" id="TenantstatusTable" >
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th><th>tenant_id</th><th>balance </th><th>kwh </th><th>status</th>
                    </tr>
                </thead>
                <tbody>
            <?php if ($cashpower && $cashpower->num_rows > 0): ?>
            <?php while ($row = $cashpower->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                
                    <td><?php echo htmlspecialchars($row['tenant_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['balance']); ?></td>
                    <td><?php echo htmlspecialchars($row['kwh']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <?php endif; ?>
            </tbody>
            </table>
        </div>

    <!-- View Landlords -->
<div id="landlords" class="form-section">
    <h4 class="section-header"><i class="fas fa-users-cog"></i> View details</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-secondary">
                <tr>
                    <th>ID</th><th>Name</th><th>Phone</th><th>Address</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
    <?php if ($landlords && $landlords->num_rows > 0): ?>
        <?php while ($row = $landlords->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" 
                        onclick="editLandlord(
                            '<?php echo $row['id']; ?>',
                            '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($row['phone'], ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($row['address'], ENT_QUOTES); ?>'
                        )">
                        <i class="fas fa-edit"></i>
                    </button>
                   
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5" class="text-center">No landlords found.</td></tr>
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
                                <a href="delete_transaction.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this transaction?');">
                                <i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>




 </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function editLandlord(id, name, phone, address) {
    document.getElementById('editLandlordId').value = id;
    document.getElementById('editLandlordName').value = name;
    document.getElementById('editLandlordPhone').value = phone;
    document.getElementById('editLandlordAddress').value = address;
    document.getElementById('editLandlordPassword').value = ''; // clear password field

    const modal = new bootstrap.Modal(document.getElementById('editLandlordModal'));
    modal.show();
}

function editTenant(id, name, phone, house) {
    document.getElementById('editTenantId').value = id;
    document.getElementById('editTenantName').value = name;
    document.getElementById('editTenantPhone').value = phone;
    document.getElementById('editTenantHouse').value = house;

    const modal = new bootstrap.Modal(document.getElementById('editTenantModal'));
    modal.show();
}

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
