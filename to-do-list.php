<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['id'];
$tasks = [];

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$error = isset($_GET['error']) ? 'Title is required' : '';

$where_completed = "";
if ($filter === "completed") {
    $where_completed = " AND is_completed = 1";
} elseif ($filter === "active") {
    $where_completed = " AND is_completed = 0";
}

$tasks_query = "SELECT id, title, due_date, is_completed
               FROM todos
               WHERE user_id = $user_id $where_completed
               ORDER BY due_date ASC, id DESC";

$tasks_result = mysqli_query($conn, $tasks_query);

if ($tasks_result) {
    while ($row = mysqli_fetch_assoc($tasks_result)) {
        $tasks[] = $row;
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>TaskMate — To-do</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body { background: #f6f7fb; }
        .todo-item { border: 1px solid rgba(0,0,0,.06); border-radius: 14px; background: #fff; margin-bottom: 12px; }
        .completed { opacity: 0.6; }
    </style>
</head>
<body>
    <?php include("header.php"); ?>
    
    <main class="container my-4">
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm p-3">
                    <h5 class="mb-3">Add new task</h5>
                    <form id="taskForm" action="to-do-list-addtask.php" method="POST">
                        <div class="mb-2">
                            <label class="small text-muted">Task Title</label><span> <?php echo $error;?></span>
                            <input type="text" id= "taskTitle" name="taskTitle" class="form-control" required placeholder="What needs doing?">
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted">Due Date</label>
                            <input type="date" id= "taskDate" name="taskDate" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Add Task</button>
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Your tasks</h5>

    <div class="btn-group" role="group" aria-label="Filter tasks">
        <a class="btn btn-sm <?php echo ($filter==='all') ? 'btn-dark' : 'btn-outline-dark'; ?>"
           href="to-do-list.php?filter=all">All</a>

        <a class="btn btn-sm <?php echo ($filter==='active') ? 'btn-dark' : 'btn-outline-dark'; ?>"
           href="to-do-list.php?filter=active">Not completed</a>

        <a class="btn btn-sm <?php echo ($filter==='completed') ? 'btn-dark' : 'btn-outline-dark'; ?>"
           href="to-do-list.php?filter=completed">Completed</a>
    </div>
</div>


                    <?php if (empty($tasks)): ?>
                        <p class="text-center text-muted">No tasks found. Add one!</p>
                    <?php endif; ?>

                    <?php foreach ($tasks as $task): ?>
                        <div class="todo-item p-3 <?php echo $task['is_completed'] ? 'completed' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold <?php echo $task['is_completed'] ? 'text-decoration-line-through' : ''; ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </div>
                                    <?php if (!empty($task['due_date']) && $task['due_date'] !== '0000-00-00'): ?>
    <small class="text-muted">
        <i class="fa-regular fa-calendar"></i> 
        <?php echo date('d M Y', strtotime($task['due_date'])); ?>
    </small>
<?php else: ?>
    <small class="text-muted-2 italic">No due date</small>
<?php endif; ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="to-do-list-toggletask.php?id=<?php echo $task['id']; ?>" class="btn btn-sm <?php echo $task['is_completed'] ? 'btn-success' : 'btn-outline-secondary'; ?>">
                                        <i class="fa-solid fa-check"></i>
                                    </a>
                                    <a href="to-do-list-edittask.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary">
        <i class="fa-solid fa-pen"></i>
    </a>
                                    <a href="to-do-list-deletetask.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this task?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById("todayDate").textContent = new Date().toLocaleDateString(undefined, { weekday: "long", year: "numeric", month: "long", day: "numeric" });
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
taskForm.submit();
});

    </script>

</body>
</html>
