<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = (int)$_SESSION['id'];

// Detect edit mode
$note_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$noteTitle = "";
$noteContent = "";

// If editing, load note data (only if it belongs to this user)
if ($note_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT title, content FROM notes WHERE id = ? AND user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ii", $note_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $noteTitle = $row['title'];
        $noteContent = $row['content'];
    } else {
        // Note not found or not yours
        redirect('notes.php'); // change to your notes listing page name
    }

    mysqli_stmt_close($stmt);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TaskMate — Note Editor</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    body { background: #f6f7fb; }
    .card { border: 0; border-radius: 16px; }
    .shadow-soft { box-shadow: 0 10px 30px rgba(0,0,0,.06); }
    .text-muted-2 { color: #6b7280; }

    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 .2rem rgba(17, 24, 39, .08);
      border-color: rgba(17, 24, 39, .25);
    }

    .editor {
      min-height: 58vh;
      resize: vertical;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial;
      line-height: 1.6;
    }

    span[id$="_error"] {
      display: inline-block;
      margin-left: 6px;
      font-size: 0.85rem;
      color: #dc2626;
      font-weight: 500;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">
<?php include("header.php");?>

<main class="container my-4 flex-grow-1">
  <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
      <div class="d-flex align-items-center gap-2">
        <a href="notes.php" class="btn btn-sm btn-outline-secondary">
          <i class="fa-solid fa-arrow-left me-2"></i>Back
        </a>
        <h2 class="mb-0">Note editor</h2>
      </div>
      <div class="text-muted-2 mt-1">
        <?php echo ($note_id > 0) ? "Edit your note and save changes." : "Write a new note and save it."; ?>
      </div>
    </div>

    <div class="d-flex flex-wrap gap-2 align-items-center">
      <!-- IMPORTANT: action goes to notes-save.php -->
      <form id="note-form" action="notes-save.php" method="POST" class="m-0">
        <?php if ($note_id > 0): ?>
          <!-- Hidden id to tell save.php to UPDATE -->
          <input type="hidden" name="note_id" value="<?php echo (int)$note_id; ?>">
        <?php endif; ?>

        <button class="btn btn-dark" type="submit">
          <i class="fa-regular fa-floppy-disk me-2"></i>Save
        </button>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="card shadow-soft">
        <div class="card-body">

          <div class="mb-3">
            <label class="form-label small text-muted-2 mb-1">Title</label>
            <input
              class="form-control form-control-lg"
              name="noteTitle"
              value="<?php echo htmlspecialchars($noteTitle, ENT_QUOTES, 'UTF-8'); ?>"
            />
          </div>

          <label class="form-label small text-muted-2 mb-1">
            Content <span id="note_error"></span>
          </label>

          <textarea
            class="form-control editor"
            rows="14"
            id="form-note"
            name="noteContent"
          ><?php echo htmlspecialchars($noteContent, ENT_QUOTES, 'UTF-8'); ?></textarea>

        </div>
      </div>
    </div>
  </div>

  </form> <!-- close form -->
</main>

<?php include("footer.php");?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const form = document.getElementById("note-form");
  const note_error = document.getElementById("note_error");

  form.addEventListener('submit', function(event){
    const note = document.getElementById("form-note").value.trim();
    note_error.textContent = "";

    if (!note) {
      event.preventDefault();
      note_error.textContent = "The note is empty!";
      return;
    }
  });
</script>

</body>
</html>
