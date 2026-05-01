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

<?php
$admin_data = $_SESSION['admin'] ?? null;
$full_name = $admin_data ? ($admin_data['first_name'] . ' ' . $admin_data['last_name']) : 'Administrator';
$initial = $admin_data ? strtoupper(substr($admin_data['first_name'], 0, 1)) : 'A';
$avatar_url = $admin_data['avatar_url'] ?? null; // Placeholder for future upload logic
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
      <input type="text" id="global-search" placeholder="Search for services (e.g. Reports, Students)..."
        autocomplete="off">

      <!-- Search Results Dropdown -->
      <div id="search-results" class="search-results-dropdown">
        <!-- Results will be injected here -->
      </div>
    </div>

    <!-- User Profile -->
    <div class="topbar-user-profile" id="user-menu">
      <a href="profile.php" class="topbar-user-link">
        <div class="topbar-avatar">
          <?php if ($avatar_url): ?>
            <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Avatar" class="avatar-img">
          <?php else: ?>
            <?= $initial ?>
          <?php endif; ?>
        </div>
        <div class="topbar-user-info">
          <span class="topbar-username"><?= htmlspecialchars($full_name) ?></span>
          <span class="topbar-user-role">Super Admin</span>
        </div>
      </a>
    </div>
  </div>

</header>

<script>
  $(document).ready(function () {
    const services = [
      { name: 'Dashboard', url: 'dashboard.php', icon: `<?= get_icon('dashboard.svg') ?>`, desc: 'System overview and statistics' },
      { name: 'Student Directory', url: 'students.php', icon: `<?= get_icon('students.svg') ?>`, desc: 'Manage students and ID validation' },
      { name: 'Manage Queue', url: 'manage-queue.php', icon: `<?= get_icon('queue.svg') ?>`, desc: 'View and handle active queue slots' },
      { name: 'Schedules', url: 'queue.php', icon: `<?= get_icon('queue.svg') ?>`, desc: 'Create and manage validation dates' },
      { name: 'Reports & Analytics', url: 'reports.php', icon: `<?= get_icon('reports.svg') ?>`, desc: 'View validation and queue data' },
      { name: 'Profile Settings', url: 'profile.php', icon: `<?= get_icon('profile.svg') ?>`, desc: 'Update your account info' },
    ];

    const $search = $('#global-search');
    const $results = $('#search-results');

    $search.on('input', function () {
      const query = $(this).val().toLowerCase().trim();
      $results.empty();

      if (query.length < 1) {
        $results.hide();
        return;
      }

      const filtered = services.filter(s =>
        s.name.toLowerCase().includes(query) ||
        s.desc.toLowerCase().includes(query)
      );

      if (filtered.length > 0) {
        filtered.forEach(s => {
          $results.append(`
            <a href="${s.url}" class="search-result-item">
              <div class="result-icon">${s.icon}</div>
              <div class="result-content">
                <div class="result-name">${s.name}</div>
                <div class="result-desc">${s.desc}</div>
              </div>
            </a>
          `);
        });
        $results.show();
      } else {
        $results.append('<div class="search-no-results">No services found...</div>').show();
      }
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
      if (!$(e.target).closest('.topbar-search-wrapper').length) {
        $results.hide();
      }
    });
  });
</script>