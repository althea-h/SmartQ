<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: ../login.php');
  exit();
}

require_once '../../../server/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch Dynamic Stats from students table
// 1. Total Students
$total_students = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();

// 2. Pending (Status ID 3)
$pending_students = $db->query("SELECT COUNT(*) FROM students WHERE status_id = 3")->fetchColumn();

// 3. Validated (Status ID 1)
$validated = $db->query("SELECT COUNT(*) FROM students WHERE status_id = 1")->fetchColumn();

// 4. Not Validated (Status ID 2)
$not_validated = $db->query("SELECT COUNT(*) FROM students WHERE status_id = 2")->fetchColumn();
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

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        <!-- ROW 1: Stat Cards (Updated with Reports UI & Icons) -->
        <section class="dash-row dash-stats" id="dash-stats">
          <div class="stat-card">
            <div class="stat-card-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
            </div>
            <div class="stat-card-content">
              <div class="stat-card-label">Total Students</div>
              <div class="stat-card-value"><?= number_format($total_students) ?></div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-card-icon" style="background: rgba(234, 179, 8, 0.1); color: #ca8a04;">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
            </div>
            <div class="stat-card-content">
              <div class="stat-card-label">Pending Review</div>
              <div class="stat-card-value"><?= number_format($pending_students) ?></div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-card-icon" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </div>
            <div class="stat-card-content">
              <div class="stat-card-label">Validated</div>
              <div class="stat-card-value"><?= number_format($validated) ?></div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-card-icon" style="background: rgba(239, 68, 68, 0.1); color: #dc2626;">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </div>
            <div class="stat-card-content">
              <div class="stat-card-label">Not Validated</div>
              <div class="stat-card-value"><?= number_format($not_validated) ?></div>
            </div>
          </div>
        </section>

        <!-- ROW 2: Interactive Analytics (Replaced Colleges & Trend) -->
        <div class="dash-row dash-panels">
          <div class="chart-card">
            <div class="chart-header">
              <span class="chart-title-text">Validation by College</span>
            </div>
            <div class="chart-container">
              <canvas id="collegeChart"></canvas>
            </div>
          </div>

          <div class="chart-card">
            <div class="chart-header">
              <span class="chart-title-text">Overall Validation Status</span>
            </div>
            <div class="chart-container">
              <canvas id="statusChart"></canvas>
            </div>
          </div>
        </div>


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

    $(document).ready(function () {
      let collegeChart, statusChart;

      function loadDashboardCharts() {
        $.ajax({
          url: '../../../server/api/reports/get_report_data.php',
          method: 'GET',
          dataType: 'json',
          success: function (res) {
            if (res.success) {
              // ── College Distribution (Bar) ──
              const collegeCtx = document.getElementById('collegeChart').getContext('2d');
              
              // Color mapping for colleges
              const collegeColors = {
                'COT': '#ff7d04',
                'CON': '#ec57ee',
                'COB': '#fac800',
                'COE': '#1c5adf',
                'CPAG': '#23c7c7',
                'CAS': '#10b981',
              };

              const barColors = res.charts.college.labels.map(label => {
                const normalized = label.trim().toUpperCase();
                return collegeColors[normalized] || '#2563eb';
              });

              collegeChart = new Chart(collegeCtx, {
                type: 'bar',
                data: {
                  labels: res.charts.college.labels,
                  datasets: [{
                    label: 'Validated Students',
                    data: res.charts.college.data,
                    backgroundColor: barColors,
                    borderRadius: 6
                  }]
                },
                options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: { legend: { display: false } },
                  scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                  }
                }
              });

              // ── Overall Status (Doughnut) ──
              const statusCtx = document.getElementById('statusChart').getContext('2d');
              const total = res.charts.status.data.reduce((a, b) => a + b, 0);
              const validatedIndex = res.charts.status.labels.findIndex(l => l.trim().toLowerCase() === 'validated');
              const validatedCount = validatedIndex !== -1 ? res.charts.status.data[validatedIndex] : 0;
              const completionRate = total > 0 ? Math.round((validatedCount / total) * 100) : 0;

              const colorMap = {
                'validated': '#22c55e',
                'pending': '#eab308',
                'not validated': '#ef4444'
              };
              const statusColors = res.charts.status.labels.map(label => {
                const normalized = label.trim().toLowerCase();
                return colorMap[normalized] || '#94a3b8';
              });

              let activeLabel = "VALIDATED";
              let activeValue = completionRate;

              statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                  labels: res.charts.status.labels,
                  datasets: [{
                    data: res.charts.status.data,
                    backgroundColor: statusColors,
                    borderWidth: 0,
                    hoverOffset: 10
                  }]
                },
                options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  onClick: (evt, elements, chart) => {
                    if (elements.length > 0) {
                      const index = elements[0].index;
                      activeLabel = chart.data.labels[index].trim().toUpperCase();
                      const val = chart.data.datasets[0].data[index];
                      activeValue = Math.round((val / total) * 100);
                    } else {
                      activeLabel = "VALIDATED";
                      activeValue = completionRate;
                    }
                    chart.draw();
                  },
                  plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 20, font: { size: 11 } } },
                    tooltip: {
                      callbacks: {
                        label: function (context) {
                          const label = context.label || '';
                          const value = context.parsed || 0;
                          const percentage = Math.round((value / total) * 100);
                          return `${label}: ${value} (${percentage}%)`;
                        }
                      }
                    }
                  },
                  cutout: '75%',
                },
                plugins: [{
                  id: 'centerText',
                  beforeDraw: function (chart) {
                    const { ctx, chartArea: { top, bottom, left, right } } = chart;
                    ctx.save();
                    const centerX = (left + right) / 2;
                    const centerY = (top + bottom) / 2;
                    const fontSize = (chart.height / 160).toFixed(2);
                    ctx.font = `700 ${fontSize}em sans-serif`;
                    ctx.textBaseline = "middle";
                    ctx.textAlign = "center";
                    ctx.fillStyle = "#1e293b";
                    ctx.fillText(activeValue + "%", centerX, centerY + 10);
                    ctx.font = `600 0.75em sans-serif`;
                    ctx.fillStyle = "#64748b";
                    ctx.fillText(activeLabel, centerX, centerY - 15);
                    ctx.restore();
                  }
                }]
              });
            }
          }
        });
      }

      loadDashboardCharts();
    });
  </script>

</body>

</html>