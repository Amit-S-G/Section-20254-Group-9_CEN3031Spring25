<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include("../database.php");

// Add task logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $task_name = trim($_POST['task_name']);
    $task_duedate = trim($_POST['task_duedate']);
    $task_description = trim($_POST['task_description']);
    $user_id = $_SESSION['user_id'];

    if (!empty($task_name)) {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name, task_duedate, task_description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $task_name, $task_duedate, $task_description);
        $stmt->execute();
        $stmt->close();
    }
}

// Mark task as complete/incomplete logic
if (isset($_GET['complete_task_id'])) {
    $task_id = $_GET['complete_task_id'];
    $stmt = $conn->prepare("UPDATE tasks SET task_completed = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['incomplete_task_id'])) {
    $task_id = $_GET['incomplete_task_id'];
    $stmt = $conn->prepare("UPDATE tasks SET task_completed = 0 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// Delete task logic
if (isset($_GET['delete_task_id'])) {
    $task_id = $_GET['delete_task_id'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// Fetch tasks for the user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, task_name, task_duedate, task_description, task_completed FROM tasks WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch counts for progress
$stmtCount = $conn->prepare("SELECT COUNT(*) AS total, SUM(task_completed) AS completed FROM tasks WHERE user_id = ?");
$stmtCount->bind_param("i", $user_id);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$countRow = $resultCount->fetch_assoc();
$totalTasks = $countRow['total'];
$completedTasks = $countRow['completed'];
$percentage = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100) : 0;
$stmtCount->close();

// For the green progress ring with r=80, circumference ≈ 2 * π * 80 ≈ 502.65
$circumference = 2 * pi() * 80;
$dashOffset = $circumference - ($circumference * $percentage / 100);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../styles/dashboard.css">
</head>
<body>
  <div class="container"> 
    <header>
      <h1>HELLO, <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>
      <h2>Your Task Dashboard</h2>
    </header>

    <!-- Progress Section -->
    <div class="progress-section">
      <div class="skill">
        <div class="inner">
          <div id="number" data-target="<?php echo $percentage; ?>">
            <?php echo $percentage; ?>%
            <p><?php echo $completedTasks; ?>/<?php echo $totalTasks; ?> Complete</p>
          </div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="220" height="220">
          <defs>
            <linearGradient id="GradientColor">
              <stop offset="0%" stop-color="#aeea00" />
              <stop offset="100%" stop-color="#02a641" />
            </linearGradient>
          </defs>
          <!-- Brown Outer Ring (larger circle) -->
          <circle class="brown-ring" cx="110" cy="110" r="100" />
          <!-- Green Progress Ring (smaller circle) -->
          <circle class="progress-ring" cx="110" cy="110" r="80" style="--target-offset: <?php echo $dashOffset; ?>px;" />
        </svg>
      </div>
    </div>

    <!-- Add Task Form Section -->
    <section class="add-task-section">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="text" id="task_name" name="task_name" placeholder="New task name" required>
        <input type="date" id="task_duedate" name="task_duedate">
        <textarea id="task_description" name="task_description" placeholder="Task description"></textarea>
        <input type="submit" name="add_task" value="Add Task">
      </form>
    </section>

    <!-- Task List Section -->
    <section class="tasks-section">
      <ul>
        <?php if ($totalTasks == 0): ?>
          <li class="no-tasks">No tasks yet. Add one above!</li>
        <?php else: ?>
          <?php while ($row = $result->fetch_assoc()) { ?>
            <li class="task-item">
              <div class="task-left">
                <input type="checkbox"
                  <?php if ($row['task_completed']) echo 'checked'; ?>
                  onclick="window.location.href='?<?php echo $row['task_completed'] ? 'incomplete_task_id' : 'complete_task_id'; ?>=<?php echo $row['id']; ?>';" />
                <div class="task-info">
                  <strong class="task-name"><?php echo htmlspecialchars($row['task_name']); ?></strong>
                  <span class="task-date">Due: <?php echo htmlspecialchars($row['task_duedate']); ?></span>
                  <span class="desc"><?php echo htmlspecialchars($row['task_description']); ?></span>
                </div>
              </div>
              <div class="task-right">
                <a class="delete-button" href="?delete_task_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this task?');">✕</a>
              </div>
            </li>
          <?php } ?>
        <?php endif; ?>
      </ul>
    </section>
  </div>
  <script src="../js/task_progress.js"></script>
</body>
</html>