<?php
session_start();

// Check if a specific role logout is requested
$role = $_GET['role'] ?? 'all';

if ($role === 'admin') {
    unset($_SESSION['admin']);
    unset($_SESSION['admin_id']);
} elseif ($role === 'student') {
    unset($_SESSION['student']);
    unset($_SESSION['student_id']);
} else {
    // Clear everything
    session_unset();
    session_destroy();
}

// Redirect to login page
header('Location: ../../../client/pages/login.php');
exit();
