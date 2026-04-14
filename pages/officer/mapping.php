<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapping - RIAS Dashboard</title>
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
            <div class="nav-item" onclick="window.location.href='reports.php'">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span class="nav-label">Reports</span>
            </div>
            <div class="nav-item" onclick="window.location.href='progress.php'">
                <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span class="nav-label">Progress</span>
            </div>
            <div class="nav-item active" onclick="window.location.href='mapping.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                <span class="nav-label">Mapping</span>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="date-container">
                    <div class="huge-date">Curriculum Mapping</div>
                </div>
            </div>

            <div class="header-actions">
                <button id="newMappingBtn" class="add-btn-box" onclick="alert('Open New Mapping Modal')">
                    + NEW MAPPING
                </button>
            </div>

            <div id="selectionGrid" class="card-grid">
                    <div class="landing-box">
                        <h2>School Of Engineering</h2>
                        <button class="view-btn"  onclick="showMapping('SOE')">VIEW MAP</button>
                    </div>

                    <div class="landing-box" onclick="showMapping('SOMA')">
                        <h2>School Of Multimedia And Arts</h2>
                        <button class="view-btn">VIEW MAP</button>
                    </div>
                </div>

                <div id="SOEContent" class="mapping-view" style="display: none;">
                    <div class="panel"> 
                        <div class="panel-header">
                            <div class="panel-title">SOE Mapping</div>
                        </div>
                        
                        <div class="view-header-actions">
                            <button onclick="showGrid()" class="back-btn-box">
                                ← BACK TO MENU
                            </button>
                        </div>

                        <div class="evaluation-container">
                            <p>Select locations to map internships...</p>
                        </div>
                    </div>

                        <div class="panel-footer-actions">
                            <button type="button" class="btn-cancel" onclick="showGrid()">CANCEL CHANGES</button>
                            <button type="button" class="btn-save" onclick="alert('Progress Saved')">SAVE CHANGES</button>
                            <button type="button" class="btn-submit" onclick="alert('Mapping Submitted')">SUBMIT</button>
                        </div>
                    </div>

                    <div id="SOMAContent" class="mapping-view" style="display: none;">
                    <div class="panel"> 
                        <div class="panel-header">
                            <div class="panel-title">SOMA Mapping</div>
                        </div>
                        
                        <div class="view-header-actions">
                            <button onclick="showGrid()" class="back-btn-box">
                                ← BACK TO MENU
                            </button>
                        </div>

                        <div class="evaluation-container">
                            <p>Select locations to map internships...</p>
                        </div>
                    </div>

                        <div class="panel-footer-actions">
                            <button type="button" class="btn-cancel" onclick="showGrid()">CANCEL CHANGES</button>
                            <button type="button" class="btn-save" onclick="alert('Progress Saved')">SAVE CHANGES</button>
                            <button type="button" class="btn-submit" onclick="alert('Mapping Submitted')">SUBMIT</button>
                        </div>
                    </div>
                </div>
            </div>

    <footer class="footer">
        <div class="footer-links">
            <a href="#">About Us</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Use</a>
        </div>
        <div class="footer-copy">
            &copy; 2026 <a href="#">RIAS</a>. All rights reserved.
        </div>
    </footer>
    <script src="adminscript.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let editingRow = null;
            const addMappingBtn = document.getElementById('addMappingBtn');
            const editMappingModal = document.getElementById('editMappingModal');
            const saveMappingBtn = document.getElementById('saveMappingBtn');
            
            const mappingCriteria = document.getElementById('mappingCriteria');
            const mappingSO = document.getElementById('mappingSO');
            const mappingCO = document.getElementById('mappingCO');

            addMappingBtn?.addEventListener('click', () => {
                editingRow = null;
                document.querySelector('#editMappingModal h2').innerText = "Add Mapping";
                saveMappingBtn.innerText = "Add Mapping";
                mappingCriteria.value = "";
                mappingSO.selectedIndex = 0;
                mappingCO.selectedIndex = 0;
                editMappingModal.classList.add('active');
            });

            document.getElementById('closeEditMappingModal')?.addEventListener('click', () => document.getElementById('editMappingModal').classList.remove('active'));
            
            saveMappingBtn?.addEventListener('click', () => {
                const criteriaVal = mappingCriteria.value.trim();
                const soVal = mappingSO.value;
                const coVal = mappingCO.value;

                if (!criteriaVal) { alert("Please enter a criteria description."); return; }

                if (editingRow) {
                    editingRow.children[0].innerText = criteriaVal;
                    editingRow.children[1].innerText = soVal;
                    editingRow.children[2].innerText = coVal;
                } else {
                    const tbody = document.querySelector('.master-table tbody');
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${criteriaVal}</td><td>${soVal}</td><td>${coVal}</td>
                        <td>
                            <div class="row-meatball">
                                <button class="meatball-btn">⋮</button>
                                <div class="meatball-drop">
                                    <button class="edit-btn">Edit</button>
                                    <button class="remove-btn" style="color: #e74c3c;">Remove</button>
                                </div>
                            </div>
                        </td>`;
                    tbody.appendChild(tr);
                }
                editMappingModal.classList.remove('active');
            });

            // Meatball and Actions Logic
            document.addEventListener('click', (e) => {
                const isMeatball = e.target.closest('.meatball-btn');
                if (isMeatball) {
                    e.stopPropagation();
                    const drop = isMeatball.nextElementSibling;
                    const isActive = drop.classList.contains('active');
                    document.querySelectorAll('.meatball-drop').forEach(d => d.classList.remove('active'));
                    if (!isActive) {
                        const rect = isMeatball.getBoundingClientRect();
                        drop.style.position = 'fixed';
                        drop.style.top = `${rect.bottom}px`;
                        drop.style.left = `${rect.right - 100}px`; 
                        drop.style.right = 'auto';
                        drop.classList.add('active');
                    }
                } else {
                    document.querySelectorAll('.meatball-drop').forEach(d => d.classList.remove('active'));
                }
                
                if (e.target.closest('.remove-btn') && confirm("Are you sure you want to remove this mapping?")) {
                    e.target.closest('tr').remove();
                }
                
                if (e.target.closest('.edit-btn')) {
                    editingRow = e.target.closest('tr');
                    const cols = editingRow.querySelectorAll('td');
                    document.querySelector('#editMappingModal h2').innerText = "Edit Mapping";
                    saveMappingBtn.innerText = "Save Changes";
                    mappingCriteria.value = cols[0].innerText;
                    mappingSO.value = cols[1].innerText;
                    mappingCO.value = cols[2].innerText;
                    editMappingModal.classList.add('active');
                }
            });
            
            document.addEventListener('scroll', () => document.querySelectorAll('.meatball-drop.active').forEach(d => d.classList.remove('active')), true);
        });
        
    </script>

    <script>
   function showMapping(type) {
    document.getElementById('selectionGrid').style.display = 'none';
    document.getElementById('newMappingBtn').style.display = 'none'; // Hide "New" button
    
    document.querySelectorAll('.mapping-view').forEach(view => {
        view.style.display = 'none';
    });

    document.getElementById(type + 'Content').style.display = 'block';
    document.getElementById('backContainer').style.display = 'block';

    const titles = {
        'SOE': 'SOE Mapping',
        'SOMA': 'SOMA Mapping'
    };
    document.getElementById('pageTitle').innerText = titles[type];
}

function showGrid() {
    document.getElementById('selectionGrid').style.display = 'flex';
    document.getElementById('newMappingBtn').style.display = 'block'; // Show "New" button again
    
    document.querySelectorAll('.mapping-view').forEach(view => view.style.display = 'none');
    document.getElementById('backContainer').style.display = 'none';
    document.getElementById('pageTitle').innerText = 'System Mapping';
}
</script>
</body>
</html>