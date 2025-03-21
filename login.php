<?php
   include("header1.html")
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Welcome to Taskemon</h2>
            <br><br>
            <!-- The required tags make the error message when one is not filled. Remove to replace with custom message from php script below -->
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" name="login" value="Login">
        </form>

        <?php
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
            $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

            if(empty($username)) {
                echo "<div class='error-message'>You must enter a username</div>";
            }
            elseif(empty($password)) {
                echo "<div class='error-message'>You must enter a password</div>";
            } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password)) {
                echo "<div class='error-message'>Password must be at least 8 characters long, contain at least one uppercase letter, and at least one symbol.</div>";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                echo "<div class='error-message'>Username: {$username}</div>";
                echo "<div class='error-message'>Password: {$password}</div>";
                echo "<div class='error-message'>Bcrypt Hash: {$hash}</div>";
            }
        }
        ?>
    </div>
</body>
</html>
