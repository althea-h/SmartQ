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
        <div class="queue-container">

          <!-- ── Header ── -->
          <header class="queue-header">
            <div class="queue-title">
              <h2>Active Schedules</h2>
              <p>You have 3 active validation schedules for this week.</p>
            </div>
            <button class="btn-add-schedule" id="openModal">
              <span>+</span> Create Schedule
            </button>
          </header>

          <!-- ── Stats Summary ── -->
          <div class="queue-stats-grid">
            <div data-component="stat-card" data-props='{"label":"Total Slots","value":"450"}'></div>
            <div data-component="stat-card" data-props='{"label":"Students Booked","value":"328"}'></div>
            <div data-component="stat-card" data-props='{"label":"Remaining","value":"122"}'></div>
          </div>

          <!-- ── Schedule Grid ── -->
          <div class="schedule-grid">

            <!-- Card 1 -->
            <div class="schedule-card">
              <div class="schedule-card-header">
                <div class="schedule-date">
                  <span class="date-day">25</span>
                  <span class="date-month">April 2024</span>
                </div>
                <span class="schedule-status status-active">Active</span>
              </div>
              <div class="schedule-info">
                <div class="info-item">
                  <span class="info-label">Time Slot</span>
                  <span class="info-value">08:00 AM - 12:00 PM</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Catered</span>
                  <span class="info-value">150 Students</span>
                </div>
              </div>
              <div class="schedule-progress">
                <div class="progress-labels">
                  <span>Booking Progress</span>
                  <span>120 / 150</span>
                </div>
                <div class="progress-track">
                  <div class="progress-bar" style="width: 80%"></div>
                </div>
              </div>
              <div class="schedule-actions">
                <a href="manage-queue.php?id=1" class="btn-manage"
                  style="text-align: center; text-decoration: none;">Manage Queue</a>
              </div>
            </div>

            <!-- Card 2 -->
            <div class="schedule-card">
              <div class="schedule-card-header">
                <div class="schedule-date">
                  <span class="date-day">26</span>
                  <span class="date-month">April 2024</span>
                </div>
                <span class="schedule-status status-full">Almost Full</span>
              </div>
              <div class="schedule-info">
                <div class="info-item">
                  <span class="info-label">Time Slot</span>
                  <span class="info-value">09:00 AM - 04:00 PM</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Catered</span>
                  <span class="info-value">200 Students</span>
                </div>
              </div>
              <div class="schedule-progress">
                <div class="progress-labels">
                  <span>Booking Progress</span>
                  <span>195 / 200</span>
                </div>
                <div class="progress-track">
                  <div class="progress-bar" style="width: 97.5%; background: var(--warning);"></div>
                </div>
              </div>
              <div class="schedule-actions">
                <button class="btn-manage">Manage Queue</button>
              </div>
            </div>

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
      <form class="schedule-form">
        <div class="form-group">
          <label>Validation Date</label>
          <input type="date" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Start Time</label>
            <input type="time" required>
          </div>
          <div class="form-group">
            <label>End Time</label>
            <input type="time" required>
          </div>
        </div>
        <div class="form-group">
          <label>Max Students to Cater</label>
          <input type="number" placeholder="e.g. 100" required>
        </div>
        <button type="submit" class="btn-add-schedule" style="width: 100%; justify-content: center; margin-top: 10px;">
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

      // Handle Form Submit (Static Demo)
      $('.schedule-form').submit(function (e) {
        e.preventDefault();
        alert('Schedule created successfully! (Static demo)');
        modal.hide();
      });
    });
  </script>

</body>

</html>