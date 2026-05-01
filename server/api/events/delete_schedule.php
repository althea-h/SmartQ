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

    // Check if the schedule is active before deleting to update stats correctly
    $check_stmt = $db->prepare("SELECT status FROM queue_schedule WHERE schedule_id = :id AND deleted_at IS NULL");
    $check_stmt->bindParam(':id', $schedule_id);
    $check_stmt->execute();
    $schedule = $check_stmt->fetch(PDO::FETCH_ASSOC);
    $was_active = $schedule && $schedule['status'] === 'active';

    // Soft delete related queue entries
    $stmt1 = $db->prepare("UPDATE queue_list SET deleted_at = NOW() WHERE schedule_id = :id AND deleted_at IS NULL");
    $stmt1->bindParam(':id', $schedule_id);
    $stmt1->execute();

    // Soft delete from queue_schedule
    $stmt2 = $db->prepare("UPDATE queue_schedule SET deleted_at = NOW() WHERE schedule_id = :id AND deleted_at IS NULL");
    $stmt2->bindParam(':id', $schedule_id);
    
    if ($stmt2->execute()) {
        if ($was_active) {
            $db->exec("UPDATE system_stats SET stat_value = GREATEST(0, stat_value - 1) WHERE stat_key = 'active_schedules'");
        }
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Schedule archived successfully']);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to archive schedule']);
    }

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
