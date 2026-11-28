<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIAS Internship Officer Dashboard</title>
    
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
            <!-- Notification Detail Panel -->
            <div class="notif-detail-panel" id="notifDetailPanel" style="display: none;">
                <h4 style="margin-bottom: 15px; color: #1e3b99; font-size: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Notification Details</h4>
                <div id="detailBody" style="font-size: 13px; color: #555; line-height: 1.6;"></div>
            </div>
            <details class="profile-menu">
                <summary class="profile-circle" aria-label="Account options"></summary>
                <div class="profile-panel">
                    <a class="logout-menu-item" href="../login/logout.php">Log out</a>
                </div>
            </details>
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
            <div class="nav-item" onclick="window.location.href='mapping.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                <span class="nav-label">Mapping</span>
            </div>
            <div class="nav-item active" onclick="window.location.href='requirements.php'">
              <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span class="nav-label">Requirements</span>
            </div>
        </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="header-left">
                <div class="date-text" id="currentDate">Wednesday, April 29, 2026</div>
                <div class="time-text" id="currentTime">09:30 AM</div>
            </div>
            <div class="header-right">
                <button class="btn-action" id="refreshRequirementsBtn" type="button" style="background-color: #29429c; color: white; padding: 8px 16px; border-radius: 5px; font-weight: 500;">
                    Refresh Checklist
                </button>
            </div>
        </div>

        <div class="stats-row" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;">
            <div class="panel stat-panel-accent">
                <div class="stat-label">Monthly Attendance Reports</div>
                <div class="stat-main">
                    <span class="stat-number" id="attendance-submitted">—</span>
                    <span class="stat-total">/ <span id="attendance-due">—</span></span>
                </div>
                <div class="stat-subtext" id="attendance-completion">Loading...</div>
            </div>

            <div class="panel stat-panel-accent">
                <div class="stat-label">Monthly Accomplishment Reports</div>
                <div class="stat-main">
                    <span class="stat-number" id="accomplishment-submitted">—</span>
                    <span class="stat-total">/ <span id="accomplishment-due">—</span></span>
                </div>
                <div class="stat-subtext" id="accomplishment-completion">Loading...</div>
            </div>

            <div class="panel stat-panel-accent" style="border-left-color: #27bda1;">
                <div class="stat-label">Final Report + Narrative</div>
                <div class="stat-main">
                    <span class="stat-number" id="final-submitted">—</span>
                    <span class="stat-total">/ <span id="final-due">—</span></span>
                </div>
                <div class="stat-subtext" id="final-completion">Required at internship end</div>
            </div>

            <div class="panel stat-panel-accent" style="border-left-color: #f1b347;">
                <div class="stat-label">Internship Status</div>
                <div class="stat-main">
                    <span class="stat-number" id="student-count">—</span>
                    <span class="stat-total">interns</span>
                </div>
                <div class="stat-subtext" id="status-breakdown">Loading...</div>
            </div>
        </div>

        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-bottom: 1px solid #eee;">
            <div class="panel-title" style="font-size: 15px; color: #1e3b99;"><b>Student Requirements Checklist</b></div>
            
            <div class="header-controls" style="display: flex; gap: 10px; align-items: center;">
                <select id="statusFilter" class="filter-select" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; color: #666;">
                    <option value="all">All Submissions</option>
                    <option value="attendance">Submitted Attendance</option>
                    <option value="accomplishment">Submitted Accomplishment</option>
                    <option value="final">Complete Final Report + Narrative</option>
                    <option value="incomplete">Incomplete Only</option>
                </select>

                <div class="search-box" style="position: relative;">
                    <input type="text" id="studentSearch" placeholder="Search name..." 
                        style="padding: 6px 12px; width: 180px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; transition: width 0.3s ease;">
                </div>
            </div>
        </div>

        <div class="table-responsive" style="padding: 10px;">
            <table class="excel-mapping-table">
                <thead>
                    <tr>
                        <th style="width: 250px;">Student Name</th>
                        <th>Monthly Attendance</th>
                        <th>Monthly Accomplishment</th>
                        <th>Final Report + Narrative</th>
                        <th>Internship Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="requirements-list">
                    <tr><td colspan="6" style="text-align: center; padding: 24px;">Loading student requirements...</td></tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>

    <script src="adminscript.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('studentSearch');
    const filterSelect = document.getElementById('statusFilter');
    const tableBody = document.getElementById('requirements-list');
    const refreshButton = document.getElementById('refreshRequirementsBtn');

    function setSummaryCount(id, count) {
        document.getElementById(id).textContent = String(count);
    }

    function completionText(submitted, due) {
        return due > 0
            ? `${Math.round((submitted / due) * 100)}% submitted`
            : 'No reports due yet';
    }

    function renderSummary(summary) {
        setSummaryCount('attendance-submitted', summary.attendance_submitted);
        setSummaryCount('attendance-due', summary.attendance_due);
        document.getElementById('attendance-completion').textContent =
            completionText(summary.attendance_submitted, summary.attendance_due);

        setSummaryCount('accomplishment-submitted', summary.accomplishment_submitted);
        setSummaryCount('accomplishment-due', summary.accomplishment_due);
        document.getElementById('accomplishment-completion').textContent =
            completionText(summary.accomplishment_submitted, summary.accomplishment_due);

        setSummaryCount('final-submitted', summary.final_submitted);
        setSummaryCount('final-due', summary.final_due);
        document.getElementById('final-completion').textContent =
            summary.final_due > 0 ? 'Complete when report and narrative are received' : 'Required at internship end';

        setSummaryCount('student-count', summary.student_count);
        const statuses = summary.status_counts;
        const statusBreakdown = [
            `${statuses.ongoing} ongoing`,
            `${statuses.completed} completed`,
            `${statuses.failed} failed`,
            `${statuses.withdrawn} withdrawn`,
            `${statuses.not_started} not started`
        ];
        if (statuses.other > 0) statusBreakdown.push(`${statuses.other} other statuses`);
        document.getElementById('status-breakdown').textContent = statusBreakdown.join(' · ');
    }

    function appendBadge(cell, text, tone) {
        const badge = document.createElement('span');
        badge.className = 'status-tag';
        badge.textContent = text;
        badge.style.color = tone;
        cell.appendChild(badge);
    }

    function reportBadgeText(submitted, due) {
        if (due === 0) return { text: 'Not due', tone: '#888' };
        if (submitted >= due) return { text: `${submitted} / ${due} submitted`, tone: '#27bda1' };
        if (submitted > 0) return { text: `${submitted} / ${due} submitted`, tone: '#f1b347' };
        return { text: `0 / ${due} submitted`, tone: '#e74c3c' };
    }

    function applyFilters() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const filterValue = filterSelect.value;
        tableBody.querySelectorAll('tr[data-student-name]').forEach(row => {
            const matchesSearch = row.dataset.studentName.includes(searchTerm);
            let matchesFilter = true;
            if (filterValue === 'attendance') matchesFilter = row.dataset.attendanceSubmitted === 'true';
            if (filterValue === 'accomplishment') matchesFilter = row.dataset.accomplishmentSubmitted === 'true';
            if (filterValue === 'final') matchesFilter = row.dataset.finalComplete === 'true';
            if (filterValue === 'incomplete') matchesFilter = row.dataset.incomplete === 'true';
            row.hidden = !(matchesSearch && matchesFilter);
        });
    }

    async function sendReminder(button, userId) {
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Sending...';
        try {
            const response = await fetch('requirements_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'The reminder could not be sent.');
            }
            button.textContent = 'Reminder Sent';
        } catch (error) {
            button.disabled = false;
            button.textContent = originalText;
            alert(error.message || 'The reminder could not be sent.');
        }
    }

    function renderStudents(students) {
        tableBody.replaceChildren();
        if (students.length === 0) {
            const row = tableBody.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 6;
            cell.style.textAlign = 'center';
            cell.style.padding = '24px';
            cell.textContent = 'No active interns found.';
            return;
        }

        students.forEach(student => {
            const row = tableBody.insertRow();
            const studentName = `${student.last_name}, ${student.first_name}`.trim().toLowerCase();
            row.dataset.studentName = `${studentName} ${student.student_id} ${student.program_name}`.toLowerCase();
            row.dataset.attendanceSubmitted = String(student.attendance_submitted > 0);
            row.dataset.accomplishmentSubmitted = String(student.accomplishment_submitted > 0);
            row.dataset.finalComplete = String(student.final_complete);
            row.dataset.incomplete = String(
                student.requirements_due > student.requirements_submitted
            );

            const studentCell = row.insertCell();
            studentCell.style.fontWeight = '500';
            const name = document.createElement('div');
            name.textContent = `${student.last_name}, ${student.first_name}`.trim();
            studentCell.appendChild(name);
            const details = document.createElement('small');
            details.style.display = 'block';
            details.style.marginTop = '4px';
            details.style.color = '#888';
            details.textContent = [
                student.student_id,
                student.program_name,
                student.internship_year ? `Year ${student.internship_year}` : ''
            ].filter(Boolean).join(' · ');
            studentCell.appendChild(details);

            const attendanceCell = row.insertCell();
            const attendance = reportBadgeText(student.attendance_submitted, student.attendance_due);
            appendBadge(attendanceCell, attendance.text, attendance.tone);

            const accomplishmentCell = row.insertCell();
            const accomplishment = reportBadgeText(student.accomplishment_submitted, student.accomplishment_due);
            appendBadge(accomplishmentCell, accomplishment.text, accomplishment.tone);

            const finalCell = row.insertCell();
            if (!student.final_due) {
                appendBadge(finalCell, 'Not due', '#888');
            } else if (student.final_complete) {
                appendBadge(finalCell, 'Report + narrative received', '#27bda1');
            } else if (student.final_report_submitted) {
                appendBadge(finalCell, 'Report received · narrative pending', '#f1b347');
            } else if (student.final_narrative_submitted) {
                appendBadge(finalCell, 'Narrative received · report pending', '#f1b347');
            } else {
                appendBadge(finalCell, 'Pending', '#e74c3c');
            }

            const statusCell = row.insertCell();
            statusCell.textContent = student.internship_status;

            const actionCell = row.insertCell();
            actionCell.style.textAlign = 'center';
            const notifyButton = document.createElement('button');
            notifyButton.type = 'button';
            notifyButton.className = 'notify-btn';
            notifyButton.textContent = 'Notify Student';
            notifyButton.disabled = student.requirements_due <= student.requirements_submitted;
            notifyButton.addEventListener('click', () => sendReminder(notifyButton, student.user_id));
            actionCell.appendChild(notifyButton);
        });

        applyFilters();
    }

    async function loadRequirements() {
        refreshButton.disabled = true;
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 24px;">Loading student requirements...</td></tr>';
        try {
            const response = await fetch('requirements_api.php', { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Student requirements could not be loaded.');
            }
            renderSummary(result.summary);
            renderStudents(result.students);
        } catch (error) {
            console.error('Unable to load student requirements:', error);
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 24px; color: #c0392b;">Student requirements could not be loaded. Please try again.</td></tr>';
            document.getElementById('attendance-completion').textContent = 'Unable to load data';
            document.getElementById('accomplishment-completion').textContent = 'Unable to load data';
            document.getElementById('final-completion').textContent = 'Unable to load data';
            document.getElementById('status-breakdown').textContent = 'Unable to load data';
        } finally {
            refreshButton.disabled = false;
        }
    }

    searchInput.addEventListener('input', applyFilters);
    filterSelect.addEventListener('change', applyFilters);
    refreshButton.addEventListener('click', loadRequirements);
    loadRequirements();
});
</script>