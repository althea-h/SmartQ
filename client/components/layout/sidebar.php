<?php
/**
 * Sidebar Component
 * 
 * Usage:  <div data-component="sidebar"></div>
 * 
 * This component renders the admin sidebar navigation.
 * It reads the current page from $_GET['active'] or defaults to 'dashboard'.
 */

$active = isset($_GET['active']) ? htmlspecialchars($_GET['active']) : 'dashboard';
?>

<aside class="sidebar" id="sidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <span class="sidebar-title">SmartQ</span>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav">
    <ul>
      <li>
        <a href="dashboard.php" class="sidebar-link <?= $active === 'dashboard' ? 'active' : '' ?>">
          <img class="sidebar-icon" src="../../assets/icons/sidebar/dashboard.svg" alt="Dashboard">
          <span class="sidebar-label">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="queue.php" class="sidebar-link <?= $active === 'queue' ? 'active' : '' ?>">
          <img class="sidebar-icon" src="../../assets/icons/sidebar/queue.svg" alt="Queue">
          <span class="sidebar-label">Queue</span>
        </a>
      </li>
      <li>
        <a href="students.php" class="sidebar-link <?= $active === 'students' ? 'active' : '' ?>">
          <img class="sidebar-icon" src="../../assets/icons/sidebar/students.svg" alt="Students">
          <span class="sidebar-label">Students</span>
        </a>
      </li>
      <li>
        <a href="reports.php" class="sidebar-link <?= $active === 'reports' ? 'active' : '' ?>">
          <img class="sidebar-icon" src="../../assets/icons/sidebar/reports.svg" alt="Reports">
          <span class="sidebar-label">Reports</span>
        </a>
      </li>
      <li>
        <a href="settings.php" class="sidebar-link <?= $active === 'settings' ? 'active' : '' ?>">
          <img class="sidebar-icon" src="../../assets/icons/sidebar/settings.svg" alt="Settings">
          <span class="sidebar-label">Settings</span>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar footer -->
  <div class="sidebar-footer">
    <a href="../../index.php" class="sidebar-link">
      <img class="sidebar-icon" src="../../assets/icons/sidebar/logout.svg" alt="Logout">
      <span class="sidebar-label">Logout</span>
    </a>
  </div>

</aside>