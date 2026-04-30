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
											$sid = htmlspecialchars($row['student_id']);
											echo "<button class='btn-action btn-approve' title='Approve Validation' data-student-id='{$sid}'>
                              <svg width='14' height='14' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                                <polyline points='20 6 9 17 4 12'></polyline>
                              </svg>
                            </button>
                            <button class='btn-action btn-reject' title='Reject / Cancel Validation' data-student-id='{$sid}'>
                              <svg width='14' height='14' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                                <line x1='18' y1='6' x2='6' y2='18'></line>
                                <line x1='6' y1='6' x2='18' y2='18'></line>
                              </svg>
                            </button>";
										} else {
											$sid = htmlspecialchars($row['student_id']);
											echo "<button class='btn-action btn-view' title='View Details' data-student-id='{$sid}'>
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
			<p id="modal-desc" style="margin:0 0 28px; font-size:0.9rem; color:var(--text-muted,#666); line-height:1.55;"></p>
			<div style="display:flex; gap:12px; justify-content:center;">
				<button id="modal-cancel-btn"
					style="flex:1; padding:10px 0; border-radius:8px; border:1px solid var(--border,#ddd); background:transparent; color:var(--text-primary,#333); font-size:0.9rem; cursor:pointer;">Cancel</button>
				<button id="modal-confirm-btn"
					style="flex:1; padding:10px 0; border-radius:8px; border:none; color:#fff; font-size:0.9rem; font-weight:600; cursor:pointer;">Confirm</button>
			</div>
		</div>
	</div>

	<!-- ── Student Details Modal ── -->
	<div id="details-modal"
		style="display:none; position:fixed; inset:0; z-index:9998; align-items:center; justify-content:center;">
		<div id="details-backdrop"
			style="position:absolute; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px);"></div>
		<div id="details-card" style="
			position:relative; background:var(--surface,#fff); border-radius:16px;
			width:min(520px, 94vw);
			box-shadow:0 24px 80px rgba(0,0,0,0.28);
			display:flex; flex-direction:column;
		">
			<!-- Header bar -->
			<div
				style="background:linear-gradient(135deg,#2563eb 0%,#1e40af 100%); padding:28px 28px 24px; border-radius:16px 16px 0 0; position:relative;">
				<button id="details-close-btn" style="
					position:absolute; top:16px; right:16px;
					background:rgba(255,255,255,0.15); border:none; border-radius:50%;
					width:32px; height:32px; cursor:pointer; color:#fff;
					display:flex; align-items:center; justify-content:center;
				">
					<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>
				<div style="display:flex; align-items:center; gap:16px;">
					<div
						style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
						<svg width="26" height="26" fill="none" stroke="#fff" stroke-width="1.8" viewBox="0 0 24 24">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
							<circle cx="12" cy="7" r="4"></circle>
						</svg>
					</div>
					<div>
						<div id="details-name" style="font-size:1.2rem;font-weight:700;color:#fff;letter-spacing:0.01em;"></div>
						<div id="details-id" style="font-size:0.82rem;color:rgba(255,255,255,0.75);margin-top:3px;"></div>
					</div>
				</div>
				<!-- Validated badge in -->
				<div style="margin-top:16px;">
					<span id="validated-by-badge"
						style="display:inline-flex;align-items:center;gap:6px;background:rgba(34,197,94,0.2);border:1px solid rgba(34,197,94,0.4);color:#86efac;border-radius:20px;padding:4px 12px;font-size:0.78rem;font-weight:600;">
						<!-- Admin name will be inserted here -->
					</span>
				</div>
			</div>

			<!-- Body -->
			<div style="padding:24px 28px 28px; display:flex; flex-direction:column; gap:0;">

				<!-- Section: Student Info -->
				<p
					style="font-size:0.7rem;font-weight:700;color:var(--text-muted,#888);text-transform:uppercase;letter-spacing:0.08em;margin:0 0 12px;">
					Student Information</p>
				<div id="details-info-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
				</div>

				<hr style="border:none;border-top:1px solid var(--border,#f0f0f0);margin:0 0 20px;">

				<!-- Section: Validation Details -->
				<p
					style="font-size:0.7rem;font-weight:700;color:var(--text-muted,#888);text-transform:uppercase;letter-spacing:0.08em;margin:0 0 12px;">
					Validation Details</p>
				<div id="details-val-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;"></div>
			</div>

			<!-- Loading overlay -->
			<div id="details-loader"
				style="display:none;position:absolute;inset:0;background:var(--surface,#fff);border-radius:16px;align-items:center;justify-content:center;">
				<div
					style="width:36px;height:36px;border:3px solid #e2e8f0;border-top-color:#2563eb;border-radius:50%;animation:spin 0.7s linear infinite;">
				</div>
			</div>
		</div>
	</div>

	<style>
		@keyframes spin {
			to {
				transform: rotate(360deg);
			}
		}

		.detail-field {
			background: var(--surface-alt, #f8fafc);
			border: 1px solid var(--border, #f0f0f0);
			border-radius: 10px;
			padding: 10px 14px;
		}

		.detail-field-label {
			font-size: 0.7rem;
			font-weight: 600;
			color: var(--text-muted, #9ca3af);
			text-transform: uppercase;
			letter-spacing: 0.06em;
			margin-bottom: 4px;
		}

		.detail-field-value {
			font-size: 0.88rem;
			font-weight: 500;
			color: var(--text-primary, #1e293b);
			word-break: break-word;
		}
	</style>

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

			// ── Validation Status Modal ──────────────────────────────────────
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
				const id = $(this).data('student-id') || $row.find('.student-id-cell').text().trim();
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
				const id = $(this).data('student-id') || $row.find('.student-id-cell').text().trim();
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
							// Update the status badge in-place
							const isApproved = (action === 'approve');
							const newStatus = isApproved ? 'Validated' : 'Not Validated';
							const newClass = isApproved ? 'validated' : 'not-validated';
							const $statusBadge = $row.find('.status-badge');
							$statusBadge
								.removeClass()
								.addClass('status-badge badge-' + newClass)
								.text(newStatus);

							// Rebuild the action buttons for the new state
							const $actionDiv = $row.find('.action-buttons');
							if (isApproved) {
								// Validated → View only
								$actionDiv.html(
									`<button class="btn-action" title="View Details">
										<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
											<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
											<circle cx="12" cy="12" r="3"></circle>
										</svg>
									</button>`
								);
							} else {
								// Not Validated → Email reminder
								$actionDiv.html(
									`<button class="btn-action btn-email" title="Send Reminder Email">
										<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
											<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
											<polyline points="22,6 12,13 2,6"></polyline>
										</svg>
									</button>`
								);
							}

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

			// ── Student Details Modal ────────────────────────────────
			const $detailsModal = $('#details-modal');
			const $detailsLoader = $('#details-loader');

			function detailField(label, value) {
				return `<div class="detail-field">
					<div class="detail-field-label">${label}</div>
					<div class="detail-field-value">${value || '<span style="color:var(--text-muted,#aaa);">N/A</span>'}</div>
				</div>`;
			}

			function openDetailsModal(studentId) {
				$detailsModal.css('display', 'flex');
				$detailsLoader.css('display', 'flex');
				$('#details-name, #details-id').text('');
				$('#details-info-grid, #details-val-grid').empty();

				$.ajax({
					url: '../../../server/api/students/get_student_details.php',
					method: 'GET',
					data: { student_id: studentId },
					dataType: 'json',
					success: function (res) {
						if (!res.success) { alert('Error: ' + res.message); closeDetailsModal(); return; }
						const s = res.student;

						// Header
						$('#details-name').text(s.first_name + ' ' + s.last_name);
						$('#details-id').text('Student ID: ' + s.student_id);

						// Student info grid
						const yearLabels = { 1: '1st Year', 2: '2nd Year', 3: '3rd Year', 4: '4th Year' };
						$('#details-info-grid').html(
							detailField('First Name', s.first_name) +
							detailField('Last Name', s.last_name) +
							detailField('Email', `<a href="mailto:${s.email}" style="color:#2563eb;text-decoration:none;">${s.email}</a>`) +
							detailField('Year Level', s.year_display) +
							detailField('College', s.college_name)
						);

						// Validation details grid
						$('#details-val-grid').html(
							detailField('Status', `<span style="color:#22c55e;font-weight:700;">${s.status_name}</span>`) +
							detailField('Validated By', s.validated_by) +
							detailField('Date Validated', s.validated_at_formatted
								? `<span style="color:#2563eb;font-weight:500;">${s.validated_at_formatted}</span>`
								: null)
						);
						// Update admin name badge in header
						$('#validated-by-badge').text(s.validated_by ? `Validated By: ${s.validated_by}` : '').css('display', s.validated_by ? 'inline-flex' : 'none');
					},
					error: function () { alert('Could not load student details.'); closeDetailsModal(); },
					complete: function () { $detailsLoader.css('display', 'none'); }
				});
			}

			function closeDetailsModal() {
				$detailsModal.fadeOut(150);
			}

			$('#details-backdrop, #details-close-btn').on('click', closeDetailsModal);

			$(document).on('click', '.btn-view', function () {
				const id = $(this).data('student-id') || $(this).closest('tr').find('.student-id-cell').text().trim();
				openDetailsModal(id);
			});

		});
	</script>

</body>

</html>