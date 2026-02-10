<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['id'];
$tasks = [];
$events =[];
$todayStr = (new DateTime())->format('Y-m-d');


$tasks_query = "SELECT id, title, due_date, is_completed
               FROM todos
               WHERE user_id = $user_id and is_completed = 0 and due_date is not null
               ORDER BY due_date ASC, id DESC LIMIT 5";
$events_query = "SELECT id, title, event_date, event_time
               FROM calendar_events
               WHERE user_id = $user_id and event_date >= $todayStr
               ORDER BY event_date ASC, id DESC LIMIT 5";

$tasks_result = mysqli_query($conn, $tasks_query);

if ($tasks_result) {
    while ($row = mysqli_fetch_assoc($tasks_result)) {
        $tasks[] = $row;
    }
}
$events_result = mysqli_query($conn, $events_query);

if ($events_result) {
    while ($row = mysqli_fetch_assoc($events_result)) {
        $events[] = $row;
    }
}
$ai_summary = "No tasks/events found to analyze.";

if (!empty($tasks) || !empty($events)) {
    $apiKey = "AIzaSyCIl6TtnqbFrTAJ0bYd9IlMoY5HXbxez9I"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;

    $task_list_string = "";
    foreach ($tasks as $t) {
        $task_list_string .= "- " . $t['title'] . " (Due: " . $t['due_date'] . ")\n";
    }
    $events_list_string = "";
    foreach ($events as $e) {
        $events_list_string .= "- " . $e['title'] . " (Date: " . $e['event_date'] . ")\n";
    }

    $prompt = "Act as a productivity coach. ";

if (!empty(trim($task_list_string))) {
    $prompt .= "Here are my top 5 upcoming tasks:\n" . $task_list_string . "\n";
}

// Add events only if they exist
if (!empty(trim($events_list_string))) {
    $prompt .= "Here are my top 5 upcoming events on the calendar:\n" . $events_list_string . "\n";
}

$prompt .= "Give me a 2-sentence productivity plan and tell me which one to focus on first. Keep it brief and encouraging.";

    $data = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $response_data = json_decode($response, true);
    curl_close($ch);


    // Extract the text from Gemini's JSON structure
    if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
        $ai_summary = $response_data['candidates'][0]['content']['parts'][0]['text'];
    } else {
        $ai_summary = "AI is resting at the moment. Try again later!";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TaskMate — Dashboard</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body { background: #f6f7fb; }
    .card { border: 0; border-radius: 16px; }
    .shadow-soft { box-shadow: 0 10px 30px rgba(0,0,0,.06); }
    .badge-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 8px; }
    .text-muted-2 { color: #6b7280; }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">
  <!-- Top Navbar -->
<?php include ("header.php");?>

  <main class="container my-4 flex-grow-1">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
      <div>
        <h2 class="mb-1">Welcome back</h2>
        <div class="text-muted-2">Here’s what’s coming up next.</div>
      </div>
      <div class="text-end">
        <div class="small text-muted-2">Today</div>
        <div class="fw-semibold" id="todayDate">—</div>
      </div>
    </div>

  

<div class="row g-3 d-flex align-items-stretch">
  <div class="col-12 col-lg-5">
    <div class="card shadow-soft h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0">Upcoming deadlines</h5>
          <a class="small text-decoration-none" href="to-do-list.php">Open to-do list</a>
        </div>
        <div class="list-group list-group-flush">
          <?php foreach ($tasks as $task): ?>
            <div class="list-group-item bg-transparent px-0">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold"><?php echo $task['title'];?></span>
                <span class="fw-semibold text-end"><?php echo date('d M Y', strtotime($task['due_date']));?></span>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if(empty($tasks)): ?>
            <div class="text-muted small py-2">No upcoming deadlines.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card shadow-soft h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0">Calendar events</h5>
          <a class="small text-decoration-none" href="calendar.php">Open calendar</a>
        </div>
        <div class="list-group list-group-flush">
          <?php foreach ($events as $event): ?>
            <div class="list-group-item bg-transparent px-0">
              <div class="fw-semibold"><?php echo $event['title'];?></div>
              <div class="small text-muted-2"><?php echo $event['event_date'] . " " . $event['event_time'];?></div>
            </div>
          <?php endforeach; ?>
          <?php if(empty($events)): ?>
            <div class="text-muted small py-2">No upcoming events.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-3">
    <div class="card shadow-soft h-100"> <div class="card-body d-flex flex-column">
        <h5 class="mb-2">AI summary</h5>
        <div class="small text-muted-2 mb-2">Your next best actions:</div>
        <div class="p-3 bg-white rounded-3 border flex-grow-1">
          <?php echo nl2br(htmlspecialchars($ai_summary)); ?>
        </div>
      </div>
    </div>
  </div>
</div>
  </main>
<?php include ("footer.php");?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById("todayDate").textContent =
      new Date().toLocaleDateString(undefined, { weekday: "long", year: "numeric", month: "long", day: "numeric" });
  </script>
</body>
</html>
