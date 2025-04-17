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
        $canPurchase = true;
        // Prevent buying the same habitat again
        if ($item['type'] === 'habitat') {
            $stmt = $conn->prepare("SELECT 1 FROM inventories WHERE user_id = ? AND item_type = 'habitat' AND item_name = ?");
            $stmt->bind_param("is", $_SESSION['user_id'], $item['item_name']);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $buyMessage = "Habitat already owned!";
                $canPurchase = false;
            }
            $stmt->close();
        }
        
        // Deduct coins
        if ($canPurchase) {
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
            $buyMessage = "Purchased " . htmlspecialchars($item['item_name']) . "!";
        }
        } else {
            $buyMessage = "Not enough coins!";
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
  <title>Shop</title>
  <link rel="stylesheet" href="../styles/shop.css">
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
            <div class="message-wrapper">
                <div class="message <?php echo $buyMessage === "❌ Not enough coins!" ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($buyMessage); ?>
                </div>
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
        const wrapper = document.querySelector('.message-wrapper');
        if (wrapper) {
            wrapper.style.transition = 'opacity 0.5s ease';
            wrapper.style.opacity = '0';
            setTimeout(() => wrapper.remove(), 500);
        }
    }, 3000);
</script>

</body>
</html>
