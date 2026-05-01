<?php
session_start();
if (!isset($_SESSION['student'])) {
  header('Location: ../login.php');
  exit();
}
require_once "../../../server/config/database.php";
$database = new Database();
$db = $database->getConnection();

$user = $_SESSION['student'];

// Fetch colleges for the dropdown
$college_query = "SELECT * FROM colleges ORDER BY college_name ASC";
$college_stmt = $db->prepare($college_query);
$college_stmt->execute();
$colleges = $college_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../../assets/logo/sq.png">
  <meta name="component-base" content="../../components/">
  <link rel="stylesheet" href="../../assets/css/main.css">
  <link rel="stylesheet" href="../../assets/css/components/components.css">
  <link rel="stylesheet" href="../../assets/css/users/student-dashboard.css">
  <title>SmartQ | My Profile</title>
  <style>
    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #475569;
      font-size: 0.9rem;
    }

    .form-control {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.2s;
      background: #f8fafc;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--student-primary);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
      background: white;
    }

    .btn-save {
      background: var(--student-primary);
      color: white;
      border: none;
      padding: 14px 24px;
      border-radius: 12px;
      font-weight: 700;
      cursor: pointer;
      width: 100%;
      transition: 0.2s;
      margin-top: 10px;
    }

    .btn-save:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }

    .profile-header {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid #f1f5f9;
    }

    .profile-avatar {
      width: 80px;
      height: 80px;
      background: #eff6ff;
      color: var(--student-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 20px;
      font-size: 2rem;
      font-weight: 800;
    }

    .section-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .section-title svg {
      color: var(--student-primary);
    }
  </style>
</head>

<body>
  <div class="admin-layout">
    <div data-component="sidebar" data-props='{"active":"profile", "role":"student"}'></div>
    <div class="admin-main">
      <div data-component="topbar"
        data-props='{"title":"My Profile", "description":"Manage your personal information."}'></div>
      <main class="admin-content">
        <div class="student-container">
          <div class="student-card">
            <div class="profile-header">
              <div class="profile-avatar">
                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
              </div>
              <div>
                <h2 style="margin:0; font-size: 1.5rem; color: #1e293b;">
                  <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                </h2>
                <p style="margin: 5px 0 0; color: #64748b; font-weight: 500;">
                  ID: <?php echo htmlspecialchars($user['student_id']); ?>
                </p>
              </div>
            </div>

            <form id="profileForm">
              <div class="section-title">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Personal Information
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                  <label>First Name</label>
                  <input type="text" name="first_name" class="form-control"
                    value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Last Name</label>
                  <input type="text" name="last_name" class="form-control"
                    value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
              </div>

              <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control"
                  value="<?php echo htmlspecialchars($user['email']); ?>" required>
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                  <label>Year Level</label>
                  <select name="yearlvl" class="form-control">
                    <option value="1" <?php echo ($user['yearlvl'] == 1) ? 'selected' : ''; ?>>1st Year</option>
                    <option value="2" <?php echo ($user['yearlvl'] == 2) ? 'selected' : ''; ?>>2nd Year</option>
                    <option value="3" <?php echo ($user['yearlvl'] == 3) ? 'selected' : ''; ?>>3rd Year</option>
                    <option value="4" <?php echo ($user['yearlvl'] == 4) ? 'selected' : ''; ?>>4th Year</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>College</label>
                  <select name="college_id" class="form-control">
                    <?php foreach ($colleges as $college): ?>
                      <option value="<?php echo $college['college_id']; ?>" <?php echo ($user['college_id'] == $college['college_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($college['college_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="section-title" style="margin-top: 20px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Change Password (Optional)
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                  <label>New Password</label>
                  <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current">
                </div>
                <div class="form-group">
                  <label>Confirm New Password</label>
                  <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your new password">
                </div>
              </div>

              <button type="submit" class="btn-save">Save Changes</button>
            </form>
          </div>
        </div>
      </main>
      <div data-component="footer"></div>
    </div>
  </div>

  <nav class="mobile-nav">
    <a href="student-dashboard.php" class="mobile-nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path
          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
        </path>
      </svg>
      <span>Dashboard</span>
    </a>
    <a href="book-queue.php" class="mobile-nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
      </svg>
      <span>Booking</span>
    </a>
    <a href="profile.php" class="mobile-nav-item active">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
      </svg>
      <span>Profile</span>
    </a>
  </nav>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../../scripts/component-loader.js"></script>
  <script>
    $(document).ready(function () {
      $('#profileForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const $btn = $('.btn-save');

        $btn.prop('disabled', true).text('Updating...');

        $.ajax({
          url: '../../../server/api/students/update_profile.php',
          type: 'POST',
          data: formData,
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              alert('Profile updated successfully!');
              location.reload();
            } else {
              alert('Error: ' + response.message);
              $btn.prop('disabled', false).text('Save Changes');
            }
          },
          error: function () {
            alert('An error occurred while updating the profile.');
            $btn.prop('disabled', false).text('Save Changes');
          }
        });
      });
    });
  </script>
</body>

</html>