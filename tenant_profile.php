<?php
session_start();
require_once 'database.php';

// Check if user is logged in as tenant
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];

// Fetch tenant details
$stmt = $conn->prepare("SELECT name, phone, house_number FROM tenants WHERE id = ?");
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Tenant not found.";
    exit();
}

$tenant = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Tenant Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-container {
            max-width: 450px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .profile-title {
            margin-bottom: 25px;
            text-align: center;
        }
        .btn-reset {
            background-color: #f39c12;
            border: none;
        }
        .btn-reset:hover {
            background-color: #d48806;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h3 class="profile-title">Tenant Profile</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars($tenant['name']); ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($tenant['phone']); ?></p>
        <p><strong>House Number:</strong> <?= htmlspecialchars($tenant['house_number']); ?></p>

        <a href="tenant_reset_password.php" class="btn btn-reset w-100 my-3">Reset Password</a>
        <a href="tenant_dashboard.php" class="btn btn-primary w-100">Back to Dashboard</a>
    </div>
</body>
</html>
