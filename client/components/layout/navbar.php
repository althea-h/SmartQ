<?php
/**
 * Navbar Component
 * 
 * Usage:  <div data-component="navbar"></div>
 * 
 * A horizontal navigation bar for public-facing pages.
 */
?>

<nav class="navbar" id="navbar">
  <div class="navbar-inner">

    <!-- Brand -->
    <a href="../../index.php" class="navbar-brand">
      <img src="../../assets/logo/sq-blue-d.png" alt="SmartQ Logo" class="navbar-logo">
      <span>SmartQ</span>
    </a>

    <!-- Links -->
    <div class="navbar-links" id="navbar-links">
      <a href="../../index.php#home">Home</a>
      <a href="../../index.php#about">About</a>
      <a href="../../index.php#services">Services</a>
      <a href="../../index.php#contact">Contact</a>
    </div>

    <!-- Actions -->
    <div class="navbar-actions">
      <a href="../login.php" class="btn-outline">Login</a>
      <a href="../signup.php" class="btn-primary">Sign Up</a>
    </div>

    <!-- Mobile toggle -->
    <button class="navbar-toggle" id="navbar-toggle" aria-label="Toggle navigation">☰</button>

  </div>
</nav>
