<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$message = "";

// Fetch user details, including valid_id
$sql = "SELECT username, email, first_name, last_name, address, barangay, city, phone, profile_picture, valid_id, password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query Error: " . $conn->error);
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>My Profile</h2>
    <?php if ($user): ?>
        <div class="profile-card">
            <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default-avatar.png'; ?>" class="profile-pic" alt="Profile Picture">
            <h3><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h3>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
            <p><strong>Barangay:</strong> <?php echo htmlspecialchars($user['barangay']); ?></p>
            <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>

            <p><strong>Valid ID:</strong> 
                <?php if (!empty($user['valid_id'])): ?>
                    <a href="<?php echo htmlspecialchars($user['valid_id']); ?>" target="_blank">View Valid ID</a>
                <?php else: ?>
                    Not uploaded
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <p>Error: User not found.</p>
    <?php endif; ?>
</div>

<p><a href="edit_profile.php">Edit your profile</a></p>
<p><a href="home.php">Back to home</a></p>
<p><a href="product_history_uploaded.php">Product history upload</a></p>

</body>
</html>
