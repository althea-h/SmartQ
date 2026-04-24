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

  <title>SQ | Queue</title>
</head>

<body>

  <!-- =============================================
       ADMIN LAYOUT
       ============================================= -->
  <div class="admin-layout">

    <!-- ── Sidebar (loaded dynamically) ── -->
    <div data-component="sidebar" data-props='{"active":"students"}'>
    </div>

    <!-- ── Main Area ── -->
    <div class="admin-main">

      <!-- Topbar -->
      <div data-component="topbar" data-props='{"title":"Students", "description":"Manage student validation"}'></div>

      <!-- Page Content -->
      <main class="admin-content">



      </main>

      <!-- Footer -->
      <div data-component="footer"></div>

    </div>
  </div>

  <!-- =============================================
       SCRIPTS
       ============================================= -->
  <!-- jQuery 3.7.1 (CDN) — required by component-loader.js -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

  <!-- SmartQ Component Loader -->
  <script src="../../scripts/component-loader.js"></script>

  <!-- Page-specific scripts -->
  <script>
    // Example: run code AFTER the sidebar finishes loading
    SmartQ.onLoad('sidebar', function ($el) {
      // Mobile sidebar toggle
      $(document).on('click', '#sidebar-toggle', function () {
        $('#sidebar').toggleClass('open');
      });
    });
  </script>

</body>

</html>