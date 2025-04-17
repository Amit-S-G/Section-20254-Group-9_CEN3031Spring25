<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}

include("../database.php");

// Check for AJAX request to update is_selected
if (isset($_POST['item_name'])) {
    $user_id = $_SESSION['user_id'];
    $item_name = $_POST['item_name'];

    // Get the item type from the shop
    $stmt = $conn->prepare("SELECT type FROM shop WHERE item_name = ?");
    $stmt->bind_param("s", $item_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $itemInfo = $result->fetch_assoc();
    $stmt->close();

    if ($itemInfo) {
        $item_type = $itemInfo['type'];

        // If 'single_select' flag is set, deselect all of this type only
        if (isset($_POST['single_select']) && $_POST['single_select'] == 1) {
            $stmt = $conn->prepare("UPDATE inventories SET is_selected = 0 WHERE user_id = ? AND item_type = ?");
            $stmt->bind_param("is", $user_id, $item_type);
            $stmt->execute();
            $stmt->close();
        }

        // Select this item
        $stmt = $conn->prepare("UPDATE inventories SET is_selected = 1 WHERE user_id = ? AND item_name = ?");
        $stmt->bind_param("is", $user_id, $item_name);
        if ($stmt->execute()) {
            echo "Selection updated.";
        } else {
            echo "Error updating selection.";
        }
        $stmt->close();
    }

    exit(); // Important: stop script after AJAX call
}
// Handle feeding action
if (isset($_POST['action']) && $_POST['action'] === 'feed') {
    $user_id = $_SESSION['user_id'];

    // Get selected item with hunger_pts
    $stmt = $conn->prepare("
        SELECT i.item_name, i.quantity, s.hunger_pts
        FROM inventories i
        JOIN shop s ON i.item_name = s.item_name
        WHERE i.user_id = ? AND i.is_selected = 1
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $hunger_pts = intval($item['hunger_pts']);
        $item_name = $item['item_name'];
        $quantity = intval($item['quantity']);

        if ($quantity > 0 && $hunger_pts > 0) {
            // Update hunger
            $update = $conn->prepare("UPDATE pets SET pet_hunger = LEAST(pet_hunger + ?, 100) WHERE user_id = ?");
            $update->bind_param("ii", $hunger_pts, $user_id);
            $update->execute();
            $update->close();

            // Decrease item quantity
            $updateQty = $conn->prepare("UPDATE inventories SET quantity = quantity - 1 WHERE user_id = ? AND item_name = ?");
            $updateQty->bind_param("is", $user_id, $item_name);
            $updateQty->execute();
            $updateQty->close();

            $new_quantity = $quantity - 1;

            if ($new_quantity <= 0) {
                $deleteStmt = $conn->prepare("DELETE FROM inventories WHERE user_id = ? AND item_name = ?");
                $deleteStmt->bind_param("is", $user_id, $item_name);
                $deleteStmt->execute();
                $deleteStmt->close();
            }

            echo json_encode([
                "success" => true,
                "hunger_pts" => $hunger_pts,
                "item_name" => $item_name,
                "new_quantity" => $new_quantity
            ]);

        } else {
            echo json_encode(["success" => false, "message" => "No usable items."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No item selected."]);
    }

    $stmt->close();
    exit();
}


// TEST OVERRIDES: Read GET parameters for pet and habitat
$testPet     = isset($_GET['pet']) ? strtolower($_GET['pet']) : '';
$testHabitat = isset($_GET['habitat']) ? strtolower($_GET['habitat']) : '';

// Habitat setup
$chosenHabitat = " ";
$backgroundImage = "../img/backgrounds/tree_house.png";
$stmtHabitat = $conn->prepare("SELECT item_name FROM inventories WHERE user_id = ? AND item_type = 'habitat' AND is_selected = 1");
$stmtHabitat->bind_param("i", $_SESSION['user_id']);
$stmtHabitat->execute();
$resultHabitat = $stmtHabitat->get_result();

if ($resultHabitat->num_rows > 0) {
    $habitatRow = $resultHabitat->fetch_assoc();
    $chosenHabitat = $habitatRow['item_name'];
    $chosenHabitat = strtolower(trim($habitatRow['item_name']));
}
$stmtHabitat->close();

// Override with test habitat
if (!empty($testHabitat)) {
    $chosenHabitat = $testHabitat;
}

if (!empty($chosenHabitat)) {
    switch (strtolower($chosenHabitat)) {
        case "pleasant grove":
            $backgroundImage = "../img/backgrounds/pleasant_grove_rendered.png";
            break;
        case "sapphire springs":
            $backgroundImage = "../img/backgrounds/sapphire_springs_rendered.png";
            break;
        case "divine waterfall":
            $backgroundImage = "../img/backgrounds/divine_waterfall_rendered.png";
            break;
        default:
            $backgroundImage = "../img/backgrounds/tree_house.png";
            break;
    }
}

//Pet setup
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

// Override with test pet
if (!empty($testPet)) {
    $pet_name = $testPet;
    $hasPet = true;
}

if ($hasPet) {
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

    $pet_hunger = 100;
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
}

$maxHunger = 100;
$hungerPercentage = min(($pet_hunger / $maxHunger) * 100, 100);

// Positioning
//Defining positions for pet image, feed button and hunger bar based on the background iamge chosen
$pet_position = "left: 150px; top: 450px;";
$feed_position = "left: 150px; top: 400px;";
$hunger_position = "left: 150px; top: 390px;";
$hunger_icon_position = "left: 140px; top: 390px;";


if(!empty($chosenHabitat))
{
    if(strtolower($chosenHabitat) == "pleasant grove")
    {
        switch(strtolower($pet_name))
        {
            case "capybara":
                $pet_position = "left: 150px; top: 330px; width: 500px";
                $feed_position = "left: 510px; top: 340px;";
                $hunger_position = "left: 405px; top: 285px;";
                $hunger_icon_position = "left: 369px; top: 295px;";
                break;
            case "alligator":
                $pet_position = "left: 150px; top: 280px;";
                $feed_position = "left: 500px; top: 450px;";
                $hunger_position = "left: 395px; top: 395px;";
                $hunger_icon_position = "left: 359px; top: 405px;";
                break;
            case "axolotl":
                $pet_position = "left: 150px; top: 340px; width: 550px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                $feed_position = "left: 500px; top: 460px;";
                $hunger_position = "left: 395px; top: 405px;";
                $hunger_icon_position = "left: 359px; top: 415px;";
                break;
            default:
                $pet_position = "left: 150px; top: 450px;";
                $feed_position = "left: 150px; top: 400px;";
                $hunger_position = "left: 150px; top: 390px;";
                $hunger_icon_position = "left: 415px; top: 285px;";
                break;
        }
    }
    elseif(strtolower($chosenHabitat) == "sapphire springs")
    {
        switch(strtolower($pet_name))
        {
            case "capybara":
                $pet_position = "left: 250px; top: 200px; width: 500px";
                $feed_position = "left: 415px; top: 205px";
                $hunger_position = "left: 310px; top: 150px";
                $hunger_icon_position = "left: 274px; top: 160px;";
                break;
            case "alligator":
                $pet_position = "left: 350px; top: 20px; width: 500px";
                $feed_position = "left: 350px; top: 150px;";
                $hunger_position = "left: 245px; top: 95px;";
                $hunger_icon_position = "left: 209px; top: 105px;";
                break;
            case "axolotl":
                $pet_position = "left: 350px; top: 50px; width: 450px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                $feed_position = "left: 350px; top: 150px;";
                $hunger_position = "left: 245px; top: 95px;";
                $hunger_icon_position = "left: 209px; top: 105px;";
                break;
            default:
                $pet_position = "left: 150px; top: 450px;";
                $feed_position = "left: 150px; top: 400px;";
                $hunger_position = "left: 150px; top: 390px;";
                $hunger_icon_position = "left: 415px; top: 285px;";
                break;
        }
    }
    elseif(strtolower($chosenHabitat) == "divine waterfall")
    {
        switch(strtolower($pet_name))
        {
            case "capybara":
                $pet_position = "left: -600px; top: 600px; width: 350px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                $feed_position = "left: 1310px; top: 600px;";
                $hunger_position = "left: 1200px; top: 540px;";
                $hunger_icon_position = "left: 1164px; top: 550px;";
                break;
            case "alligator":
                $pet_position = "left: -580px; top: 640px; width: 350px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                $feed_position = "left: 1270px; top: 720px;";
                $hunger_position = "left: 1170px; top: 660px;";
                $hunger_icon_position = "left: 1134px; top: 670px;";
                break;
            case "axolotl":
                $pet_position = "left: -580px; top: 640px; width: 350px;";
                $feed_position = "left: 1270px; top: 720px;";
                $hunger_position = "left: 1170px; top: 660px;";
                $hunger_icon_position = "left: 1134px; top: 670px;";
                break;
            default:
                $pet_position = "left: 150px; top: 450px;";
                $feed_position = "left: 150px; top: 400px;";
                $hunger_position = "left: 150px; top: 390px;";
                $hunger_icon_position = "left: 415px; top: 285px;";
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
                $hunger_icon_position = "left: 424px; top: 355px;";
                break;
            case "alligator":
                $pet_position = "left: -60px; top: 300px; width: 800px;";
                $feed_position = "left: 600px; top: 500px;";
                $hunger_position = "left: 495px; top: 445px;";
                $hunger_icon_position = "left: 459px; top: 455px;";
                break;
            case "axolotl":
                $pet_position = "left: -200px; top: 395px; width: 700px; -webkit-transform: scaleX(-1); transform: scaleX(-1);";
                $feed_position = "left: 750px; top: 550px;";
                $hunger_position = "left: 645px; top: 495px;";
                $hunger_icon_position = "left: 609px; top: 505px;";
                break;
            default:
                $pet_position = "left: 150px; top: 450px;";
                $feed_position = "left: 150px; top: 400px;";
                $hunger_position = "left: 150px; top: 390px;";
                $hunger_icon_position = "left: 415px; top: 285px;";
                break;
        }
    }
}

// Fetch inventory
$inventoryItems = [];
$stmtInventory = $conn->prepare("
    SELECT i.item_name, i.quantity, s.image_url, s.hunger_pts, i.is_selected
    FROM inventories i
    JOIN shop s ON i.item_name = s.item_name
    WHERE i.user_id = ?
");
$stmtInventory->bind_param("i", $_SESSION['user_id']);
$stmtInventory->execute();
$resultInventory = $stmtInventory->get_result();

while ($item = $resultInventory->fetch_assoc()) {
    $inventoryItems[] = $item;
}
$stmtInventory->close();

$conn->close();
?>

<?php include("header.php") ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitat</title>
    <link rel="stylesheet" href="../styles/habitat.css" />
    <link rel="icon" href="../img/allyLogo.png" />
    <style>
        body {
            background-image: url("<?php echo $backgroundImage; ?>");
            background-size: cover;
            background-repeat: no-repeat;
        }

    </style>
</head>
<body class="<?php echo 'habitat-' . strtolower(str_replace(' ', '_', $chosenHabitat)); ?>">
    <div class="container">
        <?php if ($hasPet): ?>
            <div class="hunger-icon" style="<?php echo $hunger_icon_position; ?>">
                <img src="../img/icons/hunger_icon.png" alt="Hunger Icon">
            </div>
            <div class="hunger-bar-container" style="<?php echo $hunger_position; ?>">
                <div class="hunger-bar">
                    <div class="hunger-fill" style="width: <?php echo $hungerPercentage; ?>%;">
                        <span class="hunger-text" id="hungerText"><?php echo $pet_hunger; ?>/<?php echo $maxHunger; ?></span>
                    </div>
                </div>
            </div>

            <div class="pet-display">
                <img src="<?php echo $pet_image; ?>" alt="<?php echo htmlspecialchars($pet_name); ?>" style="<?php echo $pet_position; ?>">
            </div>

            <button id="Feed" class="cta-button" style="<?php echo $feed_position; ?>">Feed</button>
        <?php endif; ?>

        <!-- Inventory Display -->
        <div class="inventory-section">
            <h2 class = "inventory-header">Your Inventory</h2>
            <div class="inventory-list">
                <?php foreach ($inventoryItems as $item): ?>
                    <div class="inventory-item <?php echo ($item['is_selected'] == 1) ? 'selected' : ''; ?>" 
                        data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                        data-item-type="<?php echo ($item['hunger_pts'] > 0) ? 'food' : 'habitat'; ?>">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" />
                        <p><?php echo htmlspecialchars($item['item_name']); ?> x<?php echo $item['quantity']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inventoryItems = document.querySelectorAll('.inventory-item');
            const hungerText = document.getElementById('hungerText');
            const feedBtn = document.getElementById('Feed');

            if (hungerText && feedBtn) {
                const hungerValue = parseInt(hungerText.textContent.split('/')[0]);
                const maxHunger = parseInt(hungerText.textContent.split('/')[1]);

                if (hungerValue >= maxHunger) {
                    feedBtn.disabled = true;
                    feedBtn.textContent = "Full!";
                    feedBtn.style.backgroundColor = "#aaa";
                    feedBtn.style.cursor = "not-allowed";
                }
            }
            

        inventoryItems.forEach(item => {
            item.addEventListener('click', function () {
                const itemName = this.getAttribute('data-item-name');
                const itemType = this.getAttribute('data-item-type');

                if (itemType === 'habitat') {
                // Deselect all items in UI
                inventoryItems.forEach(el => el.classList.remove('selected'));
                // Select this one
                this.classList.add('selected');

                // Send AJAX to update selection and reload for habitat change
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        location.reload(); // Only reload for habitat change
                    }
                };
                xhr.send('item_name=' + encodeURIComponent(itemName) + '&single_select=1');
            } else if (itemType === 'food') {
                // Allow selecting food item for feeding, but don't reload
                inventoryItems.forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');

                // Send AJAX to mark this food as selected
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('item_name=' + encodeURIComponent(itemName) + '&single_select=0'); // No need to deselect all
            }

                // Deselect all items in UI
                inventoryItems.forEach(el => el.classList.remove('selected'));
                // Select this one
                this.classList.add('selected');

                // Send AJAX to update selection
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        location.reload();
                    }
                };
                xhr.send('item_name=' + encodeURIComponent(itemName) + '&single_select=1');
            });
        });
        });
    </script>

    <script>
    document.getElementById('Feed').addEventListener('click', function () {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true); // same PHP file
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        const itemName = res.item_name;
                        const newQty = res.new_quantity;

                        // Find the inventory item div
                        const itemDivs = document.querySelectorAll('.inventory-item');
                        itemDivs.forEach(div => {
                            if (div.getAttribute('data-item-name') === itemName) {
                                if (newQty <= 0) {
                                    // Remove it from DOM
                                    div.remove();
                                } else {
                                    // Update the quantity text
                                    const p = div.querySelector('p');
                                    p.textContent = `${itemName} x${newQty}`;
                                }
                            }
                        });

                        location.reload(); // Full reload
                    } else {
                        alert(res.message || "Feeding failed.");
                    }
                } catch (e) {
                    console.error("Failed to parse response:", e);
                }
            }
            location.reload();
        };

        xhr.send('action=feed');
    });
    </script>

</body>
</html>
