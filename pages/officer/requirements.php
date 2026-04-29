<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Internship Officer Dashboard</title>
    
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
            <!-- Notification Detail Panel -->
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
            <div class="nav-item active" onclick="window.location.href='_index.php'">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span class="nav-label">Home</span>
            </div>
            <div class="nav-item" onclick="window.location.href='master_list.php'">
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
                <span class="nav-label">Reports</span>
            </div>
        </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="header-left">
                <div class="date-text" id="currentDate">Wednesday, April 29, 2026</div>
                <div class="time-text" id="currentTime">09:30 AM</div>
            </div>
            <div class="header-right">
                <button class="btn-action" style="background-color: #29429c; color: white; padding: 8px 16px; border-radius: 5px; font-weight: 500;">
                    + Generate Clearance
                </button>
            </div>
        </div>

        <div class="stats-row" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;">
            <div class="panel stat-panel-accent">
                <div class="stat-label">Midterm Papers</div>
                <div class="stat-main">
                    <span class="stat-number">48</span>
                    <span class="stat-total">/ 50</span>
                </div>
                <div class="stat-subtext">96% Completion</div>
            </div>

            <div class="panel stat-panel-accent">
                <div class="stat-label">Final Papers</div>
                <div class="stat-main">
                    <span class="stat-number">12</span>
                    <span class="stat-total">/ 50</span>
                </div>
                <div class="stat-subtext">Collection in progress</div>
            </div>

            <div class="panel stat-panel-accent" style="border-left-color: #27bda1;">
                <div class="stat-label">Attendance Logs</div>
                <div class="stat-main">
                    <span class="stat-number">Active</span>
                    <span class=\"pulse-icon\">●</span>
                </div>
                <div class="stat-subtext">Link sent: 08:45 AM</div>
            </div>

            <div class="panel stat-panel-accent" style="border-left-color: #f1b347;">
                <div class="stat-label">Clearance Rate</div>
                <div class="stat-main">
                    <span class="stat-number">24%</span>
                </div>
                <div class="stat-subtext">12 Interns Cleared</div>
            </div>
        </div>

        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-bottom: 1px solid #eee;">
            <div class="panel-title" style="font-size: 15px; color: #1e3b99;"><b>Student Requirements Checklist</b></div>
            
            <div class="header-controls" style="display: flex; gap: 10px; align-items: center;">
                <select id="statusFilter" class="filter-select" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; color: #666;">
                    <option value="all">All Submissions</option>
                    <option value="midterm">Submitted Midterm</option>
                    <option value="final">Submitted Final</option>
                    <option value="attendance">Complete Attendance</option>
                    <option value="incomplete">Incomplete Only</option>
                </select>

                <div class="search-box" style="position: relative;">
                    <input type="text" id="studentSearch" placeholder="Search name..." 
                        style="padding: 6px 12px; width: 180px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; transition: width 0.3s ease;">
                </div>
            </div>
        </div>

            <div class="table-responsive" style="padding: 10px;">
                <table class="excel-mapping-table">
                    <thead>
                        <tr>
                            <th style="width: 250px;">Student Name</th>
                            <th>Attendance today</th>
                            <th>Midterm Paper</th>
                            <th>Final Paper</th>
                            <th>Clearance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 500;">DELA CRUZ, JUAN A.</td>
                            <td><b style="color: #ff0000;">Not Submitted</b></td>
                            <td><span class="status-tag tag-deployed">RECEIVED</span></td>
                            <td><span class="status-tag tag-waiting">PENDING</span></td>
                            <td><b style="color: #f1b347;">ONGOING</b></td>
                            <td style="text-align: center;">
                                <button class="notify-btn" 
                                        data-id="101" 
                                        data-name="DELA CRUZ, JUAN A." 
                                        onclick="notifyStudent(this)">
                                    Notify Student
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('studentSearch');
    const filterSelect = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('.excel-mapping-table tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const filterValue = filterSelect.value;

        tableRows.forEach(row => {
            const studentName = row.cells[0].textContent.toLowerCase();
            const midtermStatus = row.cells[1].textContent.toUpperCase();
            const finalStatus = row.cells[2].textContent.toUpperCase();
            const clearanceStatus = row.cells[4].textContent.toUpperCase();

            // Check if name matches search
            const matchesSearch = studentName.includes(searchTerm);

            // Check if row matches filter criteria
            let matchesFilter = true;
            if (filterValue === 'midterm') matchesFilter = midtermStatus.includes('RECEIVED') || midtermStatus.includes('SUBMITTED');
            if (filterValue === 'final') matchesFilter = finalStatus.includes('RECEIVED') || finalStatus.includes('SUBMITTED');
            if (filterValue === 'attendance') matchesFilter = row.cells[3].textContent.includes('600 / 600');
            if (filterValue === 'incomplete') matchesFilter = clearanceStatus.includes('INCOMPLETE') || clearanceStatus.includes('ONGOING');

            // Show or hide row
            if (matchesSearch && matchesFilter) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Listen for typing and dropdown changes
    searchInput.addEventListener('keyup', filterTable);
    filterSelect.addEventListener('change', filterTable);

    function notifyStudent(btn) {
    const studentName = btn.getAttribute('data-name');
    const studentId = btn.getAttribute('data-id'); // For future DB query

    // Change button state to show "Sending"
    const originalText = btn.innerText;
    btn.innerText = "Sending...";
    btn.disabled = true;
    btn.style.opacity = "0.7";

    // Simulate an AJAX/Database call
    setTimeout(() => {
        // Here is where you will eventually put your fetch() or $.ajax() 
        // to a PHP file (e.g., send_notification.php)
        
        btn.innerText = "Notified!";
        btn.style.backgroundColor = "#27bda1"; // Success Green
        btn.style.color = "white";
        
        console.log(`Notification sent to ${studentName} (ID: ${studentId})`);

        // Reset the button after 3 seconds
        setTimeout(() => {
            btn.innerText = originalText;
            btn.disabled = false;
            btn.style.opacity = "1";
            btn.style.backgroundColor = ""; // Resets to CSS default
            btn.style.color = "";
        }, 3000);
    }, 1000);
}
});
</script>