<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $status = ['type' => 'danger', 'message' => 'Please fill in both password fields.'];
    } elseif ($new_password !== $confirm_password) {
        $status = ['type' => 'danger', 'message' => 'Passwords do not match.'];
    } else {
        // Update password in DB
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE tenants SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $tenant_id);

        if ($stmt->execute()) {
            $status = ['type' => 'success', 'message' => 'Password updated successfully.'];
        } else {
            $status = ['type' => 'danger', 'message' => 'Failed to update password. Try again.'];
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Reset Tenant Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .reset-container {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-title {
            margin-bottom: 25px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h3 class="form-title">Reset Password</h3>

        <?php if ($status): ?>
            <div class="alert alert-<?= htmlspecialchars($status['type']) ?>" role="alert">
                <?= htmlspecialchars($status['message']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6" />
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6" />
            </div>
            <button type="submit" class="btn btn-warning w-100">Update Password</button>
        </form>

        <a href="tenant_profile.php" class="btn btn-secondary w-100 mt-3">Back to Profile</a>
    </div>
</body>
</html>
