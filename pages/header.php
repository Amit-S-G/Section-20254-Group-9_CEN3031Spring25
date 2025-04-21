<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
    include("../database.php");

    $habitatName = "default";

    $stmtHabitat = $conn->prepare("SELECT item_name FROM inventories WHERE user_id = ? AND item_type = 'habitat' AND is_selected = 1");
    $stmtHabitat->bind_param("i", $_SESSION['user_id']);
    $stmtHabitat->execute();
    $result = $stmtHabitat->get_result();
    if ($row = $result->fetch_assoc()) {
        $habitatName = $row['item_name'];
        $habitatName = strtolower(trim($habitatName));
    }
    $stmtHabitat->close();
?>

<?php
    // Get current page name
    $page = basename($_SERVER['PHP_SELF']); 

    // Set default audio
    $audioFile = "../audio/ambient_rain.mp3";

    // Change audio based on the page
    if ($page == "dashboard.php") {
        $audioFile = "../audio/ambient_rain.mp3";
        $audioVolume = 0.1;
    } elseif ($page == "profile.php") {
        $audioFile = "../audio/ambient_rain.mp3";
        $audioVolume = 0.1;
    } elseif ($page == "friends.php") {
        $audioFile = "../audio/ambient_rain.mp3";
        $audioVolume = 0.1;
    } elseif ($page == "habitat.php") {
        switch (strtolower($habitatName)) {
            case "sapphire springs":
                $audioFile = "../audio/waterfall_loop.mp3";
                $audioVolume = 0.1;
                break;
            case "pleasant grove":
                $audioFile = "../audio/ambient_nature.mp3";
                $audioVolume = 0.8;
                break;
            case "divine waterfall":
                $audioFile = "../audio/celestial_choir.mp3";
                $audioVolume = 0.1;
                break;
            default:
                $audioFile = "../audio/ambient_rain.mp3";
                $audioVolume = 0.08;
                break;
        }
    } elseif ($page == "shop.php") {
        $audioFile = "../audio/relaxing_jingle.mp3";
        $audioVolume = 0.05;
    }
?>

<?php
date_default_timezone_set('America/New_York');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Taskémon</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="../styles/page_header.css">
        <link rel="icon" href= "../img/allyLogo.png" />
    </head>
    <body>
        <header class="site-header">
            <!-- Hamburger Menu -->
            <div class="hamburger-container">
                <div id="hamburger" onclick="toggleMenu()">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <nav class="dropdown-menu" id="menu">
                    <ul>
                        <li><a href="dashboard.php"><img id="dashboard-icon" src="../img/icons/hamburger_icons/dashboard_icon.png" alt="Dashboard Icon">Dashboard</a></li>
                        <li><a href="profile.php"><img id="profile-icon" src="../img/icons/hamburger_icons/profile_icon.png" alt="Profile Icon">Profile</a></li>
                        <li><a href="friends.php"><img id="friends-icon" src="../img/icons/hamburger_icons/friends_icon.png" alt="Friends Icon">Friends</a></li>
                        <li><a href="habitat.php"><img id="habitat-icon" src="../img/icons/hamburger_icons/habitat_icon.png" alt="Habitat Icon">Habitat</a></li>
                        <li><a href="shop.php"><img id="shop-icon" src="../img/icons/hamburger_icons/shop_icon.png" alt="Shop Icon">Shop</a></li>
                        <li><a href="#"><img id="settings-icon" src="../img/icons/hamburger_icons/settings_icon.png" alt="Settings Icon">Settings</a></li>
                        <li><a href="#"><img id="logout-icon" src="../img/icons/hamburger_icons/logout_icon.png" alt="Logout Icon">Logout</a></li>
                    </ul>
                </nav>
            </div>

            <!-- Sounds -->
            <audio id="sound" loop muted>
                <source src="<?php echo $audioFile; ?>" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>

            <button class="sound-button" onclick="toggleMute();">
                <img id="muteIcon" src="../img/icons/sound_icons/speaker_muted.png" alt="Sound Icon">
            </button>
        </header>
        <script>
            const audioVolume = <?php echo $audioVolume; ?>;
        </script>
        <script src="../js/page_header.js"></script>
    </body>
</html>
