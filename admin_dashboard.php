<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get total users
$userQuery = "SELECT COUNT(*) AS total_users FROM users";
$userResult = $conn->query($userQuery);
$totalUsers = $userResult->fetch_assoc()['total_users'];

// Get total products
$productQuery = "SELECT COUNT(*) AS total_products FROM products";
$productResult = $conn->query($productQuery);
$totalProducts = $productResult->fetch_assoc()['total_products'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        

        <!-- Main Content -->
        <div class="admin-content">
            <h1>Welcome, Admin</h1>
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <p><?php echo $totalUsers; ?></p>
                </div>
                <div class="card">
                    <h3>Total Products</h3>
                    <p><?php echo $totalProducts; ?></p>
                </div>
            </div>
        </div>
    </div>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .admin-container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            height: 100%;
            position: fixed;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 10px;
            border-bottom: 1px solid #34495e;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .sidebar ul li:hover {
            background: #1a252f;
        }

        .admin-content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }

        .dashboard-cards {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            flex: 1;
            text-align: center;
        }
    </style>

</body>
</html>
