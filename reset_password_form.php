<?php
session_start();
if (!isset($_SESSION['reset_phone'])) {
    header("Location: forgot_password.php");
    exit();
}
$phone = $_SESSION['reset_phone'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 450px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h4 class="text-center mb-4">Reset Password</h4>
        <form method="POST" action="process_reset_password.php">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" id="new_password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Update Password</button>
        </form>
    </div>
</body>
</html>
