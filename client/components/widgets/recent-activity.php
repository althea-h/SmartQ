<?php
/**
 * Recent Activity Widget - Redesigned
 * 
 * Usage:  <div data-component="recent-activity"></div>
 */

require_once '../../../server/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch Recent Validations with College info
$query = "SELECT s.first_name, s.last_name, s.student_id, s.validated_at, st.status_name, c.college_name, s.profile_image
          FROM students s
          JOIN validation_status st ON s.status_id = st.status_id
          JOIN colleges c ON s.college_id = c.college_id
          WHERE s.validated_at IS NOT NULL AND s.deleted_at IS NULL
          ORDER BY s.validated_at DESC
          LIMIT 5";

$stmt = $db->query($query);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
        $string = array('y'=>'year','m'=>'month','w'=>'week','d'=>'day','h'=>'hour','i'=>'minute','s'=>'second');
        foreach ($string as $k => &$v) {
            if ($diff->$k) { $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : ''); }
            else { unset($string[$k]); }
        }
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}
?>

</style>

<div class="recent-activity-container">
  <div class="widget-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h3 style="font-family:'Outfit', sans-serif; font-size:1.2rem; font-weight:700; color:#1e293b; margin:0;">Recent Activity</h3>
    <a href="students.php" style="font-size:0.85rem; font-weight:600; color:#2563eb; text-decoration:none;">View Detailed Log</a>
  </div>

  <ul class="activity-feed">
    <?php if (count($activities) > 0): ?>
      <?php foreach ($activities as $act): 
        $avatarPath = !empty($act['profile_image']) ? "../../assets/img/profiles/" . $act['profile_image'] : "../../assets/img/profiles/default.png";
        $fullName = htmlspecialchars($act['first_name'] . ' ' . $act['last_name']);
      ?>
        <li class="activity-feed-item">
          <div class="activity-avatar-wrapper">
            <img src="<?= $avatarPath ?>" alt="<?= $fullName ?>" class="activity-avatar" onerror="this.src='../../assets/img/profiles/default.png'">
            <div class="activity-status-dot <?= strtolower($act['status_name']) === 'validated' ? 'status-validated' : 'status-pending' ?>"></div>
          </div>
          <div class="activity-info">
            <div class="activity-header-row">
              <span class="activity-user-name"><?= $fullName ?></span>
              <span class="activity-timestamp"><?= time_elapsed_string($act['validated_at']) ?></span>
            </div>
            <div class="activity-meta">
              <span class="college-tag"><?= htmlspecialchars($act['college_name']) ?></span>
              <span>•</span>
              <span class="activity-action-text">Validated successfully</span>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    <?php else: ?>
      <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: 16px; border: 1px dashed #e2e8f0;">
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">No validation activity recorded today.</p>
      </div>
    <?php endif; ?>
  </ul>
</div>