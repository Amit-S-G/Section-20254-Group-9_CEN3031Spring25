<?php
include("database.php"); // Includes database script
include("header1.html");

session_start(); // Start session for login
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Welcome to Taskemon</h2>
            <br><br>

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $username = $_POST["username"];
                $password = $_POST["password"];

                if(empty($username)) {
                    echo "<div class='error-message'>You must enter a username</div>";
                }
                elseif(empty($password)) {
                    echo "<div class='error-message'>You must enter a password</div>";
                } else {
                    // Check if the user exists
                    $stmt = $conn->prepare("SELECT id, user, password FROM users WHERE user = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows == 1) {
                        $row = $result->fetch_assoc();

                        if (password_verify($password, $row["password"])) {
                            $_SESSION["user_id"] = $row["id"];
                            $_SESSION["username"] = $row["user"];
                            header("Location: pages/dashboard.php"); // Redirect to dashboard after login
                            exit();
                        } else {
                            echo "<div class='error-message'>Invalid username or password.</div>";
                        }
                    } else {
                        echo "<div class='error-message'>Invalid username or password.</div>";
                    }
                    $stmt->close();
                }
            }
            ?>

            <div class="input-container">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-container">
                <i class="fas fa-key"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <input type="submit" name="login" value="Login">
        </form>
        <p class="register-link"><a href="register.php">Don't have an account? Create one</a></p>
    </div>
</body>
</html>

<?php
$conn->close(); // Close DB connection
?>