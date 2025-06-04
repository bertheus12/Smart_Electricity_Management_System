<?php
session_start();
include 'database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if form data is submitted
if (!isset($_POST['user_type'], $_POST['phone'], $_POST['password'])) {
    echo "<script>alert('All fields are required.'); window.location='login.php';</script>";
    exit();
}

$user_type = $_POST['user_type'];
$phone = trim($_POST['phone']);
$password = trim($_POST['password']);

// Validate user type
if ($user_type !== 'landlord' && $user_type !== 'tenant') {
    echo "<script>alert('Invalid user type.'); window.location='login.php';</script>";
    exit();
}

// Choose table
$table = $user_type === 'landlord' ? 'landlords' : 'tenants';

// Get user by phone
$sql = "SELECT * FROM $table WHERE phone = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<script>alert('Database error.'); window.location='login.php';</script>";
    exit();
}

$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // ✅ Verify password
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_type'] = $user_type;
        $_SESSION['user_id'] = $user['id'];
        header("Location: {$user_type}_dashboard.php");
        exit();
    }
}

// If we reach here, login failed
echo "<script>alert('Invalid credentials.'); window.location='login.php';</script>";
exit();
?>


