<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Query to get schedules with count of booked students
    $query = "SELECT qs.*, 
              (SELECT COUNT(*) FROM queue_list ql WHERE ql.schedule_id = qs.schedule_id AND ql.deleted_at IS NULL) as booked_count
              FROM queue_schedule qs 
              WHERE qs.deleted_at IS NULL
              ORDER BY qs.schedule_date DESC, qs.start_time DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $schedules]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
