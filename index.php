<?php
// index.php - Entry point for Smart Electricity Management System
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Electricity Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 80px;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .btn {
            width: 100%;
            margin-bottom: 10px;
            padding: 12px;
        }
        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h1>Smart Electricity Management System</h1>
        <p class="lead">Manage your rental power usage fairly and efficiently.</p>

        <div class="d-grid gap-3 mt-2">
            <a href="login.php" class="btn btn-primary">Login </a>
         
        </div>

        <footer class="mt-5">
            <p>&copy; <?php echo date('Y'); ?> Berthe</p>
        </footer>
    </div>
</body>
</html>
