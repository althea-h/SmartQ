<?php
session_start();
require '../validate/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
       $reset_code = rand(100000, 999999);
      
       $update = $pdo->prepare("UPDATE users SET reset_code = ? WHERE email = ?");
       $update->execute([$reset_code, $email]);

      $_SESSION['email'] = $email;

      $_SESSION['email'] = $email;

      $mail = new PHPMailer(true);
      try {
          $mail->isSMTP();
          $mail->Host = 'smtp.gmail.com';
          $mail->SMTPAuth = true;
          $mail->Username = 'altheagalorportd@gmail.com';
          $mail->Password = 'xfrz ewxe yxan khoy';
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
          $mail->Port = 587;
          $mail->setFrom('altheagalorportd@gmail.com', 'Althea Hassel Daing');
          $mail->addAddress($email);
          $mail->isHTML(true);
          $mail->Subject = 'Password Reset Code ';
          $mail->Body = "
           <p> Hello, This is your password reset code: {$reset_code}</p> <br>
          ";   
          $mail->AltBody = "Use the code below: {$reset_code}";
          $mail->send();

          $_SESSION['email_sent'] = true;

          $_SESSION['success'] = "A verification code has been sent to your email";
          header('Location: enter_code.php');
          exit();

      } catch (Exception $e) {
          $_SESSION['error'] = "Failed to send email: " . $mail->ErrorInfo;
          header('Location: forgot-password.php');
          exit();
      }
   
    } else {
      $_SESSION['error'] = "No user found with that email";
      header('Location: forgot-password.php');
      exit();
    }
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

      <form action="validate.php" method="POST">
        <input name="email" type="email"     class="form-control" placeholder="Enter Email Address" required>
        <button type="submit" class="auth-btn">Send Code</button>
      </form>

      <hr class="auth-divider">
      <p class="auth-link"><a href="login.php">Back to Login</a></p>

    </div>
  </div>

</body>
</html>