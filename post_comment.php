<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to post a comment.");
}

if (isset($_POST['product_id'], $_POST['comment'])) {
    $productId = intval($_POST['product_id']);
    $userId = $_SESSION['user_id'];
    $comment = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES, 'UTF-8');

    if (!empty($comment)) {
        $query = "INSERT INTO comments (product_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $productId, $userId, $comment);

        if ($stmt->execute()) {
            echo "<div class='comment'><b>" . htmlspecialchars($_SESSION['username']) . ":</b> " . 
                 $comment . "<br><small>Just now</small></div>";
        } else {
            echo "Error adding comment.";
        }
    }
}
?>
