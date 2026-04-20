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
                    <div class="huge-date">Evaluation Forms and Outcomes Mapping</div>
                </div>
            </div>

            <div class="header-actions">
                <button id="newMappingBtn" class="add-btn-box" onclick="alert('Open New Mapping Modal')">
                    + NEW FILE
                </button>
            </div>

            <div id="selectionGrid" class="card-grid">
                    <div class="landing-box">
                        <h2>School Of Engineering</h2>
                        <button class="view-btn"  onclick="showMapping('SOE')">VIEW OUTCOMES</button>
                    </div>

                    <div class="landing-box" onclick="showMapping('SOMA')">
                        <h2>School Of Multimedia And Arts</h2>
                        <button class="view-btn">VIEW OUTCOMES</button>
                    </div>

                    <div class="landing-box" onclick="showMapping('EvalForm')">
                        <h2>2025 Evaluation Form</h2>
                        <button class="view-btn">VIEW FORM</button>
                    </div>
                </div>

              <div id="SOEContent" class="mapping-view" style="display: none;">
                    <div class="panel"> 
                        <div class="panel-header">
                            <div class="panel-title">SOE Mapping</div>
                        </div>
                        
                        <div class="view-header-actions" style="padding: 0 20px;">
                            <button onclick="showGrid()" class="back-btn-box">← BACK TO MENU</button>
                            <button class="add-btn-box" style="background-color: #28a745; margin-left: 10px;" onclick="openOutcomeModal()">
                                + ADD COURSE OUTCOME
                            </button>
                        </div>

                        <div class="evaluation-container">
                            <div class="table-responsive">
                                <table class="excel-mapping-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="criteria-col">Course Learning Outcomes</th>
                                            <th colspan="8" class="so-header">Student Outcomes (SO)</th>
                                        </tr>
                                        <tr>
                                            <th>SO1</th><th>SO2</th><th>SO3</th><th>SO4</th>
                                            <th>SO5</th><th>SO6</th><th>SO7</th><th>SO8</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="criteria-text">
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                    <span class="criteria-text-content">Accomplishment of tasks/projects assigned by their respective internship companies. </span>
                                                    <div class="row-actions" style="display: flex; gap: 5px; margin-left: 10px;">
                                                        <button onclick="openOutcomeModal(true, this)" style="color: #29429c; font-size: 10px; font-weight: bold; cursor: pointer;">EDIT</button>
                                                        <button onclick="deleteRow(this)" style="color: #e74c3c; font-size: 10px; font-weight: bold; cursor: pointer;">DEL</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                        </tr>

                                        <tr>
                                            <td class="criteria-text">
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                    <span class="criteria-text-content">Development of acceptable workplace behavior. </span>
                                                    <div class="row-actions" style="display: flex; gap: 5px; margin-left: 10px;">
                                                        <button onclick="openOutcomeModal(true, this)" style="color: #29429c; font-size: 10px; font-weight: bold; cursor: pointer;">EDIT</button>
                                                        <button onclick="deleteRow(this)" style="color: #e74c3c; font-size: 10px; font-weight: bold; cursor: pointer;">DEL</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                        </tr>

                                        <tr>
                                            <td class="criteria-text">
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                    <span class="criteria-text-content">Absorption and Employment. </span>
                                                    <div class="row-actions" style="display: flex; gap: 5px; margin-left: 10px;">
                                                        <button onclick="openOutcomeModal(true, this)" style="color: #29429c; font-size: 10px; font-weight: bold; cursor: pointer;">EDIT</button>
                                                        <button onclick="deleteRow(this)" style="color: #e74c3c; font-size: 10px; font-weight: bold; cursor: pointer;">DEL</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="panel-footer-actions">
                            <button type="button" class="btn-cancel" onclick="showGrid()">CANCEL CHANGES</button>
                            <button type="button" class="btn-save" onclick="alert('Progress Saved')">SAVE CHANGES</button>
                            <button type="button" class="btn-submit" onclick="alert('Mapping Submitted')">SUBMIT</button>
                        </div>
                    </div>
                </div>

                <div id="SOMAContent" class="mapping-view" style="display: none;">
                    <div class="panel"> 
                        <div class="panel-header">
                            <div class="panel-title">SOMA Mapping</div>
                        </div>
                        
                        <div class="view-header-actions" style="padding: 0 20px;">
                            <button onclick="showGrid()" class="back-btn-box">← BACK TO MENU</button>
                            <button class="add-btn-box" style="background-color: #28a745; margin-left: 10px;" onclick="openOutcomeModal()">
                                + ADD COURSE OUTCOME
                            </button>
                        </div>

                        <div class="evaluation-container">
                            <div class="table-responsive">
                                <table class="excel-mapping-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="criteria-col">Course Learning Outcomes</th>
                                            <th colspan="8" class="so-header">Student Outcomes (SO)</th>
                                        </tr>
                                        <tr>
                                            <th>SO1</th><th>SO2</th><th>SO3</th><th>SO4</th>
                                            <th>SO5</th><th>SO6</th><th>SO7</th><th>SO8</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="criteria-text">
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                    <span class="criteria-text-content">Apply knowledge of mathematics and science to solve engineering problems.</span>
                                                    <div class="row-actions" style="display: flex; gap: 5px; margin-left: 10px;">
                                                        <button onclick="openOutcomeModal(true, this)" style="color: #29429c; font-size: 10px; font-weight: bold; cursor: pointer;">EDIT</button>
                                                        <button onclick="deleteRow(this)" style="color: #e74c3c; font-size: 10px; font-weight: bold; cursor: pointer;">DEL</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                            <td class="cell-center"><input type="checkbox"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="panel-footer-actions">
                            <button type="button" class="btn-cancel" onclick="showGrid()">CANCEL CHANGES</button>
                            <button type="button" class="btn-save" onclick="alert('Progress Saved')">SAVE CHANGES</button>
                            <button type="button" class="btn-submit" onclick="alert('Mapping Submitted')">SUBMIT</button>
                        </div>
                    </div>
                </div>

                <div id="EvalFormContent" class="mapping-view" style="display: none;">
                    <div class="panel"> 
                        <div class="panel-header">
                            <div class="panel-title">Industry Partner Evaluation Form Builder</div>
                        </div>
                        
                        <div class="view-header-actions" style="padding: 0 20px;">
                            <button onclick="showGrid()" class="back-btn-box">← BACK TO MENU</button>
                            <button class="add-btn-box" onclick="openOutcomeModal()">+ ADD COURSE OUTCOME</button>
                            <button class="add-btn-box" style="background-color: #28a745; margin-left: 10px;" onclick="openEvalModal()">+ ADD EVALUATION CRITERIA</button>
                        </div>

                        <div class="evaluation-container">
                            <div class="table-responsive">
                                <table class="excel-mapping-table" id="evalTable">
                                    <thead>
                                        <tr>
                                            <tr>
                                                <td class="criteria-text">
                                                    <strong>Overall Performance Improvement</strong><br>
                                                    <small>Since previous evaluation, did the Intern’s over-all performance improve?</small>
                                                </td>
                                                <td class="cell-center">
                                                    <label><input type="radio" name="improve" value="yes"> YES</label>
                                                    <label style="margin-left:10px;"><input type="radio" name="improve" value="no"> NO</label>
                                                </td>
                                                <td>
                                                    <textarea class="input-box" rows="2" placeholder="Provide justification & indicate strong/weak points"></textarea>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="criteria-text">
                                                    <strong>Continued Employment/Internship</strong><br>
                                                    <small>Would you be willing to let the Intern continue or absorb them as an employee?</small>
                                                </td>
                                                <td class="cell-center">
                                                    <label><input type="radio" name="continue" value="yes"> YES</label>
                                                    <label style="margin-left:10px;"><input type="radio" name="continue" value="no"> NO</label>
                                                </td>
                                                <td>
                                                    <textarea class="input-box" rows="2" placeholder="Provide justification"></textarea>
                                                </td>
                                            </tr>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="panel-footer-actions">
                            <button type="button" class="btn-cancel" onclick="showGrid()">CANCEL</button>
                            <button type="button" class="btn-save" onclick="saveFormToDB()">SAVE FORM STRUCTURE</button>
                        </div>
                    </div>
                </div>

    <div id="outcomeModal" class="modal-overlay">
            <div class="task-modal">
                <span class="modal-close" onclick="closeOutcomeModal()">&times;</span>
                <h2 id="modalTitle">Add Course Outcome</h2>
                <input type="hidden" id="editRowIndex">
                
                <div class="form-row">
                    <label>Outcome Description:</label>
                    <textarea id="outcomeDesc" class="input-box" rows="4" placeholder="Enter course learning outcome..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button class="btn-cancel" onclick="closeOutcomeModal()">Cancel</button>
                    <button class="btn-submit" id="saveOutcomeBtn" onclick="saveOutcome()">Save Outcome</button>
                </div>
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
let editingRow = null;

