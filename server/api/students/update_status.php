<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

// STRICT: Only admins can validate students
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Only administrators can perform this action.']);
    exit;
}

$student_id = trim($_POST['student_id'] ?? '');
$action = trim(strtolower($_POST['action'] ?? '')); // 'approve' or 'reject'

if (empty($student_id)) {
  echo json_encode(['success' => false, 'message' => 'Student ID is required']);
  exit;
}

if (!in_array($action, ['approve', 'reject'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid action. Must be approve or reject.']);
  exit;
}

try {
  $database = new Database();
  $db = $database->getConnection();

  // Resolve the target status_id from validation_status table
  // 'approve' → 'Validated' | 'reject' → 'Not Validated'
  $target_status = ($action === 'approve') ? 'Validated' : 'Not Validated';

  $statusQuery = "SELECT status_id FROM validation_status WHERE LOWER(status_name) = LOWER(:name) LIMIT 1";
  $statusStmt = $db->prepare($statusQuery);
  $statusStmt->bindParam(':name', $target_status);
  $statusStmt->execute();
  $statusRow = $statusStmt->fetch(PDO::FETCH_ASSOC);

  if (!$statusRow) {
    echo json_encode(['success' => false, 'message' => "Status '{$target_status}' not found in database."]);
    exit;
  }

  $new_status_id = $statusRow['status_id'];

  // Start transaction
  $db->beginTransaction();

  // Stamp validated_at on approve/reject
  $now = date('Y-m-d H:i:s');

  // Get current logged-in admin details from session
  $admin = $_SESSION['admin'];
  $admin_id = $admin['id'] ?? null;
  $admin_first = $admin['first_name'] ?? '';
  $admin_last = $admin['last_name'] ?? '';
  $admin_name = trim($admin_first . ' ' . $admin_last);

  // If we don't have an admin name in session, we might need a fallback or error
  // But usually, an admin must be logged in to reach this API.

  $updateQuery = "UPDATE students
                    SET status_id       = :status_id,
                        validated_at    = :validated_at,
                        validated_by    = :validated_by,
                        validated_by_id = :validated_by_id
                    WHERE student_id = :student_id";
  $updateStmt = $db->prepare($updateQuery);
  $updateStmt->bindParam(':status_id', $new_status_id);
  $updateStmt->bindParam(':validated_at', $now);
  $updateStmt->bindParam(':validated_by', $admin_name);
  $updateStmt->bindParam(':validated_by_id', $admin_id);
  $updateStmt->bindParam(':student_id', $student_id);
  $updateStmt->execute();

  // If rejected, also remove from queue_list so they can book again
  if ($action === 'reject') {
    $deleteQueueQuery = "DELETE FROM queue_list WHERE student_id = :student_id";
    $deleteQueueStmt = $db->prepare($deleteQueueQuery);
    $deleteQueueStmt->bindParam(':student_id', $student_id);
    $deleteQueueStmt->execute();
  }

  $db->commit();

  if ($updateStmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found or status unchanged.']);
    exit;
  }

  $verb = ($action === 'approve') ? 'approved' : 'rejected';
  echo json_encode([
    'success' => true,
    'message' => "Student {$student_id} validation has been {$verb}.",
    'new_status' => $target_status,
    'action' => $action,
    'validated_at' => $now
  ]);

} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
