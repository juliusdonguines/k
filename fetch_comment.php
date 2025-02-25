<?php
session_start();
include('db.php');

$productId = null;

// Allow both GET and POST methods
if (isset($_GET['product_id'])) {
    $productId = intval($_GET['product_id']);
} elseif (isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
} else {
    echo json_encode(["status" => "error", "message" => "Missing product_id"]);
    exit;
}

// Ensure product ID is valid
if ($productId <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid product_id"]);
    exit;
}

$comments = [];

// Fetch top-level comments
$query = "SELECT c.id, c.comment, c.created_at, u.username 
          FROM comments c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.product_id = ? AND c.parent_id IS NULL
          ORDER BY c.created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $comments[] = [
        "id" => $row['id'],
        "username" => $row['username'],
        "comment" => $row['comment'],
        "created_at" => $row['created_at'],
        "replies" => []
    ];
}

// Fetch replies
$query = "SELECT c.id, c.comment, c.created_at, u.username, c.parent_id
          FROM comments c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.product_id = ? AND c.parent_id IS NOT NULL
          ORDER BY c.created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    foreach ($comments as &$comment) {
        if ($comment['id'] == $row['parent_id']) {  // Use `==` instead of `===` for integer comparison
            $comment['replies'][] = [
                "username" => $row['username'],
                "comment" => $row['comment'],
                "created_at" => $row['created_at']
            ];
        }
    }
}

// Return the response
echo json_encode([
    "status" => "success",
    "data" => $comments
]);

?>  