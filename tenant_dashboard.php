<?php
// tenant_dashboard.php
include 'database.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];
$tenant = $conn->query("SELECT * FROM tenants WHERE id = $tenant_id")->fetch_assoc();
$result = $conn->query("SELECT SUM(charge) AS total FROM transactions WHERE tenant_id = $tenant_id");
$row = $result->fetch_assoc();
$balance = $row['total'] ?? 0; // use 0 if NULL

$transactions = $conn->query("SELECT * FROM transactions WHERE tenant_id = $tenant_id ORDER BY created_at DESC");
$kwh = $conn->query("SELECT current_kw FROM tenant_power WHERE tenant_id = $tenant_id")->fetch_assoc()['current_kw'];
$message = '';
$active_section = 'welcome'; // default section

if (isset($_POST['submit_comment'])) {
    $comment = $conn->real_escape_string($_POST['comment']);
    $query = "INSERT INTO comments (tenant_id, comment) VALUES ($tenant_id, '$comment')";
    $conn->query($query);

    if ($conn->error) {
        $message = "<div class='alert alert-danger'>❌ Error: " . $conn->error . "</div>";
    } else {
        $message = "<div class='alert alert-success'>✅ Comment submitted successfully!</div>";
    }

    $active_section = 'addLandlord'; // stay on comment section
}

$comments = $conn->query("SELECT * FROM comments WHERE tenant_id = $tenant_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tenant Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" type="text/css" href="css/tenantstyle.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top bg-dark px-3">
  <div class="container-fluid d-flex justify-content-between align-items-center w-100">
    <div class="flex-grow-1 text-center">
      <h1 class="text-white m-0">Smart Electricity Management System</h1>
    </div>
    <a href="tenant_profile.php" class="nav-link text-warning ms-3">
      <i class="fas fa-user-circle" style="font-size: 20px; color: #4f46e5;"></i> Profile 
    </a>
    <a href="logout.php" class="nav-link text-warning ms-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</nav>

<!-- Sidebar -->
<div class="sidebar d-none d-lg-block">
  <div class="d-flex flex-column p-3" style="height:80vh;">
    <h4 class="text-white mb-4"><i class="fas fa-user-shield me-2"></i> Welcome, <?php echo $tenant['name']; ?> </h4>
    <a href="#" onclick="showSection('welcome')"><i class="fas fa-user-tie"></i> Your Status</a>
    <a href="#" onclick="showSection('landlord')"><i class="fas fa-history"></i> Transaction History</a>
    <a href="#" onclick="showSection('addLandlord')"><i class="fas fa-comments"></i> Leave a Comment</a>
  
    
  </div>
</div>

<!-- Main Content -->
<div class="content">
  <div class="dashboard">
    <?php echo $message; ?>

    <!-- Welcome Section -->
    <div id="welcome">
      <h2 class="text-center mb-4">Welcome, <?php echo $tenant['name']; ?>!</h2>
      <p><strong>Phone:</strong> <?php echo $tenant['phone']; ?></p>
      <p><strong>House Number:</strong> <?php echo $tenant['house_number']; ?></p>
      <h4 class="mt-4">Current Balance: <span class="text-success"><?php echo $balance; ?> RWF</span></h4>
       <h4 class="mt-4">Current kwh: <span class="text-success"><?php echo $kwh; ?> kw/h</span></h4>
    </div>

    <!-- Leave Comment Section -->
    <div id="addLandlord" class="form-section">
      <h5>Leave a Comment</h5>
      <form action="" method="POST">
        <input type="hidden" name="section" value="addLandlord">
        <div class="mb-3">
          <textarea name="comment" class="form-control" rows="3" placeholder="Write your comment here..." required></textarea>
        </div>
        <button type="submit" name="submit_comment" class="btn btn-primary">Submit</button>
      </form>
    </div>

    <!-- Comments Section -->
    <div id="landlords" class="form-section">
      <h5 class="mt-4">Your Comments</h5>
      <ul class="list-group">
        <?php while($row = $comments->fetch_assoc()): ?>
          <li class="list-group-item">
            <strong><?php echo $row['created_at']; ?>:</strong>
            <?php echo htmlspecialchars($row['comment']); ?>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <!-- Transaction History Section -->
    <div id="landlord" class="form-section">
      <h5 class="mt-4">Transaction History</h5>
        <button class="export-button" onclick="exportTableToCSV('TransactionHistoryTable', 'TransactionHistory.csv')">Export Transaction History</button>
      <table class="table table-bordered" id="TransactionHistoryTable">
        <thead>
          <tr>
            <th>Date</th>
            <th>Amount (RWF)</th>
            <th>kWh</th>
          </tr>
        </thead>
        <tbody>
          <?php $transactions = $conn->query("SELECT * FROM transactions WHERE tenant_id = $tenant_id ORDER BY created_at DESC"); ?>
          <?php while($row = $transactions->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['created_at']; ?></td>
              <td><?php echo $row['charge']; ?></td>
              <td><?php echo $row['kw']; ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
  function showSection(id) {
    const sections = document.querySelectorAll('.form-section, #welcome');
    sections.forEach(section => section.style.display = 'none');
    const target = document.getElementById(id);
    if (target) {
      target.style.display = 'block';
    }
  }

  window.onload = () => {
    const active = "<?php echo $active_section; ?>";
    showSection(active);
  };
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
