<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($transaction_id > 0) {
    $stmt = $conn->prepare("SELECT tenant_id, charge, kw FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    $stmt->close();
} else {
    $_SESSION['transaction_status'] = ['type' => 'danger', 'message' => 'Invalid transaction ID.'];
    header("Location: landlord_dashboard.php#transactions");
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = intval($_POST['tenant_id']);
    $charge = floatval($_POST['charge']);
    $kw = floatval($_POST['kw']);

    if ($tenant_id && $charge > 0 && $kw > 0) {
        $stmt = $conn->prepare("UPDATE transactions SET tenant_id = ?, charge = ?, kw = ? WHERE id = ?");
        $stmt->bind_param("iddi", $tenant_id, $charge, $kw, $transaction_id);

        if ($stmt->execute()) {
            $_SESSION['transaction_status'] = ['type' => 'success', 'message' => 'Transaction updated successfully.'];
        } else {
            $_SESSION['transaction_status'] = ['type' => 'danger', 'message' => 'Update failed.'];
        }
        $stmt->close();
    } else {
        $_SESSION['transaction_status'] = ['type' => 'danger', 'message' => 'All fields must be valid.'];
    }

    header("Location: landlord_dashboard.php#transactions");
    exit();
}
?>

<!-- HTML Form -->
<h2>Update Transaction</h2>
<form method="POST" style="max-width: 500px; margin: auto;">
    <label>Tenant ID:</label>
    <input type="number" name="tenant_id" value="<?= $transaction['tenant_id'] ?>" required class="form-control"><br>

    <label>Charge (RWF):</label>
    <input type="number" name="charge" value="<?= $transaction['charge'] ?>" step="0.01" required class="form-control"><br>

    <label>kWh:</label>
    <input type="number" name="kw" value="<?= $transaction['kw'] ?>" step="0.01" required class="form-control"><br>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="landlord_dashboard.php#transactions" class="btn btn-secondary">Cancel</a>
</form>
<style>
/* Page heading */
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

/* Label styling */
form label {
    font-weight: bold;
    margin-bottom: 6px;
    display: block;
    color: #444;
}

/* Input fields */
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
    cursor: pointer;
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
