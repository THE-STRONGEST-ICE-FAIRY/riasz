<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Admin Dashboard</title>
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
            <div class="nav-item" onclick="window.location.href='master_list.php'">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="nav-label">Master List</span>
            </div>
            <div class="nav-item" onclick="window.location.href='database.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                <span class="nav-label">Database</span>
            </div>
            <div class="nav-item active" onclick="window.location.href='log_activity.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                <span class="nav-label">Log Activity</span>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="date-container">
                    <div class="huge-date">Activity Logs</div>
                </div>
                <div class="header-links">
                    <span>You previously logged in on <span id="login-date"></span> ⟲</span>
                    <a href="#" id="last-visited-link" style="display: none;">Go to your last visited page: ↩</a>
                </div>
            </div>

             <!-- Logs Panel -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title-bar">
                        <div class="panel-title">
                            System Event Audit Trail
                            <span class="backend-tag">Backend Integration Ready</span>
                        </div>
                        <div style="font-size: 13px; color: #666;">
                            Total Recorded Logs: <strong id="totalLogsCount">0</strong>
                        </div>
                    </div>
                    
                    <!-- Controls Bar -->
                    <div class="search-filter-container">
                        <div class="search-box">
                            <svg class="search-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" id="logSearchInput" class="search-input" placeholder="Search by user, event action, IP address or category...">
                        </div>
                        
                        <!-- Filter & Sort Controls -->
                        <button class="filter-btn" id="sortOrderBtn" title="Toggle Sort Direction">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            <span id="sortLabel">Newest First</span>
                        </button>

                        <button class="recent-btn" id="recentlyCreatedBtn" title="Filter items created within last 24h">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Recently Created (24h)
                        </button>

                        <button class="export-btn" id="exportLogsBtn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Export JSON
                        </button>

                        <button class="add-log-btn" id="openAddLogModalBtn">+ Simulate Log</button>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="tabs-container">
                        <div class="list-tab active" data-role="all">All Activity</div>
                        <div class="list-tab" data-role="student">Student Interns</div>
                        <div class="list-tab" data-role="industry">Industry Partners</div>
                        <div class="list-tab" data-role="prog">Program Directors</div>
                        <div class="list-tab" data-role="officer">Internship Officers</div>
                        <div class="list-tab" data-role="admin">System / Admin</div>
                    </div>
                </div>

                <!-- Activity Log Table -->
                <div class="table-responsive">
                    <table class="master-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action Event</th>
                                <th>Category</th>
                                <th>IP Address</th>
                                <th style="width: 50px; text-align: center;">Details</th>
                            </tr>
                        </thead>
                        <tbody id="activityLogTableBody">
                            <!-- Dynamically Generated Logs -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Simulate Log Entry Modal -->
    <div class="modal-overlay" id="addLogModal">
        <div class="task-modal">
            <button class="modal-close" id="closeAddLogModal">&times;</button>
            <h3 style="color: #1e3b99; font-size: 18px;">Simulate Activity Log</h3>
            <p style="color: #666; font-size: 13px;">Add a mock log entry for backend test payload rendering.</p>
            
            <div style="display: flex; gap: 12px;">
                <div style="flex: 1;">
                    <label style="font-size: 11px; font-weight: bold; color: #555; display: block; margin-bottom: 4px;">User Name</label>
                    <input type="text" id="newLogUser" class="input-box" value="John Doe">
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 11px; font-weight: bold; color: #555; display: block; margin-bottom: 4px;">Role</label>
                    <select id="newLogRole" class="input-box">
                        <option value="student">Student Intern</option>
                        <option value="industry">Industry Partner</option>
                        <option value="prog">Program Director</option>
                        <option value="officer">Internship Officer</option>
                        <option value="admin">System / Admin</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="font-size: 11px; font-weight: bold; color: #555; display: block; margin-bottom: 4px;">Action / Event Description</label>
                <input type="text" id="newLogAction" class="input-box" placeholder="e.g. Uploaded Monthly Progress Report">
            </div>

            <div style="display: flex; gap: 12px;">
                <div style="flex: 1;">
                    <label style="font-size: 11px; font-weight: bold; color: #555; display: block; margin-bottom: 4px;">Category Module</label>
                    <select id="newLogCategory" class="input-box">
                        <option value="Master List">Master List</option>
                        <option value="Assessment">Assessment</option>
                        <option value="Database">Database</option>
                        <option value="Authentication">Authentication</option>
                        <option value="System">System</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 11px; font-weight: bold; color: #555; display: block; margin-bottom: 4px;">IP Address</label>
                    <input type="text" id="newLogIP" class="input-box" value="192.168.1.102">
                </div>
            </div>

            <button class="save-btn" id="saveLogBtn">Create Log Entry</button>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>
    <script src="adminscript.js"></script>
    
    <!-- JavaScript Application Logic -->
    <script>
        // Sample Mock Data for Backend Developers to view structure & bind API endpoints
        const initialMockLogs = [
             {
                id: "LOG-1009",
                timestamp: "2026-11-05 14:10:15",
                user: "Grace Hopper",
                role: "prog",
                action: "Mapped Student Outcome (SO-03: Professional Ethics) to Internship Rubric",
                category: "Outcome Mapping",
                ip: "192.168.1.80",
                payload: { program: "BSCS", outcome_id: "SO-03", mapped_rubric_id: "RUB-2026-MIDTERM", target_score: "85%" }
            },
            {
                id: "LOG-1008",
                timestamp: "2026-11-05 13:15:20",
                user: "Ada Lovelace",
                role: "officer",
                action: "Published New Student Evaluation Form (SY 2026-2027)",
                category: "Rubrics & Evaluations",
                ip: "192.168.1.45",
                payload: { created_by: "adal@apc.edu.ph", form_title: "Student Internship Final Assessment", total_criteria: 12 }
            },
            {
                id: "LOG-1007",
                timestamp: "2026-11-05 11:42:05",
                user: "Ada Lovelace",
                role: "officer",
                action: "Updated Grading Rubric for Midterm Internship Assessment",
                category: "Rubrics & Evaluations",
                ip: "192.168.1.45",
                payload: { rubric_id: "RUB-2026-MIDTERM", modified_fields: ["weights", "performance_indicators"] }
            },
            {
                id: "LOG-1006",
                timestamp: "2026-11-05 10:05:00",
                user: "Dr. Alan Turing",
                role: "prog",
                action: "Linked Program Learning Outcomes (PLO-01) to Industry Feedback Form",
                category: "Outcome Mapping",
                ip: "192.168.1.88",
                payload: { director: "alant@apc.edu.ph", school: "SoCIT", mapped_plo: ["PLO-01", "PLO-04"] }
            },
            {
                id: "LOG-1005",
                timestamp: "2026-11-05 09:10:00",
                user: "Creative Studio Admin",
                role: "industry",
                action: "Completed Performance Evaluation for Intern Jane Smith",
                category: "Assessment",
                ip: "202.125.80.4",
                payload: { company: "Creative Studio", intern_name: "Jane Smith", rating: "Exceeds Expectations" }
            },
            {
                id: "LOG-1004",
                timestamp: "2026-11-04 16:20:11",
                user: "John Doe",
                role: "student",
                action: "Uploaded Weekly Journal Entry #8",
                category: "Assessment",
                ip: "112.198.70.88",
                payload: { student_id: "2023-10011", week: 8, status: "Pending Review" }
            },
            {
                id: "LOG-1003",
                timestamp: "2026-11-04 14:05:30",
                user: "Jane Smith",
                role: "student",
                action: "Submitted Self-Evaluation Survey",
                category: "Assessment",
                ip: "110.54.221.14",
                payload: { student_id: "2023-10042", form: "Self_Eval_2026.pdf" }
            },
            {
                id: "LOG-1002",
                timestamp: "2026-11-02 08:30:00",
                user: "System Admin",
                role: "admin",
                action: "Configured User Role Permissions for Program Directors",
                category: "System",
                ip: "192.168.1.1",
                payload: { permission_scope: "Outcome Mapping Module", granted_roles: ["prog", "exec"] }
            }
        ];

        let logsData = [...initialMockLogs];
        let currentRoleFilter = 'all';
        let isRecentlyCreatedOnly = false;
        let isNewestFirst = true;

        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Toggle Logic
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('expanded');
            });

            // Set Copyright Year
            document.getElementById('current-year').innerText = new Date().getFullYear();

            // Render Initial Data Table
            renderLogsTable();

            // Setup Event Listeners
            setupTabFilters();
            setupSearchAndControls();
            setupModalLogic();
        });

        // Format Timestamp into Readable Display
        function formatDisplayTimestamp(dateStr) {
            const date = new Date(dateStr.replace(/-/g, '/'));
            if (isNaN(date.getTime())) return dateStr;
            return date.toLocaleString('en-US', {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }

        // Render Logs to DOM Table
        function renderLogsTable() {
            const tbody = document.getElementById('activityLogTableBody');
            const searchVal = document.getElementById('logSearchInput').value.toLowerCase().trim();
            tbody.innerHTML = '';

            // Filter logic
            let filtered = logsData.filter(log => {
                // Search term matching
                const matchesSearch = 
                    log.user.toLowerCase().includes(searchVal) ||
                    log.action.toLowerCase().includes(searchVal) ||
                    log.ip.toLowerCase().includes(searchVal) ||
                    log.category.toLowerCase().includes(searchVal);

                // Role Tab matching
                let matchesRole = true;
                if (currentRoleFilter === 'eval') {
                    matchesRole = log.category === 'Assessment';
                } else if (currentRoleFilter !== 'all') {
                    matchesRole = log.role === currentRoleFilter;
                }

                // "Recently Created" toggle (Past 24 hours from Nov 05, 2026 baseline)
                let matchesRecent = true;
                if (isRecentlyCreatedOnly) {
                    const logTime = new Date(log.timestamp.replace(/-/g, '/')).getTime();
                    const nowTime = new Date("2026/11/05 13:35:00").getTime();
                    const diffHours = (nowTime - logTime) / (1000 * 60 * 60);
                    matchesRecent = diffHours <= 24 && diffHours >= 0;
                }

                return matchesSearch && matchesRole && matchesRecent;
            });

            // Sort logic
            filtered.sort((a, b) => {
                const timeA = new Date(a.timestamp.replace(/-/g, '/')).getTime();
                const timeB = new Date(b.timestamp.replace(/-/g, '/')).getTime();
                return isNewestFirst ? timeB - timeA : timeA - timeB;
            });

            document.getElementById('totalLogsCount').innerText = filtered.length;

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #888;">
                            No activity log entries found matching the active criteria.
                        </td>
                    </tr>`;
                return;
            }

            filtered.forEach(log => {
                const tr = document.createElement('tr');
                tr.className = 'log-row';

                let badgeClass = 'badge-system';
                if (log.role === 'student') badgeClass = 'badge-student';
                else if (log.role === 'industry') badgeClass = 'badge-industry';
                else if (log.role === 'admin') badgeClass = 'badge-admin';
                else if (log.role === 'officer') badgeClass = 'badge-officer';

                tr.innerHTML = `
                    <td class="timestamp-text">${formatDisplayTimestamp(log.timestamp)}</td>
                    <td><strong>${log.user}</strong></td>
                    <td><span class="role-badge ${badgeClass}">${log.role}</span></td>
                    <td style="color: #222; font-weight: 500;">${log.action}</td>
                    <td><span class="category-chip">${log.category}</span></td>
                    <td style="font-family: monospace; color: #666;">${log.ip}</td>
                    <td style="text-align: center;">
                        <div class="row-meatball">
                            <button class="meatball-btn" onclick="toggleMeatballMenu(event, this)">⋮</button>
                            <div class="meatball-drop">
                                <button onclick="toggleDetailRow('${log.id}')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View Payload
                                </button>
                                <button onclick="copyLogEntry('${log.id}')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    Copy Raw Data
                                </button>
                            </div>
                        </div>
                    </td>
                `;

                // Detail Payload Drawer Row
                const detailTr = document.createElement('tr');
                detailTr.id = `detail-${log.id}`;
                detailTr.className = 'detail-row';
                detailTr.innerHTML = `
                    <td colspan="7" style="padding: 15px 20px;">
                        <div style="margin-bottom: 6px; font-size: 12px; font-weight: bold; color: #475569;">
                            JSON Payload Payload Inspection [Log ID: ${log.id}]
                        </div>
                        <div class="json-box">${JSON.stringify(log, null, 2)}</div>
                    </td>
                `;

                tbody.appendChild(tr);
                tbody.appendChild(detailTr);
            });
        }

        // Tab Filter Switching
        function setupTabFilters() {
            const tabs = document.querySelectorAll('.list-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    currentRoleFilter = tab.getAttribute('data-role');
                    renderLogsTable();
                });
            });
        }

        // Search, Sort & "Recently Created" Button Handles
        function setupSearchAndControls() {
            document.getElementById('logSearchInput').addEventListener('input', renderLogsTable);

            // Sort button
            const sortBtn = document.getElementById('sortOrderBtn');
            sortBtn.addEventListener('click', () => {
                isNewestFirst = !isNewestFirst;
                document.getElementById('sortLabel').innerText = isNewestFirst ? "Newest First" : "Oldest First";
                renderLogsTable();
            });

            // "Recently Created" filter toggle
            const recentBtn = document.getElementById('recentlyCreatedBtn');
            recentBtn.addEventListener('click', () => {
                isRecentlyCreatedOnly = !isRecentlyCreatedOnly;
                recentBtn.classList.toggle('active', isRecentlyCreatedOnly);
                renderLogsTable();
            });

            // Export JSON Logs handler
            document.getElementById('exportLogsBtn').addEventListener('click', () => {
                const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(logsData, null, 2));
                const downloadAnchor = document.createElement('a');
                downloadAnchor.setAttribute("href", dataStr);
                downloadAnchor.setAttribute("download", `RIAS_Activity_Logs_${new Date().toISOString().slice(0,10)}.json`);
                document.body.appendChild(downloadAnchor);
                downloadAnchor.click();
                downloadAnchor.remove();
            });
        }

        // Meatball Menu toggle helper
        function toggleMeatballMenu(event, element) {
            event.stopPropagation();
            const drop = element.nextElementSibling;
            const isActive = drop.classList.contains('active');
            document.querySelectorAll('.meatball-drop').forEach(d => d.classList.remove('active'));
            if (!isActive) drop.classList.add('active');
        }

        // Close dropdowns on document click
        document.addEventListener('click', () => {
            document.querySelectorAll('.meatball-drop').forEach(d => d.classList.remove('active'));
        });

        // Toggle Payload Inspector Drawer
        function toggleDetailRow(logId) {
            const detailRow = document.getElementById(`detail-${logId}`);
            if (detailRow) {
                detailRow.classList.toggle('active');
            }
        }

        // Copy Log Data to Clipboard
        function copyLogEntry(logId) {
            const item = logsData.find(l => l.id === logId);
            if (item) {
                const dummy = document.createElement("textarea");
                document.body.appendChild(dummy);
                dummy.value = JSON.stringify(item, null, 2);
                dummy.select();
                document.execCommand("copy");
                document.body.removeChild(dummy);
                alert(`Log entry ${logId} copied to clipboard!`);
            }
        }

        // Modal Logic for Adding Simulated Logs
        function setupModalLogic() {
            const modal = document.getElementById('addLogModal');
            document.getElementById('openAddLogModalBtn').addEventListener('click', () => {
                modal.classList.add('active');
            });
            document.getElementById('closeAddLogModal').addEventListener('click', () => {
                modal.classList.remove('active');
            });

            document.getElementById('saveLogBtn').addEventListener('click', () => {
                const user = document.getElementById('newLogUser').value || 'Anonymous';
                const role = document.getElementById('newLogRole').value;
                const action = document.getElementById('newLogAction').value || 'Performed standard activity';
                const category = document.getElementById('newLogCategory').value;
                const ip = document.getElementById('newLogIP').value || '127.0.0.1';

                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');

                const newEntry = {
                    id: `LOG-${Math.floor(1000 + Math.random() * 9000)}`,
                    timestamp: `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`,
                    user,
                    role,
                    action,
                    category,
                    ip,
                    payload: { note: "Simulated log created by frontend template" }
                };

                logsData.unshift(newEntry);
                modal.classList.remove('active');
                renderLogsTable();
            });
        }
    </script>
</body>
</html>