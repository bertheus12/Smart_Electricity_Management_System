<?php
include "../database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']);
    $unit = floatval($_POST['unit']);

    if ($unit <= 0) {
        die("Unit must be greater than zero.");
    }

    $price = $amount / $unit;
    $balance = $amount;
    $created_at = date("Y-m-d H:i:s");

    $sql = "INSERT INTO cashpower (amount, unit, price, balance, created_at)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("dddds", $amount, $unit, $price, $balance, $created_at);

    if ($stmt->execute()) {
        echo "Inserted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<form method="POST" action="insert_cashpower.php">
  <label>Amount (RWF):</label>
  <input type="number" name="amount" step="0.01" required><br>

  <label>Unit (kWh):</label>
  <input type="number" name="unit" step="0.01" required><br>

  <button type="submit">Submit</button>
</form>
