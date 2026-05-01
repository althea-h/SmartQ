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
          <!-- ── Booking Hero ── -->
          <div class="student-hero" style="background: linear-gradient(135deg, var(--student-primary) 0%, #1d4ed8 100%); padding: 30px; border-radius: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 15px 35px -5px rgba(59, 130, 246, 0.25);">
             <div>
                <h2 style="margin: 0; font-size: 1.6rem; font-weight: 800; letter-spacing: -0.5px;">Book Validation Slot</h2>
                <p style="margin: 5px 0 0; color: rgba(255,255,255,0.8); font-size: 0.95rem;">Choose a convenient date and time to validate your ID.</p>
             </div>
             <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 15px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.2); text-align: center;">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-bottom: 5px;"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span style="display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">Real-time slots</span>
             </div>
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
                echo '<div style="grid-column: 1/-1; background: #f0fdf4; color: #16a34a; padding: 25px; border-radius: 20px; margin-bottom: 20px; text-align: center; border: 1px solid #bdf4d4; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                            <div style="font-size: 2.5rem; margin-bottom: 10px;">✅</div>
                            <h3 style="margin-bottom: 5px; font-weight: 800;">You are already Validated!</h3>
                            <p style="opacity: 0.8; font-weight: 500;">Your ID is active. No further validation is required at this time.</p>
                          </div>';
              } else {
                // Check if student has ANY active booking
                $active_booking_query = "SELECT 1 FROM queue_list ql 
                                         JOIN queue_schedule qs ON ql.schedule_id = qs.schedule_id 
                                         WHERE ql.student_id = :sid AND qs.status = 'active' AND qs.schedule_date >= CURDATE() 
                                         LIMIT 1";
                $ab_stmt = $db->prepare($active_booking_query);
                $ab_stmt->bindParam(':sid', $user['student_id']);
                $ab_stmt->execute();
                if ($ab_stmt->fetch()) {
                  echo '<div style="grid-column: 1/-1; background: #fffbeb; color: #b45309; padding: 25px; border-radius: 20px; margin-bottom: 20px; text-align: center; border: 1px solid #fde68a; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                              <div style="font-size: 2.5rem; margin-bottom: 10px;">🕒</div>
                              <h3 style="margin-bottom: 5px; font-weight: 800;">Active Booking Found</h3>
                              <p style="opacity: 0.8; font-weight: 500;">You have an upcoming validation. Check your dashboard for details.</p>
                            </div>';
                }
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
                        <div class="student-card schedule-item" style="border: 1px solid #e2e8f0; border-radius: 20px; padding: 0; overflow: hidden; transition: all 0.3s; ' . $cardStyle . '">
                          <div style="padding: 24px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                              <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="schedule-date-square" style="background: #f8fafc; border: 1px solid #e2e8f0; width: 60px; height: 60px; border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                                  <span style="font-size: 1.3rem; font-weight: 800; color: #1e293b; line-height: 1;">' . $day . '</span>
                                  <span style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-top: 2px;">' . $date->format('M') . '</span>
                                </div>
                                <div>
                                  <h4 style="margin: 0; color: #1e293b; font-size: 1.1rem; font-weight: 700;">' . $monthYear . '</h4>
                                  <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 5px;">
                                     <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                     ' . $timeSlot . '
                                  </p>
                                </div>
                              </div>
                              <span class="status-pill ' . ($available > 10 ? 'validated' : ($available > 0 ? 'pending' : 'not-validated')) . '" style="font-size: 0.75rem; font-weight: 700; padding: 6px 12px; border-radius: 10px;">
                                ' . ($is_expired ? 'CLOSED' : ($available . ' Slots Left')) . '
                              </span>
                            </div>

                            <div style="margin-bottom: 20px;">
                              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em;">Slot Availability</span>
                                <span style="font-size: 0.85rem; color: #1e293b; font-weight: 800;">' . $booked . ' / ' . $limit . '</span>
                              </div>
                              <div style="height: 8px; background: #f1f5f9; border-radius: 99px; overflow: hidden;">
                                <div style="width: ' . $percentage . '%; height: 100%; background: ' . ($is_expired ? '#cbd5e1' : 'var(--student-primary)') . '; border-radius: 99px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                              </div>
                            </div>';

                  echo '<div style="padding: 15px; background: #f8fafc; border-top: 1px solid #f1f5f9; border-radius: 0 0 20px 20px;">';
                  if ($is_booked) {
                    echo '<button disabled style="width: 100%; background: #dcfce7; color: #16a34a; border: none; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Already Booked
                                  </button>';
                  } elseif ($is_expired) {
                    echo '<button disabled style="width: 100%; background: #f1f5f9; color: #94a3b8; border: none; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.9rem;">Schedule Closed</button>';
                  } elseif ($available <= 0) {
                    echo '<button disabled style="width: 100%; background: #f1f5f9; color: #94a3b8; border: none; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.9rem;">Fully Booked</button>';
                  } elseif ($is_validated) {
                    echo '<button disabled title="You are already validated" style="width: 100%; background: #f1f5f9; color: #94a3b8; border: none; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; cursor: not-allowed;">Validated</button>';
                  } else {
                    echo '<button class="btn-book-now" data-id="' . $row['schedule_id'] . '" style="width: 100%; background: var(--student-primary); color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 8px 20px -5px rgba(59, 130, 246, 0.4);">
                                    Book This Slot
                                  </button>';
                  }
                  echo '</div></div>';
                }
              } else {
                echo '<div style="grid-column: 1/-1; text-align: center; padding: 80px 20px; background: white; border-radius: 24px; border: 2px dashed #e2e8f0;">
                            <div style="font-size: 4rem; margin-bottom: 20px;">🗓️</div>
                            <h3 style="color: #1e293b; margin-bottom: 10px; font-size: 1.5rem; font-weight: 800;">No Active Schedules</h3>
                            <p style="color: #64748b; font-size: 1.1rem;">There are no validation schedules available at the moment.<br>Please check back later.</p>
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