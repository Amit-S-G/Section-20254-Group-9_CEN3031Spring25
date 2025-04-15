<?php
    session_start();

    // Redirect if not logged in
    if (!isset($_SESSION["username"])) {
        header("Location: ../login.php");
        exit();
    }

    include("../database.php");

    //Grabbing and Displaying Habitat information
    $chosenHabitat = " ";
    $backroundImage = "../img/backgrounds/tree_house.png";
    $stmtHabitat = $conn->prepare("SELECT item_name FROM inventories WHERE user_id = ? AND  item_type = 'habitat' AND is_selected = 1");
    $stmtHabitat->bind_param("i", $_SESSION['user_id']);
    $stmtHabitat->execute();
    $resultHabitat = $stmtHabitat->get_result();

    if($resultHabitat->num_rows > 0)
    {
        $habitatRow = $resultHabitat->fetch_assoc();
        $chosenHabitat = $habitatRow['item_name'];
    }
    $stmtHabitat->close();

    if(!empty($chosenHabitat))
    {
        switch(strtolower($chosenHabitat))
        {
            case "pleasant_grove":
                $backgroundImage = "../img/backgrounds/pleasant_grove_rendered.png";
                break;
            case "sapphire_springs":
                $backgroundImage = "../img/backgrounds/sapphire_springs_rendered.png";
                break;
            case "divine_waterfall":
                $backgroundImage = "../img/backgrounds/divine_waterfall_rendered.png";
                break;
            default:
                $backgroundImage = "../img/backgrounds/tree_house.png";
                break;
        }
    }
    
    // Displaying and Grabbing pet information
    // Initialize default values
    $pet_name = "";
    $hasPet = false;
    
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

    //Defining positions for pet image, feed button and hunger bar based on the background iamge chosen
    $pet_position = "left: 150px; top: 450px;";
    $feed_position = "left: 150px; top: 400px;";
    $hunger_position = "left: 150px; top: 390px;";

    if(!empty($chosenHabitat))
    {
        if(strtolower($chosenHabitat) == "pleasant_grove")
        {
            switch(strtolower($pet_name))
            {
                case "capybara":
                    $pet_position = "left: 150px; top: 330px; width: 500px";
                    $feed_position = "left: 510px; top: 340px;";
                    $hunger_position = "left: 405px; top: 285px;";
                    break;
                case "alligator":
                    $pet_position = "left: 150px; top: 280px;";
                    $feed_position = "left: 500px; top: 450px;";
                    $hunger_position = "left: 395px; top: 395px;";
                    break;
                case "axolotl":
                    $pet_position = "left: 150px; top: 340px; width: 550px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                    $feed_position = "left: 500px; top: 460px;";
                    $hunger_position = "left: 395px; top: 405px;";
                    break;
                default:
                    $pet_position = "left: 150px; top: 450px;";
                    $feed_position = "left: 150px; top: 400px;";
                    $hunger_position = "left: 150px; top: 390px;";
                    break;
            }
        }
        elseif(strtolower($chosenHabitat) == "sapphire_springs")
        {
            switch(strtolower($pet_name))
            {
                case "capybara":
                    $pet_position = "left: 250px; top: 200px; width: 500px";
                    $feed_position = "left: 415px; top: 205px";
                    $hunger_position = "left: 310px; top: 150px";
                    break;
                case "alligator":
                    $pet_position = "left: 350px; top: 20px; width: 500px";
                    $feed_position = "left: 350px; top: 150px;";
                    $hunger_position = "left: 245px; top: 95px;";
                    break;
                case "axolotl":
                    $pet_position = "left: 350px; top: 50px; width: 450px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                    $feed_position = "left: 350px; top: 150px;";
                    $hunger_position = "left: 245px; top: 95px;";
                    break;
                default:
                    $pet_position = "left: 150px; top: 450px;";
                    $feed_position = "left: 150px; top: 400px;";
                    $hunger_position = "left: 150px; top: 390px;";
                    break;
            }
        }
        elseif(strtolower($chosenHabitat) == "divine_waterfall")
        {
            switch(strtolower($pet_name))
            {
                case "capybara":
                    $pet_position = "left: -600px; top: 600px; width: 350px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                    $feed_position = "left: 1310px; top: 600px;";
                    $hunger_position = "left: 1200px; top: 540px;";
                    break;
                case "alligator":
                    $pet_position = "left: -580px; top: 640px; width: 350px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                    $feed_position = "left: 1270px; top: 720px;";
                    $hunger_position = "left: 1170px; top: 660px;";
                    break;
                case "axolotl":
                    $pet_position = "left: -580px; top: 640px; width: 350px;";
                    $feed_position = "left: 1270px; top: 720px;";
                    $hunger_position = "left: 1170px; top: 660px;";
                    break;
                default:
                    $pet_position = "left: 150px; top: 450px;";
                    $feed_position = "left: 150px; top: 400px;";
                    $hunger_position = "left: 150px; top: 390px;";
                    break;
            }
        }
        else
        {
            switch(strtolower($pet_name))
            {
                case "capybara":
                    $pet_position = "left: 0px; top: 390px; width: 700px;";
                    $feed_position = "left: 565px; top: 400px;";
                    $hunger_position = "left: 460px; top: 345px;";
                    break;
                case "alligator":
                    $pet_position = "left: -60px; top: 300px; width: 800px;";
                    $feed_position = "left: 600px; top: 500px;";
                    $hunger_position = "left: 495px; top: 445px;";
                    break;
                case "axolotl":
                    $pet_position = "left: -200px; top: 395px; width: 700px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                    $feed_position = "left: 750px; top: 550px;";
                    $hunger_position = "left: 645px; top: 495px;";
                    break;
                default:
                    $pet_position = "left: 150px; top: 450px;";
                    $feed_position = "left: 150px; top: 400px;";
                    $hunger_position = "left: 150px; top: 390px;";
                    break;
            }
        }
    }
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
        <style>
            body {
                background-image: url("<?php echo $backgroundImage; ?>");
                background-size: cover;
                background-repeat: no-repeat;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="hunger-bar-container" style = "<?php echo $hunger_position;?>">
                <div class="hunger-bar" style="width: <?php echo $pet_hunger; ?>%;">
                    <span class="hunger-text"><?php echo $pet_hunger; ?></span>
                </div>
            </div>

            <div class="pet-display">
                <img src="<?php echo $pet_image; ?>" alt="<?php echo htmlspecialchars($pet_name); ?>" style="<?php echo $pet_position; ?>">
            </div>

            <button id="Feed" class="cta-button" style="<?php echo $feed_position; ?>">Feed</button>
        </div>
    </body>
</html>