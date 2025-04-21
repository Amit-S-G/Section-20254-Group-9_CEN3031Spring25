<?php

session_start();

// Redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}

include("../database.php"); // Includes database script
include("header.php");

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; // fallback value
$display_name = $username;


// Send friend request
// We want to verify that the person we're sending to exists, 
// then add them as a frienship with pending status
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $friend_name = trim($_POST["friendName"]);
    if (empty($friend_name)) {
        echo "<div class='error-message'>You must enter a username</div>";
    } else {
        // Check if Friend Exists
        $stmt = $conn->prepare("SELECT user FROM users WHERE user = ?");
        $stmt->bind_param("s", $friend_name);
        $stmt->execute();
        $result = $stmt->get_result();
        // If the buddy exists
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Create new friendship
            $stmt = $conn->prepare("INSERT INTO friendship (usersname, friend_username, status) VALUES (?, ?, ?)");
            $status = 'pending';
            $stmt->bind_param("sss", $username, $friend_name, $status);
            $stmt->execute();
            $_SESSION['message'] = "<div class='success-message'>Request Sent!</div>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['message'] = "<div class='error-message'>Invalid Username.</div>";
            header("Location: " . $_SERVER['PHP_SELF']); //Prevents request being sent again everytime page is reloaded
            exit();
        }
        $stmt->close();
    }
}

// Fetch friends and friend requests
$stmt = $conn->prepare("SELECT friend_username FROM friends WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_friends = $stmt->get_result();
// All friendships i.e., pending friendships
$stmt = $conn->prepare("SELECT friend_username FROM friendship WHERE usersname = ?");
$stmt->bind_param("i", $username);
$stmt->execute();
$result_friendships = $stmt->get_result();
$conn->close(); // Close DB connection

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends</title>
    <link rel="stylesheet" href="../styles/friends.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <?php
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']); //clears the message after the message is sent once
        }
    ?>

    <div class = "friend-wrapper">
        <div class = "friends_panel">
            <h3> Friends </h3>
            <h4><i class="fas fa-user-friends"></i> Friends </h4>
            <div class = "friendship_container">
                <?php while ($row = $result_friends->fetch_assoc()): ?>
                    <p class = "friends-list-container"><?= htmlspecialchars($row['friend_username']) ?></p>
                <?php endwhile; ?>
            </div>

            <h4> <i class="fas fa-circle-notch fa-spin"></i> Pending Friend Requests </h4>
            <div class = "pending-friendship_container">
                <?php while ($row = $result_friendships->fetch_assoc()): ?>
                    <p class = "friends-list-container"><?= htmlspecialchars($row['friend_username']) ?></p>
                <?php endwhile; ?>
            </div>
        </div>

        <div class = "request_panel">
            <h3> Send Friend Request </h3>
            <form action = "<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
                <div class = "input-container">
                    <i class = "fas fa-user"></i>
                    <input type = "text" name = "friendName" placeholder="Friend's Username" required>
                </div>
                <input type = "submit" name = "send-request" value = "Send Request">
            </form>
        </div>
    </div>
</body>
<script>
    setTimeout(() => {
        document.querySelectorAll('.success-message, .error-message').forEach(msg => {
            msg.style.transition = "opacity 0.8s ease, transform 0.8s ease";
            msg.style.opacity = "0";
            msg.style.transform = "translate(-50%, -40%)";
            setTimeout(() => msg.remove(), 800);
        });
    }, 1000);
</script>
</html>