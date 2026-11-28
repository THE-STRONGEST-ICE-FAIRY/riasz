// 1. Real-Time Clock & Date
function updateTime() {
    const now = new Date();

    // Format Date (MM/DD/YYYY)
    const dateStr = String(now.getMonth()+1).padStart(2,'0') + '/' +
                    String(now.getDate()).padStart(2,'0') + '/' +
                    now.getFullYear();

    // Format Time (HH:MM:SS AM/PM)
    let hrs = now.getHours();
    const ampm = hrs >= 12 ? 'PM' : 'AM';
    hrs = hrs % 12 || 12;
    const timeStr = String(hrs).padStart(2,'0') + ':' +
                    String(now.getMinutes()).padStart(2,'0') + ':' +
                    String(now.getSeconds()).padStart(2,'0') + ' ' + ampm;

    // Only update if elements exist
    if (document.getElementById('real-time-date'))
        document.getElementById('real-time-date').innerText = dateStr;
    if (document.getElementById('real-time-clock'))
        document.getElementById('real-time-clock').innerText = timeStr;
    if (document.getElementById('current-year'))
        document.getElementById('current-year').innerText = now.getFullYear();

}

// Initialize clock immediately, then update every second
setInterval(updateTime, 1000);
updateTime();

// 2. Sidebar Toggle
document.getElementById('menuToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('expanded');
});

const bellIcon = document.getElementById('bellIcon');
const notifDropdown = document.getElementById('notifDropdown');
const notifBadge = document.getElementById('notifBadge');
const noNotifMsg = document.getElementById('noNotifMsg');
const notifDetailPanel = document.getElementById('notifDetailPanel');
const detailBody = document.getElementById('detailBody');
let unreadCount = 0;
let dashboardNotifications = [];
document.getElementById('evaluationReportBody')?.replaceChildren();

function setText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = String(value);
}

function renderCalendarEvents(events) {
    const tbody = document.getElementById('calendar-events-tbody');
    if (!tbody) return;
    tbody.replaceChildren();

    if (events.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 2;
        cell.textContent = 'No calendar events for this academic year.';
        row.appendChild(cell);
        tbody.appendChild(row);
        return;
    }

    events.forEach(event => {
        const row = document.createElement('tr');
        const dateCell = document.createElement('td');
        const titleCell = document.createElement('td');
        const eventDate = new Date(`${event.date}T00:00:00`);
        dateCell.textContent = Number.isNaN(eventDate.getTime())
            ? event.date
            : eventDate.toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' });
        const time = [event.start_time, event.end_time].filter(Boolean).join(' - ');
        titleCell.textContent = `${event.title}${time ? ` (${time})` : ''}`;
        row.append(dateCell, titleCell);
        tbody.appendChild(row);
    });
}

function renderEvaluationReport(rows) {
    const tbody = document.getElementById('evaluationReportBody');
    if (!tbody) return;
    tbody.replaceChildren();

    if (rows.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 4;
        cell.textContent = 'No active internships are assigned to industry partners.';
        row.appendChild(cell);
        tbody.appendChild(row);
        return;
    }

    rows.forEach(report => {
        const row = document.createElement('tr');
        [report.name, report.company_name, report.email, report.status].forEach(value => {
            const cell = document.createElement('td');
            cell.textContent = value || '';
            row.appendChild(cell);
        });
        tbody.appendChild(row);
    });
}

function renderNotifications(items, unread) {
    dashboardNotifications = items;
    unreadCount = unread;
    notifDropdown.querySelectorAll('.notif-item').forEach(item => item.remove());
    noNotifMsg.style.display = items.length ? 'none' : 'block';

    items.forEach(item => {
        const notification = document.createElement('div');
        notification.className = 'notif-item';
        notification.textContent = item.message;
        notification.addEventListener('click', () => {
            detailBody.replaceChildren();
            const type = document.createElement('strong');
            type.textContent = item.type || 'Notification';
            const message = document.createElement('div');
            message.textContent = item.message;
            const created = document.createElement('div');
            created.textContent = item.created_at;
            detailBody.append(type, message, created);
            notifDetailPanel.style.display = 'block';
        });
        notifDropdown.insertBefore(notification, noNotifMsg);
    });

    if (unreadCount > 0) {
        notifBadge.textContent = unreadCount;
        notifBadge.style.display = 'block';
    } else {
        notifBadge.style.display = 'none';
    }
}

