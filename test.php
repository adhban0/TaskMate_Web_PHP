<?php 
 $key = '457eac20-49d0-4359-9580-be60f7c7e71b'; 
require_once __DIR__ . '/vendor/autoload.php';

    $holiday_api = new \HolidayAPI\Client(['key' => $key]);
try {
    $holidays = $holiday_api->holidays([
        'country' => 'YE',
        'year'    => '2025',
        'public'  => true,
    ]);
} catch (Exception $e) {
    error_log("HolidayAPI error: " . $e->getMessage());
    // optional: show a friendly message or silently skip
    $holidays = null;
}
echo "<pre>";
var_dump($holidays);
echo "</pre>";
exit;

?>