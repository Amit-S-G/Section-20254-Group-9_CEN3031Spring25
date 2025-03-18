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

    if($conn){
        echo "Connected to db";
    }
 ?>