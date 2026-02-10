<?php
require_once 'config.php';

if (!isLoggedIn()) {
  redirect('login.php');
}
$user_id = (int)$_SESSION['id'];
$error = null;
$title= '';
$event_date = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  $returnYm = $_POST['return_ym'] ?? date('Y-m'); 

  
  $title = trim($_POST['title'] ?? '');
  $event_date = $_POST['event_date'] ?? '';
  $event_time = trim($_POST['event_time'] ?? ''); 
  $description = trim($_POST['description'] ?? '');

  // add 00 to the time of html or null if it is ''
  $event_time_db = ($event_time !== '') ? ($event_time . ':00') : null;
  // null if it is ''
  $description_db = ($description !== '') ? $description : null;

  try {
    if ($action === 'create') {
      if ($title === '' || $event_date === '') {
        
        $error = "Title and date are required";
      }
if (!$error){
      $sql = "INSERT INTO calendar_events (user_id, title, event_date, event_time, description)
              VALUES (?, ?, ?, ?, ?)";
      $stmt = mysqli_prepare($conn,$sql);
      mysqli_stmt_bind_param($stmt,"issss", $user_id, $title, $event_date, $event_time_db, $description_db);
      mysqli_stmt_execute( $stmt );

      redirect("calendar.php?ym=" . urlencode($returnYm));
      exit;}
    }

    if ($action === 'update') {
      $id = (int)($_POST['id'] ?? 0);
      if ($id <= 0 || $title === '' || $event_date === '') {
        $error = "Title and date are required";
      }
      if (!$error){
      $sql = "UPDATE calendar_events
              SET title = ?, event_date = ?, event_time = ?, description = ?
              WHERE id = ? AND user_id = ?";
      $stmt = mysqli_prepare($conn,$sql);
      mysqli_stmt_bind_param($stmt, $title, $event_date, $event_time_db, $description_db, $id, $user_id);
      mysqli_stmt_execute( $stmt );

      redirect("calendar.php?ym=" . urlencode($returnYm));
      exit;}
    }

    if ($action === 'delete') {
      $id = (int)($_POST['id'] ?? 0);
      if ($id > 0) {
        $sql = "DELETE FROM calendar_events WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn,$sql);
        mysqli_stmt_bind_param($stmt,"ii", $id, $user_id);
        mysqli_stmt_execute( $stmt );
      }

      redirect("calendar.php?ym=" . urlencode($returnYm));
      exit;
    }

  } catch (Throwable $e) {
      redirect("calendar.php?ym=" . urlencode($returnYm));
    exit;
  }
}


$ym = $_GET['ym'] ?? date('Y-m'); // YYYY-MM
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) $ym = date('Y-m');

[$year, $month] = array_map('intval', explode('-', $ym));
// Builds a date string like "2026-02-01" (first day of the month)
// sprintf('%04d-%02d-01', ...) ensures correct formatting:
// year always 4 digits
// month always 2 digits (02 not 2)
// Then creates a DateTime object for that date.
$firstOfMonth = new DateTime(sprintf('%04d-%02d-01', $year, $month));
$startDow = (int)$firstOfMonth->format('w'); // format('w') gives the day-of-week number:
// Makes a copy of $firstOfMonth.
  $gridStart = clone $firstOfMonth;
//   Moves $gridStart backwards until it reaches the Sunday of the first week shown.
// modify is a datetime method
$gridStart->modify("-{$startDow} days");
$gridEnd = clone $gridStart;
$gridEnd->modify("+41 days");
$gridStartStr = $gridStart->format('Y-m-d');
$gridEndStr   = $gridEnd->format('Y-m-d');
// Prev/Next month links
$prev = clone $firstOfMonth; $prev->modify('-1 month');
$next = clone $firstOfMonth; $next->modify('+1 month');
$prevYm = $prev->format('Y-m');
$nextYm = $next->format('Y-m');
$todayStr = (new DateTime())->format('Y-m-d');


