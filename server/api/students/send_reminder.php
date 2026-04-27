<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../utils/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$student_id = $_POST['student_id'] ?? '';

if (empty($student_id)) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch student details
    $query = "SELECT first_name, email FROM students WHERE student_id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $student_id);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }

    $email = $student['email'];
    $name = $student['first_name'];

    // Send email
    $mailer = new Mailer();
    $subject = "SmartQ - Account Validation Reminder";
    $body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2 style='color: #2563eb;'>Hello {$name},</h2>
            <p>This is a reminder from the <strong>SmartQ Admin Team</strong>.</p>
            <p>Your account is currently marked as <strong>NOT VALIDATED</strong>. To use all features of the SmartQ system, please visit the administration office to validate your identity.</p>
            <p>If you have already submitted your documents, please wait for the admin to approve your request.</p>
            <br>
            <p>Best regards,<br>SmartQ Team</p>
        </div>
    ";

    if ($mailer->sendEmail($email, $subject, $body)) {
        echo json_encode(['success' => true, 'message' => 'Reminder email sent successfully to ' . $email]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
