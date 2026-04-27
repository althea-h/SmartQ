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

  <title>SmartQ | Student Management</title>
</head>

<body>

  <div class="admin-layout">
    <!-- Sidebar -->
    <div data-component="sidebar" data-props='{"active":"students"}'></div>

    <div class="admin-main">
      <!-- Topbar -->
      <div data-component="topbar"
        data-props='{"title":"Student Directory", "description":"Manage and validate student identities."}'></div>

      <main class="admin-content">
        <div class="students-container">

          <!-- ── Toolbar ── -->
          <div class="students-toolbar">
            <div class="search-wrapper">
              <span class="search-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
              </span>
              <input type="text" class="search-input" placeholder="Search by ID, First Name or Last Name...">
            </div>
            <div class="filter-group">
              <select class="filter-select" id="year-filter">
                <option value="">All Years</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
              </select>
              <select class="filter-select" id="college-filter">
                <option value="">All Colleges</option>
                <option value="COT">COT (Technology)</option>
                <option value="CAS">CAS (Arts & Sciences)</option>
                <option value="CON">CON (Nursing)</option>
                <option value="COB">COB (Business)</option>
                <option value="COE">COE (Education)</option>
                <option value="CPAG">CPAG (Public Admin)</option>
              </select>
              <select class="filter-select" id="status-filter">
                <option value="">All Status</option>
                <option value="validated">Validated</option>
                <option value="not validated">Not Validated</option>
                <option value="pending">Pending</option>
              </select>

            </div>
          </div>

          <!-- ── Table ── -->
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
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>

              <tbody>
                <?php
                require_once "../../../server/config/database.php";

                $database = new Database();
                $db = $database->getConnection();

                $sql = "SELECT s.*, c.college_name, vs.status_name 
                        FROM students s 
                        LEFT JOIN colleges c ON s.college_id = c.college_id 
                        LEFT JOIN validation_status vs ON s.status_id = vs.status_id";

                $stmt = $db->prepare($sql);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $status_class = strtolower(str_replace(' ', '-', $row['status_name']));
                    echo "<tr>";
                    echo "<td class='student-id-cell'>" . htmlspecialchars($row['student_id']) . "</td>";
                    echo "<td class='student-name-cell'>" . htmlspecialchars($row['first_name']) . "</td>";
                    echo "<td class='student-name-cell'>" . htmlspecialchars($row['last_name']) . "</td>";
                    echo "<td class='email-cell'>" . htmlspecialchars($row['email']) . "</td>";
                    $year_val = $row['yearlvl'] ?? 'N/A';
                    $year_labels = [
                      1 => '1st Year',
                      2 => '2nd Year',
                      3 => '3rd Year',
                      4 => '4th Year'
                    ];
                    $display_year = isset($year_labels[$year_val]) ? $year_labels[$year_val] : ($year_val != 'N/A' ? $year_val . 'th Year' : 'N/A');

                    echo "<td class='year-cell'><span class='year-badge-small'>" . htmlspecialchars($display_year) . "</span></td>";
                    echo "<td class='college-cell'><span class='college-badge-small'>" . htmlspecialchars($row['college_name'] ?? 'N/A') . "</span></td>";
                    echo "<td class='status-cell'><span class='status-badge badge-" . $status_class . "'>" . htmlspecialchars($row['status_name'] ?? 'Pending') . "</span></td>";
                    echo "<td>
                            <div class='action-buttons' style='justify-content: flex-end;'>";

                    if (trim(strtolower($row['status_name'])) === 'not validated') {
                      // Show Email button
                      echo "<button class='btn-action btn-email' title='Send Reminder Email'>
                              <svg width='14' height='14' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                                <path d='M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z'></path>
                                <polyline points='22,6 12,13 2,6'></polyline>
                              </svg>
                            </button>";
                    } else if (trim(strtolower($row['status_name'])) === 'pending') {
                      // Show Approve/Reject
                      echo "<button class='btn-action btn-approve' title='Approve Validation'>
                              <svg width='14' height='14' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                                <polyline points='20 6 9 17 4 12'></polyline>
                              </svg>
                            </button>
                            <button class='btn-action btn-reject' title='Reject Validation'>
                              <svg width='14' height='14' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                                <line x1='18' y1='6' x2='6' y2='18'></line>
                                <line x1='6' y1='6' x2='18' y2='18'></line>
                              </svg>
                            </button>";
                    } else {
                      // Just View for Validated
                      echo "<button class='btn-action' title='View Details'>
                              <svg width='14' height='14' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                                <path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path>
                                <circle cx='12' cy='12' r='3'></circle>
                              </svg>
                            </button>";
                    }

                    echo "  </div>
                          </td>";
                    echo "</tr>";
                  }
                } else {
                  echo "<tr><td colspan='8'>No students found</td></tr>";
                }
                ?>
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

    // Multi-Filter Logic
    $(document).ready(function () {
      function filterTable() {
        const searchText = $('.search-input').val().toLowerCase();
        const yearFilter = $('#year-filter').val().toLowerCase();
        const collegeFilter = $('#college-filter').val().toLowerCase();
        const statusFilter = $('#status-filter').val().toLowerCase();

        let visibleCount = 0;

        $(".students-table tbody tr:not(.no-results-row)").each(function () {
          const rowText = $(this).text().toLowerCase();
          const rowYear = $(this).find('.year-cell').text().toLowerCase().trim();
          const rowCollege = $(this).find('.college-cell').text().toLowerCase().trim();
          const rowStatus = $(this).find('.status-cell').text().toLowerCase().trim();

          const matchesSearch = rowText.indexOf(searchText) > -1;
          const matchesYear = yearFilter === "" || rowYear === yearFilter;
          const matchesCollege = collegeFilter === "" || rowCollege.indexOf(collegeFilter.toLowerCase()) > -1;
          const matchesStatus = statusFilter === "" || rowStatus === statusFilter;

          const isVisible = matchesSearch && matchesYear && matchesCollege && matchesStatus;
          $(this).toggle(isVisible);

          if (isVisible) visibleCount++;
        });

        // Show/Hide "No results" row
        if ($('.no-results-row').length === 0) {
          $('.students-table tbody').append('<tr class="no-results-row"><td colspan="8" style="text-align:center; padding: 40px; color: var(--text-muted);">No students matching your filters found.</td></tr>');
        }
        $('.no-results-row').toggle(visibleCount === 0);
      }

      $('.search-input').on('keyup', filterTable);
      $('.filter-select').on('change', filterTable);

      // Action Handlers
      $(document).on('click', '.btn-approve', function () {
        const id = $(this).closest('tr').find('.student-id-cell').text();
        alert('Approving validation for Student ID: ' + id);
      });

      $(document).on('click', '.btn-reject', function () {
        const id = $(this).closest('tr').find('.student-id-cell').text();
        alert('Rejecting validation for Student ID: ' + id);
      });

      $(document).on('click', '.btn-email', function () {
        const $btn = $(this);
        const id = $btn.closest('tr').find('.student-id-cell').text().trim();
        
        if ($btn.hasClass('loading')) return;
        
        $btn.addClass('loading').prop('disabled', true);
        const originalHtml = $btn.html();
        $btn.html('<span class="spinner-small"></span>');

        $.ajax({
          url: '../../../server/api/students/send_reminder.php',
          method: 'POST',
          data: { student_id: id },
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              alert('Success: ' + response.message);
            } else {
              alert('Error: ' + response.message);
            }
          },
          error: function () {
            alert('Failed to connect to the server.');
          },
          complete: function () {
            $btn.removeClass('loading').prop('disabled', false).html(originalHtml);
          }
        });
      });
    });
  </script>

</body>

</html>