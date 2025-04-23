<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}

include("../database.php"); // Database connection

$reset_message = "";

// Handle password reset form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reset_password"])) {
    $username = $_SESSION["username"];
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Fetch current password hash from DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE user = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    $stmt->fetch();
    $stmt->close();

    // Validate current password
    if (!password_verify($current_password, $password_hash)) {
        $reset_message = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $reset_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $reset_message = "New password must be at least 8 characters.";
    } else {
        // Hash new password and update DB
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user = ?");
        $stmt->bind_param("ss", $new_hash, $username);
        if ($stmt->execute()) {
            $reset_message = "Password updated successfully!";
        } else {
            $reset_message = "Error updating password. Please try again.";
        }
        $stmt->close();
    }
}

include("header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="../styles/settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="settings-container">
        <h2>Account Settings</h2>
        <h3>Reset Password</h3>
        <form method="POST" action="settings.php" class="password-reset-form">
            <label for="current_password">Current Password:</label>
            <input type="password" name="current_password" id="current_password" required><br>

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required><br>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required><br>

            <button type="submit" name="reset_password">Reset Password</button>
        </form>
        <?php if (!empty($reset_message)): ?>
            <p class="reset-message"><?= htmlspecialchars($reset_message) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close(); // Close DB connection
?>
