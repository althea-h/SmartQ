<?php
/**
 * Stat Card Widget
 * 
 * Usage:
 *   <div data-component="stat-card" 
 *        data-props='{"icon":"📋","label":"Total Queues","value":"128","trend":"+12%"}'>
 *   </div>
 * 
 * Accepts:
 *   - icon  : Emoji or icon HTML
 *   - label : Card heading
 *   - value : Main stat number
 *   - trend : Optional trend indicator (e.g. "+12%")
 */

$icon  = isset($_GET['icon'])  ? htmlspecialchars($_GET['icon'])  : '📊';
$label = isset($_GET['label']) ? htmlspecialchars($_GET['label']) : 'Stat';
$value = isset($_GET['value']) ? htmlspecialchars($_GET['value']) : '—';
$trend = isset($_GET['trend']) ? htmlspecialchars($_GET['trend']) : '';
?>

<div class="stat-card">
  <div class="stat-card-icon"><?= $icon ?></div>
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
