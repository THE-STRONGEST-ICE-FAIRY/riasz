<?php
require '../../utilities/database/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
	<!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* CSS Variables for easy maintenance */
        :root {
            --primary: #2563eb;
            --primary-light: #eff6ff;
            --bg-gray: #f9fafb;
            --border-gray: #e5e7eb;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --white: #ffffff;
            
            /* User Requested Colors */
            --header-bg: #fbaf41;
            --sidebar-bg: #343a40;
            --footer-bg: #213b9a;
            
            --sidebar-width-collapsed: 80px;
            --sidebar-width-expanded: 256px;
            --header-height: 64px;
            --footer-height: 48px;
            --transition-speed: 0.3s;
            --transition-curve: cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-gray);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--header-bg);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            color: var(--white);
        }

        .logo-container {
			display: flex;
			align-items: left;
			gap: 8px;
			display: inline-block; /* important for scaling */
		}

		.logo-container img {
			width: 250px;      /* adjust size */
			height: auto;
			transition: transform 0.2s ease-in-out; /* smooth animation */
			transform-origin: left center; /* 👈 key line */
		}

		.logo-container:hover img {
			transform: scale(1.1); /* grow on hover */
		}

        .logo-img {
            height: 32px;
            width: 32px;
            border-radius: 4px;
            object-fit: cover;
            background-color: rgba(255,255,255,0.5);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
s
        .admin-label {
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-divider {
            width: 1px;
            height: 24px;
            background-color: rgba(255, 255, 255, 0.4);
            margin: 0 4px;
        }

        .notification-btn {
            position: relative;
            padding: 8px;
            color: var(--white);
            background: none;
            border: none;
            cursor: pointer;
            border-radius: 9999px;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-btn:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .profile-container {
            position: relative;
            display: inline-block;
            padding-bottom: 12px; 
            margin-bottom: -12px;
        }

        .profile-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--white);
            border: none;
            flex-shrink: 0;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .profile-circle:active {
            transform: scale(0.95);
        }

        .profile-panel {
            position: absolute;
            top: 48px;
            right: 0;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 8px;
            min-width: 140px;
            z-index: 100;
            display: none;
            border: 1px solid var(--border-gray);
        }

        .profile-panel.show {
            display: block;
        }

        .logout-btn {
            width: 100%;
            text-align: left;
            padding: 10px 16px;
            background: none;
            border: none;
            border-radius: 6px;
            color: #ef4444;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background-color: #fef2f2;
        }

        /* Sidebar Styles */
        aside {
            position: fixed;
            left: 0;
            top: var(--header-height);
            bottom: var(--footer-height);
            background: var(--sidebar-bg);
            z-index: 40;
            transition: width var(--transition-speed) var(--transition-curve);
            overflow: hidden;
            border-top-right-radius: 24px;
            border-bottom-right-radius: 24px;
            color: #e9ecef;
        }

        .sidebar-collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar-expanded {
            width: var(--sidebar-width-expanded);
        }

        .toggle-container {
            padding: 16px 28px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-toggle-btn {
            padding: 4px;
            background: none;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.2s;
            color: #ced4da;
        }

        .sidebar-toggle-btn:hover {
            background-color: rgba(255,255,255,0.1);
        }

        nav {
            flex: 1;
            margin-top: 16px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 12px 28px;
            background: none;
            border: none;
            cursor: pointer;
            color: #adb5bd;
            transition: all 0.2s;
            text-align: left;
        }

        .sidebar-item:hover {
            background-color: rgba(255,255,255,0.05);
            color: var(--white);
        }

        .sidebar-item.nav-active {
            background-color: rgba(255,255,255,0.1);
            color: var(--header-bg);
            border-right: 4px solid var(--header-bg);
        }

        .icon-container {
            width: 24px;
            display: flex;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-text {
            margin-left: 16px;
            font-weight: 500;
            white-space: nowrap;
            transition: opacity 0.2s ease-in-out;
        }

        .sidebar-collapsed .sidebar-text {
            opacity: 0;
            pointer-events: none;
        }

        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 16px 0;
        }

        /* Main Content Styles */
        main {
            flex: 1;
            padding: calc(var(--header-height) + 24px) 24px var(--footer-height);
            transition: transform var(--transition-speed) var(--transition-curve);
        }

        @media (min-width: 768px) {
            main {
                margin-left: var(--sidebar-width-collapsed);
            }
        }

        @media (max-width: 767px) {
            .sidebar-collapsed {
                width: 0;
            }
        }

        /* Dashboard Content Sections */
        section {
            display: none;
            min-height: 60vh;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        section.active {
            display: block;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        /* Masterlist Table Styles */
        .table-container {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th {
            background-color: #f8fafc;
            padding: 12px 24px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-gray);
        }

        .data-table td {
            padding: 16px 24px;
            font-size: 14px;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-gray);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background-color: #f1f5f9;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-active { background-color: #dcfce7; color: #166534; }
        .badge-archived { background-color: #fef3c7; color: #92400e; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 24px;
        }

        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .stat-card {
            background: var(--white);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            border: 1px solid var(--border-gray);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        .stat-value {
            font-size: 30px;
            font-weight: 700;
            margin-top: 4px;
        }

        .stat-trend {
            font-size: 14px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .trend-up { color: #10b981; }
        .trend-blue { color: var(--primary); }

        .progress-bar-bg {
            width: 100%;
            background-color: var(--bg-gray);
            height: 8px;
            border-radius: 9999px;
            margin-top: 16px;
        }

        .progress-bar-fill {
            background-color: var(--footer-bg);
            height: 100%;
            border-radius: 9999px;
        }

        /* Footer Styles */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--footer-height);
            background: var(--footer-bg);
            border-top: 1px solid rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            font-size: 11px;
            color: rgba(255,255,255,0.8);
            z-index: 50;
        }

        .footer-left {
            display: flex;
            align-items: center;
        }

        .footer-link-apc {
            color: var(--white);
            text-decoration: underline;
            font-weight: 600;
            margin: 0 4px;
        }

        .footer-right {
            display: flex;
            gap: 16px;
        }

        .footer-nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        .footer-nav-link:hover {
            opacity: 0.7;
        }
		
		    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
		}

		.btn {
			display: inline-flex;
			align-items: center;
			padding: 8px 16px;
			border-radius: 6px;
			font-size: 14px;
			font-weight: 500;
			cursor: pointer;
			transition: all 0.2s;
			border: none;
			text-decoration: none;
			gap: 8px;
		}

		.btn-primary { background: var(--primary); color: white; }
		.btn-primary:hover { background: var(--primary-hover); }

		.btn-outline { background: white; border: 1px solid var(--border); color: var(--text-main); }
		.btn-outline:hover { background: var(--bg-subtle); }

		.btn-sm { padding: 4px 8px; font-size: 12px; }
		
		.btn-danger-text { color: var(--danger); background: transparent; }
		.btn-danger-text:hover { background: #fef2f2; }

		/* Schools & Programs Visualization */
		.schools-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
			gap: 20px;
			margin-bottom: 40px;
		}

		.school-card {
			background: white;
			border: 1px solid var(--border);
			border-radius: 12px;
			padding: 20px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.05);
		}

		.school-title {
			font-size: 18px;
			font-weight: 700;
			color: var(--text-main);
			margin-bottom: 12px;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.program-list {
			list-style: none;
			padding: 0;
			margin: 0;
			border-top: 1px solid var(--border);
			padding-top: 12px;
		}

		.program-item {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 8px 0;
			font-size: 14px;
			color: var(--text-muted);
		}

		.program-item:not(:last-child) {
			border-bottom: 1px dashed var(--border);
		}

		.empty-state {
			font-style: italic;
			color: #9ca3af;
			padding: 10px 0;
		}
		
		/* new */
		.error-cell {
		  border: 2px solid red !important;
		  background-color: #ffe5e5;
		}

		.error-row {
		  background-color: #fff0f0;
		}
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="logo-container">
            <a href="../login/login.php" class="logo-container">
				<img src="../../assets/header_apclogo2.png" alt="Logo">
			</a>
        </div>

        <div class="header-actions">
            <span class="admin-label"></span>
            <div class="header-divider"></div>
            <button class="notification-btn">
                <i data-lucide="bell" style="width: 20px; height: 20px;"></i>
            </button>
            <div class="profile-container" id="profileMenu">
                <div class="profile-circle" id="profileTrigger"></div>
                <div class="profile-panel" id="profilePanel">
                    <button class="logout-btn" onclick="handleLogout()">
                        <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                        log out
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
	<aside id="sidebar" class="sidebar-collapsed">
		<div style="display: flex; flex-direction: column; height: 100%;">
			<div class="toggle-container">
				<button id="sidebarToggle" class="sidebar-toggle-btn">
					<i data-lucide="menu" style="width: 24px; height: 24px;"></i>
				</button>
			</div>

			<nav id="mainNav">
				<button data-page="dashboard" class="sidebar-item nav-active">
					<div class="icon-container"><i data-lucide="layout-dashboard" style="width: 20px; height: 20px;"></i></div>
					<span class="sidebar-text">Dashboard</span>
				</button>

				<button data-page="masterlist" class="sidebar-item">
					<div class="icon-container"><i data-lucide="users" style="width: 20px; height: 20px;"></i></div>
					<span class="sidebar-text">Masterlist</span>
				</button>

				<!-- CHANGED: Database → Rubrics -->
				<button data-page="rubrics" class="sidebar-item">
					<div class="icon-container"><i data-lucide="clipboard-list" style="width: 20px; height: 20px;"></i></div>
					<span class="sidebar-text">Rubrics</span>
				</button>

				<!-- CHANGED: Log Activity → Reports -->
				<button data-page="reports" class="sidebar-item">
					<div class="icon-container"><i data-lucide="bar-chart-2" style="width: 20px; height: 20px;"></i></div>
					<span class="sidebar-text">Reports</span>
				</button>
			</nav>

			<div class="sidebar-footer">
				<button class="sidebar-item" style="color: #6c757d;">
					<div class="icon-container"><i data-lucide="help-circle" style="width: 20px; height: 20px;"></i></div>
					<span class="sidebar-text" style="font-size: 13px;">Support</span>
				</button>
			</div>
		</div>
	</aside>

    <!-- Main Content -->
    <main id="mainContent">
        
        <!-- Dashboard Section -->
        <section id="dashboard" class="active">
            <h1>Dashboard Overview</h1>
			
            <div class="stats-grid">
                <div class="stat-card">
                    <p class="stat-label">Total Revenue</p>
                    <h3 class="stat-value">$54,230</h3>
                    <p class="stat-trend trend-up">
                        <i data-lucide="trending-up" style="width: 16px; height: 16px;"></i> +12.5% vs last month
                    </p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Active Users</p>
                    <h3 class="stat-value">1,284</h3>
                    <p class="stat-trend trend-blue">
                        <i data-lucide="user-check" style="width: 16px; height: 16px;"></i> 48 currently online
                    </p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Project Completion</p>
                    <h3 class="stat-value">87%</h3>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: 87%"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Masterlist Section -->
        <section id="masterlist">
            <h1>Masterlist Section</h1>
			
			<form id="internForm" onsubmit="return submitInterns(event)">

			  <div style="overflow-x:auto;">
				<table border="1" cellpadding="4" cellspacing="0" style="min-width: 1400px;">
				  <thead>
					<tr>
						<th>#</th>
					  <th>1. First Name *</th>
					  <th>2. Last Name *</th>
					  <th>3. Email *</th>
					  <th>4. School User Given ID *</th>
					  <th>5. School Name *</th>
					  <th>6. Program Name *</th>
					  <th>7. Birthdate</th>
					  <th>8. Gender</th>
					  <th>9. City</th>
					  <th>10. Province/State</th>
					  <th>11. Postal Code</th>
					  <th>12. Country</th>
					</tr>
				  </thead>
				  <tbody id="internTableBody">
				  </tbody>
				</table>
			  </div>

			  <div id="formErrors"></div>

			  <button type="submit">Submit</button>
			  
			  <button type="button" onclick="openCsvModal()">Edit CSV</button>
			  
			  <div id="csvModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
				background:rgba(0,0,0,0.5);">

				  <div style="background:#fff; width:600px; margin:10% auto; padding:20px;">
					
					<h3>CSV Editor</h3>

					<textarea id="csvText" style="width:100%; height:300px;"></textarea>

					<br><br>
					
					<h4>Import File</h4>

					<input type="file" id="csvFileInput" accept=".csv,.xlsx,.xls" />

					<label>
					  <input type="checkbox" id="csvHasHeaders">
					  File has headers
					</label>

					<br><br>

					<button type="button" onclick="importCsvFile()">Import File</button>

					<hr>

					<button type="button" onclick="closeCsvModal()">Cancel Changes</button>
					<button type="button" onclick="applyCsvChanges()">Apply Changes</button>

				  </div>
				</div>
			  
			  <script> // codes for add interns only
			  	  
			  function isRowEmpty(row) {
				  const inputs = row.querySelectorAll("input");
				  for (let input of inputs) {
					if (input.value.trim() !== "") return false;
				  }
				  return true;
				}
				
				function cleanEmptyRows() {
				  const tbody = document.getElementById("internTableBody");
				  const rows = Array.from(tbody.querySelectorAll("tr"));

				  let emptyRows = [];

				  rows.forEach(row => {
					if (isRowEmpty(row)) {
					  emptyRows.push(row);
					}
				  });

				  // Keep ONLY the last empty row
				  emptyRows.forEach((row, index) => {
					if (index !== emptyRows.length - 1) {
					  row.remove();
					}
				  });
				}
				
				function createRow() {
				  const tr = document.createElement("tr");

				  tr.innerHTML = `
					<td class="row-number"></td>
					<td><input name="first_name" type="text"></td>
					<td><input name="last_name" type="text"></td>
					<td><input name="email" type="email"></td>
					<td><input name="school_user_id" type="text"></td>
					<td><input name="school_name" type="text"></td>
					<td><input name="program_name" type="text"></td>
					<td><input name="birthdate" type="date"></td>
					<td>
					  <select name="gender">
						<option value=""></option>
						<option value="Male">Male</option>
						<option value="Female">Female</option>
					  </select>
					</td>
					<td><input name="city" type="text"></td>
					<td><input name="province" type="text"></td>
					<td><input name="postal" type="text"></td>
					<td><input name="country" type="text"></td>
					<td>
					  <button type="button" class="delete-row">Delete</button>
					</td>
				  `;

				  attachRowListeners(tr);
				  attachDeleteHandler(tr);

				  return tr;
				}
				
				function updateRowNumbers() {
				  const rows = document.querySelectorAll("#internTableBody tr");

				  rows.forEach((row, index) => {
					const cell = row.querySelector(".row-number");
					if (cell) {
					  cell.textContent = index + 1;
					}
				  });
				}
				
				function attachDeleteHandler(row) {
				  const btn = row.querySelector(".delete-row");

				  btn.addEventListener("click", () => {
					const tbody = document.getElementById("internTableBody");

					row.remove();

					// ALWAYS ensure at least one row exists
					if (tbody.querySelectorAll("tr").length === 0) {
					  tbody.appendChild(createRow());
					}

					// enforce rule: only one empty row at bottom
					handleRowExpansion();
				  });
				}
				
				function attachRowListeners(row) {
				  const inputs = row.querySelectorAll("input, select");

				  inputs.forEach(input => {
					input.addEventListener("input", () => {
					  handleRowExpansion();
					});
				  });
				}
				
				function handleRowExpansion() {
				  const tbody = document.getElementById("internTableBody");
				  const rows = tbody.querySelectorAll("tr");

				  const lastRow = rows[rows.length - 1];

				  // If last row is NOT empty → add new row
				  if (lastRow && !isRowEmpty(lastRow)) {
					tbody.appendChild(createRow());
				  }

				  // Clean duplicates everywhere
				  cleanEmptyRows();
				  
				  updateRowNumbers();
				}
				
				document.addEventListener("DOMContentLoaded", () => {
				  const tbody = document.getElementById("internTableBody");

				  tbody.appendChild(createRow());
				});
			  
				async function submitInterns(event) {
				  event.preventDefault(); // 💥 no page reload

				  const rows = document.querySelectorAll("#internTableBody tr");

					const payload = [];
					const errors = [];

					const rowArray = Array.from(rows);

					// Detect last row
					const lastRow = rowArray[rowArray.length - 1];

					// Check if last row is empty (your dynamic buffer row)
					const isLastRowEmpty = isRowEmpty(lastRow);

					rowArray.forEach((row, index) => {

					  // 🚨 Skip ONLY the last row if it's empty
					  if (index === rowArray.length - 1 && isLastRowEmpty) {
						return;
					  }

					  const get = (name) =>
						row.querySelector(`[name="${name}"]`)?.value?.trim();

					  const firstName = get("first_name");
					  const lastName = get("last_name");
					  const email = get("email");
					  const schoolUserId = get("school_user_id");
					  const schoolName = get("school_name");
					  const programName = get("program_name");

					  const birthdate = get("birthdate");
					  const gender = get("gender");
					  const city = get("city");
					  const province = get("province");
					  const postal = get("postal");
					  const country = get("country");

					  // REQUIRED VALIDATION (ONLY for real rows)
					  clearErrors();
					  let hasRowError = false;

						// REQUIRED FIELDS
						if (!firstName) {
						  markFieldError(row, "first_name");
						  hasRowError = true;
						}
						if (!lastName) {
						  markFieldError(row, "last_name");
						  hasRowError = true;
						}
						if (!email) {
						  markFieldError(row, "email");
						  hasRowError = true;
						}
						if (!schoolUserId) {
						  markFieldError(row, "school_user_id");
						  hasRowError = true;
						}
						if (!schoolName) {
						  markFieldError(row, "school_name");
						  hasRowError = true;
						}
						if (!programName) {
						  markFieldError(row, "program_name");
						  hasRowError = true;
						}

						if (hasRowError) {
						  markRowError(row);
						  errors.push(`Row ${index + 1}: Missing required fields.`);
						  return;
						}

						// GENDER VALIDATION
						if (gender && !["Male", "Female"].includes(gender)) {
						  markFieldError(row, "gender");
						  markRowError(row);
						  errors.push(`Row ${index + 1}: Gender must be Male or Female.`);
						  return;
						}

					  payload.push({
						firstName,
						lastName,
						email,
						schoolUserId,
						schoolName,
						programName,
						birthdate: birthdate || null,
						gender: gender || null,
						city: city || null,
						province: province || null,
						postal: postal || null,
						country: country || null
					  });
					});

				  const errorBox = document.getElementById("formErrors");
				  errorBox.innerHTML = "";

				  if (errors.length > 0) {
					errorBox.innerHTML = errors.map(e => `<p style="color:red;">${e}</p>`).join("");
					return;
				  }

				  const res = await fetch("handler_addinterns.php", {
					method: "POST",
					headers: { "Content-Type": "application/json" },
					body: JSON.stringify({ interns: payload })
				  });

				  const text = await res.text();

					let data;
					try {
					  data = JSON.parse(text);
					} catch (e) {
					  console.error("Non-JSON response:", text);
					  throw e;
					}

				  clearErrors();

					if (!data.success) {
						
						console.log("hi");

					  const errorBox = document.getElementById("formErrors");

					  errorBox.innerHTML = data.errors.map(e =>
						`<p style="color:red;">Row ${e.row + 1}: ${e.message}</p>`
					  ).join("");

					  // highlight rows + fields
					  data.errors.forEach(err => {
						const row = document.querySelectorAll("#internTableBody tr")[err.row];
						if (!row) return;

						row.classList.add("error-row");

						if (err.field && err.field !== "database") {
						  const input = row.querySelector(`[name="${err.field}"]`);
						  if (input) input.classList.add("error-cell");
						}
					  });

					  return;
					}
					else {

					  if (data.inserted > 0) document.getElementById("formErrors").innerHTML =
						`<p style="color:green;">Inserted ${data.inserted} rows successfully!</p>`;

					  const tbody = document.getElementById("internTableBody");
					  tbody.innerHTML = "";

					  // recreate single empty row
					  tbody.appendChild(createRow());
					}
				}
				
				function markFieldError(row, fieldName) {
				  const el = row.querySelector(`[name="${fieldName}"]`);
				  if (el) el.classList.add("error-cell");
				}
				
				function markRowError(row) {
				  row.classList.add("error-row");
				}
				
				function clearErrors() {
				  document.querySelectorAll(".error-cell").forEach(el => {
					el.classList.remove("error-cell");
				  });

				  document.querySelectorAll(".error-row").forEach(el => {
					el.classList.remove("error-row");
				  });

				  document.getElementById("formErrors").innerHTML = "";
				}
				
				function openCsvModal() {
				  const rows = document.querySelectorAll("#internTableBody tr");

				  let csv = [];

				  rows.forEach(row => {
					const get = (name) =>
					  row.querySelector(`[name="${name}"]`)?.value?.trim() || "";

					const line = [
					  get("first_name"),
					  get("last_name"),
					  get("email"),
					  get("school_user_id"),
					  get("school_name"),
					  get("program_name"),
					  get("birthdate"),
					  get("gender"),
					  get("city"),
					  get("province"),
					  get("postal"),
					  get("country")
					].join(",");

					csv.push(line);
				  });

				  document.getElementById("csvText").value = csv.join("\n");
				  document.getElementById("csvModal").style.display = "block";
				}
				
				function closeCsvModal() {
				  document.getElementById("csvModal").style.display = "none";
				}
				
				function parseCSV(text, hasHeaders = false) {
				  let lines = text.trim().split("\n");

				  if (hasHeaders) {
					lines = lines.slice(1);
				  }

				  return lines.map(line => {
					const cols = line.split(",");

					return {
					  firstName: cols[0] || "",
					  lastName: cols[1] || "",
					  email: cols[2] || "",
					  schoolUserId: cols[3] || "",
					  schoolName: cols[4] || "",
					  programName: cols[5] || "",
					  birthdate: normalizeDate(cols[6] || ""),
					  gender: cols[7] || null,
					  city: cols[8] || null,
					  province: cols[9] || null,
					  postal: cols[10] || null,
					  country: cols[11] || null
					};
				  });
				}
				
				function applyCsvChanges() {
				  const hasHeaders = document.getElementById("csvHasHeaders").checked;

				  const text = document.getElementById("csvText").value; // 💥 THIS WAS MISSING
					const data = parseCSV(text, hasHeaders);

				  const tbody = document.getElementById("internTableBody");
				  tbody.innerHTML = "";

				  data.forEach(row => {
					const tr = createRow();

					const set = (name, value) => {
					  const el = tr.querySelector(`[name="${name}"]`);
					  if (el) el.value = value || "";
					};

					set("first_name", row.firstName);
					set("last_name", row.lastName);
					set("email", row.email);
					set("school_user_id", row.schoolUserId);
					set("school_name", row.schoolName);
					set("program_name", row.programName);
					set("birthdate", normalizeDate(row.birthdate));
					set("gender", row.gender);
					set("city", row.city);
					set("province", row.province);
					set("postal", row.postal);
					set("country", row.country);

					tbody.appendChild(tr);
				  });

				  // ensure system integrity (your dynamic row logic)
				  handleRowExpansion();

				  closeCsvModal();
				}
				
				function normalizeDate(input) {
				  if (!input) return "";

				  input = String(input).trim();

				  // 1. Already ISO (YYYY-MM-DD)
				  if (/^\d{4}-\d{2}-\d{2}$/.test(input)) {
					return input;
				  }

				  // 3. Excel serial date (e.g. 45291)
				  if (!isNaN(input) && Number(input) > 20000) {
					const excelDate = new Date((Number(input) - 25569) * 86400000);
					const yyyy = excelDate.getFullYear();
					const mm = String(excelDate.getMonth() + 1).padStart(2, "0");
					const dd = String(excelDate.getDate()).padStart(2, "0");
					return `${yyyy}-${mm}-${dd}`;
				  }

				  // 2. US / EU formats (attempt parsing)
				  let d = new Date(input);
				  if (!isNaN(d.getTime())) {
					const yyyy = d.getFullYear();
					const mm = String(d.getMonth() + 1).padStart(2, "0");
					const dd = String(d.getDate()).padStart(2, "0");
					return `${yyyy}-${mm}-${dd}`;
				  }
				  // fallback (invalid → empty)
				  return "";
				}
				
				document.addEventListener("DOMContentLoaded", () => {
				  const textarea = document.getElementById("csvText");

				  textarea.addEventListener("paste", (e) => {
					const text = (e.clipboardData || window.clipboardData).getData("text");

					// Detect tab-separated Excel paste
					if (text.includes("\t")) {
					  e.preventDefault();

					  const rows = text
						.trim()
						.split("\n")
						.map(row => row.split("\t").join(","))
						.join("\n");

					  textarea.value = rows;
					}
				  });
				});
								
				function importCsvFile() {
					if (typeof XLSX === "undefined") {
					  alert("Excel library failed to load. Please refresh the page.");
					  return;
					}
					
				  const fileInput = document.getElementById("csvFileInput");
				  const hasHeaders = document.getElementById("csvHasHeaders").checked;

				  const file = fileInput.files[0];
				  if (!file) return alert("Please select a file");

				  const reader = new FileReader();

				  reader.onload = function (e) {
					const data = new Uint8Array(e.target.result);
					const workbook = XLSX.read(data, { type: "array" });

					const sheetName = workbook.SheetNames[0];
					const sheet = workbook.Sheets[sheetName];

					// Convert to CSV-like array
					let json = XLSX.utils.sheet_to_json(sheet, {
					  header: 1,
					  defval: ""
					});

					// remove headers if needed
					if (hasHeaders) json = json.slice(1);

					const csvLines = json.map(row => row.join(",")).join("\n");

					document.getElementById("csvText").value = csvLines;
				  };

				  reader.readAsArrayBuffer(file);
				}
				</script>
			</form>
		</section>

        <!-- Rubrics Section (was Database) -->
		<section id="rubrics">
			<h1>Rubrics Section</h1>

			<div style="background: white; padding: 32px; border-radius: 12px; border: 1px solid #e5e7eb; height: 384px; display: flex; align-items: center; justify-content: center;">
				<div style="text-align: center;">
					<i data-lucide="clipboard-list" style="width: 64px; height: 64px; color: #e5e7eb; margin-bottom: 16px;"></i>
					<p style="color: #6b7280;">Rubrics management system loading...</p>
				</div>
			</div>
		</section>

		<!-- Reports Section (was Log Activity) -->
		<section id="reports">
			<h1>Reports Section</h1>

			<div style="max-width: 100%; background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px;">
				<div style="border-left: 2px solid #e5e7eb; padding-left: 20px; margin-left: 10px;">
					<div style="position: relative; margin-bottom: 24px;">
						<div style="position: absolute; left: -27px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: var(--header-bg);"></div>
						<p style="font-weight: 600; font-size: 14px;">System backup completed</p>
						<p style="font-size: 12px; color: #6b7280;">2 hours ago • Server #1</p>
					</div>
				</div>
			</div>
		</section>

    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-left">
            Copyright &copy; 2026 <a href="https://www.apc.edu.ph" target="_blank" class="footer-link-apc">Asia Pacific College</a>. All rights reserved.
        </div>
        <div class="footer-right">
            <a href="#" class="footer-nav-link">ABOUT US</a>
            <a href="#" class="footer-nav-link">PRIVACY POLICY</a>
            <a href="#" class="footer-nav-link">TERMS OF USE</a>
            <a href="#" class="footer-nav-link">CONTACT US</a>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const profileTrigger = document.getElementById('profileTrigger');
        const profilePanel = document.getElementById('profilePanel');
        const profileMenuContainer = document.getElementById('profileMenu');
        const navButtons = document.querySelectorAll('#mainNav .sidebar-item');
        
        let isCollapsed = true;

        function updateLayout(collapsedState) {
            isCollapsed = collapsedState;
            const isMobile = window.innerWidth < 768;

            if (isCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                sidebar.classList.remove('sidebar-expanded');
                mainContent.style.transform = 'translateX(0)';
            } else {
                sidebar.classList.add('sidebar-expanded');
                sidebar.classList.remove('sidebar-collapsed');
                if (isMobile) {
                    mainContent.style.transform = 'translateX(256px)';
                } else {
                    mainContent.style.transform = `translateX(176px)`;
                }
            }
        }

        sidebarToggle.addEventListener('click', () => {
            updateLayout(!isCollapsed);
        });

        sidebar.addEventListener('mouseleave', () => {
            if (!isCollapsed) updateLayout(true);
        });

        function showPage(pageId) {
            document.querySelectorAll('section').forEach(sec => sec.classList.remove('active'));
            const targetSection = document.getElementById(pageId);
            if (targetSection) targetSection.classList.add('active');

            navButtons.forEach(btn => {
                if (btn.getAttribute('data-page') === pageId) {
                    btn.classList.add('nav-active');
                } else {
                    btn.classList.remove('nav-active');
                }
            });

            if (window.innerWidth < 768) updateLayout(true);
        }

        navButtons.forEach(button => {
            button.addEventListener('click', () => {
                showPage(button.getAttribute('data-page'));
            });
        });

        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            profilePanel.classList.toggle('show');
        });

        profileMenuContainer.addEventListener('mouseleave', () => {
            profilePanel.classList.remove('show');
        });

        function handleLogout() {
            window.location.replace('../login/login.php');
        }

        window.addEventListener('resize', () => updateLayout(isCollapsed));
    </script>
</body>
</html>