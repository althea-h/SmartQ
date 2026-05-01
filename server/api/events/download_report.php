<?php
session_start();
require_once '../../config/database.php';

$schedule_id = $_GET['id'] ?? '';

if (empty($schedule_id)) {
    die("Schedule ID is required");
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch schedule info
    $stmt = $db->prepare("SELECT * FROM queue_schedule WHERE schedule_id = :id");
    $stmt->bindParam(':id', $schedule_id);
    $stmt->execute();
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        die("Schedule not found");
    }

    // Fetch booked students
    $query = "SELECT ql.queue_number, s.student_id, s.first_name, s.last_name, s.email, s.college_id 
              FROM queue_list ql
              JOIN students s ON ql.student_id = s.student_id
              LEFT JOIN colleges c ON s.college_id = c.college_id
              WHERE ql.schedule_id = :id
              ORDER BY ql.queue_number ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $schedule_id);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate CSV
    $filename = "SmartQ_Report_" . $schedule_id . "_" . date('Y-m-d') . ".csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, ['SmartQ Event Report']);
    fputcsv($output, ['Schedule ID', $schedule['schedule_id']]);
    fputcsv($output, ['Date', $schedule['schedule_date']]);
    fputcsv($output, ['Time Slot', $schedule['start_time'] . ' - ' . $schedule['end_time']]);
    fputcsv($output, ['Status', $schedule['status']]);
    fputcsv($output, []); // Blank line

    fputcsv($output, ['Queue No', 'Student ID', 'First Name', 'Last Name', 'Email', 'College ID']);

    foreach ($students as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    die("Error generating report: " . $e->getMessage());
}
