<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$schedule_id = $_POST['schedule_id'] ?? '';

if (empty($schedule_id)) {
    echo json_encode(['success' => false, 'message' => 'Schedule ID is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Instead of deleting, we update the status to 'cancelled'
    $query = "UPDATE queue_schedule SET status = 'cancelled' WHERE schedule_id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $schedule_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Schedule cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel schedule']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
