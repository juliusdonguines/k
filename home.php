<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$selectedUser = '';

if (isset($_GET['user'])) {
    $selectedUser = mysqli_real_escape_string($conn, $_GET['user']);
    $showChatBox = true;
} else {
    $showChatBox = false;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-time Chat</title>
    <link href="style.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>My Account</h1>
        <a href="logout.php" class="logout">Logout</a>
        <a href="userprofile.php" class="profile">Profile</a>

        <!-- Notification Dropdown -->
        <div class="notification-dropdown"> 
    <button class="notif-btn">🔔 Notifications <span id="notif-count">0</span></button>
    <ul class="notif-list"></ul>
</div>


<style>
.notification-dropdown {
    position: relative;
    display: inline-block;
}

.notif-btn {
    background: none;
    border: none;
    font-weight: bold;
    cursor: pointer;
    color: red;
}

.notif-list {
    display: none;
    position: absolute;
    right: 0;
    background: white;
    list-style: none;
    padding: 0;
    margin: 0;
    border: 1px solid gray;
    width: 250px;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0px 2px 5px rgba(0,0,0,0.2);
}

.notif-list li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.notif-list li:hover {
    background: lightgray;
}
</style>


        <!-- Messages dropdown -->
        <div class="messages-dropdown">
            <button class="messages-btn">Messages ▼</button>
            <ul class="messages-list">
                <?php 
                $sql = "SELECT username FROM users WHERE username != ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $user = ucfirst($row['username']);
                    echo "<li><a href='#' class='select-user' data-user='" . htmlspecialchars($row['username']) . "'>$user</a></li>";
                }
                ?>
            </ul>
        </div>

        <a href="edit_profile.php" class="settings">Settings</a>
    </div>

    <div class="account-info">
        <div class="welcome">
            <h2>Welcome, <?php echo ucfirst($username); ?>!</h2>
        </div>
    </div>


 <!-- Add Product Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Add Product</h2>
        <form action="addproduct.php" method="POST" enctype="multipart/form-data">
        <label for="item">Item Name:</label>
        <input type="text" id="item" name="item" required>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" required>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required>

        <label for="details">More Details:</label>
        <textarea id="details" name="details" rows="4" required></textarea>

        <label>Upload Photos:</label>
        <div id="photo-container">
            <input type="file" name="photos[]" accept="image/*" required>
        </div>
        <button type="button" onclick="addPhotoInput()">+ Add More Photos</button>

        <button type="submit">Post Product</button>
        </form>
    </div>
</div>

<!-- Separate Add Product Button -->
<div class="add-product">
    <button class="btn" onclick="openModal()">Add Product</button>
</div>

<script>
    function openModal() {
        document.getElementById("addProductModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("addProductModal").style.display = "none";
    }

    window.onclick = function(event) {
        var modal = document.getElementById("addProductModal");
        if (event.target == modal) {
            closeModal();
        }
    };
</script>



<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 20px;
        border-radius: 10px;
        width: 40%;
    }
    .close-btn {
        float: right;
        font-size: 24px;
        cursor: pointer;
    }
</style>


    <!-- Chat box (hidden by default) -->
    <div class="chat-box" id="chat-box" style="display: none;">
        <div class="chat-box-header">
            <h2 id="chat-user-name"></h2>
            <?php if (!empty($row['email'])): ?>
  
<?php else: ?>
     <a href="view_profile.php?user=<?php echo urlencode($row['email']); ?>">View Profile</a>
<?php endif; ?>

            <button class="close-btn" onclick="closeChat()">✖</button>
            <li>
</li>



        </div>
        <div class="chat-box-body" id="chat-box-body">
            <!-- Chat messages will be loaded here -->
        </div>
        <form class="chat-form" id="chat-form">
            <input type="hidden" id="sender" value="<?php echo $username; ?>">
            <input type="hidden" id="receiver">
            <input type="text" id="message" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>
    </div>

    <div class="product-list">
        <h2>Preloved Items</h2>
        <table border="1">
        <tr>
    <th>Photo</th>
    <th>Item</th>
    <th>Price</th>
    <th>Address</th>
    <th>Details</th>
    <th>Posted By</th>
    <th>Actions</th>
</tr>

<?php
include('db.php'); 

