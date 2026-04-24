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
    .manage-header {
      background: var(--card-bg);
      padding: var(--space-lg) var(--space-xl);
      border-radius: var(--radius-lg);
      border: 1px solid rgba(0, 0, 0, 0.04);
      box-shadow: var(--shadow-sm);
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: var(--space-xl);
    }

    .queue-details h2 {
      font-size: var(--font-lg);
      margin-bottom: 4px;
    }

    .queue-meta {
      display: flex;
      gap: var(--space-md);
      color: var(--text-muted);
      font-size: var(--font-sm);
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .btn-back {
      color: var(--text-muted);
      text-decoration: none;
      font-weight: 600;
      font-size: var(--font-sm);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-back:hover {
      color: var(--primary-color);
    }
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
          <a href="queue.php" class="btn-back">Back to Schedules</a>

          <header class="manage-header" style="margin-top: var(--space-md);">
            <div class="queue-details">
              <h2>April 25, 2024 Schedule</h2>
              <div class="queue-meta">
                <span class="meta-item"> 08:00 AM - 12:00 PM</span>
                <span class="meta-item"> 120 / 150 Students</span>
              </div>
            </div>
            <div class="queue-actions">
              <button class="btn-primary">Download List</button>
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
                <!-- Student 1 -->
                <tr>
                  <td><strong style="color: var(--primary-color);">#001</strong></td>
                  <td class="student-id-cell">20210001234</td>
                  <td class="student-name-cell">John Michael Doe</td>
                  <td><span class="college-badge-small">COT</span></td>
                  <td><span class="status-badge badge-pending">Pending</span></td>
                  <td>
                    <div class="action-buttons" style="justify-content: flex-end;">
                      <button class="btn-action btn-validate" title="Approve ID"></button>
                      <button class="btn-action" title="Reject / Missing Doc" style="color: var(--error);"></button>
                    </div>
                  </td>
                </tr>

                <!-- Student 2 -->
                <tr>
                  <td><strong style="color: var(--primary-color);">#002</strong></td>
                  <td class="student-id-cell">20220005678</td>
                  <td class="student-name-cell">Jane Smith</td>
                  <td><span class="college-badge-small">CAS</span></td>
                  <td><span class="status-badge badge-pending">Pending</span></td>
                  <td>
                    <div class="action-buttons" style="justify-content: flex-end;">
                      <button class="btn-action btn-validate" title="Approve ID"></button>
                      <button class="btn-action" title="Reject / Missing Doc" style="color: var(--error);"></button>
                    </div>
                  </td>
                </tr>

                <!-- Student 3 -->
                <tr>
                  <td><strong style="color: var(--primary-color);">#003</strong></td>
                  <td class="student-id-cell">20230004321</td>
                  <td class="student-name-cell">Maria Clara De la Cruz</td>
                  <td><span class="college-badge-small">CON</span></td>
                  <td><span class="status-badge badge-pending">Pending</span></td>
                  <td>
                    <div class="action-buttons" style="justify-content: flex-end;">
                      <button class="btn-action btn-validate" title="Approve ID"></button>
                      <button class="btn-action" title="Reject / Missing Doc" style="color: var(--error);"></button>
                    </div>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>

        </div>
      </main>

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
      // Mock Action Handler
      $('.btn-validate').click(function () {
        if (confirm('Approve this student ID validation?')) {
          const row = $(this).closest('tr');
          row.fadeOut(400, function () {
            row.remove();
            alert('Student validated successfully!');
          });
        }
      });
    });
  </script>

</body>

</html>