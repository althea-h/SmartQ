<?php
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION['admin'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

require_once "../../config/database.php";
$database = new Database();
$db = $database->getConnection();

$year = isset($_GET['year']) ? $_GET['year'] : '';
$college = isset($_GET['college']) ? $_GET['college'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// ── 1. Fetch Preview Data (Filtered) ──
$preview_sql = "SELECT s.student_id, s.first_name, s.last_name, s.email, s.yearlvl, c.college_name, vs.status_name 
               FROM students s
               LEFT JOIN colleges c ON s.college_id = c.college_id
               LEFT JOIN validation_status vs ON s.status_id = vs.status_id
               WHERE 1=1";
$params = [];

if ($year !== '') {
    $preview_sql .= " AND s.yearlvl = :year";
    $params[':year'] = $year;
}
if ($college !== '') {
    $preview_sql .= " AND s.college_id = :college";
    $params[':college'] = $college;
}
if ($status !== '') {
    $preview_sql .= " AND s.status_id = :status";
    $params[':status'] = $status;
}

$preview_sql .= " ORDER BY s.student_id DESC LIMIT 10";
$preview_stmt = $db->prepare($preview_sql);
$preview_stmt->execute($params);
$preview = $preview_stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for year display
$year_labels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
foreach ($preview as &$s) {
    $s['year_display'] = $year_labels[$s['yearlvl']] ?? $s['yearlvl'] . 'th Year';
}

// ── 2. Fetch College Distribution (Validated only) ──
$college_chart_sql = "SELECT c.college_name, COUNT(s.student_id) as count 
                     FROM colleges c
                     LEFT JOIN students s ON c.college_id = s.college_id 
                     LEFT JOIN validation_status vs ON s.status_id = vs.status_id
                     WHERE vs.status_name = 'Validated' OR s.status_id IS NULL
                     GROUP BY c.college_id
                     ORDER BY c.college_name";
$college_stmt = $db->prepare($college_chart_sql);
$college_stmt->execute();
$college_data = $college_stmt->fetchAll(PDO::FETCH_ASSOC);

$college_labels = [];
$college_counts = [];
foreach ($college_data as $row) {
    $college_labels[] = $row['college_name'];
    $college_counts[] = (int)$row['count'];
}

// ── 3. Fetch Overall Status ──
$status_chart_sql = "SELECT vs.status_name, COUNT(s.student_id) as count 
                    FROM validation_status vs
                    LEFT JOIN students s ON vs.status_id = s.status_id
                    GROUP BY vs.status_id
                    ORDER BY vs.status_id";
$status_stmt = $db->prepare($status_chart_sql);
$status_stmt->execute();
$status_data = $status_stmt->fetchAll(PDO::FETCH_ASSOC);

$status_labels = [];
$status_counts = [];
foreach ($status_data as $row) {
    $status_labels[] = $row['status_name'];
    $status_counts[] = (int)$row['count'];
}

echo json_encode([
    "success" => true,
    "preview" => $preview,
    "charts" => [
        "college" => [
            "labels" => $college_labels,
            "data" => $college_counts
        ],
        "status" => [
            "labels" => $status_labels,
            "data" => $status_counts
        ]
    ]
]);