async function refreshNotifications() {
    const response = await fetch('manage_notifications.php?action=notifications', { cache: 'no-store' });
    const result = await response.json();
    if (!response.ok || result.status !== 'success') {
        throw new Error(result.message || 'Could not refresh notifications.');
    }

    renderNotifications(result.data.notifications, result.data.unread_count);
}

function populateRecipientFilters(schools, programs) {
    const roleSelect = document.getElementById('taskRole');
    const schoolSelect = document.getElementById('taskSchool');
    const programSelect = document.getElementById('taskProgram');
    if (!roleSelect || !schoolSelect || !programSelect) return;

    schoolSelect.replaceChildren(new Option('All Schools', ''));
    schools.forEach(school => schoolSelect.add(new Option(school.name, school.id)));

    const updatePrograms = () => {
        const role = roleSelect.value;
        const canFilterSchool = role !== 'supervisor';
        const canFilterProgram = role !== 'supervisor' && role !== 'executive';
        schoolSelect.disabled = !canFilterSchool;
        programSelect.disabled = !canFilterProgram;
        if (!canFilterSchool) schoolSelect.value = '';
        if (!canFilterProgram) programSelect.value = '';

        const selectedSchool = schoolSelect.value;
        const filteredPrograms = selectedSchool
            ? programs.filter(program => String(program.school_id) === selectedSchool)
            : programs;
        programSelect.replaceChildren(new Option('All Programs', ''));
        filteredPrograms.forEach(program => programSelect.add(new Option(program.name, program.id)));
    };

    roleSelect.onchange = updatePrograms;
    schoolSelect.onchange = updatePrograms;
    updatePrograms();
}

async function loadDashboard() {
    const response = await fetch('manage_notifications.php?action=dashboard', { cache: 'no-store' });
    const result = await response.json();
    if (!response.ok || result.status !== 'success') {
        throw new Error(result.message || 'Could not load dashboard data.');
    }

    const data = result.data;
    const counts = data.role_counts;
    setText('statTotalUsers', counts.total_users);
    setText('statStudentInterns', counts.student_interns);
    setText('statInternshipOfficers', counts.internship_officers);
    setText('statProgramDirectors', counts.program_directors);
    setText('statExecutiveDirectors', counts.executive_directors);
    setText('statIndustryPartners', counts.industry_partners);
    setText('statusDeployed', data.status_counts.deployed);
    setText('statusWaiting', data.status_counts.waiting);
    setText('statusUndeployed', data.status_counts.undeployed);
    setText('statusUnlisted', data.status_counts.unlisted);
    setText('evaluationSubmitted', data.evaluation_progress.submitted);
    setText('evaluationPending', data.evaluation_progress.pending);
    setText('display-acad-year', data.academic_year.label);

    const academicStart = document.getElementById('acadStartDate');
    const academicEnd = document.getElementById('acadEndDate');
    if (academicStart && data.academic_year.start) academicStart.value = data.academic_year.start;
    if (academicEnd && data.academic_year.end) academicEnd.value = data.academic_year.end;

    renderCalendarEvents(data.events);
    renderEvaluationReport(data.evaluation_report);
    renderNotifications(data.notifications, data.unread_count);
    populateRecipientFilters(data.schools, data.programs);
}

loadDashboard().catch(error => {
    console.error('Could not load dashboard data:', error);
    alert(error.message);
});

const notificationRefreshInterval = 15000;
setInterval(() => {
    if (document.visibilityState === 'visible') {
        refreshNotifications().catch(error => {
            console.error('Could not refresh notifications:', error);
        });
    }
}, notificationRefreshInterval);

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        refreshNotifications().catch(error => {
            console.error('Could not refresh notifications:', error);
        });
    }
});

bellIcon.addEventListener('click', async (e) => {
    e.stopPropagation();
    const isOpening = !notifDropdown.classList.contains('active');
    if (isOpening) {
        try {
            await refreshNotifications();
        } catch (error) {
            console.error('Could not refresh notifications:', error);
        }
    }
    notifDropdown.classList.toggle('active');
    
    if (notifDropdown.classList.contains('active')) {
        if (unreadCount > 0) {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            try {
                const response = await fetch('manage_notifications.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Could not mark notifications as read.');
                }
                unreadCount = 0;
                dashboardNotifications.forEach(item => { item.is_read = 1; });
                notifBadge.style.display = 'none';
            } catch (error) {
                console.error('Could not mark notifications as read:', error);
                alert(error.message);
            }
        }
    } else {
        if (notifDetailPanel) notifDetailPanel.style.display = 'none';
    }
});

