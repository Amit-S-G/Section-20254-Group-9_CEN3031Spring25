<?php

session_start();

// Redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}

include("database.php"); // Includes database script
include("header1.html");

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
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        // If the buddy exists
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Create new friendship
            $stmt = $conn->prepare("INSERT INTO friendships (usersname, friend_name, status) VALUES (?, ?, /)");
            $stmt->bind_param("sss", $username, $friend_name, 'pending');
            $stmt->execute();
            echo "<div class='success-message'>Request Sent!</div>";
        } else {
            echo "<div class='error-message'>Invalid Username.</div>";
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
$stmt = $conn->prepare("SELECT friend_username FROM friendships WHERE usersname = ?");
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
    <title>Login</title>
    <link rel="stylesheet" href="styles/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <div class="friends_container">
        <div class="friendships_container">
            <h3>Your Friends</h3>
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