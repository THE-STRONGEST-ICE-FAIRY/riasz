<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Evaluation - RIAS Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="studentStyle.css">
</head>
<body>
    <header class="top-nav">
        <div class="logo-section">
            <div class="logo-rias">RIAS</div>
            <div class="logo-text"><span>Rams</span><span>Internship</span><span>Assessment System</span></div>
        </div>
        <div class="top-nav-right">
            <span class="role-text">STUDENT INTERN</span>
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
                    <div id="masterListView" class="panel">
                        <div class="panel-header">
                            <div class="panel-title"><b>Internship Portfolio Requirements</b></div>
                            <button onclick="window.location.href='student.php'" class="view-all">← Back to Dashboard</button>
                        </div>
                        <div class="evaluation-container">
                            <table class="evaluation-table">
                                <thead>
                                    <tr>
                                        <th class="col-num">#</th>
                                        <th class="col-criteria">Requirement Name</th>
                                        <th>Uploaded</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="col-num">1</td>
                                        <td class="col-criteria"><strong>Weekly Narrative Report</strong></td>
                                        <th>Mar. 29, 2026</th>
                                        <th>Apr. 20, 2026</th>
                                        <td><span class="status-badge status-pending">Pending</span></td>
                                        <td><button class="btn-action btn-portfolio" onclick="openEditor('Narrative Report')">VIEW/EDIT</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="editorView" class="panel" style="display: none;">
                        <div class="panel-header">
                            <div id="editorTitle" class="panel-title"><b>Requirement Workspace</b></div>
                            <button onclick="closeEditor()" class="view-all">← Back to List</button>
                        </div>
                        
                        <div class="evaluation-container">
                            <div class="form-section">
                                <p id="editorInstructions">Please upload your document or provide the necessary details below.</p>
                                <br>
                                <div class="input-group">
                                    <label>Upload File (PDF/DOCX)</label>
                                    <input type="file" class="input-box">
                                </div>
                                <div class="input-group">
                                    <label>Additional Comments/Notes</label>
                                    <textarea class="input-box" rows="5" style="width:100%; border:1px solid #ddd; border-radius:6px; padding:10px;"></textarea>
                                </div>
                            </div>

                            <div class="form-actions" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                                <button type="button" class="btn-action btn-edit" onclick="closeEditor()">CANCEL</button>
                                <button type="button" class="btn-action btn-portfolio" onclick="submitRequirement()">SUBMIT REQUIREMENT</button>
                            </div>
                        </div>
                    </div>
                </main>

<script>
    // 1. Cancel Button: Clears all radio selections
    document.querySelector('.btn-cancel').addEventListener('click', function() {
        if(confirm("Are you sure you want to clear all selections?")) {
            const radios = document.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => radio.checked = false);
        }
    });

    // 2. Save Button: Shows a "Saved" popup
    document.querySelector('.btn-save').addEventListener('click', function() {
        // In a real app, you'd trigger an AJAX save here
        alert("Draft saved successfully!");
    });

    // 3. Submit Button: Shows a "Submitted" popup
    document.querySelector('.btn-submit').addEventListener('click', function() {
        // You can add logic here to check if all criteria are filled
        const totalQuestions = 2; // Update this to your actual count
        const answered = document.querySelectorAll('input[type="radio"]:checked').length;

        if (answered < totalQuestions) {
            alert("Please complete all evaluation criteria before submitting.");
        } else {
            alert("Evaluation submitted successfully to RIAS!");
            // window.location.href = 'master_list.php'; // Redirect after submit
        }
    });
</script>

<script>
function viewRequirement(type) {
    // For now, we show a popup. Later, this could be: 
    // window.location.href = 'edit_requirement.php?type=' + type;
    alert("Opening the workspace for: " + type.toUpperCase());
}
</script>

<script>
function openEditor(requirementName) {
    // 1. Hide the Master List
    document.getElementById('masterListView').style.display = 'none';
    
    // 2. Set the dynamic title for the requirement
    document.getElementById('editorTitle').innerHTML = "<b>Editing: " + requirementName + "</b>";
    
    // 3. Show the Editor Workspace
    document.getElementById('editorView').style.display = 'block';
    
    // Smooth scroll to top to ensure they see the start of the form
    window.scrollTo(0, 0);
}

function closeEditor() {
    // 1. Hide the Editor
    document.getElementById('editorView').style.display = 'none';
    
    // 2. Show the Master List again
    document.getElementById('masterListView').style.display = 'block';
}

function submitRequirement() {
    alert("Requirement has been uploaded and sent for review!");
    closeEditor();
    
    // Optional: You could update the status badge here 
    // to "Complete" in a real scenario.
}

window.onload = function() {
    // 1. Get the URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const taskType = urlParams.get('task');

    // 2. If the 'task' parameter exists, trigger the editor automatically
    if (taskType === 'narrative') {
        openEditor('Weekly Narrative Report');
    } else if (taskType === 'moa') {
        openEditor('Memorandum of Agreement (MOA)');
    }
};

// Your existing openEditor and closeEditor functions below...
function openEditor(requirementName) {
    document.getElementById('masterListView').style.display = 'none';
    document.getElementById('editorTitle').innerHTML = "<b>Editing: " + requirementName + "</b>";
    document.getElementById('editorView').style.display = 'block';
    window.scrollTo(0, 0);
}

function closeEditor() {
    // When closing, we should also clean the URL so refreshing doesn't re-open the editor
    window.history.replaceState({}, document.title, "portfolio.php");
    
    document.getElementById('editorView').style.display = 'none';
    document.getElementById('masterListView').style.display = 'block';
}
</script>