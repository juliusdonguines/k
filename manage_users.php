<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all users
$sql = "SELECT id, username, email, status FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
            <h1>Manage Users</h1>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td>
                        <?php if ($row['status'] == 'active') { ?>
                            <a href="toggle_user_status.php?id=<?php echo $row['id']; ?>&action=deactivate" style="color: red;">Deactivate</a>
                        <?php } else { ?>
                            <a href="toggle_user_status.php?id=<?php echo $row['id']; ?>&action=activate" style="color: green;">Activate</a>
                        <?php } ?>
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
