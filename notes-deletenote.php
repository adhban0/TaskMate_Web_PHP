<?php
require_once 'config.php';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $user_id = $_SESSION['id'];
    
    // Ensure the task belongs to the logged-in user for security
    $sql = "DELETE FROM notes WHERE id = $id AND user_id = $user_id";
    mysqli_query($conn, $sql);
}
header("Location: notes.php");