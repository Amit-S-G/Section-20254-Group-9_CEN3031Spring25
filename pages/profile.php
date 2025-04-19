<?php
session_start();
include("../database.php");

// Redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get user profile info
$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

// Default values
$display_name = $profile['display_name'] ?? '';
$bio = $profile['bio'] ?? '';
$base_model = $profile['base_model'] ?? 'default';
$head_accessory = $profile['head_accessory'] ?? '';
$body_accessory = $profile['body_accessory'] ?? '';
$background_color = $profile['background_color'] ?? '#1a1a1a';
?>

<?php include("header.php"); ?>

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

    <!-- Circular Avatar -->
    <div class="avatar-circle" style="background-color: <?= htmlspecialchars($background_color) ?>;">
        <div class="avatar-stack">
            <!-- Back of hoodie -->
            <?php if ($body_accessory === 'hoodie'): ?>
                <img src="../img/profile-assets/body/hoodie-back.png" class="layer">
            <?php endif; ?>

            <!-- Face and outline -->
            <img src="../img/profile-assets/base/face.png" class="layer">
            <img src="../img/profile-assets/base/face-outline.png" class="layer">

            <!-- Body accessories -->
            <?php if ($body_accessory === 'hoodie'): ?>
                <img src="../img/profile-assets/body/hoodie.png" class="layer">
            <?php elseif ($body_accessory === 'hawaii-shirt'): ?>
                <img src="../img/profile-assets/body/hawaii-shirt.png" class="layer">
            <?php elseif ($body_accessory === 'collared-shirt'): ?>
                <img src="../img/profile-assets/body/collared-shirt.png" class="layer">
            <?php endif; ?>

            <!-- Head accessories -->
            <?php if ($head_accessory === 'cap'): ?>
                <img src="../img/profile-assets/head/cap.png" class="layer">
            <?php elseif ($head_accessory === 'straw-hat'): ?>
                <img src="../img/profile-assets/head/straw-hat.png" class="layer">
            <?php elseif ($head_accessory === 'sun-glasses'): ?>
                <img src="../img/profile-assets/head/sun-glasses.png" class="layer">
            <?php endif; ?>
        </div>
    </div>

    <!-- Profile Form -->
    <form id="profile-form" action="update_profile.php" method="POST">
        <div class="profile-section">
            <label>Base Model:</label>
            <select name="base_model">
                <option value="default" <?= $base_model === 'default' ? 'selected' : '' ?>>Default</option>
            </select>
        </div>

        <div class="profile-section">
            <label>Head Accessory:</label>
            <select name="head_accessory">
                <option value="" <?= $head_accessory === '' ? 'selected' : '' ?>>None</option>
                <option value="cap" <?= $head_accessory === 'cap' ? 'selected' : '' ?>>Cap</option>
                <option value="sun-glasses" <?= $head_accessory === 'sun-glasses' ? 'selected' : '' ?>>Sun Glasses</option>
                <option value="straw-hat" <?= $head_accessory === 'straw-hat' ? 'selected' : '' ?>>Straw Hat</option>
            </select>
        </div>

        <div class="profile-section">
            <label>Body Accessory:</label>
            <select name="body_accessory">
                <option value="" <?= $body_accessory === '' ? 'selected' : '' ?>>None</option>
                <option value="hoodie" <?= $body_accessory === 'hoodie' ? 'selected' : '' ?>>Hoodie</option>
                <option value="hawaii-shirt" <?= $body_accessory === 'hawaii-shirt' ? 'selected' : '' ?>>Hawaii Shirt</option>
                <option value="collared-shirt" <?= $body_accessory === 'collared-shirt' ? 'selected' : '' ?>>Collared Shirt</option>
            </select>
        </div>

        <div class="profile-section">
            <label>Profile Circle Background:</label>
            <input type="color" name="background_color" value="<?= htmlspecialchars($background_color) ?>">
        </div>

        <div class="profile-section">
            <label>Display Name:</label>
            <input type="text" name="display_name" value="<?= htmlspecialchars($display_name); ?>" placeholder="Display Name">
        </div>

        <div class="profile-section">
            <label>Bio:</label>
            <textarea name="bio" placeholder="Tell us about yourself"><?= htmlspecialchars($bio); ?></textarea>
        </div>

        <input type="submit" value="Save Changes">
    </form>

    <h2>Friends Online</h2>
    <ul>
        <!-- Friends online can go here -->
    </ul>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("profile-form");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch("update_profile.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            console.log("Profile updated via AJAX.");
            // No glow animation applied
        })
        .catch(error => {
            console.error("Error updating profile:", error);
        });
    });
});
</script>

</body>
</html>