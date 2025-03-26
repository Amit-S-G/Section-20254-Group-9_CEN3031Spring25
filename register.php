<?php
include("database.php");
include("header1.html");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
    $confirm_password = filter_input(INPUT_POST, "confirm_password", FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($username)) {
        echo "<div class='error-message'>You must enter a username</div>";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='error-message'>You must enter a valid email</div>";
    } elseif (empty($password) || empty($confirm_password)) {
        echo "<div class='error-message'>You must enter both password fields</div>";
    } elseif ($password !== $confirm_password) {
        echo "<div class='error-message'>Passwords do not match</div>";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password)) {
        echo "<div class='error-message'>Password must be at least 8 characters long, contain at least one uppercase letter, and at least one symbol.</div>";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT user FROM users WHERE user = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $username_taken = $stmt->num_rows > 0;
        $stmt->close();

        // Check if email exists
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $email_taken = $stmt->num_rows > 0;
        $stmt->close();

        if ($username_taken) {
            echo "<div class='error-message'>Username is already taken</div>";
        } elseif ($email_taken) {
            echo "<div class='error-message'>Email is already in use</div>";
        } else {
            // Insert new user with display_name set to username
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (user, email, password, display_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hash, $username);

            if ($stmt->execute()) {
                echo "<div class='success-message'>Registration successful! <a href='login.php'>Login here</a></div>";
            } else {
                echo "<div class='error-message'>Could not register user</div>";
            }
        }
    }

    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Create an Account</h2>
            <br><br>
            <!-- The required tags make the error message when one is not filled. Remove to replace with custom message from php script below -->
            <!-- Username -->
            <div class="input-container">
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>
            <!-- Email -->
            <div class="input-container">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>
            <!-- Password -->
            <div class="input-container">
                <i class="fas fa-key"></i>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-container">
                <i class="fas fa-key"></i>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            
            <input type="submit" name="register" value="Register">
        </form>
        <br><p class="login-link"><a href="login.php">Already have an account? Log in</a></p>
        </div>
</body>
</html>