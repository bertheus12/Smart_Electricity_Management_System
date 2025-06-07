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
    <div class="profile-container">
        <h2>Your Profile</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($landlord['name']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($landlord['address']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($landlord['phone']); ?></p>

        <div class="link-wrapper">
            <a class="btn" href="reset_password.php">Reset Password</a>
            <a class="btn" href="landlord_dashboard.php">Dashboard</a>
        </div>
    </div>
</body>
</html>
