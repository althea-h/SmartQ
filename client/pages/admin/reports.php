<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: ../login.php');
  exit();
}

require_once "../../../server/config/database.php";
$database = new Database();
$db = $database->getConnection();

// Quick Stats Data
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM students) as total,
    (SELECT COUNT(*) FROM students s JOIN validation_status vs ON s.status_id = vs.status_id WHERE vs.status_name = 'Validated') as validated,
    (SELECT COUNT(*) FROM students s JOIN validation_status vs ON s.status_id = vs.status_id WHERE vs.status_name = 'Pending') as pending,
    (SELECT COUNT(*) FROM students s JOIN validation_status vs ON s.status_id = vs.status_id WHERE vs.status_name = 'Not Validated') as not_validated";
$stats_stmt = $db->prepare($stats_sql);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch Colleges for Filter
$colleges_sql = "SELECT * FROM colleges ORDER BY college_name";
$colleges_stmt = $db->prepare($colleges_sql);
$colleges_stmt->execute();
$colleges = $colleges_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Validation Statuses for Filter
$statuses_sql = "SELECT * FROM validation_status";
$statuses_stmt = $db->prepare($statuses_sql);
$statuses_stmt->execute();
$statuses = $statuses_stmt->fetchAll(PDO::FETCH_ASSOC);
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
  <link rel="stylesheet" href="../../assets/css/admin/reports.css">
  <link rel="stylesheet" href="../../assets/css/admin/students.css">

  <title>SmartQ | Reports & Analytics</title>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

  <!-- =============================================
       ADMIN LAYOUT
       ============================================= -->
  <div class="admin-layout">

    <!-- ── Sidebar (loaded dynamically) ── -->
    <div data-component="sidebar" data-props='{"active":"reports"}'></div>

    <!-- ── Main Area ── -->
    <div class="admin-main">

      <!-- Topbar -->
      <div data-component="topbar"
        data-props='{"title":"Reports & Analytics", "description":"View student validation statistics and download reports."}'>
      </div>

      <!-- Page Content -->
      <main class="admin-content">
        <div class="reports-container">

          <!-- ── Stats Grid ── -->
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-header">
                <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                  </svg>
                </div>
              </div>
              <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
              <div class="stat-label">Total Students</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                </div>
              </div>
              <div class="stat-value"><?php echo number_format($stats['validated']); ?></div>
              <div class="stat-label">Validated</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <div class="stat-icon" style="background: rgba(234, 179, 8, 0.1); color: #ca8a04;">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                  </svg>
                </div>
              </div>
              <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
              <div class="stat-label">Pending Review</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #dc2626;">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </div>
              </div>
              <div class="stat-value"><?php echo number_format($stats['not_validated']); ?></div>
              <div class="stat-label">Not Validated</div>
            </div>
          </div>

          <!-- ── Filters ── -->
          <div class="reports-controls">
            <div class="controls-header">
              <h2 class="controls-title">Report Filters</h2>
            </div>
            <div class="filter-row">
              <div class="filter-item">
                <label class="filter-label">Year Level</label>
                <select id="year-filter" class="filter-select">
                  <option value="">All Year Levels</option>
                  <option value="1">1st Year</option>
                  <option value="2">2nd Year</option>
                  <option value="3">3rd Year</option>
                  <option value="4">4th Year</option>
                </select>
              </div>
              <div class="filter-item">
                <label class="filter-label">College</label>
                <select id="college-filter" class="filter-select">
                  <option value="">All Colleges</option>
                  <?php foreach ($colleges as $c): ?>
                    <option value="<?php echo $c['college_id']; ?>"><?php echo htmlspecialchars($c['college_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="filter-item">
                <label class="filter-label">Validation Status</label>
                <select id="status-filter" class="filter-select">
                  <option value="">All Statuses</option>
                  <?php foreach ($statuses as $s): ?>
                    <option value="<?php echo $s['status_id']; ?>"><?php echo htmlspecialchars($s['status_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="action-group">
              <button class="btn-download btn-primary" id="download-filtered">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"></path>
                  <polyline points="7 10 12 15 17 10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Download Filtered Report
              </button>
              <button class="btn-download btn-outline" id="download-college">
                Download Per College
              </button>
              <button class="btn-download btn-outline" id="download-year">
                Download Per Year
              </button>
              <button class="btn-download btn-outline" id="download-general">
                General Report
              </button>
            </div>
          </div>

          <!-- ── Visualizations ── -->
          <div class="reports-visuals">
            <div class="chart-card">
              <div class="chart-title">
                <span>Validation Distribution by College</span>
                <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">Real-time Data</span>
              </div>
              <div class="chart-container">
                <canvas id="collegeChart"></canvas>
              </div>
            </div>
            <div class="chart-card">
              <div class="chart-title">
                <span>Overall Status</span>
              </div>
              <div class="chart-container">
                <canvas id="statusChart"></canvas>
              </div>
            </div>
          </div>

          <!-- ── Preview Table ── -->
          <div class="report-preview" style="padding: 0; border: none; background: transparent; box-shadow: none;">
            <div class="chart-title" style="margin-bottom: 16px; padding: 0 4px;">
              <span>Data Preview (Top 50 Matching Records)</span>
            </div>
            <div class="students-table-container">
              <table class="students-table">
                <thead>
                  <tr>
                    <th>Student ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Year</th>
                    <th>College</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="preview-body">
                  <!-- Loaded via AJAX -->
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </main>

      <!-- Footer -->
      <div data-component="footer"></div>

    </div>
  </div>

  <!-- =============================================
       SCRIPTS
       ============================================= -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../../scripts/component-loader.js"></script>

  <script>
    SmartQ.onLoad('sidebar', function ($el) {
      $(document).on('click', '#sidebar-toggle', function () {
        $('#sidebar').toggleClass('open');
      });
    });

    $(document).ready(function () {
      let collegeChart, statusChart;

      function updateCharts() {
        const filters = {
          year: $('#year-filter').val(),
          college: $('#college-filter').val(),
          status: $('#status-filter').val()
        };

        $.ajax({
          url: '../../../server/api/reports/get_report_data.php',
          method: 'GET',
          data: filters,
          dataType: 'json',
          success: function (res) {
            if (res.success) {
              // Update Preview Table
              let html = '';
              if (res.preview.length > 0) {
                const collegeColors = {
                  'COT': { bg: '#fff7ed', text: '#ff7d04' },
                  'CON': { bg: '#fdf2f8', text: '#ec57ee' },
                  'COB': { bg: '#fffbeb', text: '#fac800' },
                  'COE': { bg: '#eff6ff', text: '#1c5adf' },
                  'CPAG': { bg: '#f0fdfa', text: '#23c7c7' },
                  'CAS': { bg: '#f0fdf4', text: '#10b981' },
                };

                res.preview.forEach(s => {
                  const colors = collegeColors[s.college_name] || { bg: '#f1f5f9', text: '#64748b' };
                  const statusClass = s.status_name.toLowerCase().replace(' ', '-');

                  html += `<tr>
                    <td class="student-id-cell">${s.student_id}</td>
                    <td class="student-name-cell">${s.first_name}</td>
                    <td class="student-name-cell">${s.last_name}</td>
                    <td class="email-cell">${s.email}</td>
                    <td><span class="year-badge-small">${s.year_display}</span></td>
                    <td>
                      <span class="college-badge-small" style="background:${colors.bg}; color:${colors.text}; border-color:${colors.text}20;">
                        ${s.college_name}
                      </span>
                    </td>
                    <td><span class="status-badge badge-${statusClass}">${s.status_name}</span></td>
                  </tr>`;
                });
              } else {
                html = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">No records found matching your filters</td></tr>';
              }
              $('#preview-body').html(html);

              // Update College Chart (Bar)
              const collegeCtx = document.getElementById('collegeChart').getContext('2d');
              if (collegeChart) collegeChart.destroy();

              collegeChart = new Chart(collegeCtx, {
                type: 'bar',
                data: {
                  labels: res.charts.college.labels,
                  datasets: [{
                    label: 'Validated Students',
                    data: res.charts.college.data,
                    backgroundColor: '#2563eb',
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

              // Update Status Chart (Doughnut)
              const statusCtx = document.getElementById('statusChart').getContext('2d');
              if (statusChart) statusChart.destroy();

              // Calculate overall validation % for center text
              const total = res.charts.status.data.reduce((a, b) => a + b, 0);
              const validatedIndex = res.charts.status.labels.findIndex(l => l.trim().toLowerCase() === 'validated');
              const validatedCount = validatedIndex !== -1 ? res.charts.status.data[validatedIndex] : 0;
              const completionRate = total > 0 ? Math.round((validatedCount / total) * 100) : 0;

              // Map colors to labels (Case-insensitive & trimmed)
              const colorMap = {
                'validated': '#22c55e',     // Green
                'pending': '#eab308',       // Yellow
                'not validated': '#ef4444'  // Red
              };
              const statusColors = res.charts.status.labels.map(label => {
                const normalized = label.trim().toLowerCase();
                return colorMap[normalized] || '#94a3b8'; // Fallback to gray if no match
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

                    // Draw Percentage
                    const fontSize = (chart.height / 160).toFixed(2);
                    ctx.font = `700 ${fontSize}em sans-serif`;
                    ctx.textBaseline = "middle";
                    ctx.textAlign = "center";
                    ctx.fillStyle = "#1e293b";
                    ctx.fillText(activeValue + "%", centerX, centerY + 10);

                    // Draw Label
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

      // Initial Load
      updateCharts();

      // Filter changes
      $('.filter-select').on('change', updateCharts);

      // Download Actions
      $('#download-filtered').on('click', function () {
        const url = '../../../server/api/reports/download.php?type=filtered' +
          '&year=' + $('#year-filter').val() +
          '&college=' + $('#college-filter').val() +
          '&status=' + $('#status-filter').val();
        window.location.href = url;
      });

      $('#download-college').on('click', function () {
        window.location.href = '../../../server/api/reports/download.php?type=college';
      });

      $('#download-year').on('click', function () {
        window.location.href = '../../../server/api/reports/download.php?type=year';
      });

      $('#download-general').on('click', function () {
        window.location.href = '../../../server/api/reports/download.php?type=general_percent';
      });
    });
  </script>

</body>

</html>