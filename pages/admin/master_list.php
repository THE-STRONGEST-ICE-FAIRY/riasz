<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master List - Admin Dashboard</title>
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
            <span class="role-text">ADMIN</span>
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
            <div class="profile-circle"></div>
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
            <div class="nav-item active" onclick="window.location.href='adminmasterlist.php'">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="nav-label">Master List</span>
            </div>
            <div class="nav-item" onclick="window.location.href='database.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                <span class="nav-label">Database</span>
            </div>
            <div class="nav-item" onclick="window.location.href='log_activity.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                <span class="nav-label">Log Activity</span>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="date-container">
                    <div class="huge-date">Master List</div>
                </div>
                <div class="header-links">
                    <span>You previously logged in on <span id="login-date"></span> ⟲</span>
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
                        <button class="add-user-btn" id="databaseViewBtn" style="background: #2e96ff;" onclick="window.location.href='database.php'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px; vertical-align: middle; margin-top: -2px;"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                            Database View
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
                        <tbody>
                            <tr>
                                <td>John Doe</td><td>TechCorp</td><td>BSIT</td><td>john.doe@student.apc.edu.ph</td>
                                <td><span style="color:#27bda1; font-weight:bold;">Active</span></td>
                                <td>
                                    <div class="row-meatball">
                                        <button class="meatball-btn">⋮</button>
                                        <div class="meatball-drop">
                                            <button class="edit-btn">Edit</button>
                                            <button class="remove-btn" style="color: #e74c3c;">Remove</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Jane Smith</td><td>Innovate Inc</td><td>BSCS</td><td>jane.s@student.apc.edu.ph</td>
                                <td><span style="color:#2e96ff; font-weight:bold;">Waiting</span></td>
                                <td>
                                    <div class="row-meatball">
                                        <button class="meatball-btn">⋮</button>
                                        <div class="meatball-drop">
                                            <button class="edit-btn">Edit</button>
                                            <button class="remove-btn" style="color: #e74c3c;">Remove</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Industry Partners Tab -->
                <div id="tab-industry" class="tab-content table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>COMPANY</th><th>DEPARTMENT</th><th>EMAIL</th><th style="width: 40px;"></th></tr></thead>
                        <tbody>
                            <tr class="partner-row" style="cursor: pointer;" title="Click to view details">
                                <td>Mark Johnson</td><td>Creative Studio</td><td>Design</td><td>mark.j@creativestudio.com</td>
                                <td>
                                    <div class="row-meatball">
                                        <button class="meatball-btn">⋮</button>
                                        <div class="meatball-drop">
                                            <button class="edit-btn">Edit</button>
                                            <button class="remove-btn" style="color: #e74c3c;">Remove</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="partner-details" style="display: none;">
                                <td colspan="5" style="background-color: #f9fafc; padding: 20px; border-bottom: 2px solid #eaeaea;">
                                    <div style="display: flex; gap: 25px; align-items: flex-start;">
                                        <div style="width: 80px; height: 80px; background: #fff; border: 1px dashed #ccc; border-radius: 8px; display: flex; justify-content: center; align-items: center; font-size: 10px; color: #999; font-weight: bold;">LOGO</div>
                                        <div style="flex: 1; line-height: 1.6; font-size: 13px; color: #555;">
                                            <h3 style="margin-bottom: 8px; color: #1e3b99; font-size: 16px;">Creative Studio</h3>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                                <div><b>Company Address:</b> 123 Innovation Drive, Tech City</div>
                                                <div><b>Company Email:</b> contact@creativestudio.com</div>
                                                <div><b>Business Hours:</b> Mon-Fri, 9:00 AM - 6:00 PM</div>
                                                <div><b>Status:</b> <span class="partner-status-text"><span style="color: #27bda1; font-weight: bold;">Active Partner</span></span></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Executive Directors Tab -->
                <div id="tab-exec" class="tab-content table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>SCHOOL</th><th>PROGRAM</th><th>EMAIL</th><th style="width: 40px;"></th></tr></thead>
                        <tbody>
                            <tr>
                                <td>Dr. Alan Turing</td><td>SoCIT</td><td>Computer Science</td><td>alant@apc.edu.ph</td>
                                <td>
                                    <div class="row-meatball">
                                        <button class="meatball-btn">⋮</button>
                                        <div class="meatball-drop">
                                            <button class="edit-btn">Edit</button>
                                            <button class="remove-btn" style="color: #e74c3c;">Remove</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Program Directors Tab -->
                <div id="tab-prog" class="tab-content table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>SCHOOL</th><th>PROGRAM</th><th>EMAIL</th><th style="width: 40px;"></th></tr></thead>
                        <tbody>
                            <tr>
                                <td>Grace Hopper</td><td>SoCIT</td><td>Information Technology</td><td>graceh@apc.edu.ph</td>
                                <td>
                                    <div class="row-meatball">
                                        <button class="meatball-btn">⋮</button>
                                        <div class="meatball-drop">
                                            <button class="edit-btn">Edit</button>
                                            <button class="remove-btn" style="color: #e74c3c;">Remove</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Internship Officer Tab -->
                <div id="tab-officer" class="tab-content table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>NO OF INTERNS</th><th>EMAIL</th><th style="width: 40px;"></th></tr></thead>
                        <tbody>
                            <tr>
                                <td>Ada Lovelace</td><td>130</td><td>adal@apc.edu.ph</td>
                                <td>
                                    <div class="row-meatball">
                                        <button class="meatball-btn">⋮</button>
                                        <div class="meatball-drop">
                                            <button class="edit-btn">Edit</button>
                                            <button class="remove-btn" style="color: #e74c3c;">Remove</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Richard Stallman</td><td>84</td><td>richards@apc.edu.ph</td>
                                <td>
                                    <div class="row-meatball">
                                        <button class="meatball-btn">⋮</button>
                                        <div class="meatball-drop">
                                            <button class="edit-btn">Edit</button>
                                            <button class="remove-btn" style="color: #e74c3c;">Remove</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
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
            
            addUserBtn.addEventListener('click', () => {
                editingRow = null;
                document.querySelector('.task-modal h2').innerText = "Add New User";
                document.getElementById('saveNewUserBtn').innerText = "Add User";
                document.querySelectorAll('.task-modal .input-box').forEach(input => {
                    if(input.id !== 'newUserRole' && input.id !== 'newUserStatus') input.value = '';
                });
                document.getElementById('newUserRole').value = 'student';
                document.getElementById('newUserRole').dispatchEvent(new Event('change'));
                addUserModal.classList.add('active');
            });
            
            closeAddUserModal.addEventListener('click', () => addUserModal.classList.remove('active'));
            
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
            
            // Insert data into active table
            saveNewUserBtn.addEventListener('click', async () => {
                const role = roleSelect.value;
                const name = document.getElementById('newUserName').value;
                const email = document.getElementById('newUserEmail').value;
                const compSchool = document.getElementById('newUserCompSchool').value;
                const deptProg = document.getElementById('newUserDeptProg').value;
                const status = document.getElementById('newUserStatus').value;
                const partnerAddress = document.getElementById('newPartnerAddress').value || 'Not specified';
                const partnerHours = document.getElementById('newPartnerHours').value || '9:00 AM - 6:00 PM';
                const logoInput = document.getElementById('newPartnerLogoFile');
                let partnerLogoHTML = 'LOGO';

                let statusHTML = '';
                if (role === 'student') {
                    if (status === 'Active') statusHTML = '<span style="color:#27bda1; font-weight:bold;">Active</span>';
                    else if (status === 'Waiting') statusHTML = '<span style="color:#2e96ff; font-weight:bold;">Waiting</span>';
                    else statusHTML = '<span style="color:#ff8a8a; font-weight:bold;">Undeployed</span>';
                } else if (role === 'industry') {
                    if (status === 'Active Partner') statusHTML = '<span style="color:#27bda1; font-weight:bold;">Active Partner</span>';
                    else if (status === 'Inactive Partner') statusHTML = '<span style="color:#ff8a8a; font-weight:bold;">Inactive Partner</span>';
                    else statusHTML = '<span style="color:#f1b347; font-weight:bold;">New Partner</span>';
                }

                // Read the uploaded image from the device
                if (logoInput && logoInput.files && logoInput.files[0]) {
                    const file = logoInput.files[0];
                    const base64Url = await new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = e => resolve(e.target.result);
                        reader.readAsDataURL(file);
                    });
                    partnerLogoHTML = `<img src="${base64Url}" style="max-width: 100%; max-height: 100%;" alt="Logo">`;
                }

                if(!name || !email) { alert("Name and Email are required!"); return; }

                if (editingRow) {
                    if (editingRow.classList.contains('partner-row')) {
                        const next = editingRow.nextElementSibling;
                        if (next && next.classList.contains('partner-details')) next.remove();
                    }
                    editingRow.remove();
                    editingRow = null;
                }

                const meatballHTML = `
                    <div class="row-meatball">
                        <button class="meatball-btn">⋮</button>
                        <div class="meatball-drop">
                            <button class="edit-btn">Edit</button>
                            <button class="remove-btn" style="color: #e74c3c;">Remove</button>
                        </div>
                    </div>`;

                const targetTbody = document.querySelector(`#tab-${role} tbody`);
                if(targetTbody) {
                    const tr = document.createElement('tr');
                    if(role === 'officer') {
                        tr.innerHTML = `<td>${name}</td><td>${deptProg || '0'}</td><td>${email}</td><td>${meatballHTML}</td>`;
                        targetTbody.appendChild(tr);
                    } else if (role === 'industry') {
                        tr.className = 'partner-row';
                        tr.style.cursor = 'pointer';
                        tr.innerHTML = `<td>${name}</td><td>${compSchool || '-'}</td><td>${deptProg || '-'}</td><td>${email}</td><td>${meatballHTML}</td>`;
                        targetTbody.appendChild(tr);
                        
                        const detailsTr = document.createElement('tr');
                        detailsTr.className = 'partner-details';
                        detailsTr.style.display = 'none';

                        detailsTr.innerHTML = `
                            <td colspan="5" style="background-color: #f9fafc; padding: 20px; border-bottom: 2px solid #eaeaea;">
                                <div style="display: flex; gap: 25px; align-items: flex-start;">
                                    <div style="width: 80px; height: 80px; background: #fff; border: 1px dashed #ccc; border-radius: 8px; display: flex; justify-content: center; align-items: center; font-size: 10px; color: #999; font-weight: bold; overflow: hidden;">${partnerLogoHTML}</div>
                                    <div style="flex: 1; line-height: 1.6; font-size: 13px; color: #555;">
                                        <h3 style="margin-bottom: 8px; color: #1e3b99; font-size: 16px;">${compSchool || 'Company Name'}</h3>
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                            <div><b>Company Address:</b> ${partnerAddress}</div>
                                            <div><b>Company Email:</b> ${email}</div>
                                            <div><b>Business Hours:</b> ${partnerHours}</div>
                                            <div><b>Status:</b> <span class="partner-status-text">${statusHTML}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </td>`;
                        targetTbody.appendChild(detailsTr);
                        setupExpandableRows(); // Reattach listener
                    } else if (role === 'student') {
                        tr.innerHTML = `<td>${name}</td><td>${compSchool || '-'}</td><td>${deptProg || '-'}</td><td>${email}</td><td>${statusHTML}</td><td>${meatballHTML}</td>`;
                        targetTbody.appendChild(tr);
                    } else {
                        tr.innerHTML = `<td>${name}</td><td>${compSchool || '-'}</td><td>${deptProg || '-'}</td><td>${email}</td><td>${meatballHTML}</td>`;
                        targetTbody.appendChild(tr);
                    }
                }

                // Clear Form & Close
                document.getElementById('newUserName').value = '';
                document.getElementById('newUserEmail').value = '';
                document.getElementById('newUserCompSchool').value = '';
                document.getElementById('newUserDeptProg').value = '';
                document.getElementById('newPartnerAddress').value = '';
                document.getElementById('newPartnerHours').value = '';
                if (document.getElementById('newPartnerLogoFile')) document.getElementById('newPartnerLogoFile').value = '';
                addUserModal.classList.remove('active');
                
                filterActiveTable(); // Update search constraints if needed
            });
            
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
                
                // Handle Remove
                if (e.target.closest('.remove-btn')) {
                    if (confirm("Are you sure you want to remove this user?")) {
                        const row = e.target.closest('tr');
                        if (row.classList.contains('partner-row')) {
                            const next = row.nextElementSibling;
                            if (next && next.classList.contains('partner-details')) next.remove();
                        }
                        row.remove();
                    }
                }
                
                // Handle Edit
                if (e.target.closest('.edit-btn')) {
                    editingRow = e.target.closest('tr');
                    const cols = editingRow.querySelectorAll('td');
                    const tabId = editingRow.closest('.tab-content').id;

                    document.querySelector('.task-modal h2').innerText = "Edit User";
                    document.getElementById('saveNewUserBtn').innerText = "Save Changes";
                    document.getElementById('newUserName').value = cols[0].innerText;
                    
                    if (tabId === 'tab-officer') {
                        document.getElementById('newUserRole').value = 'officer';
                        document.getElementById('newUserRole').dispatchEvent(new Event('change'));
                        document.getElementById('newUserDeptProg').value = cols[1].innerText;
                        document.getElementById('newUserEmail').value = cols[2].innerText;
                    } else if (tabId === 'tab-industry') {
                        document.getElementById('newUserRole').value = 'industry';
                        document.getElementById('newUserRole').dispatchEvent(new Event('change'));
                        document.getElementById('newUserCompSchool').value = cols[1].innerText;
                        document.getElementById('newUserDeptProg').value = cols[2].innerText;
                        document.getElementById('newUserEmail').value = cols[3].innerText;
                        
                        const nextRow = editingRow.nextElementSibling;
                        if (nextRow && nextRow.classList.contains('partner-details')) {
                            const statusSpan = nextRow.querySelector('.partner-status-text');
                            if (statusSpan) document.getElementById('newUserStatus').value = statusSpan.innerText.trim();
                        }
                        
                        document.getElementById('newPartnerAddress').value = '';
                        document.getElementById('newPartnerHours').value = '';
                    } else {
                        const roleVal = tabId === 'tab-exec' ? 'exec' : (tabId === 'tab-prog' ? 'prog' : 'student');
                        document.getElementById('newUserRole').value = roleVal;
                        document.getElementById('newUserRole').dispatchEvent(new Event('change'));
                        document.getElementById('newUserCompSchool').value = cols[1].innerText;
                        document.getElementById('newUserDeptProg').value = cols[2].innerText;
                        document.getElementById('newUserEmail').value = cols[3].innerText;
                        
                        if (roleVal === 'student' && cols[4]) {
                            document.getElementById('newUserStatus').value = cols[4].innerText.trim();
                        }
                    }
                    
                    document.getElementById('addUserModal').classList.add('active');
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
</body>
</html>