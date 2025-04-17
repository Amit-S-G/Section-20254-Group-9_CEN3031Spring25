<?php
session_start();
include("../database.php");

// Redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

?>

<!-- Hamburger and Sound -->
<?php
 include("header.php")
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <link rel="stylesheet" href="../styles/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

    <div class="profile-content">
        <h1>Your Profile</h1>
        <div class=input-container>
            <div class="profile-section">
                <label>Profile Picture:</label><br>
            </div>

            <div class="profile-section">
                <label>Display Name:</label>
                <div class="input-container">
                    <input type="text" name="display_name" placeholder="Display Name">
                </div>
            </div>


            <div class="profile-section">
                <label>Bio:</label>
                <div class="input-container">
                    <textarea name="bio" placeholder="Tell Us About Yourself"></textarea>
                </div>
            </div>

            <input type="submit">
        </div>

        <h2>Friends Online</h2>
        <ul>
        </ul>
    </div>

</body>
</html>