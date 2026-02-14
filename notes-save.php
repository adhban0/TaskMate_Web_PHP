<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = (int)$_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('notes.php'); 
}

$note_id = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;
$title   = isset($_POST['noteTitle']) ? trim($_POST['noteTitle']) : "";
$content = isset($_POST['noteContent']) ? trim($_POST['noteContent']) : "";

if (empty($content)) {
    redirect('editor.php' . ($note_id > 0 ? '?id=' . $note_id . '&error=notitle': '?error=notitle'));
}


if ($note_id > 0) {
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE notes
         SET title = ?, content = ?, updated_at = NOW()
         WHERE id = ? AND user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssii", $title, $content, $note_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

} else {
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO notes (user_id, title, content, updated_at)
         VALUES (?, ?, ?, NOW())"
    );
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $title, $content);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

redirect('notes.php');