notifDropdown.addEventListener('click', (e) => e.stopPropagation());
if (notifDetailPanel) notifDetailPanel.addEventListener('click', (e) => e.stopPropagation());

document.addEventListener('click', () => {
    notifDropdown.classList.remove('active');
    if (notifDetailPanel) notifDetailPanel.style.display = 'none';
});

// 4. Academic Year Dropdown Logic
const changeYearBtn = document.getElementById('changeYearBtn');
const yearDropdown = document.getElementById('yearDropdown');
const displayAcadYear = document.getElementById('display-acad-year');
const applyAcadYearBtn = document.getElementById('applyAcadYearBtn');
const acadStartDate = document.getElementById('acadStartDate');
const acadEndDate = document.getElementById('acadEndDate');

if (changeYearBtn) {
    changeYearBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        yearDropdown.classList.toggle('active');
    });
}

if (yearDropdown) {
    // Prevent dropdown from closing when clicking inside it
    yearDropdown.addEventListener('click', (e) => {
        e.stopPropagation();
    });
}

if (applyAcadYearBtn) {
    applyAcadYearBtn.addEventListener('click', async () => {
        if (!acadStartDate.value || !acadEndDate.value || acadStartDate.value > acadEndDate.value) {
            alert('Choose a valid start and end date for the academic year.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'save_academic_year');
        formData.append('start_date', acadStartDate.value);
        formData.append('end_date', acadEndDate.value);
        applyAcadYearBtn.disabled = true;
        try {
            const response = await fetch('manage_notifications.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Could not save the academic year.');
            }
            await loadDashboard();
            yearDropdown.classList.remove('active');
        } catch (error) {
            console.error('Could not save academic year:', error);
            alert(error.message);
        } finally {
            applyAcadYearBtn.disabled = false;
        }
    });
}

if (yearDropdown) {
    document.addEventListener('click', () => {
        yearDropdown.classList.remove('active');
    });
}

// 5. Modal Logic (Set Event/Task)
const modalOverlay = document.getElementById('taskModalOverlay');
const openModalBtn = document.getElementById('openTaskModalBtn');
const closeModalBtn = document.getElementById('closeModalBtn');
const saveTaskBtn = document.getElementById('saveTaskBtn');

if (openModalBtn) {
    openModalBtn.addEventListener('click', () => {
        modalOverlay.classList.add('active');
    });
}

if (closeModalBtn) {
    closeModalBtn.addEventListener('click', () => {
        modalOverlay.classList.remove('active');
    });
}

if (modalOverlay) {
    // Close modal if clicking outside the white box
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            modalOverlay.classList.remove('active');
        }
    });
}

// Save the calendar item and optional system notifications.
if (saveTaskBtn) {
    saveTaskBtn.addEventListener('click', async () => {
        const titleInput = document.getElementById('taskTitle');
        const dateInput = document.getElementById('taskDate');
        const startTimeInput = document.getElementById('taskStartTime');
        if (!titleInput.value.trim() || !dateInput.value || !startTimeInput.value) {
            alert('Please fill in the title, date, and start time before saving.');
            return;
        }

        const targetRole = document.getElementById('taskRole').value;
        const notifyAll = document.getElementById('notifyAll').checked;
        if (notifyAll && !targetRole) {
            alert('Select a recipient role before choosing Notify All.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'create_event');
        formData.append('title', titleInput.value.trim());
        formData.append('date', dateInput.value);
        formData.append('start_time', startTimeInput.value);
        formData.append('end_time', document.getElementById('taskEndTime').value);
        formData.append('description', document.getElementById('taskDescription').value.trim());
        formData.append('event_type', document.querySelector('.tab-btn.active')?.dataset.eventType || 'task');
        formData.append('target_role', targetRole);
        formData.append('target_school_id', document.getElementById('taskSchool').value);
        formData.append('target_program_id', document.getElementById('taskProgram').value);
        formData.append('notify_all', notifyAll ? '1' : '0');
        formData.append('notification_method', document.getElementById('notificationChannel').value);

        const originalText = saveTaskBtn.textContent;
        saveTaskBtn.textContent = 'Saving...';
        saveTaskBtn.disabled = true;

        try {
            const response = await fetch('manage_notifications.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Could not save the calendar item.');
            }

            modalOverlay.classList.remove('active');
            document.querySelectorAll('.task-modal .input-box').forEach(input => { input.value = ''; });
            document.querySelectorAll('.task-modal .select-box').forEach(select => { select.selectedIndex = 0; });
            document.getElementById('notifyAll').checked = false;
            const today = new Date();
            dateInput.value = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
            await loadDashboard();
            alert(result.message);
        } catch (error) {
            console.error('Could not save calendar item:', error);
            alert(error.message);
        } finally {
            saveTaskBtn.textContent = originalText;
            saveTaskBtn.disabled = false;
        }
    });
}

// 6. Modal Tabs Toggle (EVENT / TASK)
const tabBtns = document.querySelectorAll('.tab-btn');
tabBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        tabBtns.forEach(b => b.classList.remove('active', 'inactive'));
        tabBtns.forEach(b => b.classList.add('inactive'));
        this.classList.remove('inactive');
        this.classList.add('active');
        // You can add logic here to show/hide fields depending on tab
    });
});

