<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all products
$sql = "SELECT p.id, p.item, p.price, p.address, p.details, p.photo, u.username 
        FROM products p 
        JOIN users u ON p.username = u.username
        ORDER BY p.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="admin-content">
            <h1>Manage Products</h1>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Address</th>
                    <th>Details</th>
                    <th>Photo</th>
                    <th>Posted By</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['item']); ?></td>
                    <td>₱<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['details']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($row['photo']); ?>" width="100" height="100" alt="Product Image"></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>
                        <a href="delete_product.php?id=<?php echo $row['id']; ?>" style="color: red;">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <style>
        .admin-container {
            display: flex;
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


        .admin-content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
    </style>
</body>
</html>
