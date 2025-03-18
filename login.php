<?php
    include("header1.html")
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p> Login Page </p>
    <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
        <h2>Welcome to Taskemon</h2>
        Somebody save our 1900s looking webpage design<br><br>
        Username:<br>
        <input type="text" name="username"><br>
        Password:<br>
        <input type="password" name="password"><br><br>
        <input type="submit" name="login" value="login"><br>
</body>
</html>

<?php

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

    if(empty($username)){
        echo"You must enter a username";
    }
    elseif(empty($password)){
        echo"You must enter a password";
    }
    else{
        $hash = password_hash($password, PASSWORD_DEFAULT);
        echo "Username: {$username}<br>";
        echo "Password: {$password}<br>";
        echo "Bcrypt Hash: {$hash}<br>";
    }
}
?>