// 1. Modal Logic
function openOutcomeModal(isEdit = false, btn = null) {
    const modal = document.getElementById('outcomeModal');
    
    // CLEANUP: Remove the weight field if it was added by the Eval Modal
    const weightRow = document.getElementById('weightRow');
    if (weightRow) weightRow.remove();
    
    // RESET: Ensure the button points back to the standard saveOutcome
    document.getElementById('saveOutcomeBtn').onclick = saveOutcome;

    const title = document.getElementById('modalTitle');
    const descInput = document.getElementById('outcomeDesc');
    
    if (isEdit) {
        editingRow = btn.closest('tr');
        const currentText = editingRow.querySelector('.criteria-text-content').innerText;
        title.innerText = "Edit Course Outcome";
        descInput.value = currentText;
    } else {
        editingRow = null;
        title.innerText = "Add Course Outcome";
        descInput.value = "";
    }
    modal.classList.add('active');
}

function closeOutcomeModal() {
    document.getElementById('outcomeModal').classList.remove('active');
}

// 2. Save Logic (Create & Update)
function saveOutcome() {
    const desc = document.getElementById('outcomeDesc').value.trim();
    if (!desc) return alert("Please enter a description.");

    if (editingRow) {
        editingRow.querySelector('.criteria-text-content').innerText = desc;
    } else {
        // FIX: Find which view is currently visible
        const activeView = document.querySelector('.mapping-view[style*="display: block"]');
        const tbody = activeView.querySelector('.excel-mapping-table tbody');
        
        const newRow = document.createElement('tr');
        
        let checkboxes = '';
        for(let i=1; i<=8; i++) {
            checkboxes += `<td class="cell-center"><input type="checkbox"></td>`;
        }

        newRow.innerHTML = `
            <td class="criteria-text">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <span class="criteria-text-content">${desc}</span>
                    <div class="row-actions" style="display: flex; gap: 5px; margin-left: 10px;">
                        <button onclick="openOutcomeModal(true, this)" style="color: #29429c; font-size: 10px; font-weight: bold; cursor: pointer;">EDIT</button>
                        <button onclick="deleteRow(this)" style="color: #e74c3c; font-size: 10px; font-weight: bold; cursor: pointer;">DEL</button>
                    </div>
                </div>
            </td>
            ${checkboxes}
        `;
        tbody.appendChild(newRow);
    }
    closeOutcomeModal();
}

