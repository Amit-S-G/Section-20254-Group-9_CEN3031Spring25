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
            $stmt = $conn->prepare("INSERT INTO friendships (usersname, friend_username, status) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $friend_name, $status);
            $status = 'pending';
            $stmt->execute();
            echo "<div class='success-message'>Request Sent!</div>";
        } else {
            //echo "<div class='error-message'>Invalid Username.</div>";
        }
        $stmt->close();
    }
}

// Fetch friends and friend requests
$stmt = $conn->prepare("SELECT friend_username FROM friends WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_friends = $stmt->get_result();

// All friendships which the user has sent
$stmt = $conn->prepare("SELECT friend_username FROM friendships WHERE usersname = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result_friendships = $stmt->get_result();

// All friendships this person has received
$stmt = $conn->prepare("SELECT usersname FROM friendships WHERE friend_username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result_friends_pending = $stmt->get_result();

$sum = $result_friends->num_rows + $result_friendships->num_rows + $result_friends_pending->num_rows;

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
    <div class="friends_container">
        <div class="friendships_container">

            <h3>Friends</h3>
            <?php while ($row = $result_friends->fetch_assoc()): ?>
                <div class="friend-box confirmed">
                    <p style="color: black;"><?= htmlspecialchars($row['friend_username']) ?></p>
                    <button class="visit-button">Visit</button>
                </div>
            <?php endwhile; ?>

            <!-- Received Friend Requests -->
            <?php while ($row = $result_friends_pending->fetch_assoc()): ?>
                <div class="friend-box incoming-request">
                    <p style="color: black;"><?= htmlspecialchars($row['usersname']) ?></p>
                    <form method="POST" action="handle_request.php">
                        <input type="hidden" name="requester" value="<?= htmlspecialchars($row['usersname']) ?>">
                        <button name="action" value="accept">Accept</button>
                        <button name="action" value="reject">Reject</button>
                    </form>
                </div>
            <?php endwhile; ?>

            <!-- Sent Friend Requests (Pending) -->
            <?php while ($row = $result_friendships->fetch_assoc()): ?>
                <div class="friend-box pending">
                    <p style="color: black;"><?= htmlspecialchars($row['friend_username'] . ' (Pending)') ?></p>
                </div>
            <?php endwhile; ?>

        </div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="friend_request_container">
                <h3>Send a Friend Request</h3>
                <div class="input-container">
                    <i class="fas fa-user"></i>
                    <input type="text" name="friendName" placeholder="Friend's Username" required>
                </div>
                <input type="submit" name="login" value="Send Request">
        </form>
    </div>
</body>

</html>