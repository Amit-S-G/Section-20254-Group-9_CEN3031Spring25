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
    task VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE = InnoDB;


*/

 ?>