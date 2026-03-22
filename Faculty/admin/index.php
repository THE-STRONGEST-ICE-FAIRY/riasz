<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Internship Officer Dashboard</title>
    <style>
        /* --- Base & Reset --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; flex-direction: column; min-height: 100vh; overflow-x: hidden; }
        button { cursor: pointer; border: none; background: none; }
        
        /* --- Top Navigation --- */
        .top-nav { background-color: #f1b347; height: 70px; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; color: white; z-index: 20; position: relative; }
        .logo-section { display: flex; align-items: center; gap: 15px; }
        .logo-rias { font-family: 'Times New Roman', Times, serif; font-size: 36px; font-weight: bold; letter-spacing: 2px; }
        .logo-text { display: flex; flex-direction: column; font-size: 11px; letter-spacing: 3px; text-transform: uppercase; font-weight: 600; }
        .top-nav-right { display: flex; align-items: center; gap: 25px; position: relative; }
        .role-text { font-size: 18px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
        .nav-icon { width: 24px; height: 24px; fill: white; cursor: pointer; }
        .profile-circle { width: 40px; height: 40px; background-color: white; border-radius: 50%; cursor: pointer; }

        /* --- Notifications Dropdown --- */
        .notification-dropdown { display: none; position: absolute; top: 60px; right: 50px; background: white; width: 300px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #ddd; z-index: 100; flex-direction: column; }
        .notification-dropdown.active { display: flex; }
        .notif-header { padding: 15px; border-bottom: 1px solid #eee; font-weight: bold; color: #333; }
        .notif-item { padding: 15px; border-bottom: 1px solid #eee; font-size: 13px; color: #555; cursor: pointer; transition: background 0.2s; }
        .notif-item:hover { background: #f9f9f9; }
        .notif-item:last-child { border-bottom: none; }

        /* --- Layout & Sidebar --- */
        .layout-container { display: flex; flex: 1; position: relative; }
        .sidebar { background-color: #353b41; width: 70px; display: flex; flex-direction: column; padding-top: 20px; gap: 10px; transition: width 0.3s ease; z-index: 15; overflow: hidden; white-space: nowrap; }
        .sidebar.expanded { width: 220px; }
        
        .nav-item { display: flex; align-items: center; padding: 15px 23px; cursor: pointer; color: #aeb3b7; transition: 0.2s; }
        .nav-item:hover, .nav-item.active { background-color: rgba(255,255,255,0.05); color: white; }
        .nav-item svg { width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 1.5; min-width: 24px; }
        .nav-label { margin-left: 20px; font-size: 14px; opacity: 0; transition: opacity 0.3s; }
        .sidebar.expanded .nav-label { opacity: 1; }

        /* --- Main Content --- */
        .main-content { flex: 1; padding: 30px 40px; overflow-y: auto; transition: margin-left 0.3s; }
        
        /* Header / Date Section */
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .date-container { display: flex; align-items: baseline; gap: 15px; }
        .huge-date { font-size: 42px; font-weight: 300; color: #222; }
        .live-time { font-size: 18px; color: #666; font-weight: 400; }
        .header-links { font-size: 13px; color: #555; display: flex; gap: 15px; }
        .header-links a { color: #555; text-decoration: none; border-bottom: 1px solid #555; padding-bottom: 2px; }

        /* Stats Row */
        .stats-row { display: flex; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); padding: 20px 30px; justify-content: space-between; margin-bottom: 30px; }
        .stat-card { display: flex; flex-direction: column; gap: 5px; }
        .stat-label { font-size: 12px; font-weight: 600; color: #111; }
        .stat-value { font-size: 24px; font-weight: bold; color: #1e3b99; }

        /* Panels Shared Styles */
        .panel { background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border: 1px solid #eaeaea; margin-bottom: 30px; overflow: hidden; position: relative; }
        .panel-header { padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .panel-title { font-size: 18px; font-weight: 300; color: #333; }
        .panel-title b { font-size: 28px; font-weight: 600; margin-left: 10px; transition: color 0.3s; }

        /* Calendar Table */
        .calendar-table { width: 100%; border-collapse: collapse; }
        .calendar-table th, .calendar-table td { padding: 15px 20px; text-align: left; font-size: 14px; }
        .calendar-table th { font-weight: 600; border-top: 1px solid #eaeaea; border-bottom: 1px solid #eaeaea; }
        .calendar-table tr:nth-child(1) td { background-color: #fdf5d3; }
        .calendar-table tr:nth-child(2) td { background-color: #ffffff; }
        .calendar-table tr:nth-child(3) td { background-color: #f0ebf8; }

        .panel-footer { padding: 15px 20px; display: flex; justify-content: space-between; font-size: 13px; color: #1e3b99; font-weight: 500; border-top: 1px solid #eaeaea; position: relative; }
        .panel-footer .action-btn { display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 5px 10px; border-radius: 4px; transition: background 0.2s; }
        .panel-footer .action-btn:hover { background: #f0f4ff; }

        /* Academic Year Dropdown */
        .year-dropdown { display: none; position: absolute; bottom: 100%; right: 20px; background: white; border: 1px solid #ccc; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 6px; z-index: 10; padding: 5px 0; min-width: 150px; }
        .year-dropdown.active { display: block; }
        .year-option { padding: 10px 15px; cursor: pointer; transition: 0.2s; color: #333; }
        .year-option:hover { background: #f0f4ff; color: #1e3b99; }

        /* Bottom Section Grid */
        .bottom-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        
        /* Modals Overlay */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(2px); }
        .modal-overlay.active { display: flex; }

        /* Set Task/Event Modal (Based on image_9a0003.png) */
        .task-modal { background: white; width: 500px; border-radius: 12px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; display: flex; flex-direction: column; gap: 15px; }
        .modal-close { position: absolute; top: 15px; right: 15px; font-size: 20px; color: #999; cursor: pointer; }
        .modal-close:hover { color: #333; }
        
        .input-box { border: 1px solid #ccc; padding: 12px 15px; border-radius: 6px; font-size: 14px; width: 100%; outline: none; transition: border-color 0.2s; }
        .input-box:focus { border-color: #1e3b99; }
        
        .tabs-row { display: flex; gap: 10px; margin-top: 5px; }
        .tab-btn { padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 13px; display: flex; align-items: center; gap: 8px; border: 1px solid transparent; }
        .tab-btn.inactive { background: #dcdedf; color: #777; }
        .tab-btn.active { background: #29429c; color: white; box-shadow: 0 4px 10px rgba(41, 66, 156, 0.3); }

        .form-row { display: flex; gap: 10px; align-items: center; }
        .form-icon { width: 18px; height: 18px; stroke: #333; fill: none; stroke-width: 2; margin-right: 5px; }
        .select-box { padding: 10px; border: 1px solid #ccc; border-radius: 6px; background: white; font-size: 13px; flex: 1; outline: none; }
        
        .add-btn { background: #f0f2f5; border: 1px solid #ddd; padding: 8px 12px; border-radius: 6px; font-size: 13px; cursor: pointer; }
        .add-btn:hover { background: #e4e6e9; }

        .save-btn { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; padding: 12px; border-radius: 6px; font-size: 16px; font-weight: bold; width: 150px; align-self: flex-end; margin-top: 10px; transition: 0.2s; }
        .save-btn:hover { background: #c8e6c9; }

        /* Footer */
        .footer { background-color: #29429c; color: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; z-index: 10; }
        .footer-links a { color: white; text-decoration: none; margin-right: 20px; }
        .footer-links a:hover { text-decoration: underline; }
        .footer-copy a { color: white; text-decoration: underline; }
    </style>
</head>
<body>

    <header class="top-nav">
        <div class="logo-section">
            <div class="logo-rias">RIAS</div>
            <div class="logo-text"><span>Rams</span><span>Internship</span><span>Assessment System</span></div>
        </div>
        <div class="top-nav-right">
            <span class="role-text">INTERNSHIP OFFICER</span>
            <svg class="nav-icon" id="bellIcon" viewBox="0 0 24 24">
                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
            </svg>
            <div class="notification-dropdown" id="notifDropdown">
                <div class="notif-header">Recent Notifications</div>
                <div class="notif-item"><strong>Task Added:</strong> Complete evaluation for School of Engineering.</div>
                <div class="notif-item"><strong>Event Updated:</strong> Internship consultation moved to Dec 16.</div>
                <div class="notif-item"><strong>System:</strong> Academic year rolled over to 2024-2025.</div>
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
            <div class="nav-item">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span class="nav-label">Home</span>
            </div>
            <div class="nav-item active">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="nav-label">Master List</span>
            </div>
            <div class="nav-item">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span class="nav-label">Reports</span>
            </div>
            <div class="nav-item">
                <svg viewBox="0 0 24 24"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                <span class="nav-label">Log Activity</span>
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
                    <a href="#">Go to your last visited page: Master List ↩</a>
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

            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Internship Calendar Events for the Academic Year <b id="display-acad-year">2024-2025</b></div>
                    <svg style="width: 20px; height: 20px; stroke: #1e3b99; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <table class="calendar-table">
                    <thead><tr><th>Date</th><th>Event</th></tr></thead>
                    <tbody>
                        <tr><td>September 1</td><td>INTERN2 End</td></tr>
                        <tr><td>August 30</td><td>INTERN1 End</td></tr>
                        <tr><td>December 16</td><td>Internship consultation</td></tr>
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
                            <div class="year-option" data-year="2023-2024">2023-2024</div>
                            <div class="year-option" data-year="2024-2025">2024-2025</div>
                            <div class="year-option" data-year="2025-2026">2025-2026</div>
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
                    <button style="width: 100%; background: #e8f5e9; border: 1px solid #c8e6c9; padding: 8px; border-radius: 4px; color: #2e7d32;">Notify Remaining</button>
                </div>
                <div class="panel" style="padding: 20px;">
                    <div style="color: #1e3b99; font-weight: bold; font-size: 20px;">130 Interns</div>
                    <div style="font-size: 12px; margin-top: 10px; line-height: 2;">
                        <div><span style="color: #ff8a8a;">●</span> Management <span style="float: right; background: #1e3b99; color: white; padding: 2px 6px; border-radius: 4px;">3</span></div>
                        <div><span style="color: #2e96ff;">●</span> Computing <span style="float: right; background: #1e3b99; color: white; padding: 2px 6px; border-radius: 4px;">12</span></div>
                        <div><span style="color: #27bda1;">●</span> Multimedia <span style="float: right; background: #1e3b99; color: white; padding: 2px 6px; border-radius: 4px;">21</span></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="taskModalOverlay">
        <div class="task-modal">
            <span class="modal-close" id="closeModalBtn">&times;</span>
            
            <div style="position: relative;">
                <input type="text" class="input-box" id="taskTitle" value="Please finish your evaluations" placeholder="Enter title...">
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
                <input type="text" class="input-box" value="Wednesday, March 29" style="flex: 2;">
                <input type="text" class="input-box" value="6:00 PM" style="flex: 1;">
                <span>-</span>
                <input type="text" class="input-box" value="7:00 PM" style="flex: 1;">
            </div>

            <div class="form-row">
                <svg class="form-icon" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <input type="text" class="input-box" value="Industry Partners">
                <input type="text" class="input-box" value="School Of Engineering">
                <span>-</span>
                <input type="text" class="input-box" value="All Programs">
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
                <input type="text" class="input-box" value="Finish it please" style="border: none; border-bottom: 1px solid #ccc; border-radius: 0;">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 10px;">
                <button class="add-btn" style="display: flex; gap: 5px; align-items: center;">
                    <span style="color: #27bda1;">●</span> <svg style="width: 14px; height: 14px; stroke: #333; fill: none;" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <button class="save-btn" id="saveTaskBtn">Save</button>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>

    <script>
        // 1. Real-Time Clock & Date
        function updateTime() {
            const now = new Date();
            
            // Format Date (MM/DD/YYYY)
            const dateStr = String(now.getMonth()+1).padStart(2,'0') + '/' + 
                            String(now.getDate()).padStart(2,'0') + '/' + 
                            now.getFullYear();
            
            // Format Time (HH:MM:SS AM/PM)
            let hrs = now.getHours();
            const ampm = hrs >= 12 ? 'PM' : 'AM';
            hrs = hrs % 12 || 12; 
            const timeStr = String(hrs).padStart(2,'0') + ':' + 
                            String(now.getMinutes()).padStart(2,'0') + ':' + 
                            String(now.getSeconds()).padStart(2,'0') + ' ' + ampm;

            document.getElementById('real-time-date').innerText = dateStr;
            document.getElementById('real-time-clock').innerText = timeStr;
            document.getElementById('current-year').innerText = now.getFullYear();
            
            // Mock Login Date logic (Setting it to a few days ago for realism)
            const login = new Date(now); login.setDate(login.getDate() - 3);
            const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            document.getElementById('login-date').innerText = `${days[login.getDay()]}, ${months[login.getMonth()]} ${String(login.getDate()).padStart(2,'0')}, ${login.getFullYear()}`;
        }
        setInterval(updateTime, 1000);
        updateTime();

        // 2. Sidebar Toggle
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('expanded');
        });

        // 3. Notifications Dropdown Toggle
        const bellIcon = document.getElementById('bellIcon');
        const notifDropdown = document.getElementById('notifDropdown');
        bellIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('active');
        });
        document.addEventListener('click', () => notifDropdown.classList.remove('active'));

        // 4. Academic Year Dropdown Logic
        const changeYearBtn = document.getElementById('changeYearBtn');
        const yearDropdown = document.getElementById('yearDropdown');
        const displayAcadYear = document.getElementById('display-acad-year');
        
        changeYearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            yearDropdown.classList.toggle('active');
        });
        
        document.querySelectorAll('.year-option').forEach(option => {
            option.addEventListener('click', function() {
                const selectedYear = this.getAttribute('data-year');
                displayAcadYear.innerText = selectedYear;
                // Add a little flash effect to show it updated
                displayAcadYear.style.color = '#f1b347';
                setTimeout(() => displayAcadYear.style.color = '#333', 500);
            });
        });
        document.addEventListener('click', () => yearDropdown.classList.remove('active'));

        // 5. Modal Logic (Set Event/Task)
        const modalOverlay = document.getElementById('taskModalOverlay');
        const openModalBtn = document.getElementById('openTaskModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const saveTaskBtn = document.getElementById('saveTaskBtn');

        openModalBtn.addEventListener('click', () => modalOverlay.classList.add('active'));
        closeModalBtn.addEventListener('click', () => modalOverlay.classList.remove('active'));
        
        // Close modal if clicking outside the white box
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) modalOverlay.classList.remove('active');
        });

        // Save Button Mock Action
        saveTaskBtn.addEventListener('click', () => {
            const title = document.getElementById('taskTitle').value;
            alert(`Saved successfully!\nTitle: ${title}`);
            modalOverlay.classList.remove('active');
        });
    </script>
</body>
</html>