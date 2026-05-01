<?php
/**
 * Recent Activity Widget
 * 
 * Usage:  <div data-component="recent-activity"></div>
 */

require_once '../../../server/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch Recent Validations
$query = "SELECT s.first_name, s.last_name, s.student_id, s.validated_at, st.status_name
          FROM students s
          JOIN validation_status st ON s.status_id = st.status_id
          WHERE s.validated_at IS NOT NULL
          ORDER BY s.validated_at DESC
          LIMIT 5";

$stmt = $db->query($query);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>

<div class="recent-activity">
  <div class="widget-header">
    <h3>Recent Activity</h3>
    <a href="students.php" class="widget-link">View All</a>
  </div>
  <ul class="activity-list" id="activity-list">
    <?php if (count($activities) > 0): ?>
      <?php foreach ($activities as $act): ?>
        <li class="activity-item">
          <span class="activity-icon <?= strtolower($act['status_name']) === 'validated' ? 'icon-success' : 'icon-warning' ?>"></span>
          <div class="activity-details">
            <p class="activity-text">
              <strong><?= htmlspecialchars($act['first_name'] . ' ' . $act['last_name']) ?></strong> 
              has been <?= strtolower($act['status_name']) ?>
            </p>
            <span class="activity-time"><?= time_elapsed_string($act['validated_at']) ?></span>
          </div>
        </li>
      <?php endforeach; ?>
    <?php else: ?>
      <li class="activity-item">
        <p class="activity-text" style="color:var(--text-muted); font-size:0.9rem; padding: 10px 0;">No recent activity found.</p>
      </li>
    <?php endif; ?>
  </ul>
</div>