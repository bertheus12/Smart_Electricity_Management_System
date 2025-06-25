<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

$tenant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch existing tenant data
if ($tenant_id > 0) {
    $stmt = $conn->prepare("SELECT name, phone, house_number FROM tenants WHERE id = ?");
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tenant = $result->fetch_assoc();
    $stmt->close();
} else {
    $_SESSION['tenant_status'] = ['type' => 'danger', 'message' => 'Invalid tenant ID.'];
    header("Location: landlord_dashboard.php#tenants");
    exit();
}

// Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $house_number = trim($_POST['house_number']);

    if (empty($name) || empty($phone) || empty($house_number)) {
        $_SESSION['tenant_status'] = ['type' => 'danger', 'message' => 'All fields are required.'];
    } else {
        $stmt = $conn->prepare("UPDATE tenants SET name = ?, phone = ?, house_number = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $house_number, $tenant_id);

        if ($stmt->execute()) {
            $_SESSION['tenant_status'] = ['type' => 'success', 'message' => 'Tenant updated successfully.'];
        } else {
            $_SESSION['tenant_status'] = ['type' => 'danger', 'message' => 'Failed to update tenant.'];
        }

        $stmt->close();
        header("Location: landlord_dashboard.php#tenants");
        exit();
    }
}
?>
<style>
/* Container */
form {
    background-color: #f9f9f9;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
    margin: 30px auto;
    max-width: 500px;
    font-family: Arial, sans-serif;
}

/* Headline */
h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 22px;
    color: #333;
}

/* Labels */
form label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
    color: #444;
}

/* Inputs */
form input[type="text"],
form input[type="tel"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #bbb;
    border-radius: 5px;
    margin-bottom: 15px;
    box-sizing: border-box;
}

/* Buttons */
form .btn {
    display: inline-block;
    padding: 10px 18px;
    margin-top: 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
    text-align: center;
    transition: 0.3s ease;
}

form .btn-primary {
    background-color: #007bff;
    color: white;
    border: none;
}

form .btn-primary:hover {
    background-color: #0056b3;
}

form .btn-secondary {
    background-color: #6c757d;
    color: white;
    border: none;
    margin-left: 10px;
}

form .btn-secondary:hover {
    background-color: #5a6268;
}
</style>

<!-- HTML Form -->
<h2>Edit Tenant</h2>
<form method="POST" style="max-width: 500px;">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($tenant['name']) ?>" required class="form-control"><br>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($tenant['phone']) ?>" required class="form-control"><br>

    <label>House Number:</label>
    <input type="text" name="house_number" value="<?= htmlspecialchars($tenant['house_number']) ?>" required class="form-control"><br>

    <button type="submit" class="btn btn-primary">Update Tenant</button>
    <a href="landlord_dashboard.php#tenants" class="btn btn-secondary">Cancel</a>
</form>
