<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['taskTitle'])) {
    $title = mysqli_real_escape_string($conn, $_POST['taskTitle']);
    if (empty($title)){
        redirect('to-do-list.php');
    }
    $due_date = !empty($_POST['taskDate']) ? "'" . mysqli_real_escape_string($conn, $_POST['taskDate']) . "'" : "NULL";
    $user_id = $_SESSION['id'];

    $sql = "INSERT INTO todos (user_id, title, due_date, is_completed) VALUES ('$user_id', '$title', $due_date, 0)";
    mysqli_query($conn, $sql);
}
header("Location: to-do-list.php"); ?>