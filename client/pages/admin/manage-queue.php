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
  <link rel="stylesheet" href="../../assets/css/admin/students.css">
  <link rel="stylesheet" href="../../assets/css/admin/queue.css">

  <title>SmartQ | Manage Queue</title>
  <style>

  </style>
</head>

<body>

  <div class="admin-layout">
    <!-- Sidebar -->
    <div data-component="sidebar" data-props='{"active":"queue"}'></div>

    <div class="admin-main">
      <!-- Topbar -->
      <div data-component="topbar"
        data-props='{"title":"Queue Validation", "description":"Approve or reject student ID validations for this slot."}'>
      </div>

      <main class="admin-content">
        <div class="manage-container">

          <!-- ── Back & Header ── -->
          <a href="queue.php" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Back to Schedules
          </a>

          <?php
          require_once "../../../server/config/database.php";
          $database = new Database();
          $db = $database->getConnection();

          $schedule_id = $_GET['id'] ?? '';

          if (empty($schedule_id)) {
            echo '<div class="alert alert-danger">No schedule ID provided.</div>';
            exit;
          }

          try {
            // 1. Fetch Schedule Details
            $schQuery = "SELECT * FROM queue_schedule WHERE schedule_id = :id";
            $schStmt = $db->prepare($schQuery);
            $schStmt->bindParam(':id', $schedule_id);
            $schStmt->execute();
            $schedule = $schStmt->fetch(PDO::FETCH_ASSOC);

            if (!$schedule) {
              echo '<div class="alert alert-danger">Schedule not found.</div>';
              exit;
            }

            // Get booked count
            $countQuery = "SELECT COUNT(*) FROM queue_list WHERE schedule_id = :id";
            $countStmt = $db->prepare($countQuery);
            $countStmt->bindParam(':id', $schedule_id);
            $countStmt->execute();
            $bookedCount = $countStmt->fetchColumn();

            $date = new DateTime($schedule['schedule_date']);
            $startTime = new DateTime($schedule['start_time']);
            $endTime = new DateTime($schedule['end_time']);

            // 2. Fetch Queued Students
            $queueQuery = "SELECT ql.*, s.first_name, s.last_name, c.college_name, vs.status_name 
                           FROM queue_list ql
                           JOIN students s ON ql.student_id = s.student_id
                           LEFT JOIN colleges c ON s.college_id = c.college_id
                           LEFT JOIN validation_status vs ON s.status_id = vs.status_id
                           WHERE ql.schedule_id = :id
                           ORDER BY ql.queue_number ASC";
            $queueStmt = $db->prepare($queueQuery);
            $queueStmt->bindParam(':id', $schedule_id);
            $queueStmt->execute();
            $queuedStudents = $queueStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <header class="manage-header" style="margin-top: var(--space-md);">
              <div class="queue-details">
                <h2><?= $date->format('F d, Y') ?> Schedule</h2>
                <div class="queue-meta">
                  <span class="meta-item"> <?= $startTime->format('h:i A') ?> - <?= $endTime->format('h:i A') ?></span>
                  <span class="meta-item"> <?= $bookedCount ?> / <?= $schedule['slot_limit'] ?> Students</span>
                </div>
              </div>
              <div class="queue-actions">
                <a href="../../../server/api/events/download_report.php?id=<?= $schedule_id ?>" class="btn-primary"
                  style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">Download
                  List</a>
              </div>
            </header>

            <!-- ── Pending Validation Table ── -->
            <div class="students-table-container">
              <table class="students-table">
                <thead>
                  <tr>
                    <th>Queue Number</th>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>College</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($queuedStudents) > 0): ?>
                    <?php foreach ($queuedStudents as $row):
                      $status_class = strtolower(str_replace(' ', '-', $row['status_name'] ?? 'pending'));
                      ?>
                      <tr>
                        <td><strong
                            style="color: var(--primary-color);">#<?= str_pad($row['queue_number'], 3, '0', STR_PAD_LEFT) ?></strong>
                        </td>
                        <td class="student-id-cell"><?= htmlspecialchars($row['student_id']) ?></td>
                        <td class="student-name-cell"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                        </td>
                        <td><span class="college-badge-small"><?= htmlspecialchars($row['college_name'] ?? 'N/A') ?></span>
                        </td>
                        <td><span
                            class="status-badge badge-<?= $status_class ?>"><?= htmlspecialchars($row['status_name'] ?? 'Pending') ?></span>
                        </td>
                        <td>
                          <div class="action-buttons" style="justify-content: flex-end;">
                            <?php if (trim(strtolower($row['status_name'])) === 'pending'): ?>
                              <button class="btn-action btn-approve" title="Approve ID"
                                data-student-id="<?= htmlspecialchars($row['student_id']) ?>">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                                  viewBox="0 0 24 24">
                                  <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                              </button>
                              <button class="btn-action btn-reject" title="Reject / Missing Doc" style="color: var(--error);"
                                data-student-id="<?= htmlspecialchars($row['student_id']) ?>">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                                  viewBox="0 0 24 24">
                                  <line x1="18" y1="6" x2="6" y2="18"></line>
                                  <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                              </button>
                            <?php else: ?>
                              <span
                                style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Completed</span>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" style="text-align:center; padding: 40px; color: var(--text-muted);">No students have
                        queued for this schedule yet.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <?php
          } catch (Exception $e) {
            echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
          }
          ?>

        </div>
      </main>

      <!-- ── Confirmation Modal ── -->
      <div id="status-modal"
        style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
        <!-- Backdrop -->
        <div id="modal-backdrop"
          style="position:absolute; inset:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(3px);"></div>
        <!-- Card -->
        <div
          style="position:relative; background:var(--surface,#fff); border-radius:14px; padding:36px 32px; max-width:420px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.25); text-align:center;">
          <div id="modal-icon"
            style="width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
            <!-- icon injected by JS -->
          </div>
          <h3 id="modal-title" style="margin:0 0 10px; font-size:1.2rem; color:var(--text-primary,#111);"></h3>
          <p id="modal-desc" style="margin:0 0 28px; font-size:0.9rem; color:var(--text-muted,#666); line-height:1.55;">
          </p>
          <div style="display:flex; gap:12px; justify-content:center;">
            <button id="modal-cancel-btn"
              style="flex:1; padding:10px 0; border-radius:8px; border:1px solid var(--border,#ddd); background:transparent; color:var(--text-primary,#333); font-size:0.9rem; cursor:pointer;">Cancel</button>
            <button id="modal-confirm-btn"
              style="flex:1; padding:10px 0; border-radius:8px; border:none; color:#fff; font-size:0.9rem; font-weight:600; cursor:pointer;">Confirm</button>
          </div>
        </div>
      </div>

      <div data-component="footer"></div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../../scripts/component-loader.js"></script>

  <script>
    SmartQ.onLoad('sidebar', function ($el) {
      $(document).on('click', '#sidebar-toggle', function () {
        $('#sidebar').toggleClass('open');
      });
    });

    $(document).ready(function () {
      const $modal = $('#status-modal');
      const $modalIcon = $('#modal-icon');
      const $modalTitle = $('#modal-title');
      const $modalDesc = $('#modal-desc');
      const $confirmBtn = $('#modal-confirm-btn');
      const $cancelBtn = $('#modal-cancel-btn');

      let pendingAction = null; // { studentId, action, $row }

      function openModal(config) {
        $modalIcon.css('background', config.iconBg).html(config.iconSvg);
        $modalTitle.text(config.title);
        $modalDesc.text(config.desc);
        $confirmBtn.css('background', config.confirmColor);
        $modal.css('display', 'flex');
      }

      function closeModal() {
        $modal.fadeOut(150);
        pendingAction = null;
      }

      $('#modal-backdrop, #modal-cancel-btn').on('click', closeModal);

      // Approve button
      $(document).on('click', '.btn-approve', function () {
        const $row = $(this).closest('tr');
        const id = $(this).data('student-id');
        pendingAction = { studentId: id, action: 'approve', $row };
        openModal({
          iconBg: 'rgba(34,197,94,0.15)',
          iconSvg: '<svg width="24" height="24" fill="none" stroke="#22c55e" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>',
          title: 'Approve Validation?',
          desc: `Are you sure you want to approve ID validation for Student ${id}? Their status will be set to Validated.`,
          confirmColor: '#22c55e'
        });
      });

      // Reject button
      $(document).on('click', '.btn-reject', function () {
        const $row = $(this).closest('tr');
        const id = $(this).data('student-id');
        pendingAction = { studentId: id, action: 'reject', $row };
        openModal({
          iconBg: 'rgba(239,68,68,0.12)',
          iconSvg: '<svg width="24" height="24" fill="none" stroke="#ef4444" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
          title: 'Reject / Cancel Validation?',
          desc: `Are you sure you want to reject the validation request for Student ${id}? Their status will be reset to Not Validated.`,
          confirmColor: '#ef4444'
        });
      });

      // Confirm action → AJAX
      $confirmBtn.on('click', function () {
        if (!pendingAction) return;

        const { studentId, action, $row } = pendingAction;
        $confirmBtn.prop('disabled', true).text('Processing...');

        $.ajax({
          url: '../../../server/api/students/update_status.php',
          method: 'POST',
          data: { student_id: studentId, action: action },
          dataType: 'json',
          success: function (res) {
            if (res.success) {
              const isApproved = (action === 'approve');
              const newStatus = isApproved ? 'Validated' : 'Not Validated';
              const newClass = isApproved ? 'validated' : 'not-validated';
              const $statusBadge = $row.find('.status-badge');
              $statusBadge
                .removeClass()
                .addClass('status-badge badge-' + newClass)
                .text(newStatus);

              // Update action cell
              $row.find('.action-buttons').html('<span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Completed</span>');
              closeModal();
            } else {
              alert('Error: ' + res.message);
            }
          },
          error: function () {
            alert('Failed to connect to the server.');
          },
          complete: function () {
            $confirmBtn.prop('disabled', false).text('Confirm');
          }
        });
      });
    });
  </script>

</body>

</html>