const notifyRemainingBtn = document.getElementById('notifyRemainingBtn');
if (notifyRemainingBtn) {
    notifyRemainingBtn.addEventListener('click', async () => {
        const formData = new FormData();
        formData.append('action', 'notify_pending');
        notifyRemainingBtn.disabled = true;
        try {
            const response = await fetch('manage_notifications.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Could not send evaluation reminders.');
            }
            alert(result.message);
        } catch (error) {
            console.error('Could not send evaluation reminders:', error);
            alert(error.message);
        } finally {
            notifyRemainingBtn.disabled = false;
        }
    });
}

// 7. Simple Calendar View
function renderCalendar() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    const today = now.getDate();

    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    let html = `<div style="text-align:center;font-weight:bold;margin-bottom:5px;">${months[month]} ${year}</div>`;
    html += `<table style="width:100%;border-collapse:collapse;font-size:13px;text-align:center;">
        <tr style="color:#1e3b99;"><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th></tr><tr>`;

    let day = 1;
    for (let i = 0; i < 6; i++) { // 6 weeks max
        for (let j = 0; j < 7; j++) {
            if ((i === 0 && j < firstDay) || day > daysInMonth) {
                html += `<td style="padding:4px 0;"></td>`;
            } else {
                let style = (day === today) ? 'background:#f1b347;color:white;border-radius:50%;font-weight:bold;' : '';
                html += `<td style="padding:4px 0;${style}">${day}</td>`;
                day++;
            }
        }
        html += '</tr>';
        if (day > daysInMonth) break;
        if (i < 5) html += '<tr>';
    }
    html += '</table>';
    const calendarView = document.getElementById('calendar-view');
    if (calendarView) {
        calendarView.innerHTML = html;
    }
}
renderCalendar();

// --- Modal Date & Time Pickers Setup ---
const taskDateInput = document.getElementById('taskDate');

// Set initial value
if (taskDateInput) {
    const today = new Date();
    const localDate = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
    taskDateInput.value = localDate;
}

// --- 8. Last Visited Page Tracking ---
const pageNames = {
    '_index.php': 'Home',
    'master_list.php': 'Master List',
    'reports.php': 'Reports',
    'progress.php': 'Progress',
    'mapping.php': 'Mapping'
};

// Get the exact filename from the URL, or default to Home if omitted
let currentPath = window.location.pathname.split('/').pop();
if (!currentPath) currentPath = '_index.php';

let lastPath = localStorage.getItem('currentPagePath');

// If the user navigated to a different page, update the 'last visited' record
if (lastPath && lastPath !== currentPath) {
    localStorage.setItem('lastVisitedPath', lastPath);
}
localStorage.setItem('currentPagePath', currentPath);

const lastVisitedPath = localStorage.getItem('lastVisitedPath');
const lastVisitedLink = document.getElementById('last-visited-link');

if (lastVisitedLink) {
    if (lastVisitedPath && pageNames[lastVisitedPath] && lastVisitedPath !== currentPath) {
        lastVisitedLink.href = lastVisitedPath;
        lastVisitedLink.innerText = `Go to your last visited page: ${pageNames[lastVisitedPath]} ↩`;
        lastVisitedLink.style.display = 'inline';
    } else {
        lastVisitedLink.style.display = 'none';
    }
}

