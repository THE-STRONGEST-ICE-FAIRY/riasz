<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Admin</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="datatable.js" defer></script>
    <script src="admin_scripts.js" defer></script>
    <link rel="stylesheet" href="admin_styles.css">
    <link rel="stylesheet" href="admin_masterlist.css">
</head>
<body>
    <header>
        <div class="logo-container">
            <a href="../login/login.php" class="logo-container">
				<img src="../../assets/header_apclogo2.png" alt="Logo">
			</a>
        </div>

        <div class="header-actions">
            <span class="admin-label">ADMIN</span>
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
                <button data-page="database" class="sidebar-item">
                    <div class="icon-container"><i data-lucide="database" style="width: 20px; height: 20px;"></i></div>
                    <span class="sidebar-text">Database</span>
                </button>
                <button data-page="log-activity" class="sidebar-item">
                    <div class="icon-container"><i data-lucide="history" style="width: 20px; height: 20px;"></i></div>
                    <span class="sidebar-text">Log Activity</span>
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
                    <button type="button" class="btn btn-primary" data-act="add-school">+ Add school</button>
                    <button type="button" class="btn btn-primary" data-act="add-program">+ Add program</button>
                    <button type="button" class="btn btn-primary" data-act="add-user">+ Add user</button>
                    <button type="button" class="btn btn-outline" data-ml-view="bulk-add">
                        <i data-lucide="table-2" style="width: 16px; height: 16px;"></i> Bulk Add
                    </button>
                </div>

                <div class="stats-grid four">
                    <div class="stat-card">
                        <p class="stat-label">Schools</p>
                        <p class="stat-value" id="statSchools">—</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Programs</p>
                        <p class="stat-value" id="statPrograms">—</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Users</p>
                        <p class="stat-value" id="statUsers">—</p>
                        <p class="stat-sub" id="statUsersSub">Loading…</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Schools needing setup</p>
                        <p class="stat-value" id="statAttention">—</p>
                        <p class="stat-sub" id="statAttentionSub">Loading…</p>
                    </div>
                </div>

                <div class="dash-grid">
                    <div class="panel">
                        <div class="panel-head">
                            <h3>Schools and programs</h3>
                            <button type="button" class="btn btn-outline btn-sm" data-ml-view="schools">View all</button>
                        </div>
                        <div id="homeSchools"></div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h3>Recently added users</h3>
                            <button type="button" class="btn btn-outline btn-sm" data-ml-view="users">View all</button>
                        </div>
                        <div id="homeUsers"></div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head"><h3>Users by role</h3></div>
                    <div id="roleBreakdown"></div>
                </div>
            </div>

            <!-- ===== Schools and programs page ===== -->
            <div class="ml-view" id="ml-schools">
                <div class="page-head">
                    <button type="button" class="btn btn-outline" data-ml-view="home">&larr; Back to Masterlist</button>
                    <h1>Schools and Programs</h1>
                    <div class="head-actions">
                        <button type="button" class="btn btn-primary" data-act="add-school">+ Add school</button>
                        <button type="button" class="btn btn-primary" data-act="add-program">+ Add program</button>
                        <button type="button" class="btn btn-outline" onclick="openBulkAddModal('schools')">
                            <i data-lucide="table-2" style="width: 15px; height: 15px;"></i> Bulk Add
                        </button>
                    </div>
                </div>
                <div id="schoolTableMount"></div>
            </div>

            <!-- ===== User masterlist page ===== -->
            <div class="ml-view" id="ml-users">
                <div class="page-head">
                    <button type="button" class="btn btn-outline" data-ml-view="home">&larr; Back to Masterlist</button>
                    <h1>User Masterlist</h1>
                    <div class="head-actions">
                        <button type="button" class="btn btn-primary" data-act="add-user">+ Add user</button>
                        <button type="button" class="btn btn-outline" onclick="openBulkAddModal('users')">
                            <i data-lucide="table-2" style="width: 15px; height: 15px;"></i> Bulk Add
                        </button>
                    </div>
                </div>
                <div id="userTableMount"></div>
            </div>

            <!-- ===== Bulk Add page ===== -->
            <div class="ml-view" id="ml-bulk-add">
                <div class="page-head">
                    <button type="button" class="btn btn-outline" data-ml-view="home">&larr; Back to Masterlist</button>
                    <h1>Bulk Add Entries</h1>
                    <div class="head-actions">
                        <div class="bulk-mode-toggle" role="tablist">
                            <button type="button" class="btn btn-sm btn-primary" id="bulkModeSchools" onclick="setBulkMode('schools')">Schools &amp; Programs</button>
                            <button type="button" class="btn btn-sm btn-outline" id="bulkModeUsers" onclick="setBulkMode('users')">Users</button>
                        </div>
                    </div>
                </div>

                <div class="panel bulk-container">
                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar-left">
                            <span class="bulk-mode-title" id="bulkTitle">Bulk Add Schools &amp; Programs</span>
                            <span class="muted small">Fields marked with <span class="required">*</span> are required.</span>
                        </div>
                        <div class="bulk-toolbar-right">
                            <button type="button" class="btn btn-outline btn-sm" id="bulkViewToggleBtn" onclick="toggleBulkTextEditor()">
                                <i data-lucide="file-code" style="width: 15px; height: 15px;"></i> Text Editor (CSV)
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('bulkFileInput').click()">
                                <i data-lucide="upload" style="width: 15px; height: 15px;"></i> Import CSV / Excel
                            </button>
                            <input type="file" id="bulkFileInput" accept=".csv, .txt, .tsv, .xlsx, .xls" style="display:none" onchange="handleBulkFileImport(event)">
                            <button type="button" class="btn btn-primary btn-sm" onclick="submitBulkData()">
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
                            <span class="muted small" id="bulkHeaderHint">Expected columns: School Name, Program Name</span>
                        </div>
                    </div>

                    <!-- Spreadsheet Table View -->
                    <div id="bulkTableViewWrap" class="dt-wrap" style="max-height: 62vh;">
                        <table class="dt-table dt-standard bulk-grid-table" id="bulkTable">
                            <thead>
                                <tr id="bulkTheadRow"></tr>
                            </thead>
                            <tbody id="bulkTbody"></tbody>
                        </table>
                    </div>

                    <!-- Plaintext Monospaced Editor View -->
                    <div id="bulkTextViewWrap" style="display:none; padding: 16px 20px;">
                        <div class="small muted" style="margin-bottom: 8px;" id="bulkCsvHelpText">
                            Edit comma-separated values directly. Keep the first line as column headers for reference.
                        </div>
                        <textarea id="bulkTextarea" class="mono-editor" rows="15" oninput="syncBulkFromText()"></textarea>
                    </div>

                    <div class="bulk-footer-bar">
                        <span class="small muted" id="bulkStatusMsg">0 valid rows ready</span>
                        <button type="button" class="btn btn-outline btn-sm" onclick="resetBulkTable()">Clear Table</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Database Section -->
        <section id="database">
            <h1>Resource Database</h1>
            <div style="background: white; padding: 32px; border-radius: 12px; border: 1px solid #e5e7eb; height: 384px; display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center;">
                    <i data-lucide="database" style="width: 64px; height: 64px; color: #e5e7eb; margin-bottom: 16px;"></i>
                    <p style="color: #6b7280;">Central database management system loading...</p>
                </div>
            </div>
        </section>

        <!-- Log Activity Section -->
        <section id="log-activity">
            <h1>Activity Logs</h1>
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

    <!-- Modals -->
    <div class="modal-backdrop" id="addSchoolModal" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addSchoolTitle">
            <div class="modal-head">
                <h3 id="addSchoolTitle">Add school</h3>
                <button type="button" class="modal-close" data-close-modal aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="schoolForm" class="stacked-form">
                    <label for="school_name_input">School name <span class="required">*</span></label>
                    <input type="text" id="school_name_input" name="school_name" placeholder="Enter school name" required>
                    
                    <div id="optionalProgramsContainer" style="margin-top: 4px;">
                        <label>Programs under this school (optional)</label>
                        <div class="optional-program-row">
                            <input type="text" name="optional_programs[]" placeholder="Program name (optional)">
                            <button type="button" class="btn btn-outline btn-sm" onclick="addOptionalProgramInput()" title="Add another program">+</button>
                        </div>
                    </div>

                    <div class="modal-checkbox-row">
                        <input type="checkbox" id="openUserAfterSchool" name="open_user_after" value="1">
                        <label for="openUserAfterSchool">Create executive director for this school immediately afterwards</label>
                    </div>

                    <input type="hidden" name="action" value="add_school">
                    
                    <div class="modal-links">
                        <a href="#" onclick="openBulkAddModal('schools'); return false;" class="modal-bulk-link">Open Bulk Add for Schools &amp; Programs &rarr;</a>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-outline" data-close-modal>Cancel</button>
                        <button type="submit" class="btn btn-primary">Add school</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="addProgramModal" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addProgramTitle">
            <div class="modal-head">
                <h3 id="addProgramTitle">Add program</h3>
                <button type="button" class="modal-close" data-close-modal aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="programForm" class="stacked-form">
                    <label for="program_name_input">Program name <span class="required">*</span></label>
                    <input type="text" id="program_name_input" name="program_name" placeholder="Enter program name" required>
                    
                    <label for="schoolDropdown">School <span class="required">*</span></label>
                    <select name="school_id" id="schoolDropdown" required>
                        <option value="">Loading schools...</option>
                    </select>

                    <div class="modal-checkbox-row">
                        <input type="checkbox" id="openUserAfterProgram" name="open_user_after" value="1">
                        <label for="openUserAfterProgram">Create program director for this program immediately afterwards</label>
                    </div>

                    <input type="hidden" name="action" value="add_program">

                    <div class="modal-links">
                        <a href="#" onclick="openBulkAddModal('schools'); return false;" class="modal-bulk-link">Open Bulk Add for Schools &amp; Programs &rarr;</a>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-outline" data-close-modal>Cancel</button>
                        <button type="submit" class="btn btn-primary">Add program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="addUserModal" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addUserTitle">
            <div class="modal-head">
                <h3 id="addUserTitle">Add user</h3>
                <button type="button" class="modal-close" data-close-modal aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="userForm" class="stacked-form" action="createuser.php" method="POST" onsubmit="handleFormSubmit(event)">
                    <label for="user_first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="user_first_name" name="user_first_name" required>

                    <label for="user_last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="user_last_name" name="user_last_name" required>

                    <label for="user_email">Email <span class="required">*</span></label>
                    <input type="email" id="user_email" name="user_email" required>

                    <label for="schooluser_given_id">Given ID <span class="required">*</span></label>
                    <input type="text" id="schooluser_given_id" name="schooluser_given_id" required>

                    <label for="user_role">Role <span class="required">*</span></label>
                    <select id="user_role" name="user_role" required>
                        <option value="officer">Officer</option>
                        <option value="program">Program Director</option>
                        <option value="executive">Executive Director</option>
                    </select>

                    <div id="schoolDropdownContainer" style="display: none;">
                        <label for="school_id">School <span class="required">*</span></label>
                        <select id="school_id" name="school_id" disabled></select>
                    </div>

                    <div id="programDropdownContainer" style="display: none;">
                        <label for="program_id">Program <span class="required">*</span></label>
                        <select id="program_id" name="program_id" disabled></select>
                    </div>

                    <div class="modal-links">
                        <a href="#" onclick="openBulkAddModal('users'); return false;" class="modal-bulk-link">Open Bulk Add for Users &rarr;</a>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-outline" data-close-modal>Cancel</button>
                        <input type="submit" value="Create User" class="btn btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Generic dialog (view details, edit, manage programs) -->
    <div class="modal-backdrop" id="dynModal" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="dynTitle">
            <div class="modal-head">
                <h3 id="dynTitle"></h3>
                <button type="button" class="modal-close" data-close-modal aria-label="Close">&times;</button>
            </div>
            <div class="modal-body" id="dynBody"></div>
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
