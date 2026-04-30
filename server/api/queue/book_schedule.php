<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$schedule_id = $_POST['schedule_id'] ?? '';
$student_id = $_SESSION['user']['student_id'];

if (empty($schedule_id)) {
    echo json_encode(['success' => false, 'message' => 'Schedule ID is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Check if schedule exists and is active
    $query = "SELECT * FROM queue_schedule WHERE schedule_id = :id AND status = 'active' LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $schedule_id);
    $stmt->execute();
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        echo json_encode(['success' => false, 'message' => 'Schedule not found or inactive']);
        exit;
    }

    // 2. Check if student already booked for this schedule
    $query = "SELECT * FROM queue_list WHERE student_id = :sid AND schedule_id = :schid LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':sid', $student_id);
    $stmt->bindParam(':schid', $schedule_id);
    $stmt->execute();
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already booked for this schedule']);
        exit;
    }

    // 3. Check availability
    $query = "SELECT COUNT(*) FROM queue_list WHERE schedule_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $schedule_id);
    $stmt->execute();
    $booked_count = $stmt->fetchColumn();

    if ($booked_count >= $schedule['slot_limit']) {
        echo json_encode(['success' => false, 'message' => 'This schedule is already full']);
        exit;
    }

    // 4. Generate next queue number
    $queue_number = $booked_count + 1;

    // 5. Book the slot
    $query = "INSERT INTO queue_list (created_by, student_id, schedule_id, queue_number) 
              VALUES (:creator, :sid, :schid, :qnum)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':creator', $student_id);
    $stmt->bindParam(':sid', $student_id);
    $stmt->bindParam(':schid', $schedule_id);
    $stmt->bindParam(':qnum', $queue_number);

    if ($stmt->execute()) {
        // 6. Update student status to 'Pending Review' (status_id = 3)
        $updateStatusQuery = "UPDATE students SET status_id = 3 WHERE student_id = :sid";
        $updateStatusStmt = $db->prepare($updateStatusQuery);
        $updateStatusStmt->bindParam(':sid', $student_id);
        $updateStatusStmt->execute();

        // Also update session to reflect status immediately
        if(isset($_SESSION['user']['student_id']) && $_SESSION['user']['student_id'] == $student_id) {
            $_SESSION['user']['status_id'] = 3;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Booking successful',
            'queue_number' => $queue_number
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to process booking']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
