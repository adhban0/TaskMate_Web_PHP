<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = (int)$_SESSION['id'];

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('notes.php'); // change to your notes listing page name
}

// Get inputs
$note_id = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;
$title   = isset($_POST['noteTitle']) ? trim($_POST['noteTitle']) : "";
$content = isset($_POST['noteContent']) ? trim($_POST['noteContent']) : "";

// Basic validation
if (empty($content)) {
    redirect('editor.php' . ($note_id > 0 ? '?id=' . $note_id : ''));
}

// Optional: default title if empty

if ($note_id > 0) {
    // UPDATE (only if note belongs to user)
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
    // INSERT
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO notes (user_id, title, content, updated_at)
         VALUES (?, ?, ?, NOW())"
    );
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $title, $content);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Go back to notes list
redirect('notes.php'); // change to your notes listing page name
