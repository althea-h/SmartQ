<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$schedule_id = $_POST['schedule_id'] ?? '';

if (empty($schedule_id)) {
    echo json_encode(['success' => false, 'message' => 'Schedule ID is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Find the next queue number that exists in the list for this schedule
    // We don't just increment by 1 because there might be gaps (though unlikely)
    // Actually, simple increment is fine as the student view handles "Current serving: No. X"
    
    $query = "UPDATE queue_schedule SET current_number = current_number + 1 WHERE schedule_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $schedule_id);

    if ($stmt->execute()) {
        // Fetch the new current number to return it
        $stmt = $db->prepare("SELECT current_number FROM queue_schedule WHERE schedule_id = :id");
        $stmt->execute(['id' => $schedule_id]);
        $newNumber = $stmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'message' => 'Queue advanced',
            'current_number' => $newNumber
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to advance queue']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