// --- 9. File Upload & Pie Chart Logic ---
const fileUpload = document.getElementById('internDataUpload');
let internChart = null;

if (fileUpload) {
    fileUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                // XLSX parses both Excel AND CSV automatically
                const workbook = XLSX.read(data, {type: 'array'});
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, {header: 1});
                
                processChartData(jsonData);
            } catch (err) {
                console.error("Error parsing file:", err);
                alert("Failed to parse file. Ensure it is a valid CSV or Excel file.");
            }
        };
        reader.readAsArrayBuffer(file);
    });
}

function processChartData(data) {
    // Filter out completely empty rows
    data = data.filter(row => row && row.length > 0);
    if (!data || data.length === 0) return;
    
    const firstRow = data[0];
    if (!firstRow || firstRow.length === 0) return;
    
    let labels = [];
    let values = [];
    
    // Check if second column exists and next rows have numeric values in it (Key-Value format)
    let isKeyValue = firstRow.length >= 2 && data.length > 1 && !isNaN(Number(data[1][1]));
    
    if (isKeyValue) {
        // Skip header row if it's text-text, but data is text-number
        let startIdx = isNaN(Number(data[0][1])) ? 1 : 0;
        for(let i = startIdx; i < data.length; i++) {
            labels.push(data[i][0] || 'Unknown');
            values.push(Number(data[i][1]) || 0);
        }
    } else {
        // Frequency map of the first column (e.g., flat list of programs row by row)
        let counts = {};
        // Assume first row is header, start at 1
        for(let i = 1; i < data.length; i++) {
            let val = data[i][0];
            if (val !== undefined && val !== null && val !== '') {
                counts[val] = (counts[val] || 0) + 1;
            }
        }
        labels = Object.keys(counts);
        values = Object.values(counts);
    }
    
    renderPieChart(labels, values);
}

function renderPieChart(labels, values) {
    const canvas = document.getElementById('internPieChart');
    const placeholder = document.getElementById('chartPlaceholderText');
    const legendContainer = document.getElementById('chartLegend');
    const titleContainer = document.getElementById('internTotalTitle');
    
    if (!canvas) return;
    
    canvas.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
    
    if (internChart) {
        internChart.destroy();
    }
    
    const backgroundColors = ['#ff8a8a', '#2e96ff', '#27bda1', '#f1b347', '#9b59b6', '#e67e22', '#34495e', '#16a085', '#e74c3c'];
    
    // Check if Chart is available via CDN
    if (typeof Chart === 'undefined') {
        alert("Chart.js failed to load. Please check your internet connection.");
        return;
    }

    internChart = new Chart(canvas, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // Hide default legend to use our custom formatted one
                }
            }
        }
    });
    
    // Populate Custom Legend and dynamic title
    if (legendContainer) {
        let legendHTML = '';
        let total = values.reduce((a, b) => a + b, 0);
        if (titleContainer) titleContainer.innerText = `${total} Interns`;
        
        labels.forEach((label, i) => {
            let color = backgroundColors[i % backgroundColors.length];
            legendHTML += `<div><span style="color: ${color};">●</span> ${label} <span style="float: right; background: #1e3b99; color: white; padding: 2px 6px; border-radius: 4px;">${values[i]}</span></div>`;
        });
        legendContainer.innerHTML = legendHTML;
    }
}

// --- 10. Dynamic Program Dropdown based on School ---
const taskSchoolSelect = document.getElementById('taskSchool');
const taskProgramSelect = document.getElementById('taskProgram');

if (taskSchoolSelect && taskProgramSelect) {
    const programsBySchool = {
        'SoE': ['Civil Engineering', 'Computer Engineering', 'Electronics Engineering'],
        'SoCIT': ['Computer Science', 'Information Technologies'],
        'SoM': ['Accountancy', 'Business Administration', 'Tourism Management'],
        'SoMA': ['Psychology', 'Multimedia Arts']
    };

    taskSchoolSelect.addEventListener('change', function() {
        const school = this.value;
        taskProgramSelect.innerHTML = '<option value="">All Programs</option>';
        
        if (programsBySchool[school]) {
            programsBySchool[school].forEach(program => {
                const option = document.createElement('option');
                option.value = program;
                option.textContent = program;
                taskProgramSelect.appendChild(option);
            });
        }
    });
}