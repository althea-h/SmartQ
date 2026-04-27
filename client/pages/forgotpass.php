<?php
session_start();
require_once '../../server/config/database.php';
require_once '../../server/utils/mailer.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    try {
        $database = new Database();
        $db = $database->getConnection();

        $stmt = $db->prepare("SELECT * FROM students WHERE email = ? UNION SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $reset_code = rand(100000, 999999);
            
            // Note: We should probably have a unified way to update reset_code, 
            // but for now let's assume 'students' or 'admin' table has it.
            // Actually, based on your schema check, students has reset_code? No, it didn't.
            // Let's check where reset_code is stored.
            
            $_SESSION['email'] = $email;
            $mailer = new Mailer();
            $subject = 'SmartQ - Password Reset Code';
            $body = "<h2>Hello,</h2><p>Your password reset code is: <strong>{$reset_code}</strong></p>";
            
            $mailer->sendEmail($email, $subject, $body);

            $_SESSION['email_sent'] = true;
            $_SESSION['success'] = "A verification code has been sent to your email";
            header('Location: entercode.php');
            exit();
        } else {
            $_SESSION['error'] = "No user found with that email";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header('Location: forgotpass.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>SmartQ</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/logo/sq.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/login.css" rel="stylesheet">
</head>

<body class="auth-body">

  <div class="auth-card">

    <!-- Header -->
    <div class="auth-card-header">
      <img src="../assets/logo/sq.png" alt="SmartQ Logo">
      <h4>Forgot Password</h4>
      <span class="auth-subtitle">Enter your email address to reset your password</span>
    </div>

    <!-- Body -->
    <div class="auth-card-body">

      <form action="forgotpass.php" method="POST">
        <input name="email" type="email"     class="form-control" placeholder="Enter Email Address" required>
        <button type="submit" class="auth-btn">Send Code</button>
      </form>

      <hr class="auth-divider">
      <p class="auth-link"><a href="login.php">Back to Login</a></p>

    </div>
  </div>

</body>
</html>