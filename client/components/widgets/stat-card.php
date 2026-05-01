<?php
/**
 * Stat Card Widget
 */

function get_stat_icon($filename) {
    $path = __DIR__ . '/../../assets/icons/sidebar/' . $filename;
    if (file_exists($path)) {
        return file_get_contents($path);
    }
    return '';
}

$iconName = isset($_GET['icon']) ? $_GET['icon'] : '';
$label = isset($_GET['label']) ? htmlspecialchars($_GET['label']) : 'Stat';
$value = isset($_GET['value']) ? htmlspecialchars($_GET['value']) : '—';
$trend = isset($_GET['trend']) ? htmlspecialchars($_GET['trend']) : '';

// Resolve icon: if it ends in .svg, try to fetch it
$iconContent = '';
if (str_ends_with($iconName, '.svg')) {
    $iconContent = get_stat_icon($iconName);
} else {
    $iconContent = htmlspecialchars($iconName);
}
?>

<div class="stat-card">
  <?php if ($iconContent): ?>
    <div class="stat-card-icon"><?= $iconContent ?></div>
  <?php endif; ?>
  <div class="stat-card-body">
    <p class="stat-card-label"><?= $label ?></p>
    <h3 class="stat-card-value"><?= $value ?></h3>
    <?php if ($trend): ?>
      <span class="stat-card-trend <?= str_starts_with($trend, '+') ? 'up' : 'down' ?>">
        <?= $trend ?>
      </span>
    <?php endif; ?>
  </div>
</div>