<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Mapping - Program Director</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="Programdistyle.css">
    <style>
        .likert-scale { display: flex; gap: 5px; justify-content: center; }
        .likert-circle { width: 24px; height: 24px; border-radius: 50%; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #777; background: #f9f9f9; }
        .so-badge { display: inline-block; background: #e3f2fd; color: #1e88e5; padding: 3px 8px; border-radius: 12px; font-size: 11px; margin-right: 5px; font-weight: 500; border: 1px solid #bbdefb; margin-bottom: 2px;}
        .mapped-so-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #fff; border: 1px solid #eee; margin-bottom: 5px; border-radius: 4px; }
        .remove-so-btn { color: #e74c3c; cursor: pointer; background: none; border: none; font-size: 18px; line-height: 1; }
    </style>
</head>
<body>

    <header class="top-nav">
        <div class="logo-section">
            <div class="logo-rias">RIAS</div>
            <div class="logo-text"><span>Rams</span><span>Internship</span><span>Assessment System</span></div>
        </div>
        <div class="top-nav-right">
            <span class="role-text">PROGRAM DIRECTOR</span>
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
            <div class="nav-item active" onclick="window.location.href='_index.php'">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span class="nav-label">Home</span>
            </div>
            <div class="nav-item" onclick="window.location.href='master_list.php'">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="nav-label">Master List</span>
            </div>
            <div class="nav-item" onclick="window.location.href='mapping.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                <span class="nav-label">Mapping</span>
            </div>
        </aside>


      <main class="main-content" style="background: #e9ecef;">
    <div class="mapping-panel">
        
        <div id="main-mapping-view">
            <a href="master_list.php" class="back-link">&lt;&lt; Back to Filter</a>
            
            <div class="action-cards-wrapper">
                <a href="#" id="btn-mapping-main" class="action-card card-orange">
                    <svg class="card-icon" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="5" cy="12" r="2.5"></circle>
                        <path d="M7.5 12h3l3-5h4"></path>
                        <circle cx="19" cy="7" r="2.5"></circle>
                        <path d="M10.5 12l3 5h4"></path>
                        <circle cx="19" cy="17" r="2.5"></circle>
                        <path d="M14 12h3"></path>
                        <circle cx="19" cy="12" r="2.5"></circle>
                        <path d="M16 6l1.5 1-1.5 1"></path>
                        <path d="M16 11l1.5 1-1.5 1"></path>
                        <path d="M16 16l1.5 1-1.5 1"></path>
                    </svg>
                    <span>MAPPING</span>
                </a>

                <a href="view_edit.php" class="action-card card-gray">
                    <svg class="card-icon" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 10s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                        <path d="M12 17v2"></path>
                        <path d="M7 19h10"></path>
                        <path d="M7 19v2"></path>
                        <path d="M12 19v2"></path>
                        <path d="M17 19v2"></path>
                        <rect x="5" y="21" width="4" height="2" fill="currentColor"></rect>
                        <rect x="10" y="21" width="4" height="2" fill="currentColor"></rect>
                        <rect x="15" y="21" width="4" height="2" fill="currentColor"></rect>
                    </svg>
                    <span>VIEW AND EDIT</span>
                </a>
            </div>
        </div>

        <div id="sub-mapping-view" style="display: none;">
            <a href="#" id="btn-back-selection" class="back-link">&lt;&lt; Back to selection</a>

            <div class="sub-options-container">
                <div class="sub-column">
                    <div class="column-header">View</div>
                    <a href="#" class="action-card card-gray sub-card">
                        <svg class="card-icon" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="7" r="3.5"></circle>
                            <path d="M8 15v3l4 2 4-2v-3"></path>
                            <path d="M8 15c-2 0-4.5 1.5-4.5 4v1l4.5 2"></path>
                            <path d="M16 15c2 0 4.5 1.5 4.5 4v1l-4.5 2"></path>
                            <line x1="12" y1="15" x2="12" y2="22"></line>
                        </svg>
                        <span>COURSE OUTCOMES to<br>EVALUATION FORMS</span>
                    </a>
                </div>
                
                <div class="sub-column border-left">
                    <div class="column-header">Map</div>
                    <a href="#" id="btn-eval-so" class="action-card card-orange sub-card">
                        <svg class="card-icon" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="7" r="3.5"></circle>
                            <path d="M8 15v3l4 2 4-2v-3"></path>
                            <path d="M8 15c-2 0-4.5 1.5-4.5 4v1l4.5 2"></path>
                            <path d="M16 15c2 0 4.5 1.5 4.5 4v1l-4.5 2"></path>
                            <line x1="12" y1="15" x2="12" y2="22"></line>
                        </svg>
                        <span>EVALUATION FORMS to<br>STUDENT OUTCOMES</span>
                    </a>
                </div>

                <div class="sub-column border-left"></div>
            </div>
        </div>

        <div id="eval-so-view" style="display: none; padding-top: 10px;">
            <a href="#" id="btn-back-eval-so" class="back-link">&lt;&lt; Back to Mapping Options</a>
            
            <div class="panel-header" style="margin-top: 15px; margin-bottom: 15px; padding: 0;">
                <div class="panel-title" style="font-size: 18px;">Evaluation Forms to Student Outcomes</div>
            </div>
            
            <div class="table-responsive" style="background: white; border-radius: 8px; border: 1px solid #eaeaea;">
                <table class="master-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th>Evaluation Question / Criteria</th>
                            <th style="width: 220px; text-align: center;">Likert Scale</th>
                            <th>Mapped SOs</th>
                            <th style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center;">1</td>
                            <td>Applies technical knowledge and skills to complete assigned tasks.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span class="so-badge">SO1</span><span class="so-badge">SO2</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Applies technical knowledge and skills to complete assigned tasks." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">2</td>
                            <td>Demonstrates problem-solving capabilities in unexpected situations.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span class="so-badge">SO1</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Demonstrates problem-solving capabilities in unexpected situations." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">3</td>
                            <td>Communicates effectively with team members and supervisors.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span class="so-badge">SO3</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Communicates effectively with team members and supervisors." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">4</td>
                            <td>Works collaboratively within a multidisciplinary environment.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span style="color:#999; font-size:11px;">None</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Works collaboratively within a multidisciplinary environment." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">5</td>
                            <td>Exhibits professional and ethical responsibility at all times.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span class="so-badge">SO4</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Exhibits professional and ethical responsibility at all times." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">6</td>
                            <td>Produces quality work that meets or exceeds industry standards.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span class="so-badge">SO1</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Produces quality work that meets or exceeds industry standards." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">7</td>
                            <td>Adapts quickly to new tools, software, and environments.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span style="color:#999; font-size:11px;">None</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Adapts quickly to new tools, software, and environments." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">8</td>
                            <td>Manages time effectively and meets project deadlines.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span class="so-badge">SO6</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Manages time effectively and meets project deadlines." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">9</td>
                            <td>Takes initiative and works with minimal supervision.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span style="color:#999; font-size:11px;">None</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Takes initiative and works with minimal supervision." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">10</td>
                            <td>Understands the impact of engineering/computing solutions globally.</td>
                            <td><div class="likert-scale"><div class="likert-circle" title="N/A">N/A</div><div class="likert-circle">1</div><div class="likert-circle">2</div><div class="likert-circle">3</div><div class="likert-circle">4</div><div class="likert-circle">5</div></div></td>
                            <td class="mapped-sos-cell"><span class="so-badge">SO2</span></td>
                            <td><button class="view-info-btn map-so-btn" data-question="Understands the impact of engineering/computing solutions globally." style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 6px 12px; border-radius: 4px; color: #1e88e5; cursor: pointer; font-weight: 500; font-size: 12px;">Add/Edit SO</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>
    </div>

    <!-- SO Mapping Modal -->
    <div class="modal-overlay" id="soMappingModal">
        <div class="task-modal" style="max-width: 500px;">
            <span class="modal-close" id="closeSoMappingModal">&times;</span>
            <h2 style="font-weight: 500; color: #1e3b99; margin-bottom: 5px;">Add/Edit Student Outcomes</h2>
            <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Map Student Outcomes (SO) to the selected evaluation criteria.</p>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #eee;">
                <strong style="color:#555;">Evaluation Question:</strong><br>
                <span id="soModalQuestionText" style="color:#333; font-size:14px; font-weight:500;">...</span>
            </div>

            <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                <select id="newSoSelect" class="select-box" style="flex: 1;">
                    <option value="">Select SO...</option>
                    <option value="SO1">SO1 - Problem Solving & Technical Knowledge</option>
                    <option value="SO2">SO2 - Design & Global Impact</option>
                    <option value="SO3">SO3 - Communication Skills</option>
                    <option value="SO4">SO4 - Ethics & Professionalism</option>
                    <option value="SO5">SO5 - Multidisciplinary Teamwork</option>
                    <option value="SO6">SO6 - Life-long Learning & Time Management</option>
                </select>
                <button id="addSoToQuestionBtn" class="add-btn" style="white-space: nowrap;">Add SO +</button>
            </div>

            <div class="column-header" style="font-size: 13px; margin-bottom: 10px;">Currently Mapped Outcomes</div>
            <ul id="mappedSoList" style="list-style: none; padding: 0; margin: 0; min-height: 50px;">
                <!-- dynamically inserted -->
            </ul>

            <div style="text-align: right; margin-top: 25px;">
                <button class="save-btn" id="saveSoMappingBtn" style="width: 100%;">Save Changes</button>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a> <a href="#">CONTACT US</a></div>
        <div class="footer-copy">Copyright © <span id="current-year">2024</span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>

    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnMappingMain = document.getElementById('btn-mapping-main');
            const btnBackSelection = document.getElementById('btn-back-selection');
            const mainView = document.getElementById('main-mapping-view');
            const subView = document.getElementById('sub-mapping-view');
            const evalSoView = document.getElementById('eval-so-view');
            const btnEvalSo = document.getElementById('btn-eval-so');
            const btnBackEvalSo = document.getElementById('btn-back-eval-so');
            if (btnMappingMain && btnBackSelection && mainView && subView) {
                btnMappingMain.addEventListener('click', (e) => {
                    e.preventDefault();
                    mainView.style.display = 'none';
                    subView.style.display = 'block';
                });

                btnBackSelection.addEventListener('click', (e) => {
                    e.preventDefault();
                    subView.style.display = 'none';
                    mainView.style.display = 'block';
                });
            }
            
            if (btnEvalSo && evalSoView && btnBackEvalSo) {
                btnEvalSo.addEventListener('click', (e) => {
                    e.preventDefault();
                    subView.style.display = 'none';
                    evalSoView.style.display = 'block';
                });

                btnBackEvalSo.addEventListener('click', (e) => {
                    e.preventDefault();
                    evalSoView.style.display = 'none';
                    subView.style.display = 'block';
                });
            }

            // --- Modal Logic for Add/Edit SO Mapping ---
            const soMappingModal = document.getElementById('soMappingModal');
            const closeSoMappingModal = document.getElementById('closeSoMappingModal');
            const soModalQuestionText = document.getElementById('soModalQuestionText');
            const mappedSoList = document.getElementById('mappedSoList');
            const addSoToQuestionBtn = document.getElementById('addSoToQuestionBtn');
            const newSoSelect = document.getElementById('newSoSelect');
            const saveSoMappingBtn = document.getElementById('saveSoMappingBtn');
            let currentRowForMapping = null;

            document.querySelectorAll('.map-so-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    currentRowForMapping = e.target.closest('tr');
                    const question = e.target.getAttribute('data-question');
                    soModalQuestionText.innerText = question;
                    
                    mappedSoList.innerHTML = ''; // Clear current
                    const existingBadges = currentRowForMapping.querySelectorAll('.so-badge');
                    existingBadges.forEach(badge => addSoToModalList(badge.innerText));
                    
                    soMappingModal.classList.add('active');
                });
            });

            closeSoMappingModal?.addEventListener('click', () => soMappingModal.classList.remove('active'));

            function addSoToModalList(soValue) {
                const li = document.createElement('li');
                li.className = 'mapped-so-item';
                li.innerHTML = `<span style="font-weight: 500; color: #1e3b99;">${soValue}</span> <button class="remove-so-btn" title="Remove this SO">&times;</button>`;
                mappedSoList.appendChild(li);
            }

            addSoToQuestionBtn?.addEventListener('click', () => {
                const val = newSoSelect.value;
                if (!val) return;
                
                let exists = false;
                mappedSoList.querySelectorAll('span').forEach(span => { if (span.innerText === val) exists = true; });
                
                if (!exists) addSoToModalList(val);
                newSoSelect.selectedIndex = 0;
            });

            mappedSoList?.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-so-btn')) e.target.closest('li').remove();
            });

            saveSoMappingBtn?.addEventListener('click', () => {
                if (currentRowForMapping) {
                    const cell = currentRowForMapping.querySelector('.mapped-sos-cell');
                    cell.innerHTML = '';
                    mappedSoList.querySelectorAll('span').forEach(span => {
                        const badge = document.createElement('span');
                        badge.className = 'so-badge';
                        badge.innerText = span.innerText;
                        cell.appendChild(badge);
                    });
                    if(cell.innerHTML === '') cell.innerHTML = '<span style="color:#999; font-size:11px;">None</span>';
                }
                soMappingModal.classList.remove('active');
            });
        });
    </script>
</body>
</html>