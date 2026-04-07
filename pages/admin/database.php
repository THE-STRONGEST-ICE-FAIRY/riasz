<?php
// Database Connection Settings (Update these for your XAMPP environment)
$host = 'localhost';
$dbname = 'aeerr'; // <-- Change this to your actual database name
$user = 'root';
$pass = '';

$tables = [];
$db_error = '';

try {
    // Create a PDO instance (connect to the database)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all table names dynamically
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database - Admin Dashboard</title>
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
                <svg class="nav-icon" id="bellIcon" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
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
            <div class="nav-item active" onclick="window.location.href='database.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                <span class="nav-label">Database</span>
            </div>
            <div class="nav-item" onclick="window.location.href='log_activity.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                <span class="nav-label">Log Activity</span>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="date-container">
                    <div class="huge-date">Live Database View</div>
                </div>
            </div>

            <div class="panel" style="overflow: visible;">
                <div class="panel-header" style="flex-direction: column; align-items: flex-start; gap: 20px;">
                    <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                        <div class="panel-title">Connected to: <b><?php echo htmlspecialchars($dbname); ?></b></div>
                    </div>
                    
                    <!-- Dynamic Table Tabs -->
                    <div class="tabs-container">
                        <?php if (!empty($tables)): ?>
                            <?php foreach ($tables as $index => $table): ?>
                                <div class="list-tab <?php echo $index === 0 ? 'active' : ''; ?>" data-target="tbl-<?php echo htmlspecialchars($table); ?>">
                                    <?php echo htmlspecialchars($table); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-tab active" data-target="tbl-error">Status</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Database Status/Error -->
                <?php if (!empty($db_error)): ?>
                    <div id="tbl-error" class="tab-content active table-responsive" style="padding: 20px;">
                        <h3 style="color: #e74c3c;">Database Connection Failed</h3>
                        <p style="color: #666; margin-top: 10px;"><?php echo htmlspecialchars($db_error); ?></p>
                        <p style="color: #666; margin-top: 10px;">Please open <b>database.php</b> and ensure the `$dbname` matches your local database name.</p>
                    </div>
                <?php elseif (empty($tables)): ?>
                    <div id="tbl-error" class="tab-content active table-responsive" style="padding: 20px;">
                        <p style="color: #666;">No tables found in the database. Please import your SQL file in phpMyAdmin.</p>
                    </div>
                <?php else: ?>
                    <!-- Dynamic Content For Each Table -->
                    <?php foreach ($tables as $index => $table): ?>
                        <div id="tbl-<?php echo htmlspecialchars($table); ?>" class="tab-content <?php echo $index === 0 ? 'active' : ''; ?> table-responsive">
                            <table class="master-table" style="font-family: Consolas, monospace; white-space: nowrap;">
                                <?php
                                try {
                                    $query = $pdo->query("SELECT * FROM `" . $table . "` LIMIT 50");
                                    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($rows) > 0) {
                                        // Table Headers
                                        echo "<thead><tr>";
                                        foreach (array_keys($rows[0]) as $colName) {
                                            echo "<th>" . htmlspecialchars($colName) . "</th>";
                                        }
                                        echo "</tr></thead><tbody>";
                                        
                                        // Table Data
                                        foreach ($rows as $row) {
                                            echo "<tr>";
                                            foreach ($row as $val) {
                                                echo "<td>" . htmlspecialchars((string)$val) . "</td>";
                                            }
                                            echo "</tr>";
                                        }
                                        echo "</tbody>";
                                    } else {
                                        echo "<tr><td style='padding: 20px;'>0 rows retrieved. Table is empty.</td></tr>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<tr><td style='padding: 20px; color: red;'>Error loading data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                }
                                ?>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>
    
    <script src="adminscript.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.list-tab');
            const contents = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    tab.classList.add('active');
                    const targetContent = document.getElementById(tab.getAttribute('data-target'));
                    if (targetContent) targetContent.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>