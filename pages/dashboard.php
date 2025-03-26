<?php
// Start the session to access user data
session_start();

// Check if the user is logged in, if not, redirect to the login page
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include("../database.php");

// Add task logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $task = filter_input(INPUT_POST, 'task', FILTER_SANITIZE_SPECIAL_CHARS);
    $user_id = $_SESSION['user_id'];

    if (!empty($task)) {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task);
        $stmt->execute();
        $stmt->close();
    }
}

// Delete task logic
if (isset($_GET['delete_task_id'])) {
    $task_id = $_GET['delete_task_id'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// Fetch tasks for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, task FROM tasks WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <p>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
    <p>Welcome to your dashboard.</p>
    <p>This is a basic dashboard page for tasks once the user logs in.</p>

    <!-- Add Task Form -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="text" name="task" placeholder="Enter a task" required>
        <input type="submit" name="add_task" value="Add Task">
    </form>

    <h3>Your Tasks:</h3>
    <ul>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <li>
                <?php echo htmlspecialchars($row['task']); ?>
                <a href="?delete_task_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
            </li>
        <?php } ?>
    </ul>

    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>
</html>
