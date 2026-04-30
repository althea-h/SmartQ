<?php
session_start();
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login_input = trim($_POST['studentid']);
    $password = $_POST['password'];

    try {
        $database = new Database();
        $db = $database->getConnection();

        // 1. Try checking Admin table (STRICT: email only for admin)
        $query = "SELECT amdin_id as id, first_name, last_name, email, admin_pass as password FROM admin 
                  WHERE email = :input OR amdin_id = :input LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':input', $login_input);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $role = 'admin';

        // 2. If not found in Admin, try Students table (Check email OR student_id)
        if (!$user) {
            $query = "SELECT student_id, first_name, last_name, email, student_pass as password, status_id, college_id, yearlvl FROM students 
                      WHERE email = :input OR student_id = :input LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':input', $login_input);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $role = 'student';
        }

        // 3. Verify password
        if ($user) {
            $is_plain = ($password === $user['password']);
            $is_hashed = @password_verify($password, $user['password']);
            
            if ($is_plain || $is_hashed) {
                $_SESSION['user_id'] = $user['student_id'] ?? $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $role;
                $_SESSION['user'] = $user; // Store full user data for dashboard

                // Redirect based on role
                if ($role === 'admin') {
                    header('Location: ../../../client/pages/admin/dashboard.php');
                } else {
                    header('Location: ../../../client/pages/users/student-dashboard.php');
                }
                exit();
            }
        }

        // If we reach here, login failed
        $_SESSION['error'] = "Invalid ID/Email or Password";
        header('Location: ../../../client/pages/login.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Login Error: " . $e->getMessage();
        header('Location: ../../../client/pages/login.php');
        exit();
    }
}
?>