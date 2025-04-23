<!-- This will be for connecting to MySQL database later 
 It is NOT for insertion into the database, only setting up the connection
 Include it and use MySQL in other files where necessary, and make sure to close the connection -->

<?php

$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "users";
$conn = "";

try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception) {
    echo "Could not connect to db";
}

/* 

################################################
####  SQL QUERY FOR CREATING 'users' TABLE  ####
################################################


CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user VARCHAR(30) NOT NULL,
    password CHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    display_name VARCHAR(50) NOT NULL,
    reg_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    coins INT(11) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (user)
) ENGINE = InnoDB; 
 

################################################
####  SQL QUERY FOR CREATING 'tasks' TABLE  ####
################################################


CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    task_name VARCHAR(255),
    task_duedate DATE,
    task_description VARCHAR(255),
    task_completed TINYINT(1) NOT NULL DEFAULT 0,
    point_value INT NOT NULL DEFAULT 10,
    hunger_decreased TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE = InnoDB;


################################################
###  SQL QUERY FOR CREATING 'friendship' TABLE  ###
################################################


CREATE TABLE friendship (
    usersname VARCHAR(50) NOT NULL,
    friend_username VARCHAR(50) NOT NULL,
    status ENUM('pending', 'accepted')
) ENGINE = InnoDB; 
 

################################################
###  SQL QUERY FOR CREATING 'friends' TABLE  ###
################################################


CREATE TABLE friends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    friend_username VARCHAR(30),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE = InnoDB;


################################################
#  SQL QUERY FOR CREATING 'inventories' TABLE  #
################################################


CREATE TABLE inventories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('habitat', 'food') NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_description VARCHAR(255),
    quantity INT DEFAULT 0,
    is_selected TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;


################################################
###  SQL QUERY FOR CREATING 'pets' TABLE  ###
################################################


CREATE TABLE pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    pet_name VARCHAR(255),
    pet_hunger TINYINT(10) UNSIGNED NOT NULL DEFAULT 100,
    last_hunger_update DATE DEFAULT CURDATE(),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE = InnoDB;


################################################
###  SQL QUERY FOR CREATING 'shop' TABLE  ###
################################################


CREATE TABLE shop (
    id INT PRIMARY KEY,
    item_name VARCHAR(50) NOT NULL,
    type VARCHAR(20) NOT NULL,
    item_description TEXT,
    cost INT NOT NULL,
    hunger_pts INT,
    habitat_pts INT,
    image_url VARCHAR(255)
);

####################################################
### SQL QUERY FOR CREATING 'user_profiles" TABLE ###
####################################################

CREATE TABLE user_profiles (
    user_id INT PRIMARY KEY,
    display_name VARCHAR(255) DEFAULT '',
    bio TEXT,
    base_model VARCHAR(50) NOT NULL DEFAULT 'default',
    head_accessory VARCHAR(50),
    body_accessory VARCHAR(50),
    background_color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO shop (id, item_name, type, item_description, cost, hunger_pts, habitat_pts, image_url) VALUES
(1, 'Baked Potato', 'food', 'A perfectly baked potato with a crisp, golden skin encasing a soft, fluffy interior. Ideal for butter, herbs, or a hearty topping that enhances its comforting simplicity.', 10, 20, NULL, '../img/food/baked_potato.png'),
(2, 'Bread', 'food', 'A freshly baked loaf with a crunchy, caramelized crust and a pillowy, tender crumb. This versatile staple evokes the aroma of traditional ovens and pairs wonderfully with any meal.', 10, 20, NULL, '../img/food/bread.png'),
(3, 'Plain Rice', 'food', 'Light, fluffy, and expertly steamed, this plain rice serves as the ideal neutral base for absorbing rich sauces and flavors, making every bite balanced and satisfying.', 10, 20, NULL, '../img/food/grilled_fish.png'),
(4, 'Comforting Stew', 'food', 'A slow-simmered medley of tender meats and garden-fresh vegetables in a deeply savory broth. Its rich, hearty flavors offer a warm, soul-soothing dining experience during chilly days.', 20, 40, NULL, '../img/food/comforting_stew.png'),
(5, 'Cheese Pasta', 'food', 'Decadent pasta enveloped in a luxuriously smooth, melted cheese sauce. Each bite delivers a combination of creamy indulgence and timeless comfort, perfect for a cozy meal.', 20, 40, NULL, '../img/food/cheese_pasta.png'),
(6, 'Grilled Fish', 'food', 'Delicately seasoned and expertly grilled, this fish boasts a smoky char on its tender, flaky flesh. The natural flavors are enhanced by light, aromatic herbs and lemon for a delightful, healthy dish.', 20, 40, NULL, '../img/food/grilled_fish.png'),
(7, 'Ratatouille', 'food', 'A vibrant, rustic dish featuring a medley of colorful vegetables lovingly stewed in a fragrant tomato and herb sauce. This classic brings a taste of Mediterranean sunshine to your table.', 30, 60, NULL, '../img/food/ratatouille.png'),
(8, 'Honey Cake', 'food', 'Moist and sweet, this honey cake is infused with a delicate floral aroma and a hint of spice. Every slice offers a blissful balance of sweetness and subtle complexity.', 30, 60, NULL, '../img/food/honey_cake.png'),
(9, 'Steak', 'food', 'A juicy, succulent steak grilled to perfection, where a charred, smoky exterior gives way to a tender, melt-in-your-mouth interior. It’s a carnivorous delight that speaks of expert preparation and flavor.', 30, 60, NULL, '../img/food/steak.png'),
(10, 'Pleasant Grove', 'habitat', 'A serene haven where sun-dappled clearings meet lush, thriving flora. This peaceful grove invites quiet reflection and the rejuvenating essence of nature.', 100, NULL, 3, '../img/habitats/pleasant_grove.png'),
(11, 'Sapphire Springs', 'habitat', 'Enchanting springs that bubble forth with crystal-clear water, their deep sapphire hue evoking a mesmerizing and tranquil aura. A refreshing escape into nature’s subtle beauty.', 300, NULL, 5, '../img/habitats/sapphire_springs.png'),
(12, 'Divine Waterfall', 'habitat', 'A majestic waterfall cascading dramatically from a rugged cliff, its constant roar and gentle mist combining to create a scene of natural wonder and timeless grandeur.', 500, NULL, 7, '../img/habitats/divine_waterfall_temple.png');

*/



?>