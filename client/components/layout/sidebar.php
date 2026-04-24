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

// Helper: load an SVG file and inject a CSS class into the root <svg> element
function sidebar_icon($filename)
{
  $path = __DIR__ . '/../../assets/icons/sidebar/' . $filename;
  if (!file_exists($path))
    return '';
  $svg = file_get_contents($path);
  // Inject the sidebar-icon class into the <svg> tag
  $svg = preg_replace('/<svg\b/', '<svg class="sidebar-icon"', $svg, 1);
  // Strip the XML declaration so it doesn't render as text
  $svg = preg_replace('/<\?xml[^?]*\?>/', '', $svg);
  return $svg;
}
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
          <?= sidebar_icon('dashboard.svg') ?>
          <span class="sidebar-label">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="queue.php" class="sidebar-link <?= $active === 'queue' ? 'active' : '' ?>">
          <?= sidebar_icon('queue.svg') ?>
          <span class="sidebar-label">Queue</span>
        </a>
      </li>
      <li>
        <a href="students.php" class="sidebar-link <?= $active === 'students' ? 'active' : '' ?>">
          <?= sidebar_icon('students.svg') ?>
          <span class="sidebar-label">Students</span>
        </a>
      </li>
      <li>
        <a href="reports.php" class="sidebar-link <?= $active === 'reports' ? 'active' : '' ?>">
          <?= sidebar_icon('reports.svg') ?>
          <span class="sidebar-label">Reports</span>
        </a>
      </li>
      <li>
        <a href="settings.php" class="sidebar-link <?= $active === 'settings' ? 'active' : '' ?>">
          <?= sidebar_icon('settings.svg') ?>
          <span class="sidebar-label">Settings</span>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar footer -->
  <div class="sidebar-footer">
    <a href="../../index.php" class="sidebar-link">
      <?= sidebar_icon('logout.svg') ?>
      <span class="sidebar-label">Logout</span>
    </a>
  </div>

</aside>