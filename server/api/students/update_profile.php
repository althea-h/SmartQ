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
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$yearlvl = trim($_POST['yearlvl'] ?? '');
$college_id = trim($_POST['college_id'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($first_name) || empty($last_name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Check if email is already taken by another student
    $checkEmailQuery = "SELECT student_id FROM students WHERE email = :email AND student_id != :sid";
    $ceStmt = $db->prepare($checkEmailQuery);
    $ceStmt->bindParam(':email', $email);
    $ceStmt->bindParam(':sid', $student_id);
    $ceStmt->execute();
    if ($ceStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email is already in use by another student']);
        exit;
    }

    // Build the update query
    $sql = "UPDATE students SET 
            first_name = :first_name, 
            last_name = :last_name, 
            email = :email, 
            yearlvl = :yearlvl, 
            college_id = :college_id";
    
    $params = [
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':email' => $email,
        ':yearlvl' => $yearlvl,
        ':college_id' => $college_id,
        ':sid' => $student_id
    ];

    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            exit;
        }
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql .= ", student_pass = :password";
        $params[':password'] = $hashed_password;
    }

    $sql .= " WHERE student_id = :sid";

    $stmt = $db->prepare($sql);
    
    if ($stmt->execute($params)) {
        // Update session data
        $_SESSION['student']['first_name'] = $first_name;
        $_SESSION['student']['last_name'] = $last_name;
        $_SESSION['student']['email'] = $email;
        $_SESSION['student']['yearlvl'] = $yearlvl;
        $_SESSION['student']['college_id'] = $college_id;

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