$sql = "SELECT p.id, p.item, p.price, p.address, p.details, p.photo, p.likes, p.reports, u.username 
        FROM products p 
        JOIN users u ON p.username = u.username
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productId = $row['id'];
        $postedBy = htmlspecialchars($row['username']);
        $reports = $row['reports']; 

        echo "<tr>";
        echo "<td><img src='" . htmlspecialchars($row['photo']) . "' width='100' height='100' alt='Product Image'></td>";
        echo "<td>" . htmlspecialchars($row['item']) . "</td>";
        echo "<td>₱" . number_format($row['price'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
        echo "<td>" . htmlspecialchars($row['details']) . "</td>";
        echo "<td>" . $postedBy . "</td>";
        echo "<td>
            <button class='like-btn' data-id='" . $productId . "'>❤️ <span class='like-count'>" . $row['likes'] . "</span></button>
            <a href='#' class='select-user' data-user='" . $postedBy . "'>💬 Message</a>
            <button class='comment-btn' data-id='" . $productId . "'>💬 Comment</button>
            <button class='view-comments-btn' data-id='" . $productId . "'>👁️ View Comments</button>
            
            <div class='dropdown'>
                <div class='dropdown-content'>
                    <a href='#' class='report-product' data-id='$productId' data-user='$postedBy'>🚩 Report</a>
                </div>
            </div>
  
            <div class='comment-section' id='comment-section-$productId' style='display:none;'>
                <div class='comment-list' id='comment-list-$productId'></div>
                <input type='text' class='comment-input' id='comment-input-$productId' placeholder='Add a comment...'>
                <button class='submit-comment' data-id='$productId'>Post</button>
            </div>
        </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No products added yet.</td></tr>";
}
?>


<script>
    document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".report-product").forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.getAttribute("data-id");
            const username = this.getAttribute("data-user");

            if (confirm("Are you sure you want to report this product?")) {
                fetch("report_product.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `product_id=${productId}&username=${username}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                });
            }
        });
    });
});

</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $(".view-comments-btn").click(function () {
        let productId = $(this).data("id");
        let commentSection = $("#comment-section-" + productId);
        let commentList = $("#comment-list-" + productId);

        // Toggle visibility of the comment section
        commentSection.toggle();

        if (commentSection.is(":visible")) {
            console.log("Fetching comments for product ID:", productId); // Debugging
            $.ajax({
                url: "fetch_comments.php",
                type: "POST",
                data: { product_id: productId },
                success: function (response) {
                    console.log("Response from server:", response); // Debugging
                    commentList.html(response); // Display comments
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching comments:", status, error);
                }
            });
        }
    });
});


    // Posting a new comment
    $(".submit-comment").click(function () {
        let productId = $(this).data("id");
        let commentInput = $("#comment-input-" + productId);
        let commentText = commentInput.val();

        if (commentText.trim() !== "") {
            $.ajax({
                url: "post_comment.php",
                type: "POST",
                data: { product_id: productId, comment: commentText },
                success: function (response) {
                    $("#comment-list-" + productId).append(response); // Append new comment
                    commentInput.val(""); // Clear input field
                }
            });
        }
    });
</script>

        </table>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
$(document).ready(function() {
    $(".comment-btn").click(function() {
        var productId = $(this).data("id");
        var comment = prompt("Enter your comment:");

        if (comment) {
            $.ajax({
                url: "add_comment.php",
                type: "POST",
                data: { product_id: productId, comment: comment },
                success: function(response) {
                    console.log("Server Response:", response); // Debugging
                    if (response.trim() === "success") {
                        alert("Comment added successfully!");
                        location.reload(); // Reload page to show the new comment
                    } else {
                        alert("Error: " + response);
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error:", error);
                }
            });
        }
    });
});
</script>
</body>
</html>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    function fetchNotifications() {
        $.ajax({
            url: "fetch_notifications.php",
            type: "GET",
            success: function(response) {
                console.log("Server Response:", response); // Debugging

                let notifications = JSON.parse(response);
                let notifCount = notifications.length;
                $("#notif-count").text(notifCount > 0 ? notifCount : "");

                let html = "";
                notifications.forEach(function(notif) {
                    html += `<li>${notif.message} <small>${notif.created_at}</small></li>`;
                });

                $(".notif-list").html(html);
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error:", error);
            }
        });
    }

    // Fetch notifications every 5 seconds
    setInterval(fetchNotifications, 5000);
    fetchNotifications();

    // Show dropdown when clicked
    $(".notif-btn").click(function() {
        $(".notif-list").toggle();
    });
});



</script>


<script>
$(document).ready(function() {
    let productId = 1; // Change this dynamically if needed

    function fetchComments() {
        $.ajax({
            url: "fetch_comment.php",
            type: "GET",
            data: { product_id: productId },
            success: function(response) {
                try {
                    let comments = JSON.parse(response);
                    let html = '';

                    comments.forEach(commentData => {
                        html += `<div class='comment'>
                            <strong>${commentData.username}</strong>: ${commentData.comment} <small>${commentData.created_at}</small>
                            <button onclick="replyComment(${commentData.id})">Reply</button>
                            <div id="replies-${commentData.id}" class="replies">`;

                        commentData.replies.forEach(reply => {
                            html += `<div class='reply'>
                                <strong>${reply.username}</strong>: ${reply.comment} <small>${reply.created_at}</small>
                            </div>`;
                        });

                        html += `</div>
                            <textarea id="reply-input-${commentData.id}" style="display:none;"></textarea>
                            <button id="reply-btn-${commentData.id}" onclick="addComment(${commentData.id})" style="display:none;">Post Reply</button>
                        </div>`;
                    });

                    $("#comments-container").html(html);
                } catch (e) {
                    console.error("Error parsing comments:", response, e);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + error);
            }
        });
    }

    fetchComments();
});




