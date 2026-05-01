<?php
session_start();
if (!isset($_SESSION['student'])) {
  header('Location: ../login.php');
  exit();
}
require_once "../../../server/config/database.php";
$database = new Database();
$db = $database->getConnection();

$user = $_SESSION['student'];
$student_id = $user['student_id'];

// Fetch latest student status and validation info
$query = "SELECT s.*, vs.status_name, c.college_name 
          FROM students s
          LEFT JOIN validation_status vs ON s.status_id = vs.status_id
          LEFT JOIN colleges c ON s.college_id = c.college_id
          WHERE s.student_id = :sid LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':sid', $student_id);
$stmt->execute();
$student_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch queue history (active or past bookings)
$queue_query = "SELECT ql.*, qs.schedule_date, qs.start_time, qs.end_time, qs.status as schedule_status
                FROM queue_list ql
                JOIN queue_schedule qs ON ql.schedule_id = qs.schedule_id
                WHERE ql.student_id = :sid
                ORDER BY qs.schedule_date DESC";
$q_stmt = $db->prepare($queue_query);
$q_stmt->bindParam(':sid', $student_id);
$q_stmt->execute();
$history = $q_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../../assets/logo/sq.png">
  <meta name="component-base" content="../../components/">
  <link rel="stylesheet" href="../../assets/css/main.css">
  <link rel="stylesheet" href="../../assets/css/components/components.css">
  <link rel="stylesheet" href="../../assets/css/users/student-dashboard.css">
  <title>SmartQ | My History</title>
</head>

<body>
  <div class="admin-layout">
    <div data-component="sidebar" data-props='{"active":"dashboard", "role":"student"}'></div>
    <div class="admin-main">
      <div data-component="topbar"
        data-props='{"title":"My History", "description":"View your validation and queue history."}'></div>
      <main class="admin-content">
        <div class="student-container">
          
          <!-- ── Current Status Card ── -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
              <div data-component="stat-card" data-props='{"label":"Current Status", "value":"<?php echo htmlspecialchars($student_data['status_name'] ?? 'Not Validated'); ?>", "trend": "<?php echo $student_data['status_id'] == 1 ? 'up' : 'flat'; ?>"}'></div>
              <div data-component="stat-card" data-props='{"label":"Validation Date", "value":"<?php echo $student_data['validated_at'] ? date('M d, Y', strtotime($student_data['validated_at'])) : 'Pending'; ?>", "trend": "flat"}'></div>
              <?php if($student_data['validated_by']): ?>
                <div data-component="stat-card" data-props='{"label":"Validated By", "value":"<?php echo htmlspecialchars($student_data['validated_by']); ?>", "trend": "flat"}'></div>
              <?php endif; ?>
          </div>

          <!-- ── Queue History ── -->
          <div class="student-card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0;">
            <div style="padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; color: #1e293b; font-size: 1.1rem; font-weight: 700;">Queue History</h3>
                <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;"><?php echo count($history); ?> total entries</span>
            </div>
            <div style="padding: 0;">
              <?php if (count($history) > 0): ?>
                <div style="overflow-x: auto;">
                  <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                      <tr style="background: #f8fafc;">
                        <th style="padding: 15px 20px; color: #64748b; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Date</th>
                        <th style="padding: 15px 20px; color: #64748b; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Time Slot</th>
                        <th style="padding: 15px 20px; color: #64748b; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Queue No.</th>
                        <th style="padding: 15px 20px; color: #64748b; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($history as $row): 
                        $date = new DateTime($row['schedule_date']);
                        $start = new DateTime($row['start_time']);
                        $end = new DateTime($row['end_time']);
                      ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                          <td style="padding: 15px 20px;">
                             <div style="font-weight: 600; color: #1e293b;"><?php echo $date->format('M d, Y'); ?></div>
                             <div style="font-size: 0.75rem; color: #94a3b8;"><?php echo $date->format('l'); ?></div>
                          </td>
                          <td style="padding: 15px 20px; color: #475569; font-size: 0.9rem;"><?php echo $start->format('h:i A') . ' - ' . $end->format('h:i A'); ?></td>
                          <td style="padding: 15px 20px;">
                             <span style="font-weight: 800; color: var(--student-primary); background: rgba(59, 130, 246, 0.1); padding: 4px 8px; border-radius: 6px;">#<?php echo str_pad($row['queue_number'], 3, '0', STR_PAD_LEFT); ?></span>
                          </td>
                          <td style="padding: 15px 20px; text-align: right;">
                            <span class="status-pill <?php echo $row['schedule_status'] == 'active' ? 'validated' : 'not-validated'; ?>" style="font-size: 0.7rem; padding: 4px 10px;">
                              <?php echo ucfirst($row['schedule_status']); ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div style="text-align: center; padding: 60px 20px;">
                  <div style="font-size: 3rem; margin-bottom: 15px;">📜</div>
                  <p style="color: #64748b; font-weight: 500;">No queue history found for your account.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        </div>
      </main>
      <div data-component="footer"></div>
    </div>
  </div>

  <nav class="mobile-nav">
    <a href="student-dashboard.php" class="mobile-nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
      </svg>
      <span>Dashboard</span>
    </a>
    <a href="book-queue.php" class="mobile-nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
      </svg>
      <span>Booking</span>
    </a>
    <a href="profile.php" class="mobile-nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
      </svg>
      <span>Profile</span>
    </a>
  </nav>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../../scripts/component-loader.js"></script>
</body>

</html>
