<?php
/**
 * Topbar Component
 * 
 * Usage:  <div data-component="topbar" data-props='{"title":"Dashboard"}'></div>
 * 
 * Accepts:
 *   - title : The page heading displayed in the topbar
 */

$title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : 'Dashboard';
?>

<header class="topbar" id="topbar">

  <!-- Hamburger toggle for mobile -->
  <button class="topbar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
    <span></span>
    <span></span>
    <span></span>
  </button>

  <!-- Page title -->
  <h1 class="topbar-title"><?= $title ?></h1>

  <!-- Right-side actions -->
  <div class="topbar-actions">
    <!-- Search -->
    <div class="topbar-search">
      <input type="text" id="global-search" placeholder="Search…" autocomplete="off">
    </div>

    <!-- Notifications -->
    <button class="topbar-btn" id="notifications-btn" aria-label="Notifications">
      🔔 <span class="badge" id="notif-count">3</span>
    </button>

    <!-- User avatar -->
    <div class="topbar-user" id="user-menu">
      <span class="topbar-avatar">👤</span>
      <span class="topbar-username">Admin</span>
    </div>
  </div>

</header>