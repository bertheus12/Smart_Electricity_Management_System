<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, address, phone FROM landlords WHERE id = ?");
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();

$landlord = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landlord Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .profile-container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #333333;
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            color: #444;
            margin-bottom: 10px;
        }

        strong {
            color: #111;
        }

        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            font-size: 15px;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #4338ca;
        }

        .link-wrapper {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- <div class="profile-container">
        <h2>Your Profile</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($landlord['name']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($landlord['address']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($landlord['phone']); ?></p>

        <div class="link-wrapper">
            <a class="btn" href="reset_password.php">Reset Password</a>
            <a class="btn" href="landlord_dashboard.php">Dashboard</a>
        </div>
    </div> -->     


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landlord Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a2d9d6a6f3.js" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #f0f2f5;
            padding-top: 80px;
        }
        .profile-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

<!-- Top navbar -->
<nav class="navbar navbar-expand-lg fixed-top bg-dark px-3">
  <div class="container-fluid d-flex justify-content-between align-items-center w-100">
    <div class="flex-grow-1 text-center">
      <h4 class="text-white m-0">Smart Electricity Management System</h4>
    </div>
    <div class="d-flex align-items-center">
      <a href="landlord_profile.php" class="nav-link text-warning">
        <i class="fas fa-user-circle" style="font-size: 20px;"></i> Profile
      </a>
      <a href="logout.php" class="nav-link text-warning ms-3">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>
</nav>

<!-- Profile card -->
<div class="profile-container">
    <div class="profile-header">
        <h3>Landlord Profile</h3>
    </div>
    <p><strong>Name:</strong> <?= htmlspecialchars($landlord['name']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($landlord['address']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($landlord['phone']) ?></p>

    <div class="btn-group mt-4">
        <a href="reset_password.php" class="btn btn-outline-primary w-100 me-2">Reset Password</a>
        <a href="landlord_dashboard.php" class="btn btn-outline-secondary w-100">Back to Dashboard</a>
    </div>
</div>

</body>
</html>
