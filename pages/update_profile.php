<?php
session_start();
include("../database.php");

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get and sanitize form data
$display_name = trim($_POST['display_name']);
$bio = trim($_POST['bio']);
$base_model = $_POST['base_model'] ?? 'default';
$head_accessory = $_POST['head_accessory'] ?? '';
$body_accessory = $_POST['body_accessory'] ?? '';
$background_color = $_POST['background_color'] ?? '#1a1a1a'; // Default fallback color

// Upsert profile info
$stmt = $conn->prepare("
    INSERT INTO user_profiles (user_id, display_name, bio, base_model, head_accessory, body_accessory, background_color)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        display_name = VALUES(display_name),
        bio = VALUES(bio),
        base_model = VALUES(base_model),
        head_accessory = VALUES(head_accessory),
        body_accessory = VALUES(body_accessory),
        background_color = VALUES(background_color)
");

$stmt->bind_param("issssss", $user_id, $display_name, $bio, $base_model, $head_accessory, $body_accessory, $background_color);
$stmt->execute();
$stmt->close();

// Redirect back to profile
header("Location: profile.php");
exit();
?>