<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

$recharge_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch existing record
if ($recharge_id > 0) {
    $stmt = $conn->prepare("SELECT amount, unit FROM cashpower WHERE id = ?");
    $stmt->bind_param("i", $recharge_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recharge = $result->fetch_assoc();
    $stmt->close();
} else {
    $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Invalid recharge ID.'];
    header("Location: landlord_dashboard.php#recharge_list");
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $unit = floatval($_POST['unit']);

    if ($amount <= 0 || $unit <= 0) {
        $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Amount and unit must be greater than 0.'];
    } else {
        $price = $amount / $unit;

        $stmt = $conn->prepare("UPDATE cashpower SET amount = ?, unit = ?, price = ? WHERE id = ?");
        $stmt->bind_param("dddi", $amount, $unit, $price, $recharge_id);

        if ($stmt->execute()) {
            $_SESSION['recharge_status'] = ['type' => 'success', 'message' => 'Recharge updated successfully.'];
        } else {
            $_SESSION['recharge_status'] = ['type' => 'danger', 'message' => 'Failed to update recharge.'];
        }

        $stmt->close();
        header("Location: landlord_dashboard.php#recharge_list");
        exit();
    }
}
?>

<!-- HTML Form -->
<h2>Update Recharge</h2>
<form method="POST" style="max-width: 500px; margin: auto;">
    <label>Amount (RWF):</label>
    <input type="number" name="amount" value="<?= $recharge['amount'] ?>" required step="0.01" class="form-control"><br>

    <label>Unit (kWh):</label>
    <input type="number" name="unit" value="<?= $recharge['unit'] ?>" required step="0.01" class="form-control"><br>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="landlord_dashboard.php#recharge_list" class="btn btn-secondary">Cancel</a>
</form>
<style>
/* Page title */
h2 {
    text-align: center;
    margin-top: 30px;
    color: #333;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Form container */
form {
    background-color: #f9f9f9;
    padding: 25px;
    border: 1px solid #ccc;
    border-radius: 10px;
    max-width: 500px;
    margin: 30px auto;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Input fields */
form label {
    font-weight: bold;
    margin-bottom: 6px;
    display: block;
    color: #444;
}

form input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #bbb;
    border-radius: 6px;
    box-sizing: border-box;
    margin-bottom: 20px;
    font-size: 16px;
}

/* Buttons */
form .btn {
    padding: 10px 16px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 6px;
    text-decoration: none;
    margin-right: 10px;
    display: inline-block;
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
}

form .btn-secondary:hover {
    background-color: #5a6268;
}
</style>
