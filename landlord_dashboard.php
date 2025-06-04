
<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

include 'database.php';
$landlord_id = $_SESSION['user_id'];
$tenant = $conn->query("SELECT * FROM landlords WHERE id = $landlord_id")->fetch_assoc();
$tenants = $conn->query("SELECT t.id, t.name, t.phone, t.house_number, c.balance FROM tenants t JOIN cashpower c ON t.id = c.tenant_id WHERE t.landlord_id = $landlord_id");
$transactions = $conn->query("SELECT * FROM transactions");
$landlords = $conn->query("SELECT id, name, phone, address FROM landlords");
$cashpower = $conn->query("SELECT * FROM cashpower");
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

<style>
 body {
    background-color:rgb(247, 249, 251);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    width: 220px;
    padding-top: 56px;
    background-color:rgb(7, 33, 46);
    color: #fff;
    overflow-y: auto;
    transition: width 0.3s;
    z-index: 1020;
}
.sidebar a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.2s;
}
.sidebar a:hover {
    background-color:rgb(7, 33, 46);;
    text-decoration: none;
}
.sidebar a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}
.content {
    margin-left: 220px;
    padding: 20px;
    margin-top: 56px;
    transition: margin-left 0.3s;
}
@media (max-width: 992px) {
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        padding-top: 0;
    }
    .content {
        margin-left: 0;
        margin-top: 20px;
    }
}
.section-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    border-left: rgb(7, 33, 46);;
    padding-left: 10px;
    font-weight: 600;
    font-size: 1.25rem;
}
.section-header i {
    margin-right: 10px;
    color:rgb(7, 33, 46);;
}
.form-section {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}
.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}
/* 🔒 HIDE ALL SECTIONS BY DEFAULT */
.form-section,
#welcome {
    display: none;
}
.navbar{ 
    background-color:rgb(7, 33, 46);
}
h1 {
  text-align: center;
}
/* 🔒 Hide all sections by default */
.form-section {
    display: none;
}

/* ✅ Show welcome and comment sections on load */
#welcome,
#comment {
    display: block !important;
}
.rectangle-boxes {
    display: flex;
    flex-wrap: nowrap; /* no wrapping */
    gap: 20px;
    margin-bottom: 30px;
    /* remove overflow-x if no scroll wanted */
    overflow-x: hidden;
}

.rectangle-box {
    flex: 1 1 0;
    background-color: #ffffff;
    border-left: 5px solid rgb(7, 33, 46);
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    padding: 20px;
    border-radius: 8px;
    font-size: 1.1rem;
    min-width: 0;
}
.rectangle-boxes > .rectangle-box:nth-child(1) {
    background-color: #f0a500; /* orange */
}
.rectangle-boxes > .rectangle-box:nth-child(2) {
    background-color: #00a8cc; /* blue */
}
.rectangle-boxes > .rectangle-box:nth-child(3) {
    background-color: #9b59b6; /* purple */
}
.rectangle-boxes > .rectangle-box:nth-child(4) {
    background-color: #e74c3c; /* red */
}



</style>


 



</style>
</head>
<body>


<nav class="navbar navbar-expand-lg fixed-top bg-dark px-3">
  <div class="container-fluid d-flex justify-content-between align-items-center w-100">
    <div class="flex-grow-1 text-center">
      <h1 class="text-white m-0">Smart Electricity Management System</h1>
    </div>
    <a href="logout.php" class="nav-link text-warning ms-3">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>
</nav>



<!-- Sidebar -->
<div class="sidebar d-none d-lg-block">
  <div class="d-flex flex-column p-3" style="height:80vh;">
    <h4 class="text-white mb-4"><i class="fas fa-user-shield me-2"></i> Welcome, <?php echo $tenant['name']; ?> </h4>
    <a href="#landlords"><i class="fas fa-users-cog"></i> View info</a>
    <a href="#addTenant"><i class="fas fa-user-plus"></i> Add Tenant</a>
    <a href="#Tenantstatus"><i class="fas fa-user"></i> Tenant status</a>
    <a href="#recharge"><i class="fas fa-bolt"></i> Recharge</a>
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
                $count = $conn->query("SELECT COUNT(*) AS total_active FROM cashpower WHERE balance > 0")->fetch_assoc();
                    
                $counts = $conn->query("SELECT COUNT(*) AS total_inactive FROM cashpower WHERE balance = 0")->fetch_assoc();
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
            <h3>See The  Tenant Status  Here</h3> 
            <table class="table table-bordered table-hover align-middle mb-0">
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

    
    <!-- Tenants -->
    <div id="tenants" class="form-section">
        <h4 class="section-header"><i class="fas fa-list"></i> Tenant List</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th><th>Name</th><th>Phone</th><th>House</th><th>Balance (RWF)</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $tenants->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['house_number']); ?></td>
                            <td><?php echo number_format($row['balance'], 0); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editTenant(
                                '<?php echo $row['id']; ?>',
                                '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['phone'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['house_number'], ENT_QUOTES); ?>'
                            )">
                                <i class="fas fa-edit"></i></button>
                                
                            <a href="delete_tenant.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this tenant?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                   
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transactions -->
    <div id="transactions" class="form-section">
        <h4 class="section-header"><i class="fas fa-exchange-alt"></i> Recharge Transactions</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
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

 
<!-- Edit Landlord Modal -->
<div class="modal fade" id="editLandlordModal" tabindex="-1" aria-labelledby="editLandlordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="update_landlord.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editLandlordModalLabel"><i class="fas fa-user-edit"></i> Edit Landlord</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="id" id="editLandlordId" />
          <div class="mb-3">
            <label for="editLandlordName" class="form-label">Name</label>
            <input type="text" name="name" id="editLandlordName" class="form-control" required />
          </div>
          <div class="mb-3">
            <label for="editLandlordPhone" class="form-label">Phone</label>
            <input type="text" name="phone" id="editLandlordPhone" class="form-control" required />
          </div>
          <div class="mb-3">
            <label for="editLandlordAddress" class="form-label">Address</label>
            <input type="text" name="address" id="editLandlordAddress" class="form-control" required />
          </div>
          <div class="mb-3">
            <label for="editLandlordPassword" class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" name="password" id="editLandlordPassword" class="form-control" />
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>


<!-- Edit Tenant Modal -->
<div class="modal fade" id="editTenantModal" tabindex="-1" aria-labelledby="editTenantModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="update_tenant.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editTenantModalLabel"><i class="fas fa-user-edit"></i> Edit Tenant</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="id" id="editTenantId" />
          <div class="mb-3">
            <label for="editTenantName" class="form-label">Name</label>
            <input type="text" name="name" id="editTenantName" class="form-control" required />
          </div>
          <div class="mb-3">
            <label for="editTenantPhone" class="form-label">Phone</label>
            <input type="text" name="phone" id="editTenantPhone" class="form-control" required />
          </div>
          <div class="mb-3">
            <label for="editTenantHouse" class="form-label">House Number</label>
            <input type="text" name="house_number" id="editTenantHouse" class="form-control" required />
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
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

</body>
</html>
