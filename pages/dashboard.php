<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include("../database.php");

// Add task logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $task = trim($_POST['task']);
    $duedate = trim($_POST['duedate']);
    $description = trim($_POST['description']);
    $completed = isset($_POST['completed']) ? 1 : 0;
    $user_id = $_SESSION['user_id'];

    if (!empty($task)) {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name, task_duedate, task_description, task_completed) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $user_id, $task, $duedate, $description, $completed);
        $stmt->execute();
        $stmt->close();
    }
}

// Delete task logic
if (isset($_GET['delete_task_id'])) {
    $task_id = (int)$_GET['delete_task_id'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// Toggle task completed status
if (isset($_GET['toggle_complete_id']) && isset($_GET['current_status'])) {
    $task_id = (int)$_GET['toggle_complete_id'];
    $current_status = (int)$_GET['current_status'];
    $new_status = $current_status ? 0 : 1;

    $stmt = $conn->prepare("UPDATE tasks SET task_completed = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $new_status, $task_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// Fetch tasks
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, task_name, task_duedate, task_description, task_completed FROM tasks WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .completed { color: green; }
        .incomplete { color: red; }
        li {
            margin-bottom: 1em;
        }
    </style>
</head>
<body>
    <h2>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
    <p>Welcome to your dashboard.</p>

    <!-- Add Task Form -->
    <h3>Add a New Task</h3>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="text" name="task" placeholder="Task Name" required><br><br>
        <label>Due Date: <input type="date" name="duedate" required></label><br><br>
        <textarea name="description" placeholder="Task Description" rows="3" cols="30"></textarea><br><br>
        <input type="submit" name="add_task" value="Add Task">
    </form>

    <h3>Your Tasks:</h3>
    <ul>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <li>
                <strong><?php echo htmlspecialchars($row['task_name']); ?></strong><br>
                <em>Due:</em> <?php echo htmlspecialchars($row['task_duedate']); ?><br>
                <em>Description:</em> <?php echo nl2br(htmlspecialchars($row['task_description'])); ?><br>
                <span class="<?php echo $row['task_completed'] ? 'completed' : 'incomplete'; ?>">
                    <?php echo $row['task_completed'] ? '✅ Completed' : '❌ Incomplete'; ?>
                </span><br>

                <a href="?toggle_complete_id=<?php echo $row['id']; ?>&current_status=<?php echo $row['task_completed']; ?>">
                    Mark as <?php echo $row['task_completed'] ? 'Incomplete' : 'Completed'; ?>
                </a> |
                <a href="?delete_task_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this task?');">
                    Delete
                </a>
            </li>
        <?php } ?>
    </ul>

    <?php $conn->close(); ?>
</body>
</html>
