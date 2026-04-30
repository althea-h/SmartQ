<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../../assets/logo/sq.png">

  <!-- Tell the component loader where to find components -->
  <meta name="component-base" content="../../components/">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="../../assets/css/main.css">
  <link rel="stylesheet" href="../../assets/css/components/components.css">
  <link rel="stylesheet" href="../../assets/css/admin/queue.css">

  <title>SQ | Queue Management</title>
</head>

<body>

  <div class="admin-layout">
    <!-- Sidebar -->
    <div data-component="sidebar" data-props='{"active":"queue"}'></div>

    <div class="admin-main">
      <!-- Topbar -->
      <div data-component="topbar"
        data-props='{"title":"Queue Management","description":"Create and manage ID validation schedules." }'></div>

      <main class="admin-content">
        <?php
        date_default_timezone_set('Asia/Manila');
        require_once "../../../server/config/database.php";
        $database = new Database();
        $db = $database->getConnection();
        ?>
        <div class="queue-container">

          <?php
          // Count active schedules
          $activeCount = $db->query("SELECT COUNT(*) FROM queue_schedule WHERE status = 'active'")->fetchColumn() ?: 0;

          // Total slots
          $totalSlots = $db->query("SELECT SUM(slot_limit) AS total_slots FROM queue_schedule")
            ->fetch(PDO::FETCH_ASSOC)['total_slots'] ?? 0;

          // Students booked
          $studentsBooked = $db->query("SELECT COUNT(student_id) AS students_booked FROM queue_list")
            ->fetch(PDO::FETCH_ASSOC)['students_booked'] ?? 0;

          // Remaining
          $remaining = $totalSlots - $studentsBooked;
          ?>

          <!-- ── Header ── -->
          <header class="queue-header">
            <div class="queue-title">
              <h2>Active Schedules</h2>
              <p>You have <?= $activeCount ?> active validation schedule<?= $activeCount != 1 ? 's' : '' ?> currently.
              </p>
            </div>
            <button class="btn-add-schedule" id="openModal">
              <span>+</span> Create Schedule
            </button>
          </header>

          <div class="queue-stats-grid">
            <div data-component="stat-card" data-props='{"label":"Total Slots","value":"<?= $totalSlots ?>"}'></div>
            <div data-component="stat-card" data-props='{"label":"Students Booked","value":"<?= $studentsBooked ?>"}'>
            </div>
            <div data-component="stat-card" data-props='{"label":"Remaining","value":"<?= $remaining ?>"}'></div>
          </div>

          <!-- ── Schedule Grid ── -->
          <div class="schedule-grid" id="schedule-grid">
            <?php

            try {
              // Fetch schedules (all for now, or just active)
              $query = "SELECT qs.*, 
                          (SELECT COUNT(*) FROM queue_list ql WHERE ql.schedule_id = qs.schedule_id) as booked_count
                          FROM queue_schedule qs 
                          ORDER BY (CASE WHEN qs.status = 'active' THEN 0 ELSE 1 END), qs.schedule_date DESC, qs.start_time DESC";
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

                  $booked = $row['booked_count'];
                  $limit = $row['slot_limit'];
                  $percentage = ($limit > 0) ? ($booked / $limit) * 100 : 0;

                  $status = $row['status'] ?? 'active';
                  $currentTime = new DateTime();
                  $eventEndTime = new DateTime($row['schedule_date'] . ' ' . $row['end_time']);

                  // Auto-close if time has passed
                  if ($status === 'active' && $currentTime > $eventEndTime) {
                    $status = 'closed';
                  }

                  $statusClass = 'status-active';
                  $statusText = 'Active';

                  if ($status === 'cancelled') {
                    $statusClass = 'status-full'; // Red
                    $statusText = 'Cancelled';
                  } elseif ($status === 'closed') {
                    $statusClass = 'status-closed'; // Grey
                    $statusText = 'Closed';
                  } elseif ($percentage >= 100) {
                    $statusClass = 'status-full';
                    $statusText = 'Full';
                  } elseif ($percentage >= 80) {
                    $statusClass = 'status-full';
                    $statusText = 'Almost Full';
                  }

                  echo '
                        <div class="schedule-card ' . ($status === 'cancelled' || $status === 'closed' ? 'cancelled' : '') . '" data-id="' . $row['schedule_id'] . '">
                          <div class="schedule-card-header">
                            <div class="schedule-date">
                              <span class="date-day">' . $day . '</span>
                              <span class="date-month">' . $monthYear . '</span>
                            </div>
                            <span class="schedule-status ' . $statusClass . '">' . $statusText . '</span>
                          </div>
                          <div class="schedule-info">
                            <div class="info-item">
                              <span class="info-label">Time Slot</span>
                              <span class="info-value">' . $timeSlot . '</span>
                            </div>
                            <div class="info-item">
                              <span class="info-label">Catered</span>
                              <span class="info-value">' . $limit . ' Students</span>
                            </div>
                          </div>
                          <div class="schedule-progress">
                            <div class="progress-labels">
                              <span>Booking Progress</span>
                              <span>' . $booked . ' / ' . $limit . '</span>
                            </div>
                            <div class="progress-track">
                              <div class="progress-bar" style="width: ' . $percentage . '%; ' . ($percentage >= 90 ? 'background: var(--warning);' : '') . ' ' . ($status === 'cancelled' || $status === 'closed' ? 'background: #cbd5e1;' : '') . '"></div>
                            </div>
                          </div>
                          <div class="schedule-actions" style="gap: 10px; flex-wrap: wrap;">';

                  if ($status === 'active') {
                    echo '<a href="manage-queue.php?id=' . $row['schedule_id'] . '" class="btn-manage" style="text-align: center; text-decoration: none; flex: 1; min-width: 100px;">Manage</a>
                                  <button class="btn-cancel-schedule" data-id="' . $row['schedule_id'] . '" style="background: #fee2e2; color: #ef4444; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; flex: 1; min-width: 100px;">Cancel</button>';
                  } elseif ($status === 'cancelled' || $status === 'closed') {
                    echo '<div style="display: flex; flex-direction: column; width: 100%; gap: 10px;">
                                    <div style="display: flex; gap: 10px; width: 100%;">
                                      <a href="../../../server/api/events/download_report.php?id=' . $row['schedule_id'] . '" class="btn-download" style="background: #dcfce7; color: #16a34a; text-decoration: none; text-align: center; padding: 10px; border-radius: 8px; font-weight: 600; flex: 1;">Report</a>
                                      <button class="btn-remove-schedule" data-id="' . $row['schedule_id'] . '" style="background: #f1f5f9; color: #64748b; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; flex: 1;">Remove</button>
                                    </div>
                                    <button disabled style="width: 100%; background: #f1f5f9; color: #94a3b8; border: none; padding: 10px; border-radius: 8px; font-weight: 600;">Schedule ' . ucfirst($status) . '</button>
                                  </div>';
                  }

                  echo '</div>
                        </div>';
                }
              } else {
                echo '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">No schedules created yet.</div>';
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

  <!-- ── Create Schedule Modal ── -->
  <div class="schedule-modal" id="scheduleModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Create New Schedule</h3>
        <button class="btn-close" id="closeModal">&times;</button>
      </div>
      <form class="schedule-form" id="createScheduleForm">
        <div class="form-group">
          <label>Validation Date</label>
          <input type="date" name="date" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Start Time</label>
            <input type="time" name="start_time" required>
          </div>
          <div class="form-group">
            <label>End Time</label>
            <input type="time" name="end_time" required>
          </div>
        </div>
        <div class="form-group">
          <label>Max Students to Cater</label>
          <input type="number" name="slot_limit" placeholder="e.g. 100" required>
        </div>
        <button type="submit" class="btn-add-schedule" id="btn-save-schedule"
          style="width: 100%; justify-content: center; margin-top: 10px;">
          Save Schedule
        </button>
      </form>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../../scripts/component-loader.js"></script>

  <script>
    $(document).ready(function () {
      // Modal Logic
      const modal = $('#scheduleModal');
      $('#openModal').click(() => modal.css('display', 'flex'));
      $('#closeModal').click(() => modal.hide());

      $(window).click((e) => {
        if (e.target == modal[0]) modal.hide();
      });

      // Handle Form Submit
      $('#createScheduleForm').submit(function (e) {
        e.preventDefault();

        const $btn = $('#btn-save-schedule');
        const formData = $(this).serialize();

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
          url: '../../../server/api/events/create_schedule.php',
          method: 'POST',
          data: formData,
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              alert('Success: ' + response.message);
              location.reload(); // Reload to see the new schedule
            } else {
              alert('Error: ' + response.message);
              $btn.prop('disabled', false).text('Save Schedule');
            }
          },
          error: function () {
            alert('Failed to connect to the server.');
            $btn.prop('disabled', false).text('Save Schedule');
          }
        });
      });

      // Handle Cancel Schedule
      $(document).on('click', '.btn-cancel-schedule', function () {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to cancel this schedule? This action cannot be undone.')) {
          const $btn = $(this);
          $btn.prop('disabled', true).text('...');

          $.ajax({
            url: '../../../server/api/events/cancel_schedule.php',
            method: 'POST',
            data: { schedule_id: id },
            dataType: 'json',
            success: function (response) {
              if (response.success) {
                location.reload();
              } else {
                alert('Error: ' + response.message);
                $btn.prop('disabled', false).text('Cancel');
              }
            },
            error: function () {
              alert('Failed to connect to the server.');
              $btn.prop('disabled', false).text('Cancel');
            }
          });
        }
      });

      // Handle Remove Schedule
      $(document).on('click', '.btn-remove-schedule', function () {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to permanently REMOVE this schedule from the list? All associated queue data will be deleted.')) {
          const $btn = $(this);
          $btn.prop('disabled', true).text('...');

          $.ajax({
            url: '../../../server/api/events/delete_schedule.php',
            method: 'POST',
            data: { schedule_id: id },
            dataType: 'json',
            success: function (response) {
              if (response.success) {
                alert(response.message);
                location.reload();
              } else {
                alert('Error: ' + response.message);
                $btn.prop('disabled', false).text('Remove');
              }
            },
            error: function () {
              alert('Failed to connect to the server.');
              $btn.prop('disabled', false).text('Remove');
            }
          });
        }
      });
    });
  </script>

</body>

</html>