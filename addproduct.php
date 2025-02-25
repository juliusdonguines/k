<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item = mysqli_real_escape_string($conn, $_POST['item']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);

    // Get first photo as main product photo
    $mainPhoto = !empty($_FILES['photos']['name'][0]) ? 
        'uploads/' . uniqid() . '_' . basename($_FILES['photos']['name'][0]) : 
        'uploads/default.jpg';  // Default photo path if none uploaded

    // Get user_id from username
    $userQuery = "SELECT id FROM users WHERE username = '$username'";
    $userResult = $conn->query($userQuery);
    $user = $userResult->fetch_assoc();
    $user_id = $user['id'];

    // Insert product details with photo and user_id
    $sql = "INSERT INTO products (username, item, price, address, details, photo, user_id) 
            VALUES ('$username', '$item', '$price', '$address', '$details', '$mainPhoto', '$user_id')";

    if ($conn->query($sql) === TRUE) {
        $productId = $conn->insert_id;

        // Handle multiple file uploads
        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                $fileName = basename($_FILES['photos']['name'][$key]);
                $targetDir = "uploads/";
                $targetFile = $targetDir . uniqid() . "_" . $fileName; 

                if (move_uploaded_file($tmp_name, $targetFile)) {
                    // Save file path to database
                    $conn->query("INSERT INTO product_images (product_id, image_path) VALUES ('$productId', '$targetFile')");
                }
            }
        }

        echo "<script>alert('Product added successfully!'); window.location.href='home.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!-- Rest of your HTML remains the same -->