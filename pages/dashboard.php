<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
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
    header("Location: " . $_SERVER["PHP_SELF"]); //loads the same page again but via a get request, there was a bug were the task list would duplicate whenever user refreshed the page, this prevents that
    exit();
}

if (isset($_GET['complete_task_id'])) {
  // Mark task as complete and reward coins
  $task_id = (int) $_GET['complete_task_id'];
  $user_id = (int) $_SESSION['user_id'];

  // Update the task's completion status to 1 (complete)
  $stmt = $conn->prepare("UPDATE tasks SET task_completed = 1 WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $task_id, $user_id);
  $stmt->execute();
  $stmt->close();

  // Retrieve the point value for the task
  $stmt = $conn->prepare("SELECT point_value FROM tasks WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $task_id, $user_id);
  $stmt->execute();
  $stmt->bind_result($point_value);
  if ($stmt->fetch()) {
      $stmt->close();

      // Update user's coins by adding the task's point value
      $stmt = $conn->prepare("UPDATE users SET coins = coins + ? WHERE id = ?");
      $stmt->bind_param("ii", $point_value, $user_id);
      $stmt->execute();
      $stmt->close();
  } else {
      $stmt->close();
  }
}

if (isset($_GET['incomplete_task_id'])) {
  // Mark task as incomplete and adjust coins
  $task_id = (int) $_GET['incomplete_task_id'];
  $user_id = (int) $_SESSION['user_id'];

  // Update the task's completion status to 0 (incomplete)
  $stmt = $conn->prepare("UPDATE tasks SET task_completed = 0 WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $task_id, $user_id);
  $stmt->execute();
  $stmt->close();

  // Retrieve the point value for the task
  $stmt = $conn->prepare("SELECT point_value FROM tasks WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $task_id, $user_id);
  $stmt->execute();
  $stmt->bind_result($point_value);
  if ($stmt->fetch()) {
      $stmt->close();

      // Update user's coins by subtracting the task's point value.
      // Use GREATEST() to ensure coins never fall below 0.
      $stmt = $conn->prepare("UPDATE users SET coins = GREATEST(coins - ?, 0) WHERE id = ?");
      $stmt->bind_param("ii", $point_value, $user_id);
      $stmt->execute();
      $stmt->close();
  } else {
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

//Clear Tasks
if (isset($_GET['clear_tasks'])) {
  $stmt = $conn->prepare("DELETE FROM tasks WHERE user_id = ?");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $stmt->close();
  header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
  exit();
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

$circumference = 2 * pi() * 80;
$dashOffset = $circumference - ($circumference * $percentage / 100);

//Pet Selection Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['choose_pet'])) {
    $pet_name = trim($_POST['pet_name']);
    if (!empty($pet_name)) {
        $pet_hunger = 100;
        $stmt = $conn->prepare("INSERT INTO pets (user_id, pet_name, pet_hunger) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $_SESSION['user_id'], $pet_name, $pet_hunger);
        $stmt->execute();
        $stmt->close();
        // Reload page so that the modal does not display after choosing a pet.
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    }
}

// Initialize default values
$pet_name = "";
$hasPet = false;

// Change the query to retrieve the pet's name instead of just the id.
$stmtPet = $conn->prepare("SELECT pet_name FROM pets WHERE user_id = ?");
$stmtPet->bind_param("i", $_SESSION['user_id']);
$stmtPet->execute();
$resultPet = $stmtPet->get_result();
if ($resultPet->num_rows > 0) {
    $row = $resultPet->fetch_assoc();
    $pet_name = $row['pet_name'];
    $hasPet = true;
}
$stmtPet->close();

$conn->close();
?>

<!-- Hamburger and Sound -->
<?php
 include("header.php")
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

    <!-- Pets -->
    <!-- If the user already has a pet, display its image -->
    <?php if ($hasPet): 
        // Determine the image source based on the pet's name (case insensitive)
        $pet_image = "";
        switch (strtolower($pet_name)) {
            case "capybara":
                $pet_image = "../img/pets/Capybara.gif";
                break;
            case "alligator":
                $pet_image = "../img/pets/Alligator.gif";
                break;
            case "axolotl":
                $pet_image = "../img/pets/Axolotl.gif";
                break;
        }
    ?>
        <div class="pet-display">
          <img src="<?php echo $pet_image; ?>" alt="<?php echo htmlspecialchars($pet_name); ?>">
      </div>
    <?php endif; ?>

    <?php if (!$hasPet): ?>
        <div id="pet-modal" class="modal">
            <div class="modal-content">
            <h3 class="pet-header">Select Your Pet</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="pet-options">
                <label class="pet-choice">
                    <input type="radio" name="pet_name" value="Axolotl" required>
                    <img src="../img/pets/Axolotl.png" alt="Axolotl">
                    <span>Axolotl</span>
                </label>
                <label class="pet-choice">
                    <input type="radio" name="pet_name" value="Capybara" required>
                    <img src="../img/pets/Capybara.png" alt="Capybara">
                    <span>Capybara</span>
                </label>
                <label class="pet-choice">
                    <input type="radio" name="pet_name" value="Alligator" required>
                    <img src="../img/pets/Alligator.png" alt="Alligator">
                    <span>Alligator</span>
                </label>
                </div>
                <input type="submit" name="choose_pet" value="Choose Pet">
            </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Progress Section -->
    <div class="progress-section">  
      <div class="skill">
        <div class="inner">
          <div id="number" data-target="<?php echo $percentage; ?>">
            <?php echo $percentage; ?>%
          </div>
          <div class="progress-info">
            <?php echo $completedTasks . " / " . $totalTasks; ?> tasks complete
          </div>
        </div>

        <svg xmlns="http://www.w3.org/2000/svg" width="220" height="220">
          <defs>
            <linearGradient id="GradientColor">
              <stop offset="0%" stop-color="#00ffaa" />
              <stop offset="100%" stop-color="#00eaff" />
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
        <input type="text" id="task_name" name="task_name" placeholder="New task name" maxlength="25" required>
        <input type="date" id="task_duedate" name="task_duedate">
        <textarea id="task_description" name="task_description" placeholder="Task description"></textarea>
        <input type="submit" name="add_task" value="Add Task">
      </form>
    </section>

    <!-- Task List Section -->
    <section class="tasks-section">
      <ul>
        <?php if ($totalTasks == 0): ?>
          <li class="no-tasks">No tasks yet</li>
          <?php else: ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
              <li class="task-item">
                <div class="task-left">
                  <input type="checkbox"
                  <?php if ($row['task_completed']) echo 'checked'; ?>
                  onclick="window.location.href='?<?php echo $row['task_completed'] ? 'incomplete_task_id' : 'complete_task_id'; ?>=<?php echo $row['id']; ?>';" />
                  <div class="task-info">
                    <strong class="task-name"><?php echo htmlspecialchars($row['task_name']); ?></strong>
                    <?php
                      $formattedDueDate = date("m/d/Y", strtotime($row['task_duedate']));
                    ?>
                    <span class="task-date">Due: <?php echo htmlspecialchars($formattedDueDate); ?></span>
                    <span class="desc"><?php echo htmlspecialchars($row['task_description']); ?></span>
                  </div>
                </div>
                <div class="task-right">
                  <a class="delete-button" wrap="soft" href="?delete_task_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this task?');">✕</a>
                </div>
              </li>
          <?php } ?>
        <?php endif; ?>
      </ul>
      <div class="clear-tasks">
                <a href="?clear_tasks=true" onclick="return confirm('Are you sure you want to clear all tasks?');">Clear All Tasks</a>
      </div>
    </section>
  </div>

  <script src="../js/dashboard.js"></script>
</body>
</html>