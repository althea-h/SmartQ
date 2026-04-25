<?php

session_start();
require '../validate/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
  $enteredCode = $_POST['code'];
  $email = $_SESSION['email'];

  if (!isset($_SESSION['email'])) {
    $_SESSION['error'] = "Please enter your email first";
    header('Location: forgot-password.php');
    exit();
  }

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? ");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user){

    if($enteredCode == $user['reset_code']){
      $_SESSION['reset_email'] = $email;
      $_SESSION['reset_code_verified'] = true;

      header('Location: reset-password.php');
      exit();
    } else {
      $_SESSION['error'] = "Invalid code. Please try again.";
    }
  }else{
    $_SESSION['error'] = "No user found with that email";
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
      <span class="auth-subtitle">A verification code has been sent to your email address. Please enter it below.</span>
    </div>

    <!-- Body -->
    <div class="auth-card-body">

    <?php
      if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger' role='alert'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
      }

      if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success' role='alert'>" . $_SESSION['success'] . "</div>";
        unset($_SESSION['success']);
      }
      ?>

      <form action="validate.php" method="POST">
        <input name="code" type="number"     class="form-control" placeholder="Enter Verification Code" required>
        <button type="submit" class="auth-btn">Verify Code</button>
      </form>

      <hr class="auth-divider">
      <p class="auth-link"><a href="login.php">Back to Login</a></p>

    </div>
  </div>

</body>
</html>