<!DOCTYPE html>
<html lang="en">

<head>
  <title>SmartQ — Sign Up</title>
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
      <h4>Create Account</h4>
      <span class="auth-subtitle">Join SmartQ today</span>
    </div>

    <!-- Body -->
    <div class="auth-card-body">

      <form action="signup_validate.php" method="POST">
        <input type="text" class="form-control" name="firstname" placeholder="First Name" required>
        <input type="text" class="form-control" name="lastname" placeholder="Last Name" required>
        <input type="text" class="form-control" name="username" placeholder="Username" required>
        <input type="email" class="form-control" name="email" placeholder="Email" required>
        <input type="password" class="form-control" name="password" placeholder="Password" required minlength="6">
        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required
          minlength="6">
        <button type="submit" class="auth-btn">Sign Up</button>
      </form>

      <hr class="auth-divider">
      <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>

    </div>
  </div>

</body>

</html>