<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: ../login.php');
  exit();
}

require_once '../../../server/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch Real Stats
// 1. Total Students
$stmt = $db->query("SELECT COUNT(*) FROM students");
$total_students = $stmt->fetchColumn();

// 2. Currently Queueing (Total entries in queue_list)
$stmt = $db->query("SELECT COUNT(*) FROM queue_list");
$queueing = $stmt->fetchColumn();

// 3. Validated Students
$stmt = $db->query("SELECT COUNT(*) FROM students s JOIN validation_status vs ON s.status_id = vs.status_id WHERE vs.status_name = 'Validated'");
$validated = $stmt->fetchColumn();

// 4. Not Validated Students
$stmt = $db->query("SELECT COUNT(*) FROM students s JOIN validation_status vs ON s.status_id = vs.status_id WHERE vs.status_name = 'Not Validated'");
$not_validated = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SmartQ Admin Dashboard - Student Queue Management">
  <link rel="icon" type="image/png" href="../../assets/logo/sq.png">

  <!-- Component loader base -->
  <meta name="component-base" content="../../components/">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="../../assets/css/main.css">
  <link rel="stylesheet" href="../../assets/css/components/components.css">
  <link rel="stylesheet" href="../../assets/css/admin/dashboard.css">

  <title>SmartQ | Admin Dashboard</title>
</head>

<body>
  <div class="admin-layout">

    <!-- Sidebar Navigation -->
    <div data-component="sidebar" data-props='{"active":"dashboard"}'></div>

    <!-- Main Content Area -->
    <div class="admin-main">

      <!-- Page Header -->
      <div data-component="topbar" data-props='{"title":"Dashboard","description":"Welcome back, Admin!"}'></div>

      <!-- ═══════════════════════════════════════════════════════
           DASHBOARD CONTENT
           ═══════════════════════════════════════════════════════ -->
      <main class="admin-content dash">

        <!-- ROW 1: Stat Cards (horizontal strip) -->
        <section class="dash-row dash-stats" id="dash-stats">
          <div data-component="stat-card"
            data-props='{"icon":"students.svg","label":"Total Students","value":"<?= $total_students ?>"}'></div>
          <div data-component="stat-card" 
            data-props='{"icon":"queue.svg","label":"Queueing","value":"<?= $queueing ?>"}'>
          </div>
          <div data-component="stat-card" 
            data-props='{"icon":"dashboard.svg","label":"Validated","value":"<?= $validated ?>"}'>
          </div>
          <div data-component="stat-card"
            data-props='{"icon":"reports.svg","label":"Not Validated","value":"<?= $not_validated ?>"}'>
          </div>
        </section>

        <!-- ROW 2: Graph + Colleges (side by side) -->
        <div class="dash-row dash-panels">
          <section class="dash-colleges" id="dash-colleges">
            <div data-component="colleges"></div>
          </section>
          <section class="dash-graph" id="dash-graph">
            <div data-component="graph"></div>
          </section>

        </div>

        <!-- ROW 3: Recent Activity (full width) -->
        <section class="dash-row dash-activity" id="dash-activity">
          <div data-component="recent-activity"></div>
        </section>

      </main>

    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="../../scripts/component-loader.js"></script>

  <script>
    SmartQ.onLoad('sidebar', function ($el) {
      $(document).on('click', '#sidebar-toggle', function () {
        $('#sidebar').toggleClass('open');
      });
    });
  </script>

</body>

</html>