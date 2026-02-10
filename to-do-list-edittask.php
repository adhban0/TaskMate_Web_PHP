<?php
require_once 'config.php';
if (!isLoggedIn()) { redirect('login.php'); }

$user_id = $_SESSION['id'];
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task'])) {
    $new_title = mysqli_real_escape_string($conn, $_POST['taskTitle']);
    $new_date = !empty($_POST['taskDate']) ? "'" . mysqli_real_escape_string($conn, $_POST['taskDate']) . "'" : "NULL";

    $update_sql = "UPDATE todos SET title = '$new_title', due_date = $new_date 
                   WHERE id = $task_id AND user_id = $user_id";
    
    if (mysqli_query($conn, $update_sql)) {
        header("Location: to-do-list.php");
        exit;
    }
}

// Fetch the current data to pre-fill the form
$query = "SELECT * FROM todos WHERE id = $task_id AND user_id = $user_id LIMIT 1";
$result = mysqli_query($conn, $query);
$task = mysqli_fetch_assoc($result);

if (!$task) {
    header("Location: to-do-list.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Edit Task — TaskMate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-4">
                    <h4 class="mb-4">Edit Task</h4>
                    
                    <form action="to-do-list-edittask.php?id=<?php echo $task_id; ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Task Title</label>
                            <input type="text" name="taskTitle" class="form-control" 
                                   value="<?php echo htmlspecialchars($task['title']); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Due Date</label>
                            <input type="date" name="taskDate" class="form-control" 
                                   value="<?php
  if (!empty($task['due_date']) && $task['due_date'] !== '0000-00-00') {
      echo htmlspecialchars(date('Y-m-d', strtotime($task['due_date'])));
  } else {
      echo '';
  }
?>"
>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="update_task" class="btn btn-dark w-100">Save Changes</button>
                            <a href="to-do-list.php" class="btn btn-outline-secondary w-100">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <script>
        const taskForm = document.getElementById("taskForm");

taskForm.addEventListener("submit", function(event) {

  event.preventDefault();

  const title = document.getElementByID("taskTitle").value.trim();

  const dueDate = document.getElementByID("taskDate").value;



  if (!title) {

      taskInput.focus();

    return;

  }

  title.value = "";

  dueDate.value = "";

});
    </script>
</body>
</html>