$eventsByDate = []; 
$sql = "SELECT id, title, event_date, event_time, description
        FROM calendar_events
        WHERE user_id = ? AND event_date BETWEEN ? AND ?
        ORDER BY event_date ASC, event_time ASC, id ASC";
        $stmt = mysqli_prepare($conn,$sql);
        mysqli_stmt_bind_param($stmt,"iss", $user_id, $gridStartStr, $gridEndStr);
        mysqli_stmt_execute( $stmt );
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
  $d = $row['event_date'];
  if (!isset($eventsByDate[$d])) $eventsByDate[$d] = [];

  $eventsByDate[$d][] = [
    'id' => (int)$row['id'],
    'title' => $row['title'],
     // take a substring to only record hours and minutes (seconds are irrelevant)
    'time' => $row['event_time'] ? substr($row['event_time'], 0, 5) : '',
    'notes' => $row['description'] ?? ''
  ];
}


$agenda = [];
$sql = "SELECT id, title, event_date, event_time, description
        FROM calendar_events
        WHERE user_id = ? AND event_date >= ?
        ORDER BY event_date ASC, event_time ASC, id ASC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $todayStr);
$stmt->execute();
    $result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
  $agenda[] = [
    'id' => (int)$row['id'],
    'title' => $row['title'],
    'date' => $row['event_date'],
    'time' => $row['event_time'] ? substr($row['event_time'], 0, 5) : '',
    'notes' => $row['description'] ?? ''
  ];
}
// for day detail modal
$eventsJson = json_encode($eventsByDate, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TaskMate — Calendar</title>

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

    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; }
    .cal-dow {
      font-size: .85rem; color: #6b7280; text-transform: uppercase;
      letter-spacing: .04em; text-align: center; padding: 6px 0;
    }
    .cal-day {
      background: #fff; border: 1px solid rgba(0,0,0,.06);
      border-radius: 14px; min-height: 120px; padding: 10px; position: relative;
    }
    .cal-day.muted { background: rgba(255,255,255,.55); color: #9ca3af; }
    .cal-day .num { font-weight: 700; font-size: .95rem; color: #111827; }
    .cal-day.muted .num { color: #9ca3af; }
    .cal-day.today { outline: 2px solid rgba(17,24,39,.20); outline-offset: 2px; }

    .chip {
      display: flex; align-items: center; gap: .45rem;
      border-radius: 999px; padding: .25rem .55rem;
      font-size: .82rem; margin-top: 8px;
      border: 1px solid rgba(0,0,0,.08);
      background: #fff; color: #111827;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .dot { width: 10px; height: 10px; border-radius: 50%; background: #111827; flex: 0 0 auto; }

    .pill {
      border: 1px solid rgba(0,0,0,.10);
      background: #fff;
      border-radius: 999px;
      padding: .35rem .6rem;
      font-size: .85rem;
      color: #111827;
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      white-space: nowrap;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include("header.php"); ?>

  <main class="container my-4 flex-grow-1">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
      <div>
        <h2 class="mb-1">Calendar</h2>
        <div class="text-muted-2">Plan your week, track events, and stay on schedule.</div>
      </div>
      <div class="text-end">
        <div class="small text-muted-2">Today</div>
        <div class="fw-semibold">
          <?php echo htmlspecialchars((new DateTime())->format('l, F j, Y')); ?>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-8">
        <div class="card shadow-soft">
          <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
              <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-secondary" href="calendar.php?ym=<?php echo urlencode($prevYm); ?>" title="Previous month">
                  <i class="fa-solid fa-chevron-left"></i>
                </a>

                <a class="btn btn-outline-secondary" href="calendar.php?ym=<?php echo urlencode($nextYm); ?>" title="Next month">
                  <i class="fa-solid fa-chevron-right"></i>
                </a>

                <a class="btn btn-dark" href="calendar.php?ym=<?php echo date('Y-m'); ?>">
                  <i class="fa-regular fa-calendar-check me-2"></i>Today
                </a>
              </div>
              <div class="pill">
                <i class="fa-regular fa-calendar me-1"></i>
                <?php echo htmlspecialchars($firstOfMonth->format('F Y')); ?>
              </div>
            </div>

            <div class="cal-grid mb-2">
              <div class="cal-dow">Sun</div>
              <div class="cal-dow">Mon</div>
              <div class="cal-dow">Tue</div>
              <div class="cal-dow">Wed</div>
              <div class="cal-dow">Thu</div>
              <div class="cal-dow">Fri</div>
              <div class="cal-dow">Sat</div>
            </div>

            <div class="cal-grid" id="calGrid">
              <?php
              $cursor = clone $gridStart;
              for ($i = 0; $i < 42; $i++) {
                $cellDate = $cursor->format('Y-m-d');
                $cellDay  = $cursor->format('j'); // day of month in numbers
                $isMuted = ($cursor->format('Y-m') !== $firstOfMonth->format('Y-m')); // if the day's month is different from the current month
                $isToday = ($cellDate === $todayStr);

                $cellClasses = "cal-day";
                if ($isMuted) $cellClasses .= " muted";
                if ($isToday && !$isMuted) $cellClasses .= " today";

                $dayEvents = $eventsByDate[$cellDate] ?? [];
              ?>
                <div class="<?php echo $cellClasses; ?>">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="num"><?php echo htmlspecialchars($cellDay); ?></div>

                    <!-- Add event (opens modal) -->
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary py-0 px-2"
                      title="Add"
                      data-action="add"
                      data-date="<?php echo htmlspecialchars($cellDate); ?>">
                      <i class="fa-solid fa-plus"></i>
                    </button>
                  </div>

                  <?php
                  // show first 3 chips
                  $show = array_slice($dayEvents, 0, 3);
                  foreach ($show as $ev) {
                    $t = htmlspecialchars($ev['title']);
                    $time = htmlspecialchars($ev['time']);
                    $notes = htmlspecialchars($ev['notes']);
                    $id = (int)$ev['id'];
                  ?>
                  <!-- shows each event as a button -->
                    <button
                      type="button"
                      class="chip text-start"
                      data-action="edit"
                      data-id="<?php echo $id; ?>"
                      data-date="<?php echo htmlspecialchars($cellDate); ?>"
                      data-title="<?php echo $t; ?>"
                      data-time="<?php echo $time; ?>"
                      data-notes="<?php echo $notes; ?>">
                      <span class="dot"></span>
                      <span class="text-truncate"><?php echo $t; ?></span>
                    </button>
                  <?php } ?>

                  <?php if (count($dayEvents) > 3): ?>
                    <div
                      class="small text-primary mt-2 fw-bold"
                      style="cursor:pointer;"
                      data-action="view-day"
                      data-date="<?php echo htmlspecialchars($cellDate); ?>">
                      +<?php echo (count($dayEvents) - 3); ?> more
                    </div>
                  <?php endif; ?>
                </div>
              <?php
                $cursor->modify('+1 day');
              }
              ?>
            </div>

          </div>
        </div>
      </div>

      <!-- Right: Agenda -->
      <div class="col-12 col-lg-4">
        <div class="card shadow-soft mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="mb-0">Agenda</h5>
            </div>

            <div class="list-group list-group-flush" id="agendaList">
              <?php if (count($agenda) === 0): ?>
                <div class="py-3 text-muted-2 small">No upcoming events.</div>
              <?php else: ?>
                <?php foreach ($agenda as $ev):
                  $id = (int)$ev['id'];
                  $t = htmlspecialchars($ev['title']);
                  $d = htmlspecialchars($ev['date']);
                  $time = htmlspecialchars($ev['time']);
                  $notes = htmlspecialchars($ev['notes']);
                ?>
                  <div
                    class="list-group-item bg-transparent px-0 border-bottom-0"
                    style="cursor:pointer;"
                    data-action="edit"
                    data-id="<?php echo $id; ?>"
                    data-date="<?php echo $d; ?>"
                    data-title="<?php echo $t; ?>"
                    data-time="<?php echo $time; ?>"
                    data-notes="<?php echo $notes; ?>">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <div class="fw-semibold"><?php echo $t; ?></div>
                        <div class="small text-muted-2"><?php echo $d; ?> • <?php echo $time !== '' ? $time : 'All Day'; ?></div>
                      </div>
                      <i class="fa-solid fa-chevron-right small text-muted"></i>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <div class="d-grid mt-3">
              <button class="btn btn-outline-dark" id="agendaAddBtn" type="button">
                <i class="fa-solid fa-plus me-2"></i>Add event
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>

  <!-- Add/Edit Modal (single form) -->
  <div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" method="POST" style="border-radius: 16px; border: 0;">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="eventModalTitle">Add event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body pt-0">
          <div id="modalError" class="alert alert-danger py-2 mb-2 d-none" role="alert">
  <i class="fa-solid fa-circle-exclamation me-1"></i>
  <span id="modalErrorText"></span>
</div>

          <?php if ($error): ?>
  <div class="alert alert-danger py-2 mb-2">
    <i class="fa-solid fa-circle-exclamation me-1"></i>
    <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

          <input type="hidden" name="action" id="formAction" value="create">
          <input type="hidden" name="id" id="formId" value="">
          <input type="hidden" name="return_ym" value="<?php echo htmlspecialchars($ym); ?>">

          <div class="mb-2">
            <label class="form-label small text-muted-2 mb-1">Title</label>
            <input name="title" id="eventTitle" class="form-control" placeholder="e.g., Client call" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"/>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label small text-muted-2 mb-1">Date</label>
              <input name="event_date" id="eventDate" type="date" class="form-control" value="<?php echo htmlspecialchars($_POST['event_date'] ?? ''); ?>"/>
            </div>
            <div class="col-6">
              <label class="form-label small text-muted-2 mb-1">Time</label>
              <input name="event_time" id="eventTime" type="time" class="form-control" value="<?php echo htmlspecialchars($_POST['event_time'] ?? ''); ?>"/>
            </div>
          </div>

          <div class="mt-2">
            <label class="form-label small text-muted-2 mb-1">Notes (optional)</label>
            <textarea name="description" id="eventNotes" class="form-control" rows="3" placeholder="Add details..."></textarea>
          </div>

          <div class="d-grid mt-3 gap-2">
            <button class="btn btn-dark" id="eventSaveBtn" type="submit">
              <i class="fa-solid fa-plus me-2" id="saveIcon"></i><span id="eventSaveText">Create event</span>
            </button>

            <!-- Delete button (only shown in edit mode) -->
            <button class="btn btn-outline-danger border-0 d-none" id="eventDeleteBtn" type="button">
              <i class="fa-solid fa-trash-can me-2"></i>Delete event
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Day detail modal -->
  <div class="modal fade" id="dayDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content" style="border-radius: 16px; border: 0;">
        <div class="modal-header border-0 pb-0">
          <h6 class="modal-title text-muted-2" id="dayDetailTitle">Date</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="dayDetailList" class="list-group list-group-flush"></div>
          <div class="d-grid mt-3">
            <button class="btn btn-sm btn-light" id="dayDetailAddBtn" type="button">
              <i class="fa-solid fa-plus me-1"></i> Add New
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include("footer.php"); ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const eventsByDate = <?php echo $eventsJson ?: '{}'; ?>;

    const addEventModal = new bootstrap.Modal(document.getElementById("addEventModal"));
    const dayDetailModal = new bootstrap.Modal(document.getElementById("dayDetailModal"));

    const calGrid = document.getElementById("calGrid");
    const agendaList = document.getElementById("agendaList");

    const formAction = document.getElementById("formAction");
    const formId = document.getElementById("formId");

    const eventModalTitle = document.getElementById("eventModalTitle");
    const eventSaveText = document.getElementById("eventSaveText");
    const saveIcon = document.getElementById("saveIcon");

    const eventTitle = document.getElementById("eventTitle");
    const eventDate = document.getElementById("eventDate");
    const eventTime = document.getElementById("eventTime");
    const eventNotes = document.getElementById("eventNotes");

    const eventDeleteBtn = document.getElementById("eventDeleteBtn");

    const dayDetailTitle = document.getElementById("dayDetailTitle");
    const dayDetailList = document.getElementById("dayDetailList");
    const dayDetailAddBtn = document.getElementById("dayDetailAddBtn");

    let activeDetailDate = "";

    function openCreate(dateStr) {
      formAction.value = "create";
      formId.value = "";

      eventModalTitle.textContent = "Add event";
      eventSaveText.textContent = "Create event";
      saveIcon.className = "fa-solid fa-plus me-2";

      eventDeleteBtn.classList.add("d-none");

      eventTitle.value = "";
      eventDate.value = dateStr || "";
      eventTime.value = "";
      eventNotes.value = "";

      addEventModal.show();
      setTimeout(() => eventTitle.focus(), 150);
    }

    function openEditFromDataset(ds) {
      formAction.value = "update";
      formId.value = ds.id || "";

      eventModalTitle.textContent = "Edit event";
      eventSaveText.textContent = "Save changes";
      saveIcon.className = "fa-solid fa-floppy-disk me-2";

      eventDeleteBtn.classList.remove("d-none");

      eventTitle.value = ds.title || "";
      eventDate.value = ds.date || "";
      eventTime.value = ds.time || "";
      eventNotes.value = ds.notes || "";

      addEventModal.show();
      setTimeout(() => eventTitle.focus(), 150);
    }

    // Delete: turn the same form into delete and submit
    eventDeleteBtn.addEventListener("click", () => {
      formAction.value = "delete";
      eventDeleteBtn.closest("form").submit();
    });

    // Calendar grid click handling
    calGrid.addEventListener("click", (e) => {
      const el = e.target.closest("[data-action]");
      if (!el) return;

      const action = el.dataset.action;

      if (action === "add") {
        openCreate(el.dataset.date);
      }

      if (action === "edit") {
        openEditFromDataset(el.dataset);
      }

      if (action === "view-day") {
        openDayDetail(el.dataset.date);
      }
    });

    agendaList.addEventListener("click", (e) => {
      const el = e.target.closest("[data-action='edit']");
      if (!el) return;
      openEditFromDataset(el.dataset);
    });

    document.getElementById("agendaAddBtn").addEventListener("click", () => {
      const today = new Date();
      const y = today.getFullYear();
      // add zeros to the left of the string until it reaches 2 characters in total
      const m = String(today.getMonth() + 1).padStart(2, "0");
      const d = String(today.getDate()).padStart(2, "0");
      openCreate(`${y}-${m}-${d}`);
    });

    // Day detail modal: list all events for a date
    function openDayDetail(dateStr) {
      activeDetailDate = dateStr;
      const d = new Date(dateStr + "T00:00:00");
      dayDetailTitle.textContent = d.toLocaleDateString(undefined, { month: "short", day: "numeric", year: "numeric" });

      dayDetailList.innerHTML = "";
      const list = eventsByDate[dateStr] || [];

      if (list.length === 0) {
        dayDetailList.innerHTML = `<div class="py-2 text-muted small">No events for this day.</div>`;
      } else {
        list.forEach((ev) => {
          const btn = document.createElement("button");
          btn.type = "button";
          btn.className = "list-group-item list-group-item-action border-0 px-0 d-flex align-items-center gap-2";
          btn.innerHTML = `
            <span class="dot"></span>
            <div class="flex-grow-1">
              <div class="fw-semibold small">${ev.title}</div>
              <div class="text-muted" style="font-size: 0.75rem;">${ev.time ? ev.time : "No time"}</div>
            </div>
          `;

          btn.addEventListener("click", () => {
            dayDetailModal.hide();
            openEditFromDataset({
              id: ev.id,
              title: ev.title,
              date: dateStr,
              time: ev.time || "",
              notes: ev.notes || ""
            });
          });

          dayDetailList.appendChild(btn);
        });
      }

      dayDetailModal.show();
    }

    dayDetailAddBtn.addEventListener("click", () => {
      dayDetailModal.hide();
      openCreate(activeDetailDate);
    });
    const modalForm = document.querySelector("#addEventModal form");
const modalError = document.getElementById("modalError");
const modalErrorText = document.getElementById("modalErrorText");
function showModalError(msg) {
  modalErrorText.textContent = msg;
  modalError.classList.remove("d-none");
}

function clearModalError() {
  modalError.classList.add("d-none");
  modalErrorText.textContent = "";
  eventTitle.classList.remove("is-invalid");
  eventDate.classList.remove("is-invalid");
}

function markInvalid(el) {
  el.classList.add("is-invalid");
}
modalForm.addEventListener("submit", (e) => {
  clearModalError();

  const title = eventTitle.value.trim();
  const date = eventDate.value.trim();

  if (title === "" || date === "") {
    e.preventDefault(); 

    showModalError("Title and date are required");

    // Mark and focus first missing input
    if (title === "") {
      markInvalid(eventTitle);
      eventTitle.focus();
    } else {
      markInvalid(eventDate);
      eventDate.focus();
    }
    return;
  }

});
// Clear error + invalid styles when the modal closes 
document.getElementById("addEventModal").addEventListener("hidden.bs.modal", () => {
  clearModalError();          
  eventTitle.value = "";
  eventDate.value = "";
  eventTime.value = "";
  eventNotes.value = "";
});




  </script>
  <?php if ($error): ?>
<script>
  const modal = new bootstrap.Modal(document.getElementById("addEventModal"));
  modal.show();
</script>
<?php endif; ?>

</body>
</html>
