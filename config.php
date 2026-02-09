<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'taskmate');
$key = '457eac20-49d0-4359-9580-be60f7c7e71b'; 
require_once __DIR__ . '/vendor/autoload.php';

    $holiday_api = new \HolidayAPI\Client(['key' => $key]);
function seedUserHolidays(mysqli $conn, int $user_id, array $apiResponse, int $targetYear): void
{
    if (
        empty($apiResponse) ||
        !isset($apiResponse['holidays']) ||
        !is_array($apiResponse['holidays'])
    ) {
        return;
    }

    // safety
    if ($targetYear < 1970 || $targetYear > 2100) return;

    $sql = "INSERT INTO calendar_events
            (user_id, title, event_date, event_time, description)
            VALUES (?, ?, ?, NULL, ?)
            ON DUPLICATE KEY UPDATE description = VALUES(description)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return;

    foreach ($apiResponse['holidays'] as $h) {
        $title = $h['name'] ?? null;
        $date  = $h['date'] ?? null; // e.g. 2025-05-01

        if (!$title || !$date) continue;

        // Extract month-day safely (expects YYYY-MM-DD)
        if (!preg_match('/^\d{4}-(\d{2})-(\d{2})$/', $date, $m)) continue;
        $month = $m[1];
        $day   = $m[2];

        // Build new date with the target year
        $newDate = sprintf('%04d-%02d-%02d', $targetYear, (int)$month, (int)$day);

        // Extra safety: validate date (handles leap years etc.)
        if (!checkdate((int)$month, (int)$day, $targetYear)) {
            // Example: Feb 29 when targetYear isn't leap year — skip it
            continue;
        }

        $descParts = [];
        if (isset($h['public'])) {
            $descParts[] = $h['public'] ? 'Public holiday' : 'Non-public observance';
        }

        // Also rewrite "observed" date to target year if you want to display it
        if (!empty($h['observed']) && $h['observed'] !== $date) {
            if (preg_match('/^\d{4}-(\d{2})-(\d{2})$/', $h['observed'], $o)) {
                $obsMonth = (int)$o[1];
                $obsDay   = (int)$o[2];
                if (checkdate($obsMonth, $obsDay, $targetYear)) {
                    $descParts[] = 'Observed: ' . sprintf('%04d-%02d-%02d', $targetYear, $obsMonth, $obsDay);
                }
            }
        }

        $description = implode(' | ', $descParts);

        $stmt->bind_param('isss', $user_id, $title, $newDate, $description);
        $stmt->execute();
    }

    $stmt->close();
}


// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    // Use mysqli_connect_error() for error reporting
    die('Connection failed: ' . mysqli_connect_error());
}

// Start session
session_start();

// Helper function to check if admin is logged in
function isLoggedIn() {
    return isset($_SESSION['id']);
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}
?>