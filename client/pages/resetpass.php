<?php

session_start();
require 'client/pages/validate/db.php';

if (empty($_SESSION['reset_code_verified']) || $_SESSION['reset_code_verified'] !== true) {
  header('Location: enter_code.php');
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $newPassword = $_POST['password'];
  $confirmPassword = $_POST['confirm_password'];

  if ($newPassword === $confirmPassword) {
    $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, $_SESSION['reset_email']]);

    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_code_verified']);

    $_SESSION['success'] = "Password reset successful. Please login.";
    header('Location: login.php');
    exit();
  } else {
    $_SESSION['error'] = "Passwords do not match";
    header('Location: reset-password.php');
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
      <h4>Reset Password</h4>
    </div>

    <!-- Body -->
    <div class="auth-card-body">
        <span class="auth-hint">Your new password must be at least 6 characters.</span>

        <?php
      if(isset($_SESSION['error'])){
        echo "<div class='alert alert-danger' role='alert'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
      }
      if(isset($_SESSION['success'])){
        echo "<div class='alert alert-success' role='alert'>" . $_SESSION['success'] . "</div>";
        unset($_SESSION['success']);
      }
      ?>

      <form action="validate.php" method="POST">
        <input name="newpass" type="password"     class="form-control" placeholder="New Password" required>
        <input name="confirmpass" type="password"     class="form-control" placeholder="Confirm Password" required>
        <button type="submit" class="auth-btn">Reset Password</button>
      </form>

      <hr class="auth-divider">
      <p class="auth-link"><a href="login.php">Back to Login</a></p>

    </div>
  </div>

</body>
</html>