<!-- This will be for connecting to MySQL database later 
 It is NOT for insertion into the database, only setting up the connection
 Include it and use MySQL in other files where necessary, and make sure to close the connection -->

 <?php

    $db_server = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "users";
    $conn = "";

    try{
        $conn = mysqli_connect($db_server,$db_user,$db_pass,$db_name);
    }
    catch(mysqli_sql_exception){
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
    FOREIGN KEY (user_id) REFERENCES users(id)
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
    user_id INT,
    item_name VARCHAR(255),
    item_description VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE = InnoDB;


################################################
###  SQL QUERY FOR CREATING 'pets' TABLE  ###
################################################


CREATE TABLE pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    pet_name VARCHAR(255),
    pet_hunger TINYINT(10) UNSIGNED NOT NULL DEFAULT 100,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE = InnoDB;


################################################
###  SQL QUERY FOR CREATING 'shop' TABLE  ###
################################################


CREATE TABLE shop (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255),
    item_description VARCHAR(255),
    food_or_house TINYINT(1) NOT NULL,
    cost INT(5),
    points INT(5)
) ENGINE = InnoDB;

*/

 ?>