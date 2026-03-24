<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master List - RIAS Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="IPstyle.css">
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
            <div class="profile-circle"></div>
        </div>
    </header>

    <div class="layout-container">
        <aside class="sidebar" id="sidebar">
            <div class="nav-item" id="menuToggle">
                <svg viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                <span class="nav-label">Menu</span>
            </div>
            <div class="nav-item" onclick="window.location.href='IP.php'">
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
                        <div class="panel-title">Student Intern Master List Directory</div>
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
                    </div>

                
                <!-- Student Interns Tab -->
                <div id="tab-student" class="tab-content active table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>COMPANY</th><th>DEPARTMENT</th><th></th><th>SCHOOL PROGRAM</th><th>EMAIL</th><th>STATUS</th></tr></thead>
                        <tbody>
                            <tr><td>John Doe</td><td>TechCorp</td><td>IT</td><td></td><td>BSIT</td><td>john.doe@student.apc.edu.ph</td><td class="status-complete">Complete</td></tr>
                            <tr><td>Jane Smith</td><td>Innovate Inc</td><td>Front Person</td><td></td><td>BSCS</td><td>jane.s@student.apc.edu.ph</td><td class="status-incomplete">Incomplete</td></tr>
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
            
            <div>
                <label style="font-size: 12px; font-weight: bold; color: #555; display: block; margin-bottom: 5px;">User Role</label>
                <select id="newUserRole" class="input-box" style="margin-bottom: 15px;">
                    <option value="student">Student Intern</option>
                    <option value="industry">Industry Partner</option>
                    <option value="exec">Executive Director</option>
                    <option value="prog">Program Director</option>
                    <option value="officer">Internship Officer</option>
                </select>
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

            <button class="save-btn" id="saveNewUserBtn" style="width: 100%;">Add User</button>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>
    <script src="adminscript.js"></script>
    <script>
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
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
            
            searchInput.addEventListener('input', filterActiveTable);
            
            // Use the Filter button to sort alphabetically (A-Z, Z-A)
            filterBtn.addEventListener('click', () => {
                const activeTable = document.querySelector('.tab-content.active .master-table');
                if(!activeTable) return;
                
                const tbody = activeTable.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                rows.sort((a, b) => {
                    const textA = a.querySelector('td').textContent.toLowerCase();
                    const textB = b.querySelector('td').textContent.toLowerCase();
                    if(textA < textB) return sortAsc ? -1 : 1;
                    if(textA > textB) return sortAsc ? 1 : -1;
                    return 0;
                });
                
                sortAsc = !sortAsc;
                rows.forEach(row => tbody.appendChild(row));
                
                // Toggle UI string to indicate sort format
                filterBtn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg> Filter ` + (sortAsc ? "(A-Z)" : "(Z-A)");
            });

            // --- 3. Add User Modal Logic ---
            const addUserBtn = document.querySelector('.add-user-btn');
            const addUserModal = document.getElementById('addUserModal');
            const closeAddUserModal = document.getElementById('closeAddUserModal');
            const saveNewUserBtn = document.getElementById('saveNewUserBtn');
            
            // Form fields
            const roleSelect = document.getElementById('newUserRole');
            const compSchoolWrapper = document.getElementById('compSchoolWrapper');
            const lblCompSchool = document.getElementById('lblCompSchool');
            const lblDeptProg = document.getElementById('lblDeptProg');
            
            addUserBtn.addEventListener('click', () => addUserModal.classList.add('active'));
            closeAddUserModal.addEventListener('click', () => addUserModal.classList.remove('active'));
            
            // Dynamically change labels depending on selected Role
            roleSelect.addEventListener('change', (e) => {
                const role = e.target.value;
                if (role === 'officer') {
                    compSchoolWrapper.style.display = 'none';
                    lblDeptProg.innerText = 'No. of Interns';
                } else if (role === 'exec' || role === 'prog') {
                    compSchoolWrapper.style.display = 'block';
                    lblCompSchool.innerText = 'School';
                    lblDeptProg.innerText = 'Program';
                } else {
                    compSchoolWrapper.style.display = 'block';
                    lblCompSchool.innerText = 'Company';
                    lblDeptProg.innerText = 'Department';
                }
            });
            
            // Insert data into active table
            saveNewUserBtn.addEventListener('click', () => {
                const role = roleSelect.value;
                const name = document.getElementById('newUserName').value;
                const email = document.getElementById('newUserEmail').value;
                const compSchool = document.getElementById('newUserCompSchool').value;
                const deptProg = document.getElementById('newUserDeptProg').value;

                if(!name || !email) { alert("Name and Email are required!"); return; }

                const targetTbody = document.querySelector(`#tab-${role} tbody`);
                if(targetTbody) {
                    const tr = document.createElement('tr');
                    if(role === 'officer') {
                        tr.innerHTML = `<td>${name}</td><td>${deptProg || '0'}</td><td>${email}</td>`;
                    } else {
                        tr.innerHTML = `<td>${name}</td><td>${compSchool || '-'}</td><td>${deptProg || '-'}</td><td>${email}</td>`;
                    }
                    targetTbody.appendChild(tr);
                }

                // Clear Form & Close
                document.getElementById('newUserName').value = '';
                document.getElementById('newUserEmail').value = '';
                document.getElementById('newUserCompSchool').value = '';
                document.getElementById('newUserDeptProg').value = '';
                addUserModal.classList.remove('active');
                
                filterActiveTable(); // Update search constraints if needed
            });
        });
    </script>
</body>
</html>