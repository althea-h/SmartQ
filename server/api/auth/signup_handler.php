<?php
session_start();
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentid = $_POST['studentid'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $college_id = $_POST['college'];
    $yearlvl = $_POST['yearlvl'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match";
        header('Location: ../../../client/pages/signup.php');
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Check if email OR Student ID already exists (STRICT: admin and students only)
    $check_query = "SELECT email FROM admin WHERE email = :e1 
                    UNION 
                    SELECT email FROM students WHERE email = :e2 OR student_id = :s1";
    
    $stmt = $db->prepare($check_query);
    $stmt->bindParam(':e1', $email);
    $stmt->bindParam(':e2', $email);
    $stmt->bindParam(':s1', $studentid);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email or Student ID already registered.";
        header('Location: ../../../client/pages/signup.php');
        exit();
    }

    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into students table
    $query = "INSERT INTO students (student_id, first_name, last_name, email, yearlvl, student_pass, status_id, college_id) 
              VALUES (:student_id, :first_name, :last_name, :email, :yearlvl, :password, 2, :college_id)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $studentid);
    $stmt->bindParam(':first_name', $firstname);
    $stmt->bindParam(':last_name', $lastname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':yearlvl', $yearlvl);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':college_id', $college_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Account created successfully! You can now login.";
        header('Location: ../../../client/pages/login.php');
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header('Location: ../../../client/pages/signup.php');
        exit();
    }
}
?>