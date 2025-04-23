document.addEventListener('DOMContentLoaded', function () {
    // Select all inventory items
    const inventoryItems = document.querySelectorAll('.inventory-item');

    inventoryItems.forEach(item => {
        item.addEventListener('click', function () {
            const itemName = this.getAttribute('data-item-name');
            const isSelected = this.classList.contains('selected') ? 0 : 1;  // Toggle selection

            // Highlight or unhighlight the item
            this.classList.toggle('selected', isSelected === 1);

            // Send AJAX request to update is_selected in the database
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_inventory.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    console.log('Inventory updated');
                }
            };
            xhr.send('item_name=' + encodeURIComponent(itemName) + '&is_selected=' + isSelected);
        });
    });
});