</script>

<script>
    function addPhotoInput() {
        var photoContainer = document.getElementById("photo-container");
        var newInput = document.createElement("input");
        newInput.type = "file";
        newInput.name = "photos[]"; // Keeps the input as an array
        newInput.accept = "image/*";
        newInput.required = false; // Only the first input should be required
        photoContainer.appendChild(newInput);
    }
</script>


<script>
    $(document).ready(function() {
        // Toggle messages dropdown
        $(".messages-btn").click(function() {
            $(".messages-list").toggle();
        });

        // Open chat box when user is selected
        $(".select-user").click(function() {
            var selectedUser = $(this).data("user");
            $("#chat-user-name").text(selectedUser);
            $("#receiver").val(selectedUser);
            $("#chat-box").show();
            fetchMessages();
        });

        // Like button functionality
        $(".like-btn").click(function() {
            var button = $(this);
            var productId = button.data("id");

            $.ajax({
                url: "likeproduct.php",
                type: "POST",
                data: { product_id: productId },
                success: function(response) {
                    if (response !== "error") {
                        button.find(".like-count").text(response);
                    } else {
                        alert("Error liking the product.");
                    }
                }
            });
        });

        // Fetch messages
        function fetchMessages() {
            var sender = $("#sender").val();
            var receiver = $("#receiver").val();
            
            $.ajax({
                url: "fetch_messages.php",
                type: "POST",
                data: {sender: sender, receiver: receiver},
                success: function(data) {
                    $("#chat-box-body").html(data);
                    scrollChatToBottom();
                }
            });
        }

        // Scroll chat to bottom
        function scrollChatToBottom() {
            var chatBox = $("#chat-box-body");
            chatBox.scrollTop(chatBox.prop("scrollHeight"));
        }

        // Submit chat form
        $("#chat-form").submit(function(e) {
            e.preventDefault();
            var sender = $("#sender").val();
            var receiver = $("#receiver").val();
            var message = $("#message").val();

            $.ajax({
                url: "submit_message.php",
                type: "POST",
                data: {sender: sender, receiver: receiver, message: message},
                success: function() {
                    $("#message").val('');
                    fetchMessages();
                }
            });
        });

        // Close chat box
        window.closeChat = function() {
            $("#chat-box").hide();
        };
    });
</script>

<script>
    $(document).ready(function() {
    $(".comment-btn").click(function() {
        var productId = $(this).data("id");
        var comment = prompt("Enter your comment:");

        if (comment) {
            $.ajax({
                url: "add_comment.php",
                type: "POST",
                data: { product_id: productId, comment: comment },
                success: function(response) {
                    if (response === "success") {
                        alert("Comment added successfully!");
                    } else {
                        alert("Error adding comment.");
                    }
                }
            });
        }
    });
});

</script>

<style>
.messages-dropdown {
    display: inline-block;
    position: relative;
}

.messages-btn {
    background: none;
    border: none;
    font-weight: bold;
    cursor: pointer;
    color: green;
}

.messages-list {
    display: none;
    position: absolute;
    background: white;
    list-style: none;
    padding: 0;
    margin: 0;
    border: 1px solid gray;
    width: 150px;
}

.messages-list li {
    padding: 10px;
}

.messages-list li a {
    text-decoration: none;
    color: black;
}

.messages-list li:hover {
    background: lightgray;
}
/* Chat Box (Messenger Style) */
.chat-box {
    position: fixed;
    bottom: 10px;
    right: 20px;
    width: 350px;
    max-height: 500px;
    background: white;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
}

.chat-box-header {
    background: #0084ff;
    color: white;
    padding: 10px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-box-header h2 {
    font-size: 16px;
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

.chat-box-body {
    padding: 10px;
    height: 350px;
    overflow-y: auto;
    background: #f1f1f1;
    display: flex;
    flex-direction: column;
}

.chat-box-body .message {
    padding: 8px 12px;
    border-radius: 18px;
    margin-bottom: 5px;
    max-width: 75%;
    word-wrap: break-word;
    font-size: 14px;
}

.chat-box-body .sent {
    align-self: flex-end;
    background: #0084ff;
    color: white;
}

.chat-box-body .received {
    align-self: flex-start;
    background: #e4e6eb;
    color: black;
}

.chat-form {
    display: flex;
    padding: 8px;
    border-top: 1px solid #ddd;
    background: white;
    border-radius: 0 0 10px 10px;
}

.chat-form input {
    flex: 1;
    padding: 8px;
    border: none;
    outline: none;
    border-radius: 5px;
}

.chat-form button {
    background: #0084ff;
    border: none;
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    margin-left: 5px;
    cursor: pointer;
}

</style>

</body>
</html>
