<?php
include('db.php');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['product_id'])) {
    die(json_encode(["error" => "Missing product_id"]));
}

$productId = intval($_GET['product_id']);
$comments = [];

// Fetch top-level comments
$query = "SELECT c.id, c.comment, c.created_at, u.username 
          FROM comments c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.product_id = ? AND c.parent_id IS NULL
          ORDER BY c.created_at ASC";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die(json_encode(["error" => "SQL Error: " . $conn->error]));
}

$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $comments[$row['id']] = [
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
    if (isset($comments[$row['parent_id']])) {
        $comments[$row['parent_id']]['replies'][] = [
            "username" => $row['username'],
            "comment" => $row['comment'],
            "created_at" => $row['created_at']
        ];
    }
}

// Debugging
error_log("Fetched comments: " . json_encode($comments));

echo json_encode(array_values($comments));
?>
