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

    // Start transaction to delete related queue entries first
    $db->beginTransaction();

    // Delete from queue_list first due to foreign keys (if any)
    $stmt1 = $db->prepare("DELETE FROM queue_list WHERE schedule_id = :id");
    $stmt1->bindParam(':id', $schedule_id);
    $stmt1->execute();

    // Delete from queue_schedule
    $stmt2 = $db->prepare("DELETE FROM queue_schedule WHERE schedule_id = :id");
    $stmt2->bindParam(':id', $schedule_id);
    
    if ($stmt2->execute()) {
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Schedule removed from list permanently']);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to remove schedule']);
    }

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
