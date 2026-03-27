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
                    <h1 class="huge-date">Master List</h1>
                </div>  
                <div class="header-links">
                    <span>Logged in on: <span id="login-date">Saturday, March 09, 2025</span></span>
                </div>
            </div>

           <div id="landingPage" class="card-grid">
                <div class="landing-box">
                    <h2>INTERN 1</h2>
                    <button class="view-btn intern-toggle">View Intern List</button>
                </div>
                <div class="landing-box">
                    <h2>INTERN 2</h2>
                    <button class="view-btn intern-toggle">View Intern List</button>
                </div>
            </div>

            <div class="panel" style="display: none;" id="internList">
                <div class="panel-header">
                    <div class="panel-title">Student Intern Master List Directory</div>
                    <button id="backBtn" class="back-btn-box">← Back</button>
                </div>
                
                <div class="search-filter-container" style="padding: 0 20px 20px;">
                    <div class="search-box">
                        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" class="search-input" placeholder="Search directory...">
                    </div>
                    <button class="filter-btn">Filter</button>
                </div>

                <div class="table-responsive">
                    <table class="master-table">
                        <thead>
                            <tr>
                                <th>NAME</th>
                                <th>COMPANY</th>
                                <th>DEPARTMENT</th>
                                <th>SCHOOL PROGRAM</th>
                                <th>EMAIL</th>
                                <th>INTERN</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Doe</td>
                                <td>TechCorp</td>
                                <td>IT</td>
                                <td>BSIT</td>
                                <td>john.doe@student.apc.edu.ph</td>
                                <td>Intern 1</td>
                                <td class="status-complete">Complete</td>
                            </tr>
                            <tr>
                                <td>Jane Smith</td>
                                <td>Innovate Inc</td>
                                <td>Front Person</td>
                                <td>BSCS</td>
                                <td>jane.s@student.apc.edu.ph</td>
                                <td>Intern 2</td>
                                <td class="status-incomplete">Incomplete</td>
                            </tr>
                        </tbody>
                    </table>
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
        <div class="footer-copy">Copyright © 2026 <a href="#">Asia Pacific College</a></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
        // Add this inside your existing document.addEventListener('DOMContentLoaded', () => { ... })
        // --- Panel Switching Logic ---
            const landingPage = document.getElementById("landingPage");
            const internList = document.getElementById("internList");
            const backBtn = document.getElementById("backBtn");
            const toggleBtns = document.querySelectorAll(".intern-toggle");

            // Function to show the Table
            toggleBtns.forEach(btn => {
                btn.addEventListener("click", () => {
                    landingPage.style.setProperty('display', 'none', 'important');
                    internList.style.setProperty('display', 'block', 'important');
                });
            });

            // Function to go Back to Cards
            backBtn.addEventListener("click", () => {
                internList.style.setProperty('display', 'none', 'important');
                landingPage.style.setProperty('display', 'flex', 'important');
            });
                });
    </script>

</body>
</html>