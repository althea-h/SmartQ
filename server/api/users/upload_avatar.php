<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

if (!isset($_SESSION['student']) && !isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

$file = $_FILES['avatar'];
$upload_dir = '../../../client/assets/img/profiles/';
$db_path_prefix = '../../assets/img/profiles/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WebP are allowed.']);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('profile_') . '.' . $extension;
$target_path = $upload_dir . $filename;
$db_path = $db_path_prefix . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    try {
        $database = new Database();
        $db = $database->getConnection();

        if (isset($_SESSION['student'])) {
            $student_id = $_SESSION['student']['student_id'];
            $query = "UPDATE students SET profile_image = :img WHERE student_id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':img', $db_path);
            $stmt->bindParam(':id', $student_id);
            $stmt->execute();

            // Update session
            $_SESSION['student']['profile_image'] = $db_path;
        } else if (isset($_SESSION['admin'])) {
            // Placeholder for admin avatar update if table exists
            // Assuming admins table has avatar_url column
            /*
            $admin_id = $_SESSION['admin']['id'];
            $query = "UPDATE users SET avatar_url = :img WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':img', $db_path);
            $stmt->bindParam(':id', $admin_id);
            $stmt->execute();
            $_SESSION['admin']['avatar_url'] = $db_path;
            */
        }

        echo json_encode([
            'success' => true, 
            'avatar_url' => $db_path
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
}
?>
