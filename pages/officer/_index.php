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
                <span class="nav-label">Intern Feedback and Submissions</span>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="date-container">
                    <div class="huge-date" id="real-time-date">03/12/2025</div>
                    <div class="live-time" id="real-time-clock"></div>
                </div>
                <div class="header-links">
                    <span>You previously logged in on <span id="login-date">Saturday, March 09, 2025</span> ⟲</span>
                    <a href="#" id="last-visited-link" style="display: none;">Go to your last visited page: Master List ↩</a>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card"><span class="stat-label">Total Users</span><span class="stat-value">214</span></div>
                <div class="stat-card"><span class="stat-label">Student Intern</span><span class="stat-value">130</span></div>
                <div class="stat-card"><span class="stat-label">Internship Officer</span><span class="stat-value">1</span></div>
                <div class="stat-card"><span class="stat-label">Program Director</span><span class="stat-value">36</span></div>
                <div class="stat-card"><span class="stat-label">Executive Director</span><span class="stat-value">5</span></div>
                <div class="stat-card"><span class="stat-label">Industry Partner</span><span class="stat-value">42</span></div>
            </div>

            <div class="panel" style="overflow: visible;">
                <div class="panel-header">
                    <div class="panel-title">Internship Calendar Events for the Academic Year <b id="display-acad-year">2024-2025</b></div>
                    <svg style="width: 20px; height: 20px; stroke: #1e3b99; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <table class="calendar-table">
                    <thead><tr><th>Date</th><th>Event</th></tr></thead>
                    <tbody id="calendar-events-tbody">
                    </tbody>
                </table>
                <div class="panel-footer">
                    <span class="action-btn" id="openTaskModalBtn">
                        <svg style="width: 16px; height: 16px; stroke: #1e3b99; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        Set Event / Task
                    </span>
                    <span class="action-btn" id="changeYearBtn">
                        <svg style="width: 16px; height: 16px; stroke: #1e3b99; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Change Academic Year
                        <div class="year-dropdown" id="yearDropdown">
                            <div style="margin-bottom: 10px; font-weight: bold; color: #333; font-size: 14px;">Set Academic Year</div>
                            <div style="margin-bottom: 10px;">
                                <label style="font-size: 11px; color: #666; display: block; margin-bottom: 3px;">Start Date</label>
                                <input type="date" id="acadStartDate" class="input-box" style="padding: 6px 10px;">
                            </div>
                            <div style="margin-bottom: 10px;">
                                <label style="font-size: 11px; color: #666; display: block; margin-bottom: 3px;">End Date</label>
                                <input type="date" id="acadEndDate" class="input-box" style="padding: 6px 10px;">
                            </div>
                            <button id="applyAcadYearBtn" class="add-btn" style="width: 100%; color: #1e3b99; font-weight: bold;">Apply</button>
                        </div>
                    </span>
                </div>
            </div>

            <div class="bottom-grid">
                <div class="panel" style="padding: 20px;">
                    <div style="color: #1e3b99; font-weight: bold; margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">INTERN1 <span style="font-size: 10px; color: #999;">December - March 2025</span></div>
                    <div style="display: flex; justify-content: space-between; text-align: center;">
                        <div><div style="font-size: 10px;"><span style="color: #27bda1;">●</span> Deployed</div><div style="font-size: 24px; color: #27bda1; font-weight: bold;">10</div></div>
                        <div><div style="font-size: 10px;"><span style="color: #2e96ff;">●</span> Waiting</div><div style="font-size: 24px; color: #2e96ff; font-weight: bold;">13</div></div>
                        <div><div style="font-size: 10px;"><span style="color: #ff8a8a;">●</span> Undeployed</div><div style="font-size: 24px; color: #ff8a8a; font-weight: bold;">3</div></div>
                        <div><div style="font-size: 10px;"><span style="color: #999;">●</span> Unlisted</div><div style="font-size: 24px; color: #999; font-weight: bold;">31</div></div>
                    </div>
                </div>
                <div class="panel" style="padding: 20px;">
                    <div style="color: #1e3b99; font-weight: bold; margin-bottom: 10px;">Progress</div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 5px;">Evaluation Forms (Industry Partner)</div>
                    <div style="background: #fdf5d3; padding: 10px; border-radius: 6px; display: flex; justify-content: space-around; text-align: center; margin-bottom: 10px;">
                        <div><div style="font-size: 10px; color: #2e7d32;">Total Submitted</div><div style="font-weight: bold;">40</div></div>
                        <div><div style="font-size: 10px; color: #c62828;">Not yet</div><div style="font-weight: bold; color: #c62828;">90</div></div>
                    </div>
                    <button id="openDetailedReportBtn" style="width: 100%; background: #e3f2fd; border: 1px solid #bbdefb; padding: 8px; border-radius: 4px; color: #1e88e5; margin-bottom: 10px; cursor: pointer; font-weight: 500;">Detailed Report</button>
                    <button style="width: 100%; background: #e8f5e9; border: 1px solid #c8e6c9; padding: 8px; border-radius: 4px; color: #2e7d32; cursor: pointer; font-weight: 500;">Notify Remaining</button>
                </div>
                <div class="panel" style="padding: 20px; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div id="internTotalTitle" style="color: #1e3b99; font-weight: bold; font-size: 20px;">Intern Data</div>
                        <label for="internDataUpload" style="background: #f0f2f5; color: #333; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; border: 1px solid #ddd; font-weight: bold;">Upload File</label>
                        <input type="file" id="internDataUpload" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" style="display: none;" />
                    </div>
                    <div id="pieChartContainer" style="flex: 1; min-height: 150px; display: flex; align-items: center; justify-content: center; position: relative;">
                        <canvas id="internPieChart" style="display: none; max-height: 160px;"></canvas>
                        <div id="chartPlaceholderText" style="color: #999; font-size: 12px; text-align: center; border: 1px dashed #ccc; width: 100%; padding: 30px 0; border-radius: 6px;">Upload a CSV/Excel file to generate a pie chart</div>
                    </div>
                    <div id="chartLegend" style="font-size: 12px; margin-top: 15px; line-height: 2;">
                        <!-- Dynamic legend will be rendered here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="taskModalOverlay">
        <div class="task-modal">
            <span class="modal-close" id="closeModalBtn">&times;</span>
            
            <div style="position: relative;">
                <input type="text" class="input-box" id="taskTitle" placeholder="Enter title...">
                <span style="position: absolute; right: 10px; top: 12px; font-size: 11px; color: #999;">0/30</span>
            </div>

            <div class="tabs-row">
                <button class="tab-btn inactive"><svg style="width: 16px; height: 16px; stroke: currentColor; fill: none;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> EVENT</button>
                <button class="tab-btn active"><svg style="width: 16px; height: 16px; stroke: currentColor; fill: none;" viewBox="0 0 24 24"><path d="M9 21h6"/><path d="M12 21v-4"/><path d="M12 3a5 5 0 0 0-5 5c0 2 1.5 3.5 2 5.5v1.5a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-1.5c.5-2 2-3.5 2-5.5a5 5 0 0 0-5-5z"/></svg> TASK</button>
            </div>

            <select class="select-box" style="width: 100%; margin-top: 5px;">
                <option>Evaluation Forms (Industry Partners)</option>
                <option>Accomplishment Reports</option>
            </select>
            
            <div><button class="add-btn">Add task +</button></div>

            <div class="form-row">
                <svg class="form-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <input type="date" class="input-box" id="taskDate" style="flex: 2; cursor:pointer;">
                <input type="time" class="input-box" id="taskStartTime" style="flex: 1; cursor:pointer;">
                <span>-</span>
                <input type="time" class="input-box" id="taskEndTime" style="flex: 1; cursor:pointer;">
            </div>

            <div class="form-row">
                <svg class="form-icon" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <select class="select-box" id="taskRole" style="flex: 1;">
                    <option value="">Select Role</option>
                    <option>Industry Partner</option>
                    <option>Student Intern</option>
                    <option>Executive Director</option>
                    <option>Program Director</option>
                </select>
                <select class="select-box" id="taskSchool" style="flex: 1;">
                    <option value="">Select School</option>
                    <option value="SoA">School of Architecture</option>
                    <option value="SoMA">School of Multimedia and Arts</option>
                    <option value="SoE">School of Engineering</option>
                    <option value="SoCIT">School of Computing and Information Technologies</option>
                    <option value="SoM">School Of Management</option>
                </select>
                <span>-</span>
                <select class="select-box" id="taskProgram" style="flex: 1;">
                    <option value="">All Programs</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; margin-left: 25px;">
                <input type="checkbox" id="notifyAll" style="width: 18px; height: 18px;">
                <label for="notifyAll" style="font-size: 14px;">Notify All</label>
            </div>

            <div class="form-row" style="margin-left: 25px;">
                <select class="select-box" style="flex: 1;"><option>Email</option><option>System</option></select>
                <select class="select-box" style="flex: 2;"><option>Wednesday, March 21</option></select>
                <span style="color: #999; cursor: pointer;">&times;</span>
            </div>
            
            <div style="margin-left: 25px;"><button class="add-btn">Add Notification +</button></div>

            <div class="form-row">
                <svg class="form-icon" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                <input type="text" class="input-box" placeholder="Additional details..." style="border: none; border-bottom: 1px solid #ccc; border-radius: 0;">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 10px;">
                <button class="add-btn" style="display: flex; gap: 5px; align-items: center;">
                    <span style="color: #27bda1;">●</span> <svg style="width: 14px; height: 14px; stroke: #333; fill: none;" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <button class="save-btn" id="saveTaskBtn">Save</button>
            </div>
        </div>
    </div>

    <!-- Detailed Report Modal -->
    <div class="modal-overlay" id="detailedReportModal">
        <div class="task-modal" style="max-width: 600px;">
            <span class="modal-close" id="closeDetailedReportModal">&times;</span>
            <h2 style="font-weight: 500; color: #1e3b99; margin-bottom: 5px;">Detailed Report</h2>
            <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Submission status for Evaluation Forms by Industry Partners.</p>
            
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="master-table">
                    <thead>
                        <tr>
                            <th>SUPERVISOR</th>
                            <th>COMPANY</th>
                            <th>EMAIL</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="position: relative;">
                                <span class="supervisor-name" style="color: #1e3b99; cursor: pointer; text-decoration: underline;">Mark Johnson</span>
                                <div class="tag-drop" style="display: none; position: absolute; background: white; border: 1px solid #eaeaea; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 6px; z-index: 100;">
                                    <button class="tag-supervisor-btn" style="padding: 10px 15px; border: none; background: none; font-size: 13px; cursor: pointer; color: #333; width: 100%; text-align: left; white-space: nowrap; transition: background 0.2s;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 5px; margin-top: -2px;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg> 
                                        Tag Supervisor
                                    </button>
                                </div>
                            </td>
                            <td>Creative Studio</td>
                            <td>mark.j@creativestudio.com</td>
                            <td><span style="color:#27bda1; font-weight:bold;">Submitted</span></td>
                        </tr>
                        <tr>
                            <td style="position: relative;">
                                <span class="supervisor-name" style="color: #1e3b99; cursor: pointer; text-decoration: underline;">Sarah Connor</span>
                                <div class="tag-drop" style="display: none; position: absolute; background: white; border: 1px solid #eaeaea; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 6px; z-index: 100;">
                                    <button class="tag-supervisor-btn" style="padding: 10px 15px; border: none; background: none; font-size: 13px; cursor: pointer; color: #333; width: 100%; text-align: left; white-space: nowrap; transition: background 0.2s;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 5px; margin-top: -2px;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg> 
                                        Tag Supervisor
                                    </button>
                                </div>
                            </td>
                            <td>TechCorp</td>
                            <td>sarah.c@techcorp.com</td>
                            <td><span style="color:#e74c3c; font-weight:bold;">Not Yet</span></td>
                        </tr>
                        <tr>
                            <td style="position: relative;">
                                <span class="supervisor-name" style="color: #1e3b99; cursor: pointer; text-decoration: underline;">Alan Turing</span>
                                <div class="tag-drop" style="display: none; position: absolute; background: white; border: 1px solid #eaeaea; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 6px; z-index: 100;">
                                    <button class="tag-supervisor-btn" style="padding: 10px 15px; border: none; background: none; font-size: 13px; cursor: pointer; color: #333; width: 100%; text-align: left; white-space: nowrap; transition: background 0.2s;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 5px; margin-top: -2px;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg> 
                                        Tag Supervisor
                                    </button>
                                </div>
                            </td>
                            <td>Innovate Inc</td>
                            <td>alant@innovate.inc</td>
                            <td><span style="color:#27bda1; font-weight:bold;">Submitted</span></td>
                        </tr>
                        <tr>
                            <td style="position: relative;">
                                <span class="supervisor-name" style="color: #1e3b99; cursor: pointer; text-decoration: underline;">Ada Lovelace</span>
                                <div class="tag-drop" style="display: none; position: absolute; background: white; border: 1px solid #eaeaea; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 6px; z-index: 100;">
                                    <button class="tag-supervisor-btn" style="padding: 10px 15px; border: none; background: none; font-size: 13px; cursor: pointer; color: #333; width: 100%; text-align: left; white-space: nowrap; transition: background 0.2s;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 5px; margin-top: -2px;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg> 
                                        Tag Supervisor
                                    </button>
                                </div>
                            </td>
                            <td>Analytical Engine</td>
                            <td>ada@analytical.com</td>
                            <td><span style="color:#e74c3c; font-weight:bold;">Not Yet</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>

    <!-- Load Chart.js for Pie Graph & SheetJS for parsing CSV/Excel -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="adminscript.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reportModal = document.getElementById('detailedReportModal');
            document.getElementById('openDetailedReportBtn')?.addEventListener('click', () => reportModal?.classList.add('active'));
            document.getElementById('closeDetailedReportModal')?.addEventListener('click', () => reportModal?.classList.remove('active'));
            reportModal?.addEventListener('click', (e) => e.target === reportModal && reportModal.classList.remove('active'));

            // Tag Supervisor Logic
            document.addEventListener('click', (e) => {
                const isName = e.target.closest('.supervisor-name');
                
                // Toggle Dropdown when clicking name
                if (isName) {
                    e.stopPropagation();
                    const drop = isName.nextElementSibling;
                    const isActive = drop && drop.classList.contains('active');
                    
                    // Close any other open dropdowns
                    document.querySelectorAll('.tag-drop').forEach(d => {
                        d.style.display = 'none';
                        d.classList.remove('active');
                    });
                    
                    if (!isActive && drop) {
                        // Use Fixed positioning so it escapes the table-responsive container clipping
                        const rect = isName.getBoundingClientRect();
                        drop.style.position = 'fixed';
                        drop.style.top = `${rect.bottom + 5}px`;
                        drop.style.left = `${rect.left}px`; 
                        drop.style.display = 'block';
                        drop.classList.add('active');
                    }
                } else {
                    // Close everywhere else
                    document.querySelectorAll('.tag-drop').forEach(d => {
                        d.style.display = 'none';
                        d.classList.remove('active');
                    });
                }
                
                // Handle Tag Action
                const isTagBtn = e.target.closest('.tag-supervisor-btn');
                if (isTagBtn) {
                    const supervisorName = isTagBtn.closest('td').querySelector('.supervisor-name').innerText;
                    alert(`You have successfully tagged ${supervisorName}!`);
                }
            });

            // Close dropdowns if the user scrolls the inner container
            document.querySelector('.table-responsive')?.addEventListener('scroll', () => {
                document.querySelectorAll('.tag-drop').forEach(d => {
                    d.style.display = 'none';
                    d.classList.remove('active');
                });
            }, { capture: true });
        });
    </script>
</body>
</html>