<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
  header('Location: ../login.php');
  exit();
}
require_once "../../../server/config/database.php";
$database = new Database();
$db = $database->getConnection();

$user = $_SESSION['user'];
$student_id = $user['student_id'];

// Always fetch the latest status from the database to reflect admin changes immediately
$query = "SELECT status_id FROM students WHERE student_id = :sid LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':sid', $student_id);
$stmt->execute();
$db_status = $stmt->fetch(PDO::FETCH_ASSOC);

$status_id = $db_status['status_id'] ?? 2; // Default to Not Validated
$name = $user['first_name'] . ' ' . $user['last_name'];

// Map status to labels and classes
$status_map = [
  1 => ['label' => 'Validated', 'class' => 'validated'],
  2 => ['label' => 'Not Validated', 'class' => 'not-validated'],
  3 => ['label' => 'Pending Review', 'class' => 'pending']
];
$current_status = $status_map[$status_id] ?? $status_map[2];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../../assets/logo/sq.png">

  <!-- Component Loader Meta -->
  <meta name="component-base" content="../../components/">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="../../assets/css/main.css">
  <link rel="stylesheet" href="../../assets/css/components/components.css">
  <link rel="stylesheet" href="../../assets/css/users/student-dashboard.css">

  <title>SmartQ | My Dashboard</title>
</head>

<body>

  <div class="admin-layout">
    <!-- Sidebar (Desktop) -->
    <div data-component="sidebar" data-props='{"active":"dashboard", "role":"student"}'></div>

    <div class="admin-main">
      <!-- Topbar -->
      <div data-component="topbar"
        data-props='{"title":"Student Dashboard", "description":"Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!"}'>
      </div>

      <main class="admin-content">
        <div class="student-container">

          <!-- ── Hero / Status ── -->
          <div class="student-hero">
            <div class="hero-welcome">
              <h1>Hello, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
              <p>Keep your ID validated to enjoy seamless campus services.</p>
              <div class="status-pill <?php echo $current_status['class']; ?>">
                Status: <?php echo $current_status['label']; ?>
              </div>
            </div>
          </div>

          <!-- ── Active Queue (If booked) ── -->
          <?php
          try {
            // Fetch the latest booking for the student that is in an active schedule
            $query = "SELECT ql.queue_number, qs.schedule_date, qs.start_time, qs.end_time 
                        FROM queue_list ql
                        JOIN queue_schedule qs ON ql.schedule_id = qs.schedule_id
                        WHERE ql.student_id = :sid AND qs.status = 'active' AND qs.schedule_date >= CURDATE()
                        ORDER BY qs.schedule_date ASC LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':sid', $user['student_id']);
            $stmt->execute();
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking):
              $bDate = new DateTime($booking['schedule_date']);
              $bStart = new DateTime($booking['start_time']);
              $bEnd = new DateTime($booking['end_time']);
              ?>
                  <div class="queue-highlight">
                    <div class="queue-num-box">
                      <span>No.</span>
                      <span><?php echo $booking['queue_number']; ?></span>
                    </div>
                    <div class="queue-details">
                      <h4>Active Queue Booking</h4>
                      <p>Schedule: <?php echo $bDate->format('F d, Y'); ?>
                        (<?php echo $bStart->format('h:i A') . ' - ' . $bEnd->format('h:i A'); ?>)</p>
                    </div>
                  </div>
                <?php
            endif;
          } catch (Exception $e) {
            // Silent fail for dashboard highlight
          }
          ?>

          <!-- ── Action Grid ── -->
          <div class="student-grid">

            <!-- Book Validation -->
            <div class="student-card">
              <div class="card-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
              </div>
              <h3 class="card-title">Book Validation</h3>
              <p class="card-desc">Check available time slots and book your validation schedule to avoid long lines.</p>
              <a href="book-queue.php" class="btn-student">Browse Slots</a>
            </div>

            <!-- My History -->
            <div class="student-card">
              <div class="card-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M12 8v4l3 3"></path>
                  <circle cx="12" cy="12" r="9"></circle>
                </svg>
              </div>
              <h3 class="card-title">My History</h3>
              <p class="card-desc">View your previous validation logs and queue history for this semester.</p>
              <a href="my-history.php" class="btn-student">View History</a>
            </div>

            <!-- Profile Settings -->
            <div class="student-card">
              <div class="card-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
              </div>
              <h3 class="card-title">My Profile</h3>
              <p class="card-desc">Update your personal information and ensure your email is verified.</p>
              <a href="profile.php" class="btn-student">Edit Profile</a>
            </div>

          </div>

        </div>
      </main>

      <div data-component="footer"></div>
    </div>
  </div>

  <!-- ── Mobile Bottom Navigation ── -->
  <nav class="mobile-nav">
    <a href="student-dashboard.php" class="mobile-nav-item active">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path
          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
        </path>
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