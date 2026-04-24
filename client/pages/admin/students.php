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
              <span class="search-icon">Serts</span>
              <input type="text" class="search-input" placeholder="Search by ID, First Name or Last Name...">
            </div>
            <div class="filter-group">
              <select class="filter-select" id="college-filter">
                <option value="">All Colleges</option>
                <option value="COT">COT</option>
                <option value="CAS">CAS</option>
                <option value="CON">CON</option>
                <option value="COB">COB</option>
                <option value="COE">COE</option>
                <option value="CPAG">CPAG</option>
              </select>
              <select class="filter-select" id="status-filter">
                <option value="">All Status</option>
                <option value="validated">Validated</option>
                <option value="not-validated">Not Validated</option>
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
                  <th>College</th>
                  <th>Status</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Student 1 -->
                <tr>
                  <td class="student-id-cell">20210001234</td>
                  <td class="student-name-cell">John Michael</td>
                  <td class="student-name-cell">Doe</td>
                  <td>john.doe@university.edu</td>
                  <td><span class="college-badge-small">COT</span></td>
                  <td>
                    <span class="status-badge badge-validated">Validated</span>
                  </td>
                  <td>
                    <div class="action-buttons" style="justify-content: flex-end;">
                      <button class="btn-action" title="Edit Profile"></button>
                      <button class="btn-action" title="View Details"></button>
                    </div>
                  </td>
                </tr>

                <!-- Student 2 -->
                <tr>
                  <td class="student-id-cell">20220005678</td>
                  <td class="student-name-cell">Jane</td>
                  <td class="student-name-cell">Smith</td>
                  <td>jane.smith@university.edu</td>
                  <td><span class="college-badge-small">CAS</span></td>
                  <td>
                    <span class="status-badge badge-pending">Pending</span>
                  </td>
                  <td>
                    <div class="action-buttons" style="justify-content: flex-end;">
                      <button class="btn-action btn-validate" title="Approve Validation"></button>
                      <button class="btn-action" title="Edit Profile"></button>
                    </div>
                  </td>
                </tr>

                <!-- Student 3 -->
                <tr>
                  <td class="student-id-cell">20200009999</td>
                  <td class="student-name-cell">Alexander</td>
                  <td class="student-name-cell">Hamilton</td>
                  <td>a.hamilton@university.edu</td>
                  <td><span class="college-badge-small">COB</span></td>
                  <td>
                    <span class="status-badge badge-not-validated">Not Validated</span>
                  </td>
                  <td>
                    <div class="action-buttons" style="justify-content: flex-end;">
                      <button class="btn-action" title="Send Reminder"></button>
                      <button class="btn-action" title="Edit Profile"></button>
                    </div>
                  </td>
                </tr>

                <!-- Student 4 -->
                <tr>
                  <td class="student-id-cell">20230004321</td>
                  <td class="student-name-cell">Maria Clara</td>
                  <td class="student-name-cell">De la Cruz</td>
                  <td>m.delacruz@university.edu</td>
                  <td><span class="college-badge-small">CON</span></td>
                  <td>
                    <span class="status-badge badge-validated">Validated</span>
                  </td>
                  <td>
                    <div class="action-buttons" style="justify-content: flex-end;">
                      <button class="btn-action" title="Edit Profile"></button>
                      <button class="btn-action" title="View Details"></button>
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

    // Multi-Filter Logic
    $(document).ready(function () {
      function filterTable() {
        const searchText = $('.search-input').val().toLowerCase();
        const collegeFilter = $('#college-filter').val().toLowerCase();
        const statusFilter = $('#status-filter').val().toLowerCase();

        $(".students-table tbody tr").each(function () {
          const rowText = $(this).text().toLowerCase();
          const rowCollege = $(this).find('td:nth-child(5)').text().toLowerCase();
          const rowStatus = $(this).find('td:nth-child(6)').text().toLowerCase();

          const matchesSearch = rowText.indexOf(searchText) > -1;
          const matchesCollege = collegeFilter === "" || rowCollege.indexOf(collegeFilter) > -1;
          const matchesStatus = statusFilter === "" || rowStatus.indexOf(statusFilter) > -1;

          $(this).toggle(matchesSearch && matchesCollege && matchesStatus);
        });
      }

      $('.search-input').on('keyup', filterTable);
      $('.filter-select').on('change', filterTable);
    });
  </script>

</body>

</html>