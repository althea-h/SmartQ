<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

if (!isset($_SESSION['student'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$student_id = $_SESSION['student']['student_id'];
$schedule_id = $_POST['schedule_id'] ?? '';

if (empty($schedule_id)) {
    echo json_encode(['success' => false, 'message' => 'Schedule ID is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Delete the booking
    $query = "DELETE FROM queue_list WHERE student_id = :sid AND schedule_id = :schid";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':sid', $student_id);
    $stmt->bindParam(':schid', $schedule_id);

    if ($stmt->execute()) {
        // If student has no more active bookings, we could potentially reset their status, 
        // but it's safer to keep it as 'Not Validated' (2) unless they are already validated.
        
        $checkValidatedQuery = "SELECT status_id FROM students WHERE student_id = :sid";
        $cvStmt = $db->prepare($checkValidatedQuery);
        $cvStmt->bindParam(':sid', $student_id);
        $cvStmt->execute();
        $status = $cvStmt->fetchColumn();

        if ($status == 3) { // If it was 'Pending Review', set it back to 'Not Validated'
            $updateStatusQuery = "UPDATE students SET status_id = 2 WHERE student_id = :sid";
            $usStmt = $db->prepare($updateStatusQuery);
            $usStmt->bindParam(':sid', $student_id);
            $usStmt->execute();
            $_SESSION['student']['status_id'] = 2;
        }

        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel booking']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
