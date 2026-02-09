<?php
require_once 'config.php';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $user_id = $_SESSION['id'];

    $sql = "UPDATE todos SET is_completed = NOT is_completed WHERE id = $id AND user_id = $user_id";
    mysqli_query($conn, $sql);
}
header("Location: to-do-list.php");