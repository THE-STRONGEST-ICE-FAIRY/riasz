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
		
		th {
			user-select: none;
		}

		.sort-indicator {
			margin-left: 6px;
			font-size: 12px;
			color: #666;
			visibility: hidden;
		}

		th.active .sort-indicator {
			visibility: visible;
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
            <span class="admin-label">OFFICER</span>
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
			
            <h2>Add Interns</h2>
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
					  
					  loadInterns();
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
		
            <h2>Interns List</h2>
			<input
			  type="text"
			  id="searchInput"
			  placeholder="Search interns..."
			  style="margin-bottom: 10px; padding: 6px; width: 300px;"
			>
			<button id="columnFilterBtn">Columns</button>
			<button id="refreshBtn">Refresh Table</button>
			<div style="overflow-x:auto;">
				<table border="1" id="internTable" border="1" cellpadding="4" cellspacing="0" style="min-width: 1400px;">
					<thead>
						<tr>
							<th data-key="full_name">Intern Full Name <span class="sort-indicator"></span></th>
							<th data-key="schooluser_id">School User ID <span class="sort-indicator"></span></th>
							<th data-key="program_name">Program Name <span class="sort-indicator"></span></th>
							<th data-key="school_name">School Name <span class="sort-indicator"></span></th>
							<th data-key="email">Email <span class="sort-indicator"></span></th>
							<th data-key="birthdate">Birthdate <span class="sort-indicator"></span></th>
							<th data-key="gender">Gender <span class="sort-indicator"></span></th>
							<th data-key="city">City <span class="sort-indicator"></span></th>
							<th data-key="province_or_state">Province/State <span class="sort-indicator"></span></th>
							<th data-key="postal_code">Postal Code <span class="sort-indicator"></span></th>
							<th data-key="country">Country <span class="sort-indicator"></span></th>
							<th data-key="account_status">Account Status <span class="sort-indicator"></span></th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<!-- Data will be inserted here -->
					</tbody>
				</table>
				
				<div id="columnModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
				background:rgba(0,0,0,0.5);">

				  <div style="background:#fff; width:300px; margin:10% auto; padding:20px; border-radius:8px;">
					<h3>Select Columns</h3>

					<div id="columnCheckboxes"></div>

					<br>
					<button id="closeColumnModal">Close</button>
				  </div>
				</div>
				
				<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
				background:rgba(0,0,0,0.5);">
				  
				  <div style="background:#fff; width:500px; margin:10% auto; padding:20px; border-radius:8px;">
					
					<h3>Edit Intern</h3>

					<input type="hidden" id="edit_intern_id">

					<label>First Name</label>
					<input id="edit_first_name" placeholder="First Name"><br><br>

					<label>Last Name</label>
					<input id="edit_last_name" placeholder="Last Name"><br><br>

					<label>Email</label>
					<input id="edit_email" placeholder="Email"><br><br>
					
					<label>School User ID</label>
					<input type="text" id="edit_schooluser_given_id"><br><br>

					<label>Birthdate</label>
					<input id="edit_birthdate" type="date"><br><br>

					<label>Gender</label>
					<select id="edit_gender">
					  <option value="">Select Gender</option>
					  <option value="Male">Male</option>
					  <option value="Female">Female</option>
					</select><br><br>

					<label>City</label>
					<input id="edit_city" placeholder="City"><br><br>

					<label>Province / State</label>
					<input id="edit_province" placeholder="Province/State"><br><br>

					<label>Postal Code</label>
					<input id="edit_postal" placeholder="Postal Code"><br><br>

					<label>Country</label>
					<input id="edit_country" placeholder="Country"><br><br>

					<label>School</label>
					<select id="edit_school">
					  <option value="">Select School</option>
					</select><br><br>

					<label>Program</label>
					<select id="edit_program">
					  <option value="">Select Program</option>
					</select><br><br>

					<button id="cancelEdit">Cancel</button>
					<button id="saveEdit">Save Changes</button>
				  </div>
				</div>
				
				<script>
					async function loadInterns() {
						try {
							const response = await fetch('intern_handler.php');
							const data = await response.json();
							currentData = data;
							renderTable(currentData);
						} catch (err) {
							console.error('Fetch error:', err);
						}
					}

					// Initial load
					loadInterns();

					// Refresh button
					document.getElementById('refreshBtn').addEventListener('click', loadInterns);
					
					const searchInput = document.getElementById('searchInput');
					
					let currentData = [];
					let sortState = {};
					let activeSortKey = null;

					function filterTable() {
						const query = searchInput.value.toLowerCase();
						const rows = document.querySelectorAll('#internTable tbody tr');

						rows.forEach(row => {
							const text = row.textContent.toLowerCase();
							row.style.display = text.includes(query) ? '' : 'none';
						});
					}

					searchInput.addEventListener('input', filterTable);
					
					// filtering by columns 
					if (true) {
						function updateSortIndicators() {
							document.querySelectorAll('#internTable thead th').forEach(th => {
								const key = th.dataset.key;
								const indicator = th.querySelector('.sort-indicator');

								th.classList.remove('active');

								if (!key || key !== activeSortKey) {
									if (indicator) indicator.textContent = '';
									return;
								}

								th.classList.add('active');

								if (indicator) {
									indicator.textContent = sortState[key] === 'asc' ? '▲' : '▼';
								}
							});
						}

						function renderTable(data) {
							const tbody = document.querySelector('#internTable tbody');
							tbody.innerHTML = '';

							data.forEach(intern => {
								const tr = document.createElement('tr');
								tr.innerHTML = `
									<td>${intern.full_name || ''}</td>
									<td>${intern.schooluser_given_id || ''}</td>
									<td>${intern.program_name || ''}</td>
									<td>${intern.school_name || ''}</td>
									<td>${intern.email || ''}</td>
									<td>${intern.birthdate || ''}</td>
									<td>${intern.gender || ''}</td>
									<td>${intern.city || ''}</td>
									<td>${intern.province_or_state || ''}</td>
									<td>${intern.postal_code || ''}</td>
									<td>${intern.country || ''}</td>
									<td>${intern.account_status || ''}</td>
									<td>
										<button class="editBtn" data-id="${intern.intern_id}">Edit</button>
									</td>
								`;
								tbody.appendChild(tr);
							});
						}

						function sortTable(key) {
							const direction = sortState[key] === 'asc' ? 'desc' : 'asc';
							sortState[key] = direction;
							activeSortKey = key;

							currentData.sort((a, b) => {
								let valA = (a[key] || '').toString().toLowerCase();
								let valB = (b[key] || '').toString().toLowerCase();

								if (!isNaN(valA) && !isNaN(valB)) {
									valA = Number(valA);
									valB = Number(valB);
								}

								if (key === 'birthdate') {
									valA = new Date(valA);
									valB = new Date(valB);
								}

								if (valA < valB) return direction === 'asc' ? -1 : 1;
								if (valA > valB) return direction === 'asc' ? 1 : -1;
								return 0;
							});

							renderTable(currentData);
							updateSortIndicators();
							filterTable();
						}

						document.querySelectorAll('#internTable thead th').forEach(th => {
							th.style.cursor = 'pointer';

							th.addEventListener('click', () => {
								const key = th.dataset.key;
								if (!key) return;
								sortTable(key);
							});
						});
					}
					
					// edit intern functions
					if (true) {
						window.addEventListener('DOMContentLoaded', () => {
							loadSchools();
							loadPrograms();
							loadInterns();
						});

						let schools = [];
						let programs = [];

						document.addEventListener('click', async (e) => {
							if (e.target.classList.contains('editBtn')) {
								const id = e.target.dataset.id;

								const intern = currentData.find(i => i.intern_id == id);
								if (!intern) return;

								document.getElementById('edit_intern_id').value = intern.intern_id;

								const parts = (intern.full_name || '').split(' ');
								const firstName = parts.shift() || '';
								const lastName = parts.join(' ');

								document.getElementById('edit_first_name').value = firstName;
								document.getElementById('edit_last_name').value = lastName;
								document.getElementById('edit_schooluser_given_id').value = intern.schooluser_given_id || '';

								document.getElementById('edit_email').value = intern.email || '';
								document.getElementById('edit_birthdate').value = intern.birthdate || '';
								document.getElementById('edit_gender').value = intern.gender || '';
								document.getElementById('edit_city').value = intern.city || '';
								document.getElementById('edit_province').value = intern.province_or_state || '';
								document.getElementById('edit_postal').value = intern.postal_code || '';
								document.getElementById('edit_country').value = intern.country || '';
								document.getElementById('edit_school').value = intern.school_id;

								// 🔥 SCHOOL FIRST
								document.getElementById('edit_school').value = intern.school_id;

								// 🔥 FILTER PROGRAMS
								const programSelect = document.getElementById('edit_program');
								programSelect.innerHTML = `<option value="">Select Program</option>`;

								const filteredPrograms = programs.filter(p => p.school_id == intern.school_id);

								filteredPrograms.forEach(p => {
									programSelect.innerHTML += `
										<option value="${p.program_id}">${p.program_name}</option>
									`;
								});

								// 🔥 THEN SET PROGRAM
								document.getElementById('edit_program').value = intern.program_id;
								
								document.getElementById('editModal').style.display = 'block';
							}
						});
						
						document.getElementById('cancelEdit').addEventListener('click', () => {
							document.getElementById('editModal').style.display = 'none';
						});
						
						document.getElementById('saveEdit').addEventListener('click', async () => {
							const payload = {
								intern_id: document.getElementById('edit_intern_id').value,
								first_name: document.getElementById('edit_first_name').value,
								last_name: document.getElementById('edit_last_name').value,
								email: document.getElementById('edit_email').value,
								schooluser_given_id: document.getElementById('edit_schooluser_given_id').value,
								birthdate: document.getElementById('edit_birthdate').value,
								gender: document.getElementById('edit_gender').value,
								city: document.getElementById('edit_city').value,
								province_or_state: document.getElementById('edit_province').value,
								postal_code: document.getElementById('edit_postal').value,
								country: document.getElementById('edit_country').value,
								program_id: document.getElementById('edit_program').value
							};

							try {
								const res = await fetch('update_intern.php', {
									method: 'POST',
									headers: {'Content-Type': 'application/json'},
									body: JSON.stringify(payload)
								});

								const result = await res.json();

								if (result.success) {
									document.getElementById('editModal').style.display = 'none';
									loadInterns(); // refresh table
								} else {
									alert(result.message || 'Update failed');
								}
							} catch (err) {
								console.error(err);
								alert('Server error');
							}
						});
						
						async function loadSchools() {
							const res = await fetch('get_schools.php');
							schools = await res.json();

							const schoolSelect = document.getElementById('edit_school');
							schoolSelect.innerHTML = `<option value="">Select School</option>`;

							schools.forEach(s => {
								schoolSelect.innerHTML += `
									<option value="${s.school_id}">${s.school_name}</option>
								`;
							});
						}
						
						async function loadPrograms() {
							const res = await fetch('get_programs.php');
							programs = await res.json();
						}
						
						document.getElementById('edit_school').addEventListener('change', function () {
							const schoolId = this.value;

							const programSelect = document.getElementById('edit_program');
							programSelect.innerHTML = `<option value="">Select Program</option>`;

							const filtered = programs.filter(p => p.school_id == schoolId);

							filtered.forEach(p => {
								programSelect.innerHTML += `
									<option value="${p.program_id}">${p.program_name}</option>
								`;
							});
						});
					}
					
					// column checkboxes
					if (true) {
						let selectedColumns = [];
						const columnMap = [
						{ label: "Full Name", key: "full_name" },
						{ label: "School User ID", key: "schooluser_id" },
						{ label: "Program Name", key: "program_name" },
						{ label: "School Name", key: "school_name" },
						{ label: "Email", key: "email" },
						{ label: "Birthdate", key: "birthdate" },
						{ label: "Gender", key: "gender" },
						{ label: "City", key: "city" },
						{ label: "Province/State", key: "province_or_state" },
						{ label: "Postal Code", key: "postal_code" },
						{ label: "Country", key: "country" },
						{ label: "Account Status", key: "account_status" }
						];
						
						document.getElementById('columnFilterBtn').addEventListener('click', () => {
							const container = document.getElementById('columnCheckboxes');
							container.innerHTML = '';

							columnMap.forEach(col => {
								const checked = selectedColumns.includes(col.key) ? 'checked' : '';

								container.innerHTML += `
									<label>
										<input type="checkbox" value="${col.key}" ${checked}>
										${col.label}
									</label><br>
								`;
							});

							document.getElementById('columnModal').style.display = 'block';
						});
						
						document.getElementById('closeColumnModal').addEventListener('click', () => {

							const checkboxes = document.querySelectorAll('#columnCheckboxes input[type="checkbox"]');

							selectedColumns = [];

							checkboxes.forEach(cb => {
								if (cb.checked) {
									selectedColumns.push(cb.value);
								}
							});

							document.getElementById('columnModal').style.display = 'none';

							filterTable(); // reapply filter immediately
						});
						
						function filterTable() {
							const query = searchInput.value.toLowerCase();
							const rows = document.querySelectorAll('#internTable tbody tr');

							rows.forEach(row => {
								const cells = row.querySelectorAll('td');

								let match = false;

								// if no columns selected → search ALL
								if (selectedColumns.length === 0) {
									match = row.textContent.toLowerCase().includes(query);
								} else {
									selectedColumns.forEach(key => {
										const colIndex = columnMap.findIndex(c => c.key === key);

										const cell = cells[colIndex];
										if (cell && cell.textContent.toLowerCase().includes(query)) {
											match = true;
										}
									});
								}

								row.style.display = match ? '' : 'none';
							});
						}
					}
					
				</script>
			</div>
			
            <h2>Add Companies</h2>

			<form id="companyForm" onsubmit="return submitCompanies(event)">

			  <div style="overflow-x:auto;">
				<table border="1" cellpadding="4" cellspacing="0" style="min-width: 1400px;">
				  <thead>
					<tr>
					  <th>#</th>
					  <th>Company Name *</th>
					  <th>Email *</th>
					  <th>Website</th>
					  <th>Address</th>
					  <th>Allowance</th>
					  <th>Partnership Status</th>
					  <th>Max Intern Slots</th>
					  <th>Revenue Growth</th>
					  <th>Profit Margins</th>
					  <th>ROI</th>
					  <th>ROA</th>
					</tr>
				  </thead>
				  <tbody id="companyTableBody"></tbody>
				</table>
			  </div>

			  <div id="companyFormErrors"></div>

			  <button type="submit">Submit Companies</button>

			  <button type="button" onclick="openCompanyCsvModal()">Edit CSV</button>

			  <!-- CSV MODAL -->
			  <div id="companyCsvModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
				background:rgba(0,0,0,0.5);">

				<div style="background:#fff; width:600px; margin:10% auto; padding:20px;">

				  <h3>Company CSV Editor</h3>

				  <textarea id="companyCsvText" style="width:100%; height:300px;"></textarea>

				  <br><br>

				  <input type="file" id="companyCsvFile" accept=".csv,.xlsx,.xls" />

				  <label>
					<input type="checkbox" id="companyCsvHasHeaders">
					File has headers
				  </label>

				  <br><br>

				  <button type="button" onclick="importCompanyCsvFile()">Import File</button>

				  <hr>

				  <button type="button" onclick="closeCompanyCsvModal()">Cancel</button>
				  <button type="button" onclick="applyCompanyCsvChanges()">Apply</button>

				</div>
			  </div>
			</form>
			
			<script>

			// ================= INIT =================
			document.addEventListener("DOMContentLoaded", () => {
			  const tbody = document.getElementById("companyTableBody");
			  tbody.appendChild(createCompanyRow());
			});

			// ================= ROW CREATION =================
			function createCompanyRow() {
			  const tr = document.createElement("tr");

			  tr.innerHTML = `
				<td class="row-number"></td>

				<td><input name="company_name" type="text"></td>
				<td><input name="company_email" type="email"></td>
				<td><input name="company_website" type="text"></td>
				<td><input name="company_address" type="text"></td>

				<td><input name="intern_allowance" type="number"></td>
				<td><input name="partnership_status" type="text"></td>
				<td><input name="max_internslots" type="number"></td>

				<td><input name="revenue_growth" type="number"></td>
				<td><input name="profit_margins" type="number"></td>
				<td><input name="roi" type="number"></td>
				<td><input name="roa" type="number"></td>

				<td><button type="button" class="delete-row">Delete</button></td>
			  `;

			  attachCompanyListeners(tr);
			  attachCompanyDelete(tr);

			  return tr;
			}

			// ================= LISTENERS =================
			function attachCompanyListeners(row) {
			  row.querySelectorAll("input").forEach(input => {
				input.addEventListener("input", handleCompanyExpansion);
			  });
			}

			function attachCompanyDelete(row) {
			  row.querySelector(".delete-row").addEventListener("click", () => {
				const tbody = document.getElementById("companyTableBody");

				row.remove();

				if (tbody.querySelectorAll("tr").length === 0) {
				  tbody.appendChild(createCompanyRow());
				}

				handleCompanyExpansion();
			  });
			}

			// ================= EXPANSION =================
			function isCompanyRowEmpty(row) {
			  const inputs = row.querySelectorAll("input");
			  for (let i of inputs) {
				if (i.value.trim() !== "") return false;
			  }
			  return true;
			}

			function handleCompanyExpansion() {
			  const tbody = document.getElementById("companyTableBody");
			  const rows = tbody.querySelectorAll("tr");

			  const lastRow = rows[rows.length - 1];

			  if (lastRow && !isCompanyRowEmpty(lastRow)) {
				tbody.appendChild(createCompanyRow());
			  }

			  cleanCompanyEmptyRows();
			  updateCompanyNumbers();
			}

			function cleanCompanyEmptyRows() {
			  const rows = Array.from(document.querySelectorAll("#companyTableBody tr"));

			  let empty = rows.filter(isCompanyRowEmpty);

			  empty.forEach((r, i) => {
				if (i !== empty.length - 1) r.remove();
			  });
			}

			function updateCompanyNumbers() {
			  document.querySelectorAll("#companyTableBody tr").forEach((row, i) => {
				row.querySelector(".row-number").textContent = i + 1;
			  });
			}

			// ================= SUBMIT =================
			async function submitCompanies(e) {
			  e.preventDefault();

			  const rows = document.querySelectorAll("#companyTableBody tr");
			  const payload = [];
			  const errors = [];

			  const arr = Array.from(rows);
			  const lastRow = arr[arr.length - 1];

			  clearCompanyErrors();

			  arr.forEach((row, i) => {

				if (i === arr.length - 1 && isCompanyRowEmpty(row)) return;

				const get = (n) => row.querySelector(`[name="${n}"]`)?.value?.trim();

				const company_name = get("company_name");
				const company_email = get("company_email");

				let hasError = false;

				if (!company_name) {
				  markCompanyField(row, "company_name");
				  hasError = true;
				}

				if (!company_email) {
				  markCompanyField(row, "company_email");
				  hasError = true;
				}

				if (hasError) {
				  row.classList.add("error-row");
				  errors.push(`Row ${i + 1}: Missing required fields`);
				  return;
				}

				payload.push({
				  company_name,
				  company_email,
				  company_website: get("company_website"),
				  company_address: get("company_address"),
				  intern_allowance: get("intern_allowance"),
				  partnership_status: get("partnership_status"),
				  max_internslots: get("max_internslots"),
				  revenue_growth: get("revenue_growth"),
				  profit_margins: get("profit_margins"),
				  roi: get("roi"),
				  roa: get("roa")
				});

			  });

			  if (errors.length) {
				document.getElementById("companyFormErrors").innerHTML =
				  errors.map(e => `<p style="color:red;">${e}</p>`).join("");
				return;
			  }

			  const res = await fetch("handler_companies.php", {
				method: "POST",
				headers: {"Content-Type":"application/json"},
				body: JSON.stringify({ companies: payload })
			  });

			  const data = await res.json();

			  if (!data.success) {
				document.getElementById("companyFormErrors").innerHTML =
				  `<p style="color:red;">Insert failed</p>`;
				return;
			  }

			  if (data.inserted > 0) document.getElementById("companyFormErrors").innerHTML =
				`<p style="color:green;">Inserted ${data.inserted} companies</p>`;

				loadCompanies();
			  document.getElementById("companyTableBody").innerHTML = "";
			  document.getElementById("companyTableBody").appendChild(createCompanyRow());
			}

			// ================= ERRORS =================
			function markCompanyField(row, name) {
			  const el = row.querySelector(`[name="${name}"]`);
			  if (el) el.classList.add("error-cell");
			}

			function clearCompanyErrors() {
			  document.querySelectorAll("#companyTableBody .error-cell")
				.forEach(e => e.classList.remove("error-cell"));
			  document.querySelectorAll("#companyTableBody .error-row")
				.forEach(e => e.classList.remove("error-row"));

			  document.getElementById("companyFormErrors").innerHTML = "";
			}

			// ================= CSV =================
			function openCompanyCsvModal() {
			  const rows = document.querySelectorAll("#companyTableBody tr");
			  let csv = [];

			  rows.forEach(row => {
				const get = n => row.querySelector(`[name="${n}"]`)?.value?.trim() || "";

				csv.push([
				  get("company_name"),
				  get("company_email"),
				  get("company_website"),
				  get("company_address"),
				  get("intern_allowance"),
				  get("partnership_status"),
				  get("max_internslots"),
				  get("revenue_growth"),
				  get("profit_margins"),
				  get("roi"),
				  get("roa")
				].join(","));
			  });

			  document.getElementById("companyCsvText").value = csv.join("\n");
			  document.getElementById("companyCsvModal").style.display = "block";
			}

			function closeCompanyCsvModal() {
			  document.getElementById("companyCsvModal").style.display = "none";
			}

			function applyCompanyCsvChanges() {
			  const text = document.getElementById("companyCsvText").value;
			  const lines = text.trim().split("\n");

			  const tbody = document.getElementById("companyTableBody");
			  tbody.innerHTML = "";

			  lines.forEach(line => {
				const cols = line.split(",");

				const tr = createCompanyRow();

				const set = (n, v) => {
				  const el = tr.querySelector(`[name="${n}"]`);
				  if (el) el.value = v || "";
				};

				set("company_name", cols[0]);
				set("company_email", cols[1]);
				set("company_website", cols[2]);
				set("company_address", cols[3]);
				set("intern_allowance", cols[4]);
				set("partnership_status", cols[5]);
				set("max_internslots", cols[6]);
				set("revenue_growth", cols[7]);
				set("profit_margins", cols[8]);
				set("roi", cols[9]);
				set("roa", cols[10]);

				tbody.appendChild(tr);
			  });

			  handleCompanyExpansion();
			  closeCompanyCsvModal();
			}

			</script>

			<h2>Companies List</h2>

			<input
			  type="text"
			  id="searchInput2"
			  placeholder="Search companies..."
			>

			<button id="columnFilterBtn2">Columns</button>
			<button id="refreshBtn2">Refresh Table</button>

			<div style="overflow-x:auto;">
			  <table border="1" id="companyTable" cellpadding="4" cellspacing="0" style="min-width: 1400px;">
				<thead>
				  <tr>
					<th data-key="company_name">Company Name <span class="sort-indicator"></span></th>
					<th data-key="company_email">Email <span class="sort-indicator"></span></th>
					<th data-key="company_website">Website <span class="sort-indicator"></span></th>
					<th data-key="company_address">Address <span class="sort-indicator"></span></th>
					<th data-key="intern_allowance">Allowance <span class="sort-indicator"></span></th>
					<th data-key="partnership_status">Partnership Status <span class="sort-indicator"></span></th>
					<th data-key="max_internslots">Max Intern Slots <span class="sort-indicator"></span></th>
					<th data-key="revenue_growth">Revenue Growth <span class="sort-indicator"></span></th>
					<th data-key="profit_margins">Profit Margins <span class="sort-indicator"></span></th>
					<th data-key="roi">ROI <span class="sort-indicator"></span></th>
					<th data-key="roa">ROA <span class="sort-indicator"></span></th>
					<th data-key="departments">Departments <span class="sort-indicator"></span></th>
					<th>Actions</th>
				  </tr>
				</thead>
				<tbody></tbody>
			  </table>
			</div>

			<div id="columnModal2" style=" display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
				<div style=" background:#fff; width:500px; max-width:90%; max-height:80vh; overflow-y:auto; padding:20px; border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,0.3); ">
					<h3>Select Columns</h3>
					<div id="columnCheckboxes2"></div>
					<button id="closeColumnModal2">Close</button>
				  </div>
			</div>

			<div id="editModal2" style="
			  display:none;
			  position:fixed;
			  inset:0;
			  background:rgba(0,0,0,0.5);
			  z-index:9999;
			  align-items:center;
			  justify-content:center;
			">
			  <div style="
				background:#fff;
				width:500px;
				max-width:90%;
				max-height:80vh;
				overflow-y:auto;
				padding:20px;
				border-radius:10px;
				box-shadow:0 10px 30px rgba(0,0,0,0.3);
			  ">
				<h3>Edit Company</h3>

				<input type="hidden" id="edit_company_id">

				<label>Company Name *</label>
				<input id="edit_company_name"><br><br>

				<label>Email *</label>
				<input id="edit_company_email"><br><br>

				<label>Website</label>
				<input id="edit_company_website"><br><br>

				<label>Address</label>
				<input id="edit_company_address"><br><br>

				<label>Allowance</label>
				<input id="edit_intern_allowance"><br><br>

				<label>Partnership Status</label>
				<input id="edit_partnership_status"><br><br>

				<label>Max Intern Slots</label>
				<input id="edit_max_internslots"><br><br>

				<label>Revenue Growth</label>
				<input id="edit_revenue_growth"><br><br>

				<label>Profit Margins</label>
				<input id="edit_profit_margins"><br><br>

				<label>ROI</label>
				<input id="edit_roi"><br><br>

				<label>ROA</label>
				<input id="edit_roa"><br><br>

				<button id="cancelEdit2">Cancel</button>
				<button id="saveEdit2">Save Changes</button>
			  </div>
			</div>
			
			<div id="deptModal2" style="
			  display:none;
			  position:fixed;
			  inset:0;
			  background:rgba(0,0,0,0.5);
			  z-index:9999;
			  align-items:center;
			  justify-content:center;
			">
			  <div style="
				background:#fff;
				width:600px;
				max-width:95%;
				max-height:85vh;
				overflow-y:auto;
				padding:20px;
				border-radius:10px;
			  ">

				<h3>Departments</h3>

				<div id="deptList2"></div>

				<hr>

				<h4 id="deptFormTitle2">Add Department</h4>

				<input type="hidden" id="dept_company_id">
				<input type="hidden" id="dept_id">

				<label>Department Name</label>
				<input id="dept_name" placeholder="e.g. IT, HR">

				<br><br>

				<button id="deptCancel2">Cancel</button>
				<button id="deptSubmit2">Submit</button>

			  </div>
			</div>
			
			<div id="supervisorModal2" style=" display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center; ">
			  <div style="background:#fff; width:500px; padding:20px; border-radius:10px; ">
				<h3>Add Supervisor</h3>

				<input type="hidden" id="sup_department_id">
				<input type="hidden" id="sup_user_id">

				<label>First Name</label>
				<input id="sup_first_name">

				<label>Last Name</label>
				<input id="sup_last_name">

				<label>Email</label>
				<input id="sup_email">

				<label>Job Role</label>
				<input id="sup_jobrole">
				
				<label>Contact Number</label>
				<input id="sup_contact_no">

				<br><br>

				<button id="supCancel2">Cancel</button>
				<button id="supSubmit2">Submit</button>

			  </div>
			</div>
			
			<script>
			let currentData2 = [];
			let sortState2 = {};
			let activeSortKey2 = null;

			let selectedColumns2 = [];

			const searchInput2 = document.getElementById('searchInput2');

			const columnMap2 = [
			  { label: "Company Name", key: "company_name" },
			  { label: "Email", key: "company_email" },
			  { label: "Website", key: "company_website" },
			  { label: "Address", key: "company_address" },
			  { label: "Allowance", key: "intern_allowance" },
			  { label: "Partnership Status", key: "partnership_status" },
			  { label: "Max Intern Slots", key: "max_internslots" },
			  { label: "Revenue Growth", key: "revenue_growth" },
			  { label: "Profit Margins", key: "profit_margins" },
			  { label: "ROI", key: "roi" },
			  { label: "ROA", key: "roa" },
			  { label: "Departments", key: "departments" }
			];

			async function loadCompanies() {
			  const res = await fetch('get_companies.php');
			  const data = await res.json();

			  if (!data.success) {
				console.error("API Error:", data.message);
				currentData2 = [];
				renderTable2([]);
				return;
			  }

			  currentData2 = data.data; // 👈 IMPORTANT FIX
			  renderTable2(currentData2);
			}

			loadCompanies();

			document.getElementById('refreshBtn2').addEventListener('click', loadCompanies);

			function renderTable2(data = []) {
			  const tbody = document.querySelector('#companyTable tbody');
			  if (!tbody) return;

			  if (!Array.isArray(data)) {
				console.error("renderTable2 expected array but got:", data);
				return;
			  }

			  tbody.innerHTML = '';

			  data.forEach(c => {
				const tr = document.createElement('tr');

				tr.innerHTML = `
				  <td>${c.company_name ?? ''}</td>
				  <td>${c.company_email ?? ''}</td>
				  <td>${c.company_website ?? ''}</td>
				  <td>${c.company_address ?? ''}</td>
				  <td>${c.intern_allowance ?? ''}</td>
				  <td>${c.partnership_status ?? ''}</td>
				  <td>${c.max_internslots ?? ''}</td>
				  <td>${c.revenue_growth ?? ''}</td>
				  <td>${c.profit_margins ?? ''}</td>
				  <td>${c.roi ?? ''}</td>
				  <td>${c.roa ?? ''}</td>
				  <td>${c.departments ?? 0}</td>
				  <button class="viewDeptsBtn2" data-id="${c.company_id}">
					View Depts
				  </button>
				  <button class="editBtn2" data-id="${c.company_id}">
					Edit Company
				  </button>
				`;

				tbody.appendChild(tr);
			  });
			}

			function sortTable2(key) {
			  const direction = sortState2[key] === 'asc' ? 'desc' : 'asc';
			  sortState2[key] = direction;
			  activeSortKey2 = key;

			  currentData2.sort((a, b) => {
				let valA = (a[key] ?? '').toString().toLowerCase();
				let valB = (b[key] ?? '').toString().toLowerCase();

				if (!isNaN(valA) && !isNaN(valB)) {
				  valA = Number(valA);
				  valB = Number(valB);
				}

				if (valA < valB) return direction === 'asc' ? -1 : 1;
				if (valA > valB) return direction === 'asc' ? 1 : -1;
				return 0;
			  });

			  renderTable2(currentData2);
			  updateSortIndicators2();
			  filterTable2();
			}

			function updateSortIndicators2() {
			  document.querySelectorAll('#companyTable thead th').forEach(th => {
				const key = th.dataset.key;
				const indicator = th.querySelector('.sort-indicator');

				th.classList.remove('active');

				if (!key || key !== activeSortKey2) {
				  if (indicator) indicator.textContent = '';
				  return;
				}

				th.classList.add('active');
				if (indicator) {
				  indicator.textContent = sortState2[key] === 'asc' ? '▲' : '▼';
				}
			  });
			}

			function filterTable2() {
			  const query = searchInput2.value.toLowerCase().trim();
			  const rows = document.querySelectorAll('#companyTable tbody tr');

			  rows.forEach(row => {
				const cells = row.querySelectorAll('td');

				// map column keys in order (same order as renderTable2)
				const rowData = {
				  company_name: cells[0]?.textContent ?? '',
				  company_email: cells[1]?.textContent ?? '',
				  company_website: cells[2]?.textContent ?? '',
				  company_address: cells[3]?.textContent ?? '',
				  intern_allowance: cells[4]?.textContent ?? '',
				  partnership_status: cells[5]?.textContent ?? '',
				  max_internslots: cells[6]?.textContent ?? '',
				  revenue_growth: cells[7]?.textContent ?? '',
				  profit_margins: cells[8]?.textContent ?? '',
				  roi: cells[9]?.textContent ?? '',
				  roa: cells[10]?.textContent ?? '',
				  departments: cells[11]?.textContent ?? ''
				};

				const columnsToSearch =
				  selectedColumns2.length > 0
					? selectedColumns2
					: Object.keys(rowData); // fallback: all columns

				const match = columnsToSearch.some(key =>
				  (rowData[key] ?? '').toLowerCase().includes(query)
				);

				row.style.display = match ? '' : 'none';
			  });
			}

			searchInput2.addEventListener('input', filterTable2);

			document.querySelectorAll('#companyTable thead th').forEach(th => {
			  th.style.cursor = 'pointer';
			  th.addEventListener('click', () => {
				const key = th.dataset.key;
				if (!key) return;
				sortTable2(key);
			  });
			});

			document.addEventListener('click', (e) => {
			  if (e.target.classList.contains('editBtn2')) {
				const id = e.target.dataset.id;
				const c = currentData2.find(x => x.company_id == id);
				if (!c) return;

				document.getElementById('edit_company_id').value = c.company_id;
				document.getElementById('edit_company_name').value = c.company_name || '';
				document.getElementById('edit_company_email').value = c.company_email || '';
				document.getElementById('edit_company_website').value = c.company_website || '';
				document.getElementById('edit_company_address').value = c.company_address || '';
				document.getElementById('edit_intern_allowance').value = c.intern_allowance || '';
				document.getElementById('edit_partnership_status').value = c.partnership_status || '';
				document.getElementById('edit_max_internslots').value = c.max_internslots || '';
				document.getElementById('edit_revenue_growth').value = c.revenue_growth || '';
				document.getElementById('edit_profit_margins').value = c.profit_margins || '';
				document.getElementById('edit_roi').value = c.roi || '';
				document.getElementById('edit_roa').value = c.roa || '';

				document.getElementById('editModal2').style.display = 'flex';
			  }
			});

			document.getElementById('cancelEdit2').addEventListener('click', () => {
			  document.getElementById('editModal2').style.display = 'none';
			});

			document.getElementById('saveEdit2').addEventListener('click', async () => {

			  const getVal = (id) => {
				const v = document.getElementById(id).value;
				return v === "" ? null : v;
			  };

			  const payload = {
				company_id: getVal('edit_company_id'),

				company_name: getVal('edit_company_name'),
				company_email: getVal('edit_company_email'),
				company_website: getVal('edit_company_website'),
				company_address: getVal('edit_company_address'),

				intern_allowance: getVal('edit_intern_allowance'),
				partnership_status: getVal('edit_partnership_status'),
				max_internslots: getVal('edit_max_internslots'),

				revenue_growth: getVal('edit_revenue_growth'),
				profit_margins: getVal('edit_profit_margins'),
				roi: getVal('edit_roi'),
				roa: getVal('edit_roa')
			  };
			  
				if (!payload.company_name || !payload.company_email) {
				  alert("Required fields cannot be empty");
				  return;
				}

			  const res = await fetch('company_handler.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload)
			  });

			  const result = await res.json();

			  if (result.success) {
				document.getElementById('editModal2').style.display = 'none';
				loadCompanies();
			  } else {
				alert(result.message || 'Update failed');
			  }
			});

			/* COLUMN FILTER */
			document.getElementById('columnFilterBtn2').addEventListener('click', () => {
			  const container = document.getElementById('columnCheckboxes2');
			  container.innerHTML = '';

			  columnMap2.forEach(col => {
				const checked = selectedColumns2.includes(col.key) ? 'checked' : '';

				container.innerHTML += `
				  <label>
					<input type="checkbox" value="${col.key}" ${checked}>
					${col.label}
				  </label><br>
				`;
			  });

			  document.getElementById('columnModal2').style.display = 'flex';
			});

			document.getElementById('closeColumnModal2').addEventListener('click', () => {
			  const checkboxes = document.querySelectorAll('#columnCheckboxes2 input[type="checkbox"]');

			  selectedColumns2 = [];
			  checkboxes.forEach(cb => {
				if (cb.checked) selectedColumns2.push(cb.value);
			  });

			  document.getElementById('columnModal2').style.display = 'none';
			  filterTable2();
			});
			
			let currentDeptCompanyId = null;
			
			document.addEventListener('click', async (e) => {
			  if (e.target.classList.contains('viewDeptsBtn2')) {
				const companyId = e.target.dataset.id;
				currentDeptCompanyId = companyId;

				document.getElementById('dept_company_id').value = companyId;
				document.getElementById('dept_id').value = '';
				document.getElementById('dept_name').value = '';

				document.getElementById('deptFormTitle2').textContent = "Add Department";

				await loadDepartments(companyId);

				document.getElementById('deptModal2').style.display = 'flex';
			  }
			});
			
			async function loadDepartments(companyId) {
			  const res = await fetch(`departments_get.php?company_id=${companyId}`);
			  const data = await res.json();

			  const container = document.getElementById('deptList2');
			  container.innerHTML = '';

			  data.data.forEach(d => {
				const div = document.createElement('div');

				div.style = "border:1px solid #ddd;padding:10px;margin-bottom:10px;";

				div.innerHTML = `
				  <div style="display:flex;justify-content:space-between;">
					<strong>${d.department_name}</strong>

					<button class="addSupervisorBtn2"
					  data-deptid="${d.department_id}"
					  data-deptname="${d.department_name}">
					  + Add Supervisor
					</button>
					<button class="deptEditBtn2"
					  data-id="${d.department_id}"
					  data-name="${d.department_name}">
					  Edit
					</button>
				  </div>

				  <div id="supList-${d.department_id}" style="margin-top:8px;">
					Loading supervisors...
				  </div>
				`;

				container.appendChild(div);

				loadSupervisors(d.department_id);
			  });
			}
			
			document.addEventListener('click', (e) => {
			  if (e.target.classList.contains('deptEditBtn2')) {
				document.getElementById('dept_id').value = e.target.dataset.id;
				document.getElementById('dept_name').value = e.target.dataset.name;

				document.getElementById('deptFormTitle2').textContent = "Edit Department";
			  }
			});
			
			document.getElementById('deptCancel2').addEventListener('click', () => {
			  document.getElementById('dept_id').value = '';
			  document.getElementById('dept_name').value = '';
			  document.getElementById('deptModal2').style.display = 'none';
			});
			
			document.getElementById('deptSubmit2').addEventListener('click', async () => {
			  const payload = {
				department_id: document.getElementById('dept_id').value || null,
				company_id: document.getElementById('dept_company_id').value,
				department_name: document.getElementById('dept_name').value
			  };

			  const res = await fetch('departments_save.php', {
				method: 'POST',
				headers: {'Content-Type':'application/json'},
				body: JSON.stringify(payload)
			  });

			  const result = await res.json();

			  if (result.success) {
				document.getElementById('dept_id').value = '';
				document.getElementById('dept_name').value = '';

				document.getElementById('deptFormTitle2').textContent = "Add Department";

				await loadDepartments(payload.company_id);
				await loadCompanies(); // refresh count
			  } else {
				alert(result.message);
			  }
			});
			
			async function loadSupervisors(deptId) {
			  const res = await fetch(`supervisors_get.php?department_id=${deptId}`);
			  const data = await res.json();

			  const container = document.getElementById(`supList-${deptId}`);
			  container.innerHTML = '';

			  if (!data.data.length) {
				container.innerHTML = "<small>No supervisors</small>";
				return;
			  }

			  data.data.forEach(s => {
				const div = document.createElement('div');

				div.innerHTML = `
					<div style="font-size:13px;margin-left:10px;display:flex;justify-content:space-between;align-items:center;">
					  <div>
						👤 ${s.user_first_name} ${s.user_last_name} (${s.user_email})
						<br>
						<small>Role: ${s.supervisor_jobrole ?? ''}</small>
					  </div>

					  <button class="editSupervisorBtn2"
						data-userid="${s.user_id}"
						data-first="${s.user_first_name}"
						data-last="${s.user_last_name}"
						data-email="${s.user_email}"
						data-job="${s.supervisor_jobrole}"
						  data-contact="${s.contact_no ?? ''}">
						Edit
					  </button>
					</div>
				`;

				container.appendChild(div);
			  });
			}
			
			document.addEventListener('click', (e) => {
			  if (e.target.classList.contains('addSupervisorBtn2')) {
				const deptId = e.target.dataset.deptid;

				document.getElementById('sup_department_id').value = deptId;

				document.getElementById('sup_first_name').value = '';
				document.getElementById('sup_last_name').value = '';
				document.getElementById('sup_email').value = '';
				document.getElementById('sup_jobrole').value = '';
				document.getElementById('sup_contact_no').value = '';

				document.getElementById('supervisorModal2').style.display = 'flex';
			  }
			});
			
			document.getElementById('supCancel2').addEventListener('click', () => {
			  document.getElementById('supervisorModal2').style.display = 'none';
			});
			
			document.getElementById('supSubmit2').addEventListener('click', async () => {
			  const payload = {
				user_id: document.getElementById('sup_user_id').value || null,
				department_id: document.getElementById('sup_department_id').value,
				first_name: document.getElementById('sup_first_name').value,
				last_name: document.getElementById('sup_last_name').value,
				email: document.getElementById('sup_email').value,
				jobrole: document.getElementById('sup_jobrole').value,
				contact_no: document.getElementById('sup_contact_no').value
			  };

			  const res = await fetch('supervisors_save.php', {
				method: 'POST',
				headers: {'Content-Type':'application/json'},
				body: JSON.stringify(payload)
			  });

			  const result = await res.json();

			  if (result.success) {
				document.getElementById('supervisorModal2').style.display = 'none';

				// reset form
				document.getElementById('sup_user_id').value = '';
				document.getElementById('sup_first_name').value = '';
				document.getElementById('sup_last_name').value = '';
				document.getElementById('sup_email').value = '';
				document.getElementById('sup_jobrole').value = '';
				document.getElementById('sup_contact_no').value = '';

				await loadDepartments(currentDeptCompanyId);
			  } else {
				alert(result.message);
			  }
			});
			
			document.addEventListener('click', (e) => {
			  if (e.target.classList.contains('editSupervisorBtn2')) {
				document.getElementById('sup_user_id').value = e.target.dataset.userid;

				document.getElementById('sup_first_name').value = e.target.dataset.first;
				document.getElementById('sup_last_name').value = e.target.dataset.last;
				document.getElementById('sup_email').value = e.target.dataset.email;
				document.getElementById('sup_jobrole').value = e.target.dataset.job;
				document.getElementById('sup_contact_no').value = e.target.dataset.contact || '';

				document.getElementById('supervisorModal2').style.display = 'flex';
			  }
			});
			
			</script>

			<h2>Supervisors List</h2>

			<input
			  type="text"
			  id="searchInput3"
			  placeholder="Search supervisors..."
			>

			<button id="columnFilterBtn3">Columns</button>
			<button id="refreshBtn3">Refresh Table</button>

			<div style="overflow-x:auto;">
			  <table border="1" id="supervisorTable" cellpadding="4" cellspacing="0" style="min-width: 1200px;">
				<thead>
				  <tr>
					<th data-key="full_name">Name <span class="sort-indicator"></span></th>
					<th data-key="user_email">Email <span class="sort-indicator"></span></th>
					<th data-key="contact_no">Contact No <span class="sort-indicator"></span></th>
					<th data-key="supervisor_jobrole">Job Role <span class="sort-indicator"></span></th>
					<th data-key="department_name">Department <span class="sort-indicator"></span></th>
					<th data-key="company_name">Company <span class="sort-indicator"></span></th>
				  </tr>
				</thead>
				<tbody></tbody>
			  </table>
			</div>

			<div id="columnModal3" style="
			  display:none;
			  position:fixed;
			  inset:0;
			  background:rgba(0,0,0,0.5);
			  z-index:9999;
			  align-items:center;
			  justify-content:center;
			">
			  <div style="
				background:#fff;
				width:500px;
				max-width:90%;
				max-height:80vh;
				overflow-y:auto;
				padding:20px;
				border-radius:10px;
			  ">
				<h3>Select Columns</h3>

				<div id="columnCheckboxes3"></div>

				<button id="closeColumnModal3">Close</button>
			  </div>
			</div>

			<script>

			let currentData3 = [];
			let sortState3 = {};
			let activeSortKey3 = null;

			let selectedColumns3 = [];

			const searchInput3 = document.getElementById('searchInput3');

			const columnMap3 = [
			  { label: "Name", key: "full_name" },
			  { label: "Email", key: "user_email" },
			  { label: "Contact No", key: "contact_no" },
			  { label: "Job Role", key: "supervisor_jobrole" },
			  { label: "Department", key: "department_name" },
			  { label: "Company", key: "company_name" }
			];

			async function loadSupervisorsTable3() {

			  const res = await fetch('supervisors_table_get.php');
			  const data = await res.json();

			  if (!data.success) {
				console.error(data.message);
				currentData3 = [];
				renderTable3([]);
				return;
			  }

			  currentData3 = data.data;
			  renderTable3(currentData3);
			}

			loadSupervisorsTable3();

			document.getElementById('refreshBtn3')
			.addEventListener('click', loadSupervisorsTable3);

			function renderTable3(data = []) {

			  const tbody = document.querySelector('#supervisorTable tbody');

			  if (!tbody) return;

			  tbody.innerHTML = '';

			  data.forEach(s => {

				const tr = document.createElement('tr');

				tr.innerHTML = `
				  <td>${s.full_name ?? ''}</td>
				  <td>${s.user_email ?? ''}</td>
				  <td>${s.contact_no ?? ''}</td>
				  <td>${s.supervisor_jobrole ?? ''}</td>
				  <td>${s.department_name ?? ''}</td>
				  <td>${s.company_name ?? ''}</td>
				`;

				tbody.appendChild(tr);

			  });

			}

			function sortTable3(key) {

			  const direction = sortState3[key] === 'asc'
				? 'desc'
				: 'asc';

			  sortState3[key] = direction;
			  activeSortKey3 = key;

			  currentData3.sort((a, b) => {

				let valA = (a[key] ?? '').toString().toLowerCase();
				let valB = (b[key] ?? '').toString().toLowerCase();

				if (valA < valB) return direction === 'asc' ? -1 : 1;
				if (valA > valB) return direction === 'asc' ? 1 : -1;

				return 0;
			  });

			  renderTable3(currentData3);

			  updateSortIndicators3();

			  filterTable3();
			}

			function updateSortIndicators3() {

			  document.querySelectorAll('#supervisorTable thead th')
			  .forEach(th => {

				const key = th.dataset.key;
				const indicator = th.querySelector('.sort-indicator');

				th.classList.remove('active');

				if (!key || key !== activeSortKey3) {

				  if (indicator) {
					indicator.textContent = '';
				  }

				  return;
				}

				th.classList.add('active');

				if (indicator) {
				  indicator.textContent =
					sortState3[key] === 'asc'
					  ? '▲'
					  : '▼';
				}

			  });

			}

			function filterTable3() {

			  const query = searchInput3.value
				.toLowerCase()
				.trim();

			  const rows = document.querySelectorAll('#supervisorTable tbody tr');

			  rows.forEach(row => {

				const cells = row.querySelectorAll('td');

				const rowData = {
				  full_name: cells[0]?.textContent ?? '',
				  user_email: cells[1]?.textContent ?? '',
				  supervisor_jobrole: cells[2]?.textContent ?? '',
				  contact_no: cells[3]?.textContent ?? '',
				  department_name: cells[3]?.textContent ?? '',
				  company_name: cells[4]?.textContent ?? ''
				};

				const columnsToSearch =
				  selectedColumns3.length > 0
					? selectedColumns3
					: Object.keys(rowData);

				const match = columnsToSearch.some(key =>
				  (rowData[key] ?? '')
				  .toLowerCase()
				  .includes(query)
				);

				row.style.display = match ? '' : 'none';

			  });

			}

			searchInput3.addEventListener('input', filterTable3);

			document.querySelectorAll('#supervisorTable thead th')
			.forEach(th => {

			  th.style.cursor = 'pointer';

			  th.addEventListener('click', () => {

				const key = th.dataset.key;

				if (!key) return;

				sortTable3(key);

			  });

			});

			document.getElementById('columnFilterBtn3')
			.addEventListener('click', () => {

			  const container =
				document.getElementById('columnCheckboxes3');

			  container.innerHTML = '';

			  columnMap3.forEach(col => {

				const checked =
				  selectedColumns3.includes(col.key)
					? 'checked'
					: '';

				container.innerHTML += `
				  <label>
					<input type="checkbox"
					  value="${col.key}"
					  ${checked}>
					${col.label}
				  </label>
				  <br>
				`;

			  });

			  document.getElementById('columnModal3')
				.style.display = 'flex';

			});

			document.getElementById('closeColumnModal3')
			.addEventListener('click', () => {

			  const checkboxes =
				document.querySelectorAll(
				  '#columnCheckboxes3 input[type="checkbox"]'
				);

			  selectedColumns3 = [];

			  checkboxes.forEach(cb => {

				if (cb.checked) {
				  selectedColumns3.push(cb.value);
				}

			  });

			  document.getElementById('columnModal3')
				.style.display = 'none';

			  filterTable3();

			});

			</script>
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