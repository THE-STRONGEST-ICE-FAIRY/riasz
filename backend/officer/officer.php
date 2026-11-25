<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Officer</title>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="datatable.js" defer></script>
    <script src="officer_scripts.js" defer></script>
    <link rel="stylesheet" href="officer_styles.css">
    <link rel="stylesheet" href="officer_masterlist.css">
</head>
<body>
    <header>
        <div class="logo-container">
            <a href="../login/login.php" class="logo-container">
                <img src="../../assets/header_apclogo2.png" alt="Logo">
            </a>
        </div>

        <div class="header-actions">
            <span class="admin-label">OFFICER</span>
            <div class="header-divider"></div>
            <button class="notification-btn" type="button">
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
                <button data-page="rubrics" class="sidebar-item">
                    <div class="icon-container"><i data-lucide="clipboard-list" style="width: 20px; height: 20px;"></i></div>
                    <span class="sidebar-text">Rubrics</span>
                </button>
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

    <main id="mainContent">
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

        <section id="masterlist">
            <!-- ===== Front page: overview ===== -->
            <div class="ml-view active" id="ml-home">
                <h1>Masterlist</h1>

                <div class="quick-actions">
                    <button type="button" class="btn btn-primary" data-act="add-interns">+ Add interns</button>
                    <button type="button" class="btn btn-primary" data-act="add-companies">+ Add companies</button>
                    <button type="button" class="btn btn-outline" data-ml-view="supervisors">
                        <i data-lucide="user-cog" style="width: 16px; height: 16px;"></i> Supervisors
                    </button>
                </div>

                <div class="stats-grid four">
                    <div class="stat-card">
                        <p class="stat-label">Interns</p>
                        <p class="stat-value" id="statInterns">—</p>
                        <p class="stat-sub" id="statInternsSub">Loading…</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Companies</p>
                        <p class="stat-value" id="statCompanies">—</p>
                        <p class="stat-sub" id="statCompaniesSub">Loading…</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Supervisors</p>
                        <p class="stat-value" id="statSupervisors">—</p>
                        <p class="stat-sub" id="statSupervisorsSub">Loading…</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Interns needing attention</p>
                        <p class="stat-value" id="statAttention">—</p>
                        <p class="stat-sub" id="statAttentionSub">Loading…</p>
                    </div>
                </div>

                <div class="dash-grid">
                    <div class="panel">
                        <div class="panel-head">
                            <h3>Recently added interns</h3>
                            <button type="button" class="btn btn-outline btn-sm" data-ml-view="interns">View all</button>
                        </div>
                        <div id="homeInterns"></div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h3>Companies and departments</h3>
                            <button type="button" class="btn btn-outline btn-sm" data-ml-view="companies">View all</button>
                        </div>
                        <div id="homeCompanies"></div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head"><h3>Intern accounts by status</h3></div>
                    <div id="statusBreakdown"></div>
                </div>
            </div>

            <!-- ===== Intern masterlist page ===== -->
            <div class="ml-view" id="ml-interns">
                <div class="page-head">
                    <button type="button" class="btn btn-outline" data-ml-view="home">&larr; Back to Masterlist</button>
                    <h1>Intern Masterlist</h1>
                    <div class="head-actions">
                        <button type="button" class="btn btn-primary" data-act="add-interns">+ Add interns</button>
                    </div>
                </div>
                <div id="internTableMount"></div>
            </div>

            <!-- ===== Company masterlist page ===== -->
            <div class="ml-view" id="ml-companies">
                <div class="page-head">
                    <button type="button" class="btn btn-outline" data-ml-view="home">&larr; Back to Masterlist</button>
                    <h1>Companies and Departments</h1>
                    <div class="head-actions">
                        <button type="button" class="btn btn-primary" data-act="add-companies">+ Add companies</button>
                    </div>
                </div>
                <div id="companyTableMount"></div>
            </div>

            <!-- ===== Supervisor masterlist page ===== -->
            <div class="ml-view" id="ml-supervisors">
                <div class="page-head">
                    <button type="button" class="btn btn-outline" data-ml-view="home">&larr; Back to Masterlist</button>
                    <h1>Supervisor Masterlist</h1>
                    <div class="head-actions">
                        <button type="button" class="btn btn-outline" data-ml-view="companies">Manage in Companies</button>
                    </div>
                </div>
                <p class="muted small page-note">Supervisors are added and edited from a company's departments: open <strong>Companies</strong>, then use <strong>⋮ &rarr; Manage departments</strong>.</p>
                <div id="supervisorTableMount"></div>
            </div>

            <!-- ===== Bulk Add page ===== -->
            <div class="ml-view" id="ml-bulk-add">
                <div class="page-head">
                    <button type="button" class="btn btn-outline" data-ml-view="home">&larr; Back to Masterlist</button>
                    <h1>Bulk Add Entries</h1>
                    <div class="head-actions">
                        <div class="bulk-mode-toggle" role="tablist">
                            <button type="button" class="btn btn-sm btn-primary" id="bulkModeInterns" onclick="switchBulkMode('interns')">Interns</button>
                            <button type="button" class="btn btn-sm btn-outline" id="bulkModeCompanies" onclick="switchBulkMode('companies')">Companies</button>
                        </div>
                    </div>
                </div>

                <div class="panel bulk-container">
                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar-left">
                            <span class="bulk-mode-title" id="bulkTitle">Bulk Add Interns</span>
                            <span class="muted small">Fields marked with <span class="required">*</span> are required.</span>
                        </div>
                        <div class="bulk-toolbar-right">
                            <button type="button" class="btn btn-outline btn-sm" id="bulkViewToggleBtn" onclick="toggleBulkTextEditor()">
                                <i data-lucide="file-code" style="width: 15px; height: 15px;"></i> <span id="bulkViewToggleLabel">Text Editor (CSV)</span>
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('bulkFileInput').click()">
                                <i data-lucide="upload" style="width: 15px; height: 15px;"></i> Import CSV / Excel
                            </button>
                            <input type="file" id="bulkFileInput" accept=".csv, .txt, .tsv, .xlsx, .xls" style="display:none" onchange="handleBulkFileImport(event)">
                            <button type="button" class="btn btn-primary btn-sm" id="bulkSaveBtn" onclick="submitBulkData()">
                                <i data-lucide="check" style="width: 15px; height: 15px;"></i> Save All Rows
                            </button>
                        </div>
                    </div>

                    <div class="bulk-subbar">
                        <div class="bulk-note">
                            <i data-lucide="info" style="width: 15px; height: 15px; flex-shrink: 0;"></i>
                            <span>A new blank row is automatically added at the bottom as soon as you type into the last row. You can also paste directly from Excel or select rows and press <strong>Delete</strong> to clear.</span>
                        </div>
                        <div class="bulk-import-controls">
                            <label class="bulk-checkbox-label">
                                <input type="checkbox" id="bulkHasHeaders"> Imported file has a header row
                            </label>
                            <span class="muted small" id="bulkHeaderHint">Column order: </span>
                        </div>
                    </div>

                    <!-- Server-side row errors from the last save attempt -->
                    <div class="bulk-errors" id="bulkErrorBox" hidden></div>

                    <!-- Spreadsheet Table View -->
                    <div id="bulkTableViewWrap" class="dt-wrap" style="max-height: 62vh;">
                        <table class="dt-table dt-standard bulk-grid-table" id="bulkTable">
                            <thead>
                                <tr id="bulkTheadRow"></tr>
                            </thead>
                            <tbody id="bulkTbody"></tbody>
                        </table>
                        <datalist id="bulkSchoolList"></datalist>
                    </div>

                    <!-- Plaintext Monospaced Editor View -->
                    <div id="bulkTextViewWrap" style="display:none; padding: 16px 20px;">
                        <div class="small muted" style="margin-bottom: 8px;">
                            Edit comma-separated values directly (wrap a value in "double quotes" if it contains a comma). Keep the first line as column headers for reference.
                        </div>
                        <textarea id="bulkTextarea" class="mono-editor" rows="15" oninput="syncBulkFromText()"></textarea>
                    </div>

                    <div class="bulk-footer-bar">
                        <span class="small muted" id="bulkStatusMsg">0 rows entered</span>
                        <button type="button" class="btn btn-outline btn-sm" onclick="resetBulkTable()">Clear Table</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Rubrics Section -->
        <section id="rubrics">
            <h1>Rubrics Section</h1>
            <div style="background: white; padding: 32px; border-radius: 12px; border: 1px solid #e5e7eb; height: 384px; display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center;">
                    <i data-lucide="clipboard-list" style="width: 64px; height: 64px; color: #e5e7eb; margin-bottom: 16px;"></i>
                    <p style="color: #6b7280;">Rubrics management system loading...</p>
                </div>
            </div>
        </section>

        <!-- Reports Section -->
        <section id="reports">
            <h1>Reports Section</h1>
            <div style="max-width: 100%; background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px;">
                <div style="border-left: 2px solid #e5e7eb; padding-left: 20px; margin-left: 10px;">
                    <div style="position: relative; margin-bottom: 24px;">
                        <div style="position: absolute; left: -27px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: var(--header-bg);"></div>
                        <p style="font-weight: 600; font-size: 14px;">System backup completed</p>
                        <p style="font-size: 12px; color: #6b7280;">2 hours ago &bull; Server #1</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Generic dialog (view details, edit forms, department manager) -->
    <div class="modal-backdrop" id="dynModal" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="dynTitle">
            <div class="modal-head">
                <h3 id="dynTitle"></h3>
                <button type="button" class="modal-close" data-close-modal aria-label="Close">&times;</button>
            </div>
            <div class="modal-body" id="dynBody"></div>
        </div>
    </div>

    <!-- Second dialog that stacks on top of the first (department / supervisor forms) -->
    <div class="modal-backdrop" id="subModal" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="subTitle">
            <div class="modal-head">
                <h3 id="subTitle"></h3>
                <button type="button" class="modal-close" data-close-modal aria-label="Close">&times;</button>
            </div>
            <div class="modal-body" id="subBody"></div>
        </div>
    </div>

    <div id="toastHost" aria-live="polite"></div>

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

</body>
</html>
