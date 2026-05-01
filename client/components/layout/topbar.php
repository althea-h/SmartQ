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
$description = isset($_GET['description']) ? htmlspecialchars($_GET['description']) : '';

function get_icon($filename)
{
  // Try sidebar folder first, then base icons folder
  $sidebar_path = __DIR__ . '/../../assets/icons/sidebar/' . $filename;
  $base_path = __DIR__ . '/../../assets/icons/' . $filename;

  $path = file_exists($sidebar_path) ? $sidebar_path : (file_exists($base_path) ? $base_path : '');

  if ($path === '')
    return '';

  $svg = file_get_contents($path);
  // Inject the icon class into the <svg> tag
  $svg = preg_replace('/<svg\b/', '<svg class="topbar-icon"', $svg, 1);
  // Strip the XML declaration
  $svg = preg_replace('/<\?xml[^?]*\?>/', '', $svg);
  return $svg;
}
?>

<header class="topbar" id="topbar">

  <!-- Left: Title Area -->
  <div class="topbar-content">
    <h1 class="topbar-title">
      <?= $title ?>
    </h1>
    <?php if ($description): ?>
      <p class="topbar-subtitle">
        <?= $description ?>
      </p>
    <?php endif; ?>
  </div>

  <!-- Right: Actions Area -->
  <div class="topbar-actions">
    <!-- Search Bar -->
    <div class="topbar-search-wrapper">
      <i class="fas fa-search"></i>
      <input type="text" id="global-search" placeholder="Search for anything..." autocomplete="off">
    </div>

    <!-- User Profile -->
    <div class="topbar-user-profile" id="user-menu">
      <div class="topbar-avatar">
        A
      </div>
      <div class="topbar-user-info">
        <span class="topbar-username">Administrator</span>
        <span class="topbar-user-role">Super Admin</span>
      </div>
    </div>
  </div>

</header>