<?php
include_once '../../config/database.php';
include_once '../../utils/cors.php';

$database = new Database();
$db = $database->getConnection();

// Fetch schedules (acting as the 'events')
$query = "SELECT schedule_id, schedule_date, opening_time, start_time, end_time, slot_limit FROM queue_schedule WHERE deleted_at IS NULL ORDER BY schedule_date DESC, start_time ASC";
$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if($num > 0) {
    $schedules_arr = array();
    $schedules_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $schedule_item = array(
            "schedule_id" => $schedule_id,
            "schedule_date" => $schedule_date,
            "opening_time" => $opening_time,
            "start_time" => $start_time,
            "end_time" => $end_time,
            "slot_limit" => $slot_limit
        );

        array_push($schedules_arr["records"], $schedule_item);
    }

    http_response_code(200);
    echo json_encode($schedules_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No schedules found."));
}
?>
