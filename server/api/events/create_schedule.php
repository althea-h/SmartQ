<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get data from POST
$date = $_POST['date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$slot_limit = $_POST['slot_limit'] ?? '';

// Basic validation
if (empty($date) || empty($start_time) || empty($end_time) || empty($slot_limit)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Generate a simple schedule_id (e.g., SCH-TIMESTAMP)
    $schedule_id = 'SCH-' . time() . '-' . rand(100, 999);

    // Opening time - let's default to start_time for now or allow a separate field
    // In this case, we'll just set it to start_time
    $opening_time = $start_time;

    $admin_id = $_SESSION['admin_id'] ?? null;

    $query = "INSERT INTO queue_schedule (schedule_id, schedule_date, opening_time, start_time, end_time, slot_limit, created_by, status) 
              VALUES (:id, :date, :opening, :start, :end, :limit, :creator, 'active')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $schedule_id);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':opening', $opening_time);
    $stmt->bindParam(':start', $start_time);
    $stmt->bindParam(':end', $end_time);
    $stmt->bindParam(':limit', $slot_limit);
    $stmt->bindParam(':creator', $admin_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Schedule created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create schedule']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
