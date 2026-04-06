<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Evaluation - RIAS Dashboard</title>
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
            <span class="role-text">SUPERVISOR / INDUSTRY PARTNER</span>
            <div class="bell-wrapper">
                <svg class="nav-icon" id="bellIcon" viewBox="0 0 24 24">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                </svg>
                <span id="notifBadge" class="notif-badge" style="display: none;">0</span>
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
            <div class="nav-item active">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="nav-label">Master List</span>
            </div>
        </aside>

       <main class="main-content">
            <div class="page-header">
                <div class="date-container">
                    <h1 class="huge-date">Student Evaluation</h1>
                </div>
                <div class="header-links">
                    <button onclick="window.location.href='master_list.php'" class="back-btn-box">← Back to Directory</button>
                </div>
            </div>

            <div class="student-info-card">
                <div class="info-group">
                    <label>NAME</label>
                    <div class="info-value">John Doe</div>
                </div>
                <div class="info-group">
                    <label>COMPANY / DEPT</label>
                    <div class="info-value">TechCorp - IT Department</div>
                </div>
                <div class="info-group">
                    <label>PROGRAM</label>
                    <div class="info-value">BSIT</div>
                </div>
                <div class="info-group">
                    <label>Email</label>
                    <div class="info-value">john.doe@student.apc.edu.ph</div>
                </div>
                <div class="info-group">
                    <label>CURRENT STATUS</label>
                    <span class="status-badge status-complete">Complete</span>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Performance Assessment Form</div>
                </div>
                <div class="evaluation-container">
                    <p>Please rate the intern's performance based on the criteria below...</p>
                    </div>
            </div>
        </main>