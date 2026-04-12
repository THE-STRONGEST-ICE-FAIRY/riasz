<?php
require '../../utilities/database/database.php';

// Fetch users for the Masterlist section based on the specific schema provided
$users = [];
try {
    // Querying specific columns: user_id, user_first_name, user_last_name, user_email, user_role, user_is_archived, user_date_created
    $stmt = $conn->query("SELECT user_id, user_first_name, user_last_name, user_email, user_role, user_is_archived, user_date_created FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $db_error = "Could not fetch users: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Admin</title>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* CSS Variables for easy maintenance */
        :root {
            --primary: #2563eb;
            --primary-light: #eff6ff;
            --bg-gray: #f9fafb;
            --border-gray: #e5e7eb;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --white: #ffffff;
            
            /* User Requested Colors */
            --header-bg: #fbaf41;
            --sidebar-bg: #343a40;
            --footer-bg: #213b9a;
            
            --sidebar-width-collapsed: 80px;
            --sidebar-width-expanded: 256px;
            --header-height: 64px;
            --footer-height: 48px;
            --transition-speed: 0.3s;
            --transition-curve: cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-gray);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--header-bg);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            color: var(--white);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-img {
            height: 32px;
            width: 32px;
            border-radius: 4px;
            object-fit: cover;
            background-color: rgba(255,255,255,0.5);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .admin-label {
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-divider {
            width: 1px;
            height: 24px;
            background-color: rgba(255, 255, 255, 0.4);
            margin: 0 4px;
        }

        .notification-btn {
            position: relative;
            padding: 8px;
            color: var(--white);
            background: none;
            border: none;
            cursor: pointer;
            border-radius: 9999px;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-btn:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .profile-container {
            position: relative;
            display: inline-block;
            padding-bottom: 12px; 
            margin-bottom: -12px;
        }

        .profile-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--white);
            border: none;
            flex-shrink: 0;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .profile-circle:active {
            transform: scale(0.95);
        }

        .profile-panel {
            position: absolute;
            top: 48px;
            right: 0;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 8px;
            min-width: 140px;
            z-index: 100;
            display: none;
            border: 1px solid var(--border-gray);
        }

        .profile-panel.show {
            display: block;
        }

        .logout-btn {
            width: 100%;
            text-align: left;
            padding: 10px 16px;
            background: none;
            border: none;
            border-radius: 6px;
            color: #ef4444;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background-color: #fef2f2;
        }

        /* Sidebar Styles */
        aside {
            position: fixed;
            left: 0;
            top: var(--header-height);
            bottom: var(--footer-height);
            background: var(--sidebar-bg);
            z-index: 40;
            transition: width var(--transition-speed) var(--transition-curve);
            overflow: hidden;
            border-top-right-radius: 24px;
            border-bottom-right-radius: 24px;
            color: #e9ecef;
        }

        .sidebar-collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar-expanded {
            width: var(--sidebar-width-expanded);
        }

        .toggle-container {
            padding: 16px 28px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-toggle-btn {
            padding: 4px;
            background: none;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.2s;
            color: #ced4da;
        }

        .sidebar-toggle-btn:hover {
            background-color: rgba(255,255,255,0.1);
        }

        nav {
            flex: 1;
            margin-top: 16px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 12px 28px;
            background: none;
            border: none;
            cursor: pointer;
            color: #adb5bd;
            transition: all 0.2s;
            text-align: left;
        }

        .sidebar-item:hover {
            background-color: rgba(255,255,255,0.05);
            color: var(--white);
        }

        .sidebar-item.nav-active {
            background-color: rgba(255,255,255,0.1);
            color: var(--header-bg);
            border-right: 4px solid var(--header-bg);
        }

        .icon-container {
            width: 24px;
            display: flex;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-text {
            margin-left: 16px;
            font-weight: 500;
            white-space: nowrap;
            transition: opacity 0.2s ease-in-out;
        }

        .sidebar-collapsed .sidebar-text {
            opacity: 0;
            pointer-events: none;
        }

        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 16px 0;
        }

        /* Main Content Styles */
        main {
            flex: 1;
            padding: calc(var(--header-height) + 24px) 24px var(--footer-height);
            transition: transform var(--transition-speed) var(--transition-curve);
        }

        @media (min-width: 768px) {
            main {
                margin-left: var(--sidebar-width-collapsed);
            }
        }

        @media (max-width: 767px) {
            .sidebar-collapsed {
                width: 0;
            }
        }

        /* Dashboard Content Sections */
        section {
            display: none;
            min-height: 60vh;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        section.active {
            display: block;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        /* Masterlist Table Styles */
        .table-container {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th {
            background-color: #f8fafc;
            padding: 12px 24px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-gray);
        }

        .data-table td {
            padding: 16px 24px;
            font-size: 14px;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-gray);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background-color: #f1f5f9;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-active { background-color: #dcfce7; color: #166534; }
        .badge-archived { background-color: #fef3c7; color: #92400e; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 24px;
        }

        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .stat-card {
            background: var(--white);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            border: 1px solid var(--border-gray);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        .stat-value {
            font-size: 30px;
            font-weight: 700;
            margin-top: 4px;
        }

        .stat-trend {
            font-size: 14px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .trend-up { color: #10b981; }
        .trend-blue { color: var(--primary); }

        .progress-bar-bg {
            width: 100%;
            background-color: var(--bg-gray);
            height: 8px;
            border-radius: 9999px;
            margin-top: 16px;
        }

        .progress-bar-fill {
            background-color: var(--footer-bg);
            height: 100%;
            border-radius: 9999px;
        }

        /* Footer Styles */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--footer-height);
            background: var(--footer-bg);
            border-top: 1px solid rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            font-size: 11px;
            color: rgba(255,255,255,0.8);
            z-index: 50;
        }

        .footer-left {
            display: flex;
            align-items: center;
        }

        .footer-link-apc {
            color: var(--white);
            text-decoration: underline;
            font-weight: 600;
            margin: 0 4px;
        }

        .footer-right {
            display: flex;
            gap: 16px;
        }

        .footer-nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        .footer-nav-link:hover {
            opacity: 0.7;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="logo-container">
            <img src="https://via.placeholder.com/32" alt="Logo" class="logo-img">
            <span style="font-weight: 800; font-size: 18px; letter-spacing: -0.5px;">NexusCore</span>
        </div>

        <div class="header-actions">
            <span class="admin-label">ADMIN</span>
            <div class="header-divider"></div>
            <button class="notification-btn">
                <i data-lucide="bell" style="width: 20px; height: 20px;"></i>
            </button>
            <div class="profile-container" id="profileMenu">
                <div class="profile-circle" id="profileTrigger"></div>
                <div class="profile-panel" id="profilePanel">
                    <button class="logout-btn" onclick="handleLogout()">
                        <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                        log out
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar-collapsed">
        <div style="display: flex; flex-direction: column; height: 100%;">
            <div class="toggle-container">
                <button id="sidebarToggle" class="sidebar-toggle-btn">
                    <i data-lucide="menu" style="width: 24px; height: 24px;"></i>
                </button>
            </div>

            <nav id="mainNav">
                <button data-page="dashboard" class="sidebar-item nav-active">
                    <div class="icon-container"><i data-lucide="layout-dashboard" style="width: 20px; height: 20px;"></i></div>
                    <span class="sidebar-text">Dashboard</span>
                </button>
                <button data-page="masterlist" class="sidebar-item">
                    <div class="icon-container"><i data-lucide="users" style="width: 20px; height: 20px;"></i></div>
                    <span class="sidebar-text">Masterlist</span>
                </button>
                <button data-page="database" class="sidebar-item">
                    <div class="icon-container"><i data-lucide="database" style="width: 20px; height: 20px;"></i></div>
                    <span class="sidebar-text">Database</span>
                </button>
                <button data-page="log-activity" class="sidebar-item">
                    <div class="icon-container"><i data-lucide="history" style="width: 20px; height: 20px;"></i></div>
                    <span class="sidebar-text">Log Activity</span>
                </button>
            </nav>

            <div class="sidebar-footer">
                <button class="sidebar-item" style="color: #6c757d;">
                    <div class="icon-container"><i data-lucide="help-circle" style="width: 20px; height: 20px;"></i></div>
                    <span class="sidebar-text" style="font-size: 13px;">Support</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main id="mainContent">
        
        <!-- Dashboard Section -->
        <section id="dashboard" class="active">
            <h1>Dashboard Overview</h1>
			
            <div class="stats-grid">
                <div class="stat-card">
                    <p class="stat-label">Total Revenue</p>
                    <h3 class="stat-value">$54,230</h3>
                    <p class="stat-trend trend-up">
                        <i data-lucide="trending-up" style="width: 16px; height: 16px;"></i> +12.5% vs last month
                    </p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Active Users</p>
                    <h3 class="stat-value">1,284</h3>
                    <p class="stat-trend trend-blue">
                        <i data-lucide="user-check" style="width: 16px; height: 16px;"></i> 48 currently online
                    </p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Project Completion</p>
                    <h3 class="stat-value">87%</h3>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: 87%"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Masterlist Section -->
        <section id="masterlist">
            <h1>User Masterlist</h1>
            
            <?php if (isset($db_error)): ?>
                <div style="background: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                    <?php echo $db_error; ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Date Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 48px; color: var(--text-muted);">
                                    No user records found in the database.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td style="font-family: monospace; font-weight: 600; color: var(--primary);">
                                        #<?php echo htmlspecialchars($user['user_id']); ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;">
                                            <?php echo htmlspecialchars($user['user_first_name'] . ' ' . $user['user_last_name']); ?>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-muted);"><?php echo htmlspecialchars($user['user_email']); ?></td>
                                    <td>
                                        <span style="font-weight: 500;"><?php echo htmlspecialchars($user['user_role'] ?? 'User'); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            // Handling user_is_archived (tinyint)
                                            $isArchived = (bool)($user['user_is_archived'] ?? false);
                                            $statusText = $isArchived ? 'Archived' : 'Active';
                                            $statusClass = $isArchived ? 'badge-archived' : 'badge-active';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 13px; color: var(--text-muted);">
                                        <?php echo date('M d, Y', strtotime($user['user_date_created'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        
			<?php
				// get all tables
				$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

				foreach ($tables as $table) {
					echo "<h2>Table: $table</h2>";

					// get columns for each table
					$columns = $conn->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);

					echo "<ul>";
					foreach ($columns as $column) {
						echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")</li>";
					}
					echo "</ul>";
				}
			?>
		</section>

        <!-- Database Section -->
        <section id="database">
            <h1>Resource Database</h1>
            <div style="background: white; padding: 32px; border-radius: 12px; border: 1px solid #e5e7eb; height: 384px; display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center;">
                    <i data-lucide="database" style="width: 64px; height: 64px; color: #e5e7eb; margin-bottom: 16px;"></i>
                    <p style="color: #6b7280;">Central database management system loading...</p>
                </div>
            </div>
        </section>

        <!-- Log Activity Section -->
        <section id="log-activity">
            <h1>Activity Logs</h1>
            <div style="max-width: 100%; background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px;">
                <div style="border-left: 2px solid #e5e7eb; padding-left: 20px; margin-left: 10px;">
                    <div style="position: relative; margin-bottom: 24px;">
                        <div style="position: absolute; left: -27px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: var(--header-bg);"></div>
                        <p style="font-weight: 600; font-size: 14px;">System backup completed</p>
                        <p style="font-size: 12px; color: #6b7280;">2 hours ago • Server #1</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-left">
            Copyright &copy; 2026 <a href="https://www.apc.edu.ph" target="_blank" class="footer-link-apc">Asia Pacific College</a>. All rights reserved.
        </div>
        <div class="footer-right">
            <a href="#" class="footer-nav-link">ABOUT US</a>
            <a href="#" class="footer-nav-link">PRIVACY POLICY</a>
            <a href="#" class="footer-nav-link">TERMS OF USE</a>
            <a href="#" class="footer-nav-link">CONTACT US</a>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const profileTrigger = document.getElementById('profileTrigger');
        const profilePanel = document.getElementById('profilePanel');
        const profileMenuContainer = document.getElementById('profileMenu');
        const navButtons = document.querySelectorAll('#mainNav .sidebar-item');
        
        let isCollapsed = true;

        function updateLayout(collapsedState) {
            isCollapsed = collapsedState;
            const isMobile = window.innerWidth < 768;

            if (isCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                sidebar.classList.remove('sidebar-expanded');
                mainContent.style.transform = 'translateX(0)';
            } else {
                sidebar.classList.add('sidebar-expanded');
                sidebar.classList.remove('sidebar-collapsed');
                if (isMobile) {
                    mainContent.style.transform = 'translateX(256px)';
                } else {
                    mainContent.style.transform = `translateX(176px)`;
                }
            }
        }

        sidebarToggle.addEventListener('click', () => {
            updateLayout(!isCollapsed);
        });

        sidebar.addEventListener('mouseleave', () => {
            if (!isCollapsed) updateLayout(true);
        });

        function showPage(pageId) {
            document.querySelectorAll('section').forEach(sec => sec.classList.remove('active'));
            const targetSection = document.getElementById(pageId);
            if (targetSection) targetSection.classList.add('active');

            navButtons.forEach(btn => {
                if (btn.getAttribute('data-page') === pageId) {
                    btn.classList.add('nav-active');
                } else {
                    btn.classList.remove('nav-active');
                }
            });

            if (window.innerWidth < 768) updateLayout(true);
        }

        navButtons.forEach(button => {
            button.addEventListener('click', () => {
                showPage(button.getAttribute('data-page'));
            });
        });

        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            profilePanel.classList.toggle('show');
        });

        profileMenuContainer.addEventListener('mouseleave', () => {
            profilePanel.classList.remove('show');
        });

        function handleLogout() {
            window.location.replace('../login/login.php');
        }

        window.addEventListener('resize', () => updateLayout(isCollapsed));
    </script>
</body>
</html>