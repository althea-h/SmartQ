<?php
session_start();
if (!isset($_SESSION['student'])) {
  header('Location: ../login.php');
  exit();
}
$user = $_SESSION['student'];
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
  <title>SmartQ | Book Validation</title>
</head>

<body>
  <div class="admin-layout">
    <div data-component="sidebar" data-props='{"active":"book", "role":"student"}'></div>
    <div class="admin-main">
      <div data-component="topbar"
        data-props='{"title":"Book Validation", "description":"Select a schedule to validate your ID."}'></div>
      <main class="admin-content">
        <div class="student-container">
          <div style="margin-bottom: 25px;">
            <h2 style="color: #1e293b; font-size: 1.5rem; font-weight: 700;">Validation Schedules</h2>
            <p style="color: #64748b;">Choose a convenient date and time to validate your ID.</p>
          </div>

          <!-- ── Schedule Grid ── -->
          <div class="student-grid" id="booking-grid">
            <?php
            require_once "../../../server/config/database.php";
            $database = new Database();
            $db = $database->getConnection();

            date_default_timezone_set('Asia/Manila');
            $now = new DateTime();

            try {
              // Check if student is already validated
              $status_query = "SELECT status_id FROM students WHERE student_id = :sid LIMIT 1";
              $status_stmt = $db->prepare($status_query);
              $status_stmt->bindParam(':sid', $user['student_id']);
              $status_stmt->execute();
              $is_validated = ($status_stmt->fetchColumn() == 1);

              if ($is_validated) {
                echo '<div style="grid-column: 1/-1; background: #dcfce7; color: #16a34a; padding: 20px; border-radius: 16px; margin-bottom: 20px; text-align: center; border: 1px solid #bdf4d4;">
                            <h3 style="margin-bottom: 5px;">You are already Validated!</h3>
                            <p>You don\'t need to book any more validation schedules.</p>
                          </div>';
              }

              // Fetch only active schedules
              $query = "SELECT qs.*, 
                          (SELECT COUNT(*) FROM queue_list ql WHERE ql.schedule_id = qs.schedule_id) as booked_count
                          FROM queue_schedule qs 
                          WHERE qs.status = 'active' AND qs.schedule_date >= CURDATE()
                          ORDER BY qs.schedule_date ASC, qs.start_time ASC";
              $stmt = $db->prepare($query);
              $stmt->execute();
              $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

              if (count($schedules) > 0) {
                foreach ($schedules as $row) {
                  $date = new DateTime($row['schedule_date']);
                  $day = $date->format('d');
                  $monthYear = $date->format('F Y');

                  $startTime = new DateTime($row['start_time']);
                  $endTime = new DateTime($row['end_time']);
                  $timeSlot = $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A');

                  $eventEndTime = new DateTime($row['schedule_date'] . ' ' . $row['end_time']);
                  $is_expired = ($now > $eventEndTime);

                  $booked = $row['booked_count'];
                  $limit = $row['slot_limit'];
                  $available = $limit - $booked;
                  $percentage = ($limit > 0) ? ($booked / $limit) * 100 : 0;

                  // Check if student already has a booking for this schedule
                  $check_query = "SELECT 1 FROM queue_list WHERE student_id = :sid AND schedule_id = :schid";
                  $check_stmt = $db->prepare($check_query);
                  $check_stmt->bindParam(':sid', $user['student_id']);
                  $check_stmt->bindParam(':schid', $row['schedule_id']);
                  $check_stmt->execute();
                  $is_booked = $check_stmt->fetch();

                  $cardStyle = $is_expired ? 'opacity: 0.7; filter: grayscale(0.5);' : '';

                  echo '
                        <div class="student-card schedule-item" style="' . $cardStyle . '">
                          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                            <div class="schedule-date" style="display: flex; flex-direction: column;">
                              <span style="font-size: 1.5rem; font-weight: 800; color: ' . ($is_expired ? '#94a3b8' : 'var(--student-primary)') . ';">' . $day . '</span>
                              <span style="font-size: 0.8rem; color: #64748b; font-weight: 600; text-transform: uppercase;">' . $monthYear . '</span>
                            </div>
                            <div style="text-align: right;">
                              <span style="display: block; font-size: 0.75rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Status</span>
                              <span style="font-size: 0.95rem; font-weight: 700; color: ' . ($is_expired ? '#94a3b8' : ($available > 10 ? '#22c55e' : '#ef4444')) . ';">
                                ' . ($is_expired ? 'CLOSED' : ($available . ' Slots Left')) . '
                              </span>
                            </div>
                          </div>

                          <div style="margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; color: #475569;">
                              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                              <span style="font-size: 0.9rem; font-weight: 500;">' . $timeSlot . '</span>
                            </div>
                            <div style="height: 6px; background: #f1f5f9; border-radius: 99px; overflow: hidden;">
                              <div style="width: ' . $percentage . '%; height: 100%; background: ' . ($is_expired ? '#cbd5e1' : 'var(--student-primary)') . '; border-radius: 99px;"></div>
                            </div>
                          </div>';

                  if ($is_booked) {
                    echo '<button disabled style="width: 100%; background: #dcfce7; color: #16a34a; border: none; padding: 12px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Already Booked
                                  </button>';
                  } elseif ($is_expired) {
                    echo '<button disabled style="width: 100%; background: #f1f5f9; color: #94a3b8; border: none; padding: 12px; border-radius: 12px; font-weight: 700;">Schedule Closed</button>';
                  } elseif ($available <= 0) {
                    echo '<button disabled style="width: 100%; background: #f1f5f9; color: #94a3b8; border: none; padding: 12px; border-radius: 12px; font-weight: 700;">Fully Booked</button>';
                  } elseif ($is_validated) {
                    echo '<button disabled title="You are already validated" style="width: 100%; background: #f1f5f9; color: #94a3b8; border: none; padding: 12px; border-radius: 12px; font-weight: 700; cursor: not-allowed;">Validated</button>';
                  } else {
                    echo '<button class="btn-book-now" data-id="' . $row['schedule_id'] . '" style="width: 100%; background: var(--student-primary); color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">
                                    Book This Slot
                                  </button>';
                  }

                  echo '</div>';
                }
              } else {
                echo '<div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; background: white; border-radius: 20px; border: 1px dashed #cbd5e1;">
                            <div style="font-size: 3rem; margin-bottom: 15px;">🗓️</div>
                            <h3 style="color: #1e293b; margin-bottom: 8px;">No Active Schedules</h3>
                            <p style="color: #64748b;">There are no validation schedules available at the moment. Please check back later.</p>
                          </div>';
              }
            } catch (Exception $e) {
              echo '<div style="grid-column: 1/-1; color: var(--error);">Error loading schedules: ' . $e->getMessage() . '</div>';
            }
            ?>
          </div>
        </div>
      </main>

      <div data-component="footer"></div>
    </div>
  </div>

  <nav class="mobile-nav">
    <a href="student-dashboard.php" class="mobile-nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path
          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
        </path>
      </svg>
      <span>Dashboard</span>
    </a>
    <a href="book-queue.php" class="mobile-nav-item active">
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

  <script>
    $(document).ready(function () {
      // Handle Booking
      $('.btn-book-now').click(function () {
        const scheduleId = $(this).data('id');
        const $btn = $(this);

        if (confirm('Are you sure you want to book this validation slot?')) {
          $btn.prop('disabled', true).text('Booking...');

          $.ajax({
            url: '../../../server/api/queue/book_schedule.php',
            method: 'POST',
            data: { schedule_id: scheduleId },
            dataType: 'json',
            success: function (response) {
              if (response.success) {
                alert('Success! Your queue number is: ' + response.queue_number);
                location.href = 'student-dashboard.php';
              } else {
                alert('Error: ' + response.message);
                $btn.prop('disabled', false).text('Book This Slot');
              }
            },
            error: function () {
              alert('Failed to connect to the server.');
              $btn.prop('disabled', false).text('Book This Slot');
            }
          });
        }
      });
    });
  </script>
</body>

</html>