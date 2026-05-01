<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

require_once "../../config/database.php";
$database = new Database();
$db = $database->getConnection();

$type = isset($_GET['type']) ? $_GET['type'] : 'filtered';
$filename = "SmartQ_Report_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

if ($type === 'filtered') {
    // ── Filtered Detailed Report ──
    $year = isset($_GET['year']) ? $_GET['year'] : '';
    $college = isset($_GET['college']) ? $_GET['college'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    $sql = "SELECT s.student_id, s.first_name, s.last_name, s.email, s.yearlvl, c.college_name, vs.status_name, s.validated_at, s.validated_by 
            FROM students s
            LEFT JOIN colleges c ON s.college_id = c.college_id
            LEFT JOIN validation_status vs ON s.status_id = vs.status_id
            WHERE 1=1";
    $params = [];

    if ($year !== '') { $sql .= " AND s.yearlvl = :year"; $params[':year'] = $year; }
    if ($college !== '') { $sql .= " AND s.college_id = :college"; $params[':college'] = $college; }
    if ($status !== '') { $sql .= " AND s.status_id = :status"; $params[':status'] = $status; }

    $sql .= " ORDER BY s.last_name ASC, s.first_name ASC";
    
    fputcsv($output, ['Student ID', 'First Name', 'Last Name', 'Email', 'Year Level', 'College', 'Status', 'Validated At', 'Validated By']);
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $year_labels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
        $row['yearlvl'] = $year_labels[$row['yearlvl']] ?? $row['yearlvl'] . 'th Year';
        fputcsv($output, $row);
    }

} elseif ($type === 'college') {
    // ── Detailed Report Grouped by College, Sorted by Last Name ──
    $sql = "SELECT s.student_id, s.first_name, s.last_name, s.email, s.yearlvl, c.college_name, vs.status_name, s.validated_at, s.validated_by 
            FROM students s
            LEFT JOIN colleges c ON s.college_id = c.college_id
            LEFT JOIN validation_status vs ON s.status_id = vs.status_id
            ORDER BY c.college_name ASC, s.last_name ASC, s.first_name ASC";
    
    fputcsv($output, ['College', 'Student ID', 'First Name', 'Last Name', 'Email', 'Year Level', 'Status', 'Validated At', 'Validated By']);
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $year_labels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
        $row['yearlvl'] = $year_labels[$row['yearlvl']] ?? $row['yearlvl'] . 'th Year';
        fputcsv($output, $row);
    }

} elseif ($type === 'year') {
    // ── Detailed Report Grouped by Year, Sorted by Last Name ──
    $sql = "SELECT s.student_id, s.first_name, s.last_name, s.email, s.yearlvl, c.college_name, vs.status_name, s.validated_at, s.validated_by 
            FROM students s
            LEFT JOIN colleges c ON s.college_id = c.college_id
            LEFT JOIN validation_status vs ON s.status_id = vs.status_id
            ORDER BY s.yearlvl ASC, s.last_name ASC, s.first_name ASC";
    
    fputcsv($output, ['Year Level', 'Student ID', 'First Name', 'Last Name', 'Email', 'College', 'Status', 'Validated At', 'Validated By']);
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    $year_labels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['yearlvl'] = $year_labels[$row['yearlvl']] ?? $row['yearlvl'] . 'th Year';
        fputcsv($output, $row);
    }

} elseif ($type === 'general_percent') {
    // ── General Percentage Report ──
    $sql = "SELECT 
            (SELECT COUNT(*) FROM students) as total,
            (SELECT COUNT(*) FROM students s JOIN validation_status vs ON s.status_id = vs.status_id WHERE vs.status_name = 'Validated') as validated,
            (SELECT COUNT(*) FROM students s JOIN validation_status vs ON s.status_id = vs.status_id WHERE vs.status_name = 'Pending') as pending,
            (SELECT COUNT(*) FROM students s JOIN validation_status vs ON s.status_id = vs.status_id WHERE vs.status_name = 'Not Validated') as not_validated";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    fputcsv($output, ['Metric', 'Count', 'Percentage']);
    
    $total = $row['total'] ?: 1; // Avoid division by zero
    
    fputcsv($output, ['Validated', $row['validated'], round(($row['validated'] / $total) * 100, 2) . '%']);
    fputcsv($output, ['Pending', $row['pending'], round(($row['pending'] / $total) * 100, 2) . '%']);
    fputcsv($output, ['Not Validated', $row['not_validated'], round(($row['not_validated'] / $total) * 100, 2) . '%']);
    fputcsv($output, ['Total Students', $row['total'], '100%']);
}

fclose($output);
exit();
