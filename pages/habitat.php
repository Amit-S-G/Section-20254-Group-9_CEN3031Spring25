<?php
    session_start();

    // Redirect if not logged in
    if (!isset($_SESSION["username"])) {
        header("Location: ../login.php");
        exit();
    }

    include("../database.php");
    // Initialize default values
    $pet_name = "";
    $hasPet = false;

    // Change the query to retrieve the pet's name instead of just the id.
    $stmtPet = $conn->prepare("SELECT pet_name FROM pets WHERE user_id = ?");
    $stmtPet->bind_param("i", $_SESSION['user_id']);
    $stmtPet->execute();
    $resultPet = $stmtPet->get_result();
    if ($resultPet->num_rows > 0) {
        $row = $resultPet->fetch_assoc();
        $pet_name = $row['pet_name'];
        $hasPet = true;
    }
    $stmtPet->close();
    if ($hasPet): 
        // Determine the image source based on the pet's name (case insensitive)
        $pet_image = "";
        switch (strtolower($pet_name)) {
            case "capybara":
                $pet_image = "../img/pets/Capybara.gif";
                break;
            case "alligator":
                $pet_image = "../img/pets/Alligator.gif";
                break;
            case "axolotl":
                $pet_image = "../img/pets/Axolotl.gif";
                break;
        }

        // Default hunger value
        $pet_hunger = 100;

        // Get the pet hunger value for the logged-in user
        $userId = $_SESSION['user_id'];  
        $stmt = $conn->prepare("SELECT pet_hunger FROM pets WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $pet_hunger = $row['pet_hunger'];
        }
        $stmt->close();
    ?>

    <?php endif;

    
    $conn->close();
?>

<!-- Hamburger and Sound -->
<?php
 include("header.php")
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitat</title>
    <link rel="stylesheet" href="../styles/habitat.css" />
    <link rel="icon" href= "../img/allyLogo.png" />
</head>
<body>
    <div class="container">
            <div class="hunger-bar-container">
                <div class="hunger-bar" style="width: <?php echo $pet_hunger; ?>%;">
                    <span class="hunger-text"><?php echo $pet_hunger; ?></span>
                </div>
            </div>
            <button id = "Feed" class="cta-button">Feed</button>
            <div class="pet-display">
                <img src="<?php echo $pet_image; ?>" alt="<?php echo htmlspecialchars($pet_name); ?>">
            </div>
    </div>
</body>
</html>