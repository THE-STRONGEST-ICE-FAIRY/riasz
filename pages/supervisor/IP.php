<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Supervisor Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="IPstyle.css">
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
            <div class="nav-item active" onclick="window.location.href='_index.php'">
                <svg viewBox="0 0 24 24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                <span class="nav-label">Home</span>
            </div>
            <div class="nav-item" onclick="window.location.href='master_list.php'">
                <svg viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <span class="nav-label">Master List</span>
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

            <div class="panel" style="padding: 25px; margin-bottom: 20px;">
                <div class="welcome-section">
                    <h1>Welcome back, <span class="supervisor-name">Prof. [Name]</span>!</h1>
                    <p>Here’s an overview of your intern assessments for today.</p>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card">
                    <span class="stat-label">Student Intern</span>
                    <span class="stat-value">130</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Completed student Assessment</span>
                    <span class="stat-value">3</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Incomplete student Assessment</span>
                    <span class="stat-value">36</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Ongoing student Assessment</span>
                    <span class="stat-value">5</span>
                </div>
            </div>
 
            <div class="bottom-grid">
                <div class="panel" style="padding: 20px;">
                    <div style="color: #1e3b99; font-weight: bold; margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
                        INTERN1 <span style="font-size: 10px; color: #999;">December - March 2025</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; text-align: center;">
                        <div>
                            <div style="font-size: 10px;"><span style="color: #27bda1;">●</span> Deployed</div>
                            <div style="font-size: 24px; color: #27bda1; font-weight: bold;">10</div>
                        </div>
                        <div>
                            <div style="font-size: 10px;"><span style="color: #2e96ff;">●</span> Waiting</div>
                            <div style="font-size: 24px; color: #2e96ff; font-weight: bold;">13</div>
                        </div>
                        <div>
                            <div style="font-size: 10px;"><span style="color: #ff8a8a;">●</span> Undeployed</div>
                            <div style="font-size: 24px; color: #ff8a8a; font-weight: bold;">3</div>
                        </div>
                        <div>
                            <div style="font-size: 10px;"><span style="color: #999;">●</span> Unlisted</div>
                            <div style="font-size: 24px; color: #999; font-weight: bold;">31</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

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