<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master List - RIAS Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Programdistyle.css">
</head>
<body>
    <header class="top-nav">
        <div class="logo-section">
            <div class="logo-rias">RIAS</div>
            <div class="logo-text"><span>Rams</span><span>Internship</span><span>Assessment System</span></div>
        </div>
        <div class="top-nav-right">
            <span class="role-text">PROGRAM DIRECTOR</span>
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
            <div class="nav-item active" onclick="window.location.href='master_list.php'">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="nav-label">Master List</span>
            </div>
            <div class="nav-item" onclick="window.location.href='mapping.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                <span class="nav-label">Mapping</span>
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
                        <button class="add-user-btn" id="generateReportBtn" style="background: #27bda1;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px; vertical-align: middle; margin-top: -2px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Generate Report
                        </button>
                    </div>

                    <!-- Role Tabs -->
                    <div class="tabs-container">
                        <div class="list-tab active" data-target="tab-student">Student Intern</div>
                        <div class="list-tab" data-target="tab-industry">Industry Partners</div>
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
                                <td><button class="view-info-btn" style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 5px 10px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500;">View Info</button></td>
                            </tr>
                            <tr>
                                <td>Jane Smith</td><td>Innovate Inc</td><td>BSCS</td><td>jane.s@student.apc.edu.ph</td>
                                <td><span style="color:#2e96ff; font-weight:bold;">Waiting</span></td>
                                <td><button class="view-info-btn" style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 5px 10px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500;">View Info</button></td>
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
                                <td><button class="view-info-btn" style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 5px 10px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500;">View Info</button></td>
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

                <!-- Internship Officer Tab -->
                <div id="tab-officer" class="tab-content table-responsive">
                    <table class="master-table">
                        <thead><tr><th>NAME</th><th>NO OF INTERNS</th><th>EMAIL</th><th style="width: 40px;"></th></tr></thead>
                        <tbody>
                            <tr>
                                <td>Ada Lovelace</td><td>130</td><td>adal@apc.edu.ph</td>
                                <td><button class="view-info-btn" style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 5px 10px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500;">View Info</button></td>
                            </tr>
                            <tr>
                                <td>Richard Stallman</td><td>84</td><td>richards@apc.edu.ph</td>
                                <td><button class="view-info-btn" style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 5px 10px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500;">View Info</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <!-- View Info Modal -->
    <div class="modal-overlay" id="viewInfoModal">
        <div class="task-modal" style="max-width: 450px;">
            <span class="modal-close" id="closeViewInfoModal">&times;</span>
            <h2 style="font-weight: 500; color: #1e3b99; margin-bottom: 5px;">User Information</h2>
            <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Detailed information about the selected user.</p>
            
            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    <span style="font-weight: bold; color: #555; font-size: 13px;">Name:</span>
                    <span id="infoName" style="color: #333; font-size: 13px; font-weight: 500;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    <span style="font-weight: bold; color: #555; font-size: 13px;">School / Department:</span>
                    <span id="infoSchool" style="color: #333; font-size: 13px;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    <span style="font-weight: bold; color: #555; font-size: 13px;">Job Role:</span>
                    <span id="infoRole" style="color: #333; font-size: 13px;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    <span style="font-weight: bold; color: #555; font-size: 13px;">Company:</span>
                    <span id="infoCompany" style="color: #333; font-size: 13px;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    <span style="font-weight: bold; color: #555; font-size: 13px;">Supervisor:</span>
                    <span id="infoSupervisor" style="color: #333; font-size: 13px;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    <span style="font-weight: bold; color: #555; font-size: 13px;">Status:</span>
                    <span id="infoStatus" style="color: #333; font-size: 13px; font-weight: bold;">-</span>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>
    <script src="script.js"></script>
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
            
            // --- View Info Logic ---
            const viewInfoModal = document.getElementById('viewInfoModal');
            const closeViewInfoModal = document.getElementById('closeViewInfoModal');

            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('view-info-btn')) {
                    const row = e.target.closest('tr');
                    const cols = row.querySelectorAll('td');
                    const tabId = row.closest('.tab-content').id;

                    let name = cols[0].innerText;
                    let company = '-';
                    let school = '-';
                    let status = 'Active';
                    let jobRole = 'Not Specified';
                    let supervisor = 'Not Assigned'; // Placeholder supervisor logic

                    if (tabId === 'tab-student') {
                        company = cols[1].innerText;
                        school = cols[2].innerText;
                        status = cols[4].innerText.trim();
                        jobRole = 'Student Intern';
                    } else if (tabId === 'tab-industry') {
                        company = cols[1].innerText;
                        school = cols[2].innerText;
                        jobRole = 'Industry Partner';
                    } else if (tabId === 'tab-officer') {
                        school = 'Asia Pacific College';
                        jobRole = 'Internship Officer';
                    }

                    document.getElementById('infoName').innerText = name;
                    document.getElementById('infoSchool').innerText = school;
                    document.getElementById('infoCompany').innerText = company;
                    document.getElementById('infoRole').innerText = jobRole;
                    document.getElementById('infoSupervisor').innerText = supervisor;
                    
                    const infoStatusEl = document.getElementById('infoStatus');
                    infoStatusEl.innerText = status;
                    if(status === 'Active') infoStatusEl.style.color = '#27bda1';
                    else if(status === 'Waiting') infoStatusEl.style.color = '#2e96ff';
                    else infoStatusEl.style.color = '#ff8a8a';

                    viewInfoModal.classList.add('active');
                }
            });
            
            if (closeViewInfoModal) {
                closeViewInfoModal.addEventListener('click', () => viewInfoModal.classList.remove('active'));
            }
            if (viewInfoModal) {
                viewInfoModal.addEventListener('click', (e) => {
                    if (e.target === viewInfoModal) viewInfoModal.classList.remove('active');
                });
            }
            
            // --- 5. Expandable Industry Partner Rows ---
            function setupExpandableRows() {
                const partnerRows = document.querySelectorAll('.partner-row');
                partnerRows.forEach(row => {
                    if (row.dataset.hasListener) return; // Prevent duplicate listeners
                    row.dataset.hasListener = "true";
                    
                    row.addEventListener('click', (e) => {
                        if (e.target.closest('.view-info-btn')) return; // Ignore View Info clicks
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