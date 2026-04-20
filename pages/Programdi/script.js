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

    // Mock Login Date logic (Setting it to a few days ago for realism)
    const login = new Date(now);
    login.setDate(login.getDate() - 3);
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    if (document.getElementById('login-date'))
        document.getElementById('login-date').innerText = `${days[login.getDay()]}, ${months[login.getMonth()]} ${String(login.getDate()).padStart(2,'0')}, ${login.getFullYear()}`;
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

bellIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    notifDropdown.classList.toggle('active');
    
    if (notifDropdown.classList.contains('active')) {
        if (notifBadge) {
            notifBadge.style.display = 'none';
            unreadCount = 0;
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
    applyAcadYearBtn.addEventListener('click', () => {
        if (acadStartDate.value && acadEndDate.value) {
            const startYear = new Date(acadStartDate.value).getFullYear();
            const endYear = new Date(acadEndDate.value).getFullYear();
            
            displayAcadYear.innerText = `${startYear}-${endYear}`;
            
            // Add a brief flash effect to show it updated
            displayAcadYear.style.color = '#f1b347';
            setTimeout(() => displayAcadYear.style.color = '#333', 500);
            
            yearDropdown.classList.remove('active');
        } else {
            alert('Please select both a start date and an end date.');
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

// Save Button Action - Creates Notification
if (saveTaskBtn) {
    saveTaskBtn.addEventListener('click', async () => {
        const titleInput = document.getElementById('taskTitle');
        const dateInput = document.getElementById('taskDate');
        const startTimeInput = document.getElementById('taskStartTime');

        // Show error message if required inputs are missing
        if (!titleInput.value.trim() || !dateInput.value || !startTimeInput.value) {
            alert("Error: Please fill in the required inputs (Title, Date, Start Time) before saving.");
            return;
        }

        const title = document.getElementById('taskTitle').value;
        const date = document.getElementById('taskDate').value;
        const startTime = document.getElementById('taskStartTime').value;
        const endTime = document.getElementById('taskEndTime').value;

        // Prepare data to send
        const formData = new FormData();
        formData.append('title', title);
        formData.append('date', date);
        formData.append('start_time', startTime);
        formData.append('end_time', endTime);
        formData.append('action', 'create_notification');

        // Change button text to show loading state
        const originalText = saveTaskBtn.innerText;
        saveTaskBtn.innerText = 'Saving...';
        saveTaskBtn.disabled = true;

        try {
            const response = await fetch('manage_notifications.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                // Add notification to the dropdown dynamically
                const notifHeader = notifDropdown.querySelector('.notif-header');
                
                if (noNotifMsg) noNotifMsg.style.display = 'none';
                
                unreadCount++;
                if (notifBadge) {
                    notifBadge.innerText = unreadCount;
                    notifBadge.style.display = 'block';
                }
                
                const newNotif = document.createElement('div');
                newNotif.className = 'notif-item';
                newNotif.innerHTML = `<strong>Task Added:</strong> ${result.data.title}`;
                
                newNotif.addEventListener('click', () => {
                    if (newNotif.classList.contains('active-notif')) {
                        notifDetailPanel.style.display = 'none';
                        newNotif.style.background = '';
                        newNotif.classList.remove('active-notif');
                    } else {
                        detailBody.innerHTML = `
                            <div style="margin-bottom: 8px;"><strong>Title:</strong> ${result.data.title}</div>
                            <div style="margin-bottom: 8px;"><strong>Date:</strong> ${result.data.date}</div>
                            <div style="margin-bottom: 8px;"><strong>Time:</strong> ${result.data.start_time} - ${result.data.end_time}</div>
                        `;
                        notifDetailPanel.style.display = 'block';
                        
                        notifDropdown.querySelectorAll('.notif-item').forEach(item => {
                            item.style.background = '';
                            item.classList.remove('active-notif');
                        });
                        newNotif.style.background = '#f0f4ff';
                        newNotif.classList.add('active-notif');
                    }
                });
                
                notifHeader.insertAdjacentElement('afterend', newNotif); // Insert right below header
                
                modalOverlay.classList.remove('active');

                // Update the calendar events table
                const calTbody = document.getElementById('calendar-events-tbody');
                if (calTbody && date && title) {
                    // Clear out any preset/dummy data if it still exists in the browser cache
                    if (calTbody.innerHTML.includes('INTERN2 End')) {
                        calTbody.innerHTML = '';
                    }

                    // Format the date to "Month Day" (e.g. "September 1")
                    const [year, month, day] = date.split('-');
                    const dateObj = new Date(year, month - 1, day);
                    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                    const dateText = `${months[dateObj.getMonth()]} ${dateObj.getDate()}`;
                    
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `<td>${dateText}</td><td>${title}</td>`;
                    
                    // Insert at the top of the table body
                    calTbody.insertBefore(newRow, calTbody.firstChild);
                }
                
                // Clear modal inputs
                document.querySelectorAll('.task-modal .input-box').forEach(input => {
                    input.value = '';
                });
                document.querySelectorAll('.task-modal .select-box').forEach(select => {
                    select.selectedIndex = 0;
                });
                if (document.getElementById('taskProgram')) {
                    document.getElementById('taskProgram').innerHTML = '<option value="">All Programs</option>';
                }
                // Reset date picker back to today's date
                const today = new Date();
                document.getElementById('taskDate').value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
            } else {
                alert('Failed to save task: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('A connection error occurred.');
        } finally {
            saveTaskBtn.innerText = originalText;
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