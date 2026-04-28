<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - RIAS Dashboard</title>
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
        </div>
    </header>

    <main class="main-content">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title"><b>Edit Profile & Supervisor Details</b></div>
                <button onclick="window.location.href='student.php'" class="view-all">← Back to Dashboard</button>
            </div>

    

            <form action="update_logic.php" method="POST" class="edit-form-container">

                <div class="info-group">
                    <label>Internship Status</label>
                    <div class="status-selector-mini">
                        <input type="hidden" name="intern_status" id="internStatus" value="waiting">
                        <button type="button" class="s-btn s-deployed" onclick="setStatus('deployed')" title="Deployed">D</button>
                        <button type="button" class="s-btn s-waiting active" onclick="setStatus('waiting')" title="Waiting">W</button>
                        <button type="button" class="s-btn s-no-progress" onclick="setStatus('no-progress')" title="No Progress">NP</button>
                        <span id="status-label" style="font-size: 11px; margin-left: 8px; color: #666; font-weight: 500;">Waiting</span>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Internship Information</h3>
                    <div class="input-group">
                        <label for="job_role">Your Current Job Role</label>
                        <input type="text" id="job_role" name="job_role" placeholder="e.g. Front-end Developer Intern" value="Front-end Developer Intern">
                    </div>
                </div>

                <div class="form-section">
                    <h3>Industry Professor / Supervisor Details</h3>
                    <div class="input-group">
                        <label for="supervisor_name">Supervisor Name</label>
                        <input type="text" id="supervisor_name" name="supervisor_name" placeholder="Full Name">
                    </div>
                    
                    <div class="form-row-dual">
                        <div class="input-group">
                            <label for="supervisor_email">Supervisor Email</label>
                            <input type="email" id="supervisor_email" name="supervisor_email" placeholder="email@company.com">
                        </div>
                        <div class="input-group">
                            <label for="supervisor_contact">Contact Number</label>
                            <input type="text" id="supervisor_contact" name="supervisor_contact" placeholder="+63 XXX XXX XXXX">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-action btn-edit" onclick="window.location.href='student.php'">Cancel</button>
                    
                    <button type="button" class="btn-action btn-portfolio" onclick="handleSave()">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<script>
function handleSave() {
    // 1. Show the success popup
    alert("Profile changes have been saved successfully!");

    // 2. Redirect back to the student homepage
    window.location.href = 'student.php';
}
</script>

<script>
function setStatus(status) {
    // 1. Update hidden input and active button state
    document.getElementById('internStatus').value = status;
    const buttons = document.querySelectorAll('.s-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.s-${status}`).classList.add('active');

    // 2. Update status text label
    const label = document.getElementById('status-label');
    label.innerText = status === 'no-progress' ? 'Undeployed' : 
                     status.charAt(0).toUpperCase() + status.slice(1);

    // 3. Select all interactive elements in editProfile
    // We target inputs, textareas, and the action buttons
    const formElements = document.querySelectorAll('input:not([type="hidden"]), textarea, .btn-save, .btn-submit');
    const isUndeployed = (status === 'no-progress');

    formElements.forEach(el => {
        el.disabled = isUndeployed;
        
        if (isUndeployed) {
            el.style.backgroundColor = "#f0f0f0"; // Gray background
            el.style.color = "#999";             // Dimmed text
            el.style.cursor = "not-allowed";     // Visual "lock" cursor
            el.style.opacity = "0.7";
        } else {
            el.style.backgroundColor = "";       // Reset to default
            el.style.color = "";                 // Reset to default
            el.style.cursor = "pointer";
            el.style.opacity = "1";
        }
    });
}
</script>