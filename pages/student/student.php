<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Supervisor Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="studentStyle.css">
</head>
<body>

    <header class="top-nav">
        <div class="logo-section">
            <div class="logo-rias">RIAS</div>
            <div class="logo-text">
                <span>Rams</span><span>Internship</span><span>Assessment System</span>
            </div>
        </div>
        <div class="top-nav-right">
            <span class="role-text">INDUSTRY PROFESSOR</span>
            <div class="bell-wrapper">
                <svg class="nav-icon" id="bellIcon" viewBox="0 0 24 24">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z" />
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
                <svg viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18" /></svg>
                <span class="nav-label">Menu</span>
            </div>
            <div class="nav-item active" onclick="window.location.href='student.php'">
                <svg viewBox="0 0 24 24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                <span class="nav-label">Home</span>
            </div>
            <div class="nav-item" onclick="window.location.href='portfolio.php'">
                <svg viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <span class="nav-label">Portfolio</span>
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

            <div class="student-info-card">
                            <div class="info-group">
                                <label>NAME</label>
                                <div class="info-value">John Doe</div>
                            </div>
                            <div class="info-group">
                                <label>PROGRAM</label>
                                <div class="info-value">BSIT</div>
                            </div>
                            <div class="info-group">
                                <label>COMPANY / DEPT</label>
                                <div class="info-value">TechCorp - IT Department</div>
                            </div>
                            <div class="info-group">
                                <label>Industry Professor Email</label>
                                <div class="info-value">john.doe@student.apc.edu.ph</div>
                            </div>
                            <div class="info-group">
                                <label>CURRENT STATUS</label>
                                <span class="info-value">Intern 1</span>
                            </div>
                    
                    <div class="student-actions">
                        <button class="btn-action btn-edit" onclick="location.href='editProfile.php'">
                            EDIT PROFILE
                        </button>
                    </div>
                </div>

            <div class="panel" style="padding: 30px;">
                <div class="panel-header" style="padding: 0 0 20px 0;">
                    <div class="panel-title"><b>Overall Requirement Submissions</b></div>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="font-weight: 500; color: #555; font-size: 14px;">Completion Rate This Intern Year</span>
                        <span style="color: #1e3b99; font-weight: bold; font-size: 16px;">65%</span>
                    </div>
                    <div style="background: #eaeaea; border-radius: 10px; height: 16px; width: 100%; overflow: hidden;">
                        <div style="background: #1e3b99; height: 100%; width: 65%; transition: width 1s ease-in-out;"></div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title"><b>Upcoming Tasks & Deadlines</b></div>
                    <a href="portfolio.php" class="view-all">View Portfolio</a>
                </div>

                <div class="task-list">
                    <div class="task-item" onclick="location.href='portfolio.php?task=narrative'">
                        <div class="task-info">
                            <span class="task-name">Weekly Narrative Report - Week 12</span>
                            <span class="task-due">Due: April 25, 2026</span>
                        </div>
                        <div class="task-status status-pending">PENDING</div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <footer class="footer">
        <div class="footer-links">
            <a href="#">ABOUT US</a> 
            <a href="#">PRIVACY POLICY</a> 
            <a href="#">TERMS OF USE</a>
        </div>
        <div class="footer-copy">
            Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="adminscript.js"></script>
</body>
</html>