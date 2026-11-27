<?php
session_start();
$sessionRole = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
if (!in_array($sessionRole, ['officer', 'internship officer'], true)) {
    header('Location: ../login/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master List - RIAS Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="adminstyle.css">
</head>
<body>
    <header class="top-nav">
        <div class="logo-section">
            <div class="logo-rias">RIAS</div>
            <div class="logo-text"><span>Rams</span><span>Internship</span><span>Assessment System</span></div>
        </div>
        <div class="top-nav-right">
            <span class="role-text">INTERNSHIP OFFICER</span>
            <div class="bell-wrapper">
                <svg class="nav-icon" id="bellIcon" viewBox="0 0 24 24">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                </svg>
                <span id="notifBadge" class="notif-badge" style="display: none;">0</span>
            </div>
            <div class="notification-dropdown" id="notifDropdown">
                <div class="notif-header">Recent Notifications</div>
                <div id="noNotifMsg" style="padding: 15px; font-size: 13px; color: #999; text-align: center;">No new notifications</div>
            </div>
            <div class="notif-detail-panel" id="notifDetailPanel" style="display: none;">
                <h4 style="margin-bottom: 15px; color: #1e3b99; font-size: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Notification Details</h4>
                <div id="detailBody" style="font-size: 13px; color: #555; line-height: 1.6;"></div>
            </div>
            <details class="profile-menu">
                <summary class="profile-circle" aria-label="Account options"></summary>
                <div class="profile-panel">
                    <a class="logout-menu-item" href="../login/logout.php">Log out</a>
                </div>
            </details>
        </div>
    </header>

    <div class="layout-container">
        <aside class="sidebar" id="sidebar">
            <div class="nav-item" id="menuToggle">
                <svg viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                <span class="nav-label">Menu</span>
            </div>
            <div class="nav-item" onclick="window.location.href='_index.php'">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span class="nav-label">Home</span>
            </div>
            <div class="nav-item active" onclick="window.location.href='master_list.php'">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="nav-label">Master List</span>
            </div>
            <div class="nav-item" onclick="window.location.href='reports.php'">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span class="nav-label">Reports</span>
            </div>
            <div class="nav-item" onclick="window.location.href='progress.php'">
                <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span class="nav-label">Progress</span>
            </div>
            <div class="nav-item" onclick="window.location.href='mapping.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                <span class="nav-label">Mapping</span>
            </div>
            <div class="nav-item" onclick="window.location.href='requirements.php'">
               <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span class="nav-label">Intern Feedback and Submissions</span>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="date-container">
                    <div class="huge-date">Master List</div>
                </div>
                <div class="header-links">
                    <span>You previously logged in on <span id="login-date">Loading...</span> ⟲</span>
                    <a href="#" id="last-visited-link" style="display: none;">Go to your last visited page: ↩</a>
                </div>
            </div>

            <div class="panel" style="overflow: visible;">
                <div class="panel-header" style="flex-direction: column; align-items: flex-start; gap: 20px;">
                    <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                        <div class="panel-title">Master List Directory</div>
                    </div>
                    
                    <!-- Search and Filter Bar -->
                    <div class="search-filter-container">
                        <div class="search-box">
                            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" class="search-input" placeholder="Search directory...">
                        </div>
                        <button class="filter-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            Filter
                        </button>
                        <button class="add-user-btn" id="generateReportBtn" style="background: #27bda1;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px; vertical-align: middle; margin-top: -2px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Generate Report
                        </button>
                        <button class="add-user-btn" id="openAddUserModalBtn">+ Add User</button>
                    </div>

                    <!-- Role Tabs -->
                    <div class="tabs-container">
                        <div class="list-tab active" data-target="tab-student">Student Intern</div>
                        <div class="list-tab" data-target="tab-industry">Industry Partners</div>
                        <div class="list-tab" data-target="tab-exec">Executive Directors</div>
                        <div class="list-tab" data-target="tab-prog">Program Directors</div>
                        <div class="list-tab" data-target="tab-officer">Internship Officer</div>
                    </div>
                </div>
                
                <!-- Student Interns Tab -->
                <div id="tab-student" class="tab-content active table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>COMPANY</th><th>DEPARTMENT</th><th>EMAIL</th><th>STATUS</th><th style="width: 40px;"></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Industry Partners Tab -->
                <div id="tab-industry" class="tab-content table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>COMPANY</th><th>DEPARTMENT</th><th>EMAIL</th><th style="width: 40px;"></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Executive Directors Tab -->
                <div id="tab-exec" class="tab-content table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>SCHOOL</th><th>PROGRAM</th><th>EMAIL</th><th style="width: 40px;"></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Program Directors Tab -->
                <div id="tab-prog" class="tab-content table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>SCHOOL</th><th>PROGRAM</th><th>EMAIL</th><th style="width: 40px;"></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Internship Officer Tab -->
                <div id="tab-officer" class="tab-content table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>NO OF INTERNS</th><th>EMAIL</th><th style="width: 40px;"></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div class="modal-overlay" id="addUserModal">
        <div class="task-modal">
            <span class="modal-close" id="closeAddUserModal">&times;</span>
            <h2 style="font-weight: 500; color: #1e3b99; margin-bottom: 5px;">Add New User</h2>
            <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Fill in the details to add a user to the directory.</p>
            
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">User Role</label>
                    <select id="newUserRole" class="input-box">
                        <option value="student">Student Intern</option>
                        <option value="industry">Industry Partner</option>
                        <option value="exec">Executive Director</option>
                        <option value="prog">Program Director</option>
                        <option value="officer">Internship Officer</option>
                    </select>
                </div>
                <div style="flex: 1;" id="statusWrapper">
                    <label style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">Status</label>
                    <select id="newUserStatus" class="input-box">
                        <option value="Active">Active</option>
                        <option value="Waiting">Waiting</option>
                        <option value="Undeployed">Undeployed</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">Full Name</label>
                    <input type="text" id="newUserName" class="input-box" placeholder="e.g. John Doe">
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">Email Address</label>
                    <input type="email" id="newUserEmail" class="input-box" placeholder="e.g. user@apc.edu.ph">
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <div style="flex: 1;" id="compSchoolWrapper">
                    <label id="lblCompSchool" style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">Company</label>
                    <input type="text" id="newUserCompSchool" class="input-box" placeholder="e.g. TechCorp">
                </div>
                <div style="flex: 1;">
                    <label id="lblDeptProg" style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">Department</label>
                    <input type="text" id="newUserDeptProg" class="input-box" placeholder="e.g. BSIT">
                </div>
            </div>

            <div id="industryExtraFields" style="display: none; flex-direction: column; gap: 15px; margin-bottom: 20px;">
                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">Business Address</label>
                        <input type="text" id="newPartnerAddress" class="input-box" placeholder="e.g. 123 Tech Street">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">Business Hours</label>
                        <input type="text" id="newPartnerHours" class="input-box" placeholder="e.g. Mon-Fri, 9:00 AM - 6:00 PM">
                    </div>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">Company Logo (Upload Image)</label>
                    <input type="file" id="newPartnerLogoFile" accept="image/*" class="input-box" style="padding: 9px 15px; cursor: pointer;">
                </div>
            </div>

            <button class="save-btn" id="saveNewUserBtn" style="width: 100%;">Add User</button>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>
    <script src="adminscript.js"></script>
    <script>
        let editingRow = null;
        
        document.addEventListener('DOMContentLoaded', () => {
            // --- 1. Tab Switching Logic ---
            const tabs = document.querySelectorAll('.list-tab');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    
                    tab.classList.add('active');
                    document.getElementById(tab.getAttribute('data-target')).classList.add('active');
                    
                    filterActiveTable(); // Re-apply search filter if tab changes
                });
            });
            
            // --- 2. Search & Filter Logic ---
            const searchInput = document.querySelector('.search-input');
            const filterBtn = document.querySelector('.filter-btn');
            let sortAsc = true;
            
            function filterActiveTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const activeTable = document.querySelector('.tab-content.active .master-table tbody');
                if (!activeTable) return;
                
                const rows = activeTable.querySelectorAll('tr');
                rows.forEach(row => {
                    if (row.classList.contains('partner-details')) {
                        row.style.display = 'none'; // Close details when filtering
                    } else {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                        row.style.backgroundColor = ''; // Reset background styling
                    }
                });
            }
            
            searchInput.addEventListener('input', filterActiveTable);
            
            // Use the Filter button to sort alphabetically (A-Z, Z-A)
            filterBtn.addEventListener('click', () => {
                const activeTable = document.querySelector('.tab-content.active .master-table');
                if(!activeTable) return;
                
                const tbody = activeTable.querySelector('tbody');
                
                // Group items with their detailed rows so they move together
                const rowGroups = [];
                let currentGroup = null;
                
                Array.from(tbody.querySelectorAll('tr')).forEach(row => {
                    if (row.classList.contains('partner-details')) {
                        if(currentGroup) currentGroup.details = row;
                    } else {
                        currentGroup = { main: row, details: null };
                        rowGroups.push(currentGroup);
                    }
                });
                
                rowGroups.sort((a, b) => {
                    const textA = a.main.querySelector('td').textContent.toLowerCase();
                    const textB = b.main.querySelector('td').textContent.toLowerCase();
                    if(textA < textB) return sortAsc ? -1 : 1;
                    if(textA > textB) return sortAsc ? 1 : -1;
                    return 0;
                });
                
                sortAsc = !sortAsc;
                tbody.innerHTML = '';
                rowGroups.forEach(group => {
                    tbody.appendChild(group.main);
                    if (group.details) tbody.appendChild(group.details);
                });
                
                // Toggle UI string to indicate sort format
                filterBtn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg> Filter ` + (sortAsc ? "(A-Z)" : "(Z-A)");
            });

            // --- 3. Add User Modal Logic ---
            const addUserBtn = document.getElementById('openAddUserModalBtn');
            const addUserModal = document.getElementById('addUserModal');
            const closeAddUserModal = document.getElementById('closeAddUserModal');
            const saveNewUserBtn = document.getElementById('saveNewUserBtn');
            
            // Form fields
            const roleSelect = document.getElementById('newUserRole');
            const compSchoolWrapper = document.getElementById('compSchoolWrapper');
            const lblCompSchool = document.getElementById('lblCompSchool');
            const lblDeptProg = document.getElementById('lblDeptProg');
            const industryExtraFields = document.getElementById('industryExtraFields');
            const statusWrapper = document.getElementById('statusWrapper');
            const newUserStatus = document.getElementById('newUserStatus');
            const userDirectory = new Map();

            function appendCell(row, value) {
                const cell = document.createElement('td');
                cell.textContent = value || '';
                row.appendChild(cell);
                return cell;
            }

            function appendActions(row) {
                const cell = document.createElement('td');
                cell.innerHTML = '<div class="row-meatball"><button class="meatball-btn">⋮</button><div class="meatball-drop"><button class="edit-btn">Edit</button><button class="remove-btn" style="color: #e74c3c;">Remove</button></div></div>';
                row.appendChild(cell);
            }

            function appendStatus(row, value) {
                const cell = document.createElement('td');
                const status = document.createElement('span');
                status.textContent = value || '—';
                status.style.fontWeight = 'bold';
                if (value === 'Active') status.style.color = '#27bda1';
                else if (value === 'Waiting' || value === 'New Partner') status.style.color = '#2e96ff';
                else if (value === 'Undeployed' || value === 'Inactive Partner') status.style.color = '#ff8a8a';
                else status.style.color = '#777';
                cell.appendChild(status);
                row.appendChild(cell);
            }

            function renderPartnerDetails(user, row) {
                const detailsRow = document.createElement('tr');
                detailsRow.className = 'partner-details';
                detailsRow.style.display = 'none';
                const cell = document.createElement('td');
                cell.colSpan = 5;
                cell.style.cssText = 'background-color:#f9fafc;padding:20px;border-bottom:2px solid #eaeaea;';

                const content = document.createElement('div');
                content.style.cssText = 'display:flex;gap:25px;align-items:flex-start;';
                const logoBox = document.createElement('div');
                logoBox.style.cssText = 'width:80px;height:80px;background:#fff;border:1px dashed #ccc;border-radius:8px;display:flex;justify-content:center;align-items:center;font-size:10px;color:#999;font-weight:bold;overflow:hidden;';
                if (user.logo_path) {
                    const image = document.createElement('img');
                    image.src = '../../' + user.logo_path;
                    image.alt = 'Company logo';
                    image.style.cssText = 'max-width:100%;max-height:100%;';
                    logoBox.appendChild(image);
                } else {
                    logoBox.textContent = 'LOGO';
                }

                const details = document.createElement('div');
                details.style.cssText = 'flex:1;line-height:1.6;font-size:13px;color:#555;';
                const heading = document.createElement('h3');
                heading.textContent = user.company_name || 'Company Name';
                heading.style.cssText = 'margin-bottom:8px;color:#1e3b99;font-size:16px;';
                const grid = document.createElement('div');
                grid.style.cssText = 'display:grid;grid-template-columns:1fr 1fr;gap:10px;';
                [
                    ['Company Address:', user.business_address],
                    ['Company Email:', user.email],
                    ['Business Hours:', user.business_hours],
                    ['Status:', user.company_status]
                ].forEach(([label, value]) => {
                    const item = document.createElement('div');
                    const strong = document.createElement('b');
                    strong.textContent = label + ' ';
                    item.append(strong, document.createTextNode(value || '—'));
                    grid.appendChild(item);
                });
                details.append(heading, grid);
                content.append(logoBox, details);
                cell.appendChild(content);
                detailsRow.appendChild(cell);
                row.parentElement.appendChild(detailsRow);
            }

            function renderUser(user) {
                const tbody = document.querySelector(`#tab-${user.role_key} tbody`);
                if (!tbody) return;

                userDirectory.set(user.id, user);
                const row = document.createElement('tr');
                row.dataset.userId = user.id;
                row.dataset.roleKey = user.role_key;

                appendCell(row, user.name);
                if (user.role_key === 'student') {
                    appendCell(row, user.company_name);
                    appendCell(row, user.role_detail);
                    appendCell(row, user.email);
                    appendStatus(row, user.status);
                } else if (user.role_key === 'industry') {
                    row.className = 'partner-row';
                    row.style.cursor = 'pointer';
                    row.title = 'Click to view details';
                    appendCell(row, user.company_name);
                    appendCell(row, user.role_detail);
                    appendCell(row, user.email);
                } else if (user.role_key === 'exec' || user.role_key === 'prog') {
                    appendCell(row, user.school_name);
                    appendCell(row, user.role_detail);
                    appendCell(row, user.email);
                } else {
                    appendCell(row, user.intern_count || user.role_detail || '0');
                    appendCell(row, user.email);
                }
                appendActions(row);
                tbody.appendChild(row);

                if (user.role_key === 'industry') {
                    renderPartnerDetails(user, row);
                }
            }

            async function loadUsers() {
                const response = await fetch('manage_users.php?action=list');
                if (response.status === 403) {
                    window.location.replace('../login/login.php');
                    return;
                }
                const result = await response.json();
                if (!response.ok || !Array.isArray(result.users)) {
                    throw new Error(result.error || 'Could not load users.');
                }

                userDirectory.clear();
                document.querySelectorAll('.tab-content .master-table tbody').forEach(tbody => {
                    tbody.replaceChildren();
                });
                result.users.forEach(renderUser);
                filterActiveTable();
                setupExpandableRows();
            }

            loadUsers().catch(error => {
                console.error('Could not load IO user directory:', error);
                alert(error.message);
            });
            
            addUserBtn.addEventListener('click', () => {
                editingRow = null;
                roleSelect.disabled = false;
                document.querySelector('.task-modal h2').innerText = "Add New User";
                document.getElementById('saveNewUserBtn').innerText = "Add User";
                document.querySelectorAll('.task-modal .input-box').forEach(input => {
                    if(input.id !== 'newUserRole' && input.id !== 'newUserStatus') input.value = '';
                });
                document.getElementById('newUserRole').value = 'student';
                document.getElementById('newUserRole').dispatchEvent(new Event('change'));
                addUserModal.classList.add('active');
            });
            
            closeAddUserModal.addEventListener('click', () => {
                addUserModal.classList.remove('active');
                editingRow = null;
                roleSelect.disabled = false;
            });
            
            // Dynamically change labels depending on selected Role
            roleSelect.addEventListener('change', (e) => {
                const role = e.target.value;
                industryExtraFields.style.display = 'none'; // Hide by default
                statusWrapper.style.display = 'none';
                newUserStatus.innerHTML = '';
                
                if (role === 'student') {
                    statusWrapper.style.display = 'block';
                    newUserStatus.innerHTML = '<option value="Active">Active</option><option value="Waiting">Waiting</option><option value="Undeployed">Undeployed</option>';
                    compSchoolWrapper.style.display = 'block';
                    lblCompSchool.innerText = 'Company';
                    lblDeptProg.innerText = 'Department';
                } else if (role === 'officer') {
                    compSchoolWrapper.style.display = 'none';
                    lblDeptProg.innerText = 'No. of Interns';
                } else if (role === 'exec' || role === 'prog') {
                    compSchoolWrapper.style.display = 'block';
                    lblCompSchool.innerText = 'School';
                    lblDeptProg.innerText = 'Program';
                } else if (role === 'industry') {
                    statusWrapper.style.display = 'block';
                    newUserStatus.innerHTML = '<option value="New Partner">New Partner</option><option value="Active Partner">Active Partner</option><option value="Inactive Partner">Inactive Partner</option>';
                    compSchoolWrapper.style.display = 'block';
                    lblCompSchool.innerText = 'Company';
                    lblDeptProg.innerText = 'Department';
                    industryExtraFields.style.display = 'flex'; // Show extra fields
                } else {
                    compSchoolWrapper.style.display = 'block';
                    lblCompSchool.innerText = 'Company';
                    lblDeptProg.innerText = 'Department';
                }
            });
            
            // Save user records to the database.
            saveNewUserBtn.addEventListener('click', async () => {
                const role = roleSelect.value;
                const formData = new FormData();
                formData.append('action', editingRow ? 'update' : 'create');
                formData.append('role', role);
                formData.append('name', document.getElementById('newUserName').value.trim());
                formData.append('email', document.getElementById('newUserEmail').value.trim());
                formData.append('company_name', role === 'industry' || role === 'student'
                    ? document.getElementById('newUserCompSchool').value.trim()
                    : '');
                formData.append('school_name', role === 'exec' || role === 'prog'
                    ? document.getElementById('newUserCompSchool').value.trim()
                    : '');
                formData.append('role_detail', document.getElementById('newUserDeptProg').value.trim());
                formData.append('status', newUserStatus.value);
                formData.append('business_address', document.getElementById('newPartnerAddress').value.trim());
                formData.append('business_hours', document.getElementById('newPartnerHours').value.trim());
                if (editingRow) formData.append('user_id', editingRow.dataset.userId);

                const logoInput = document.getElementById('newPartnerLogoFile');
                if (logoInput.files && logoInput.files[0]) {
                    formData.append('logo', logoInput.files[0]);
                }

                const originalButtonText = saveNewUserBtn.textContent;
                saveNewUserBtn.disabled = true;
                saveNewUserBtn.textContent = 'Saving...';
                try {
                    const response = await fetch('manage_users.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.error || 'Could not save this user.');
                    }

                    await loadUsers();
                    editingRow = null;
                    addUserModal.classList.remove('active');
                    roleSelect.disabled = false;
                    document.getElementById('newUserName').value = '';
                    document.getElementById('newUserEmail').value = '';
                    document.getElementById('newUserCompSchool').value = '';
                    document.getElementById('newUserDeptProg').value = '';
                    document.getElementById('newPartnerAddress').value = '';
                    document.getElementById('newPartnerHours').value = '';
                    logoInput.value = '';
                    alert(result.message);
                } catch (error) {
                    alert(error.message);
                } finally {
                    saveNewUserBtn.disabled = false;
                    saveNewUserBtn.textContent = originalButtonText;
                }
            });

            // --- 4. Generate Report (Export to CSV) ---
            const generateReportBtn = document.getElementById('generateReportBtn');
            if (generateReportBtn) {
                generateReportBtn.addEventListener('click', () => {
                    const activeTabName = document.querySelector('.list-tab.active').innerText.trim().replace(/\s+/g, '_');
                    const activeTable = document.querySelector('.tab-content.active .master-table');
                    
                    if (!activeTable) {
                        alert("No data available to export.");
                        return;
                    }

                    let csv = [];
                    const rows = activeTable.querySelectorAll('tr');
                    
                    rows.forEach(row => {
                        if (row.style.display === 'none' || row.classList.contains('partner-details')) return; // Skip hidden and details
                        let rowData = [];
                        const cols = row.querySelectorAll('th, td');
                        cols.forEach((col, index) => {
                            if (index < cols.length - 1) { // Skip the last Action column
                                rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
                            }
                        });
                        csv.push(rowData.join(','));
                    });

                    const csvContent = csv.join('\n');
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = `${activeTabName}_Report.csv`;
                    link.click();
                });
            }
            
            // --- Row Meatball Toggle & Edit/Remove Logic ---
            document.addEventListener('click', (e) => {
                const isMeatball = e.target.closest('.meatball-btn');
                if (isMeatball) {
                    e.stopPropagation();
                    const drop = isMeatball.nextElementSibling;
                    const isActive = drop.classList.contains('active');
                    document.querySelectorAll('.meatball-drop').forEach(d => d.classList.remove('active'));
                    if (!isActive) {
                        // Calculate fixed position to prevent container overflow scrollbars
                        const rect = isMeatball.getBoundingClientRect();
                        drop.style.position = 'fixed';
                        drop.style.top = `${rect.bottom}px`;
                        drop.style.left = `${rect.right - 100}px`; // Approx width of dropdown (min-width: 100px)
                        drop.style.right = 'auto';
                        drop.classList.add('active');
                    }
                } else {
                    document.querySelectorAll('.meatball-drop').forEach(d => d.classList.remove('active'));
                }
                
                // Remove users by archiving them so related records remain intact.
                if (e.target.closest('.remove-btn')) {
                    const row = e.target.closest('tr');
                    if (!row || !confirm('Are you sure you want to remove this user?')) return;

                    const formData = new FormData();
                    formData.append('action', 'archive');
                    formData.append('user_id', row.dataset.userId);
                    fetch('manage_users.php', { method: 'POST', body: formData })
                        .then(async response => {
                            const result = await response.json();
                            if (!response.ok || !result.success) {
                                throw new Error(result.error || 'Could not archive this user.');
                            }
                            await loadUsers();
                            alert(result.message);
                        })
                        .catch(error => alert(error.message));
                    return;
                }

                // Populate the existing form for a database-backed update.
                if (e.target.closest('.edit-btn')) {
                    editingRow = e.target.closest('tr');
                    const user = userDirectory.get(Number(editingRow.dataset.userId));
                    if (!user) {
                        editingRow = null;
                        alert('Could not find this user in the loaded directory.');
                        return;
                    }

                    document.querySelector('.task-modal h2').innerText = 'Edit User';
                    saveNewUserBtn.innerText = 'Save Changes';
                    roleSelect.value = user.role_key;
                    roleSelect.disabled = true;
                    roleSelect.dispatchEvent(new Event('change'));
                    document.getElementById('newUserName').value = user.name;
                    document.getElementById('newUserEmail').value = user.email;
                    document.getElementById('newUserCompSchool').value =
                        user.role_key === 'exec' || user.role_key === 'prog'
                            ? user.school_name
                            : user.company_name;
                    document.getElementById('newUserDeptProg').value =
                        user.role_key === 'officer' ? user.intern_count || user.role_detail : user.role_detail;
                    newUserStatus.value = user.role_key === 'industry'
                        ? user.company_status
                        : user.status;
                    document.getElementById('newPartnerAddress').value = user.business_address;
                    document.getElementById('newPartnerHours').value = user.business_hours;
                    document.getElementById('newPartnerLogoFile').value = '';
                    addUserModal.classList.add('active');
                }
            });
            
            // Close dropdowns on scroll to prevent detached floating menus
            document.addEventListener('scroll', () => {
                document.querySelectorAll('.meatball-drop.active').forEach(d => d.classList.remove('active'));
            }, true); // Use capture phase 'true' to catch scrolls inside the nested table-responsive container

            // --- 5. Expandable Industry Partner Rows ---
            function setupExpandableRows() {
                const partnerRows = document.querySelectorAll('.partner-row');
                partnerRows.forEach(row => {
                    if (row.dataset.hasListener) return; // Prevent duplicate listeners
                    row.dataset.hasListener = "true";
                    
                    row.addEventListener('click', (e) => {
                        if (e.target.closest('.row-meatball')) return; // Ignore meatball clicks
                        const nextRow = row.nextElementSibling;
                        if (nextRow && nextRow.classList.contains('partner-details')) {
                            if (nextRow.style.display === 'none') {
                                nextRow.style.display = 'table-row';
                                row.style.backgroundColor = '#f4f6f9';
                            } else {
                                nextRow.style.display = 'none';
                                row.style.backgroundColor = '';
                            }
                        }
                    });
                });
            }
            setupExpandableRows();
        });
    </script>
<script src="../login/login-date.js"></script>
</body>
</html>