// 3. Delete Logic
function deleteRow(btn) {
    if (confirm("Are you sure you want to delete this outcome?")) {
        btn.closest('tr').remove();
    }
}

// 4. View Switching Logic
function showMapping(type) {
    document.getElementById('selectionGrid').style.display = 'none';
    document.getElementById('newMappingBtn').style.display = 'none';
    
    document.querySelectorAll('.mapping-view').forEach(view => view.style.display = 'none');
    
    const target = document.getElementById(type + 'Content');
    if(target) target.style.display = 'block';
}

function showGrid() {
    document.getElementById('selectionGrid').style.display = 'flex';
    document.getElementById('newMappingBtn').style.display = 'block';
    document.querySelectorAll('.mapping-view').forEach(view => view.style.display = 'none');
}

let editingEvalRow = null;

function openEvalModal(isEdit = false, btn = null) {
    const modal = document.getElementById('outcomeModal');
    const title = document.getElementById('modalTitle');
    const descInput = document.getElementById('outcomeDesc');
    
    // Ensure the Weight field exists
    if (!document.getElementById('weightRow')) {
        const weightHTML = `
            <div class="form-row" id="weightRow" style="margin-top:15px;">
                <label>Weight (%):</label>
                <input type="number" id="criteriaWeight" class="input-box" style="width: 80px;" min="1" max="100">
            </div>`;
        document.querySelector('.task-modal .form-row').insertAdjacentHTML('afterend', weightHTML);
    }
    const weightInput = document.getElementById('criteriaWeight');

    if (isEdit) {
        editingEvalRow = btn.closest('tr');
        title.innerText = "Edit Evaluation Criteria";
        descInput.value = editingEvalRow.cells[0].innerText;
        weightInput.value = parseInt(editingEvalRow.cells[1].innerText);
    } else {
        editingEvalRow = null;
        title.innerText = "Add Evaluation Criteria";
        descInput.value = "";
        weightInput.value = "";
    }

    document.getElementById('saveOutcomeBtn').onclick = saveEvalCriteria;
    modal.classList.add('active');
}

function saveEvalCriteria() {
    const desc = document.getElementById('outcomeDesc').value.trim();
    const weight = document.getElementById('criteriaWeight').value.trim();
    
    if (!desc || !weight) return alert("Please fill in both fields.");

    if (editingEvalRow) {
        // UPDATE
        editingEvalRow.cells[0].innerText = desc;
        editingEvalRow.cells[1].innerText = weight + "%";
    } else {
        // CREATE
        const tbody = document.querySelector('#EvalFormContent #evalTable tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td class="criteria-text">${desc}</td>
            <td class="cell-center">${weight}%</td>
            <td class="cell-center">
                <button onclick="openEvalModal(true, this)" style="color: #29429c; font-weight: bold; margin-right: 10px;">EDIT</button>
                <button onclick="deleteRow(this)" style="color: #e74c3c; font-weight: bold;">DEL</button>
            </td>
        `;
        tbody.appendChild(newRow);
    }
    closeOutcomeModal();
}

// Ensure the "Save Form Structure" links to your PHP backend
function saveFormToDB() {
    // In your real app, loop through table rows and send via AJAX to a PHP saving script
    alert('Saving evaluation structure to database...');
}
</script>
</body>
</html>