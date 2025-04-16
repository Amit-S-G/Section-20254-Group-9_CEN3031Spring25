<?php
include("header.php");
include("../database.php");

// Get user's coins
$stmt = $conn->prepare("SELECT coins FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($coins);
$stmt->fetch();
$stmt->close();

// Handle buying
$buyMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_item_id'])) {
    $itemId = (int)$_POST['buy_item_id'];

    // Get item info
    $stmt = $conn->prepare("SELECT * FROM shop WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if ($item && $coins >= $item['cost']) {
        // Deduct coins
        $newCoins = $coins - $item['cost'];
        $stmt = $conn->prepare("UPDATE users SET coins = ? WHERE id = ?");
        $stmt->bind_param("ii", $newCoins, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        // Check if item exists in inventory
        $stmt = $conn->prepare("SELECT id, quantity FROM inventories WHERE user_id = ? AND item_name = ?");
        $stmt->bind_param("is", $_SESSION['user_id'], $item['item_name']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($inventoryId, $quantity);
            $stmt->fetch();
            $stmt->close();

            $newQuantity = $quantity + 1;
            $stmt = $conn->prepare("UPDATE inventories SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $newQuantity, $inventoryId);
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO inventories (user_id, item_type, item_name, item_description, quantity, is_selected) VALUES (?, ?, ?, ?, 1, 0)");
            $stmt->bind_param("isss", $_SESSION['user_id'], $item['type'], $item['item_name'], $item['item_description']);
        }

        $stmt->execute();
        $stmt->close();

        // Refresh coin count
        $coins = $newCoins;
        $buyMessage = "✅ Purchased " . htmlspecialchars($item['item_name']) . "!";
    } else {
        $buyMessage = "❌ Not enough coins!";
    }
}

// Get shop items
$items = [];
$result = $conn->query("SELECT * FROM shop");
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Undertale Shop</title>
  <style>
    body {
        margin: 0;
        font-family: 'Arial', sans-serif;
        color: white;
        background-image: url(../img/backgrounds/owl4.png);
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: fixed;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        height: 100vh;
    }

    .coin-counter {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: rgba(0, 0, 0, 0.7);
        color: #fff;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        display: flex;
        align-items: center;
        z-index: 1000;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    .coin-counter img {
        width: 24px;
        height: 24px;
        margin-right: 10px;
    }

    .shop-container {
        display: flex;
        justify-content: space-between;
        width: 80%;
        height: 45%;
        overflow: hidden;
        margin-top: 450px;
    }

    .shop-list {
        width: 40%;
        overflow-y: auto;
        background-color: rgba(0, 0, 0, 0.7);
        border-radius: 15px;
        padding: 15px;
        margin-right: 20px;
    }

    .shop-item {
        background-color: rgba(0, 0, 0, 0.5);
        border: 2px solid rgba(255, 255, 255, 0.2);
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 18px;
        color: #FFF;
        font-weight: bold;
        text-align: center;
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 10px;
    }

    .shop-item:hover {
        transform: scale(1.05);
        box-shadow: 0 0 10px 2px rgba(255, 255, 255, 0.6);
        background-color: rgba(255, 255, 255, 0.1);
    }

    .item-details {
        width: 55%;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
        display: none;
        position: relative;
    }

    .item-details h2 {
        font-size: 22px;
        margin-bottom: 20px;
    }

    .item-details .cost {
        font-size: 20px;
        font-weight: bold;
        margin-top: 10px;
    }

    .item-details .hunger {
        font-style: italic;
        font-size: 16px;
        color: #ccc;
        margin-top: 5px;
    }

    .item-details p {
        font-size: 16px;
        max-width: 60%;
    }

    .item-details img.item-image {
        position: absolute;
        right: 20px;
        top: 20px;
        width: 240px;
        height: auto;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
    }

    #buy-button {
        margin-top: 30px;
        padding: 12px 24px;
        font-size: 18px;
        font-weight: bold;
        color: white;
        background-color: #28a745;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    #buy-button:hover {
        transform: scale(1.05);
        box-shadow: 0 0 12px 4px rgba(40, 167, 69, 0.7);
        background-color: #32c956;
    }

    #buy-button:active {
        transform: scale(0.97);
        box-shadow: 0 0 14px 5px rgba(40, 167, 69, 0.9);
        background-color: #249c3a;
    }

    .message {
        margin-top: 10px;
        font-weight: bold;
        font-size: 18px;
        text-align: center;
        padding: 10px;
        border-radius: 5px;
        transition: opacity 0.5s ease;
    }

    .message.success {
        background-color: rgba(0, 128, 0, 0.1);
        color: green;
    }

    .message.error {
        background-color: rgba(255, 0, 0, 0.1);
        color: red;
    }
  </style>
</head>

<body>

<!-- Coin Counter -->
<div class="coin-counter">
    <img src="../img/icons/dollar.png" alt="Coin Icon" />
    <?php echo htmlspecialchars((string)$coins); ?>
</div>

<div class="shop-container">

    <!-- Shop List -->
    <div class="shop-list">
        <?php foreach ($items as $item): ?>
            <div class="shop-item" onclick="showDetails(<?php echo $item['id']; ?>)">
                <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                <div class="cost">
                    <img src="../img/icons/dollar.png" alt="Coin Icon" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;">
                    <?php echo number_format($item['cost']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Item Details -->
    <div id="item-details" class="item-details">
        <h2 id="item-name"></h2>
        <p id="item-description"></p>
        <div id="item-cost" class="cost"></div>
        <div id="item-hunger" class="hunger"></div>
        <img id="item-image" class="item-image" src="" alt="Item Image" style="display: none;">

        <form method="POST" id="buy-form" style="display:none;">
            <input type="hidden" name="buy_item_id" id="buy-item-id" value="">
            <button id="buy-button" type="submit">BUY</button>
        </form>

    </div>

    <?php if ($buyMessage): ?>
            <!-- Display the purchase message -->
            <div class="message <?php echo $buyMessage === "❌ Not enough coins!" ? 'error' : 'success'; ?>" style="
                position: fixed;
                top: 20%;
                right: 20%;
                transform: translate(-50%, -50%);
                z-index: 1000;
                background-color: rgba(0, 0, 0, 0.85);
                color: white;
                padding: 15px 25px;
                border-radius: 10px;
                font-size: 18px;
                font-weight: bold;
                box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
                text-align: center;
            ">
        <?php echo htmlspecialchars($buyMessage); ?>
    </div>
        <?php endif; ?>
</div>

<script>
    function showDetails(itemId) {
        const items = <?php echo json_encode($items); ?>;
        const item = items.find((item) => parseInt(item.id) === itemId);

        if (item) {
            document.getElementById('item-name').textContent = item.item_name;
            document.getElementById('item-description').textContent = item.item_description;
            document.getElementById('item-cost').innerHTML = '<img src="../img/icons/dollar.png" alt="Coin Icon" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;">' + item.cost;
            document.getElementById('item-hunger').textContent = item.type === 'food' ? "🍽 Hunger Points: " + item.hunger_pts : "";

            const imgElement = document.getElementById('item-image');
            if (item.image_url) {
                imgElement.src = item.image_url;
                imgElement.style.display = "block";
            } else {
                imgElement.style.display = "none";
            }

            document.getElementById('item-details').style.display = "block";

            // Show buy form
            document.getElementById('buy-form').style.display = "block";
            document.getElementById('buy-item-id').value = item.id;
        }
    }
</script>

<script>
    setTimeout(() => {
        const msg = document.querySelector('.message');
        if (msg) {
            msg.style.transition = 'opacity 0.5s ease';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }
    }, 3000);
</script>

</body>
</html>
