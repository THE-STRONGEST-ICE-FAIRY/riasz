<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - RIAS Dashboard</title>
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
            <div class="nav-item active" onclick="window.location.href='reports.php'">
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
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="date-container">
                    <div class="huge-date">Generate Reports</div>
                </div>
            </div>

            <div class="panel" style="padding: 30px;">
                <div style="color: #1e3b99; font-weight: bold; font-size: 18px; margin-bottom: 20px;">Available Reports</div>
                <p style="color: #666; font-size: 14px; margin-bottom: 30px;">Select a report module below to generate analytical data and download copies for your records.</p>
                
                <div class="bottom-grid">
                    <div class="panel" style="padding: 25px; text-align: center; border: 2px dashed #eaeaea;">
                        <h3 style="color: #333; font-size: 16px; font-weight: 500;">Deployment Status</h3>
                        <p style="font-size: 12px; color: #777; margin-top: 10px; line-height: 1.5;">Export a list of all interns and their current deployment statuses into an Excel spreadsheet.</p>
                        <button class="add-btn" id="btn-generate-deployment" style="width: 100%; margin-top: 20px; color: #1e3b99; font-weight: bold;">Generate &rarr;</button>
                    </div>
                    <div class="panel" style="padding: 25px; text-align: center; border: 2px dashed #eaeaea;">
                        <h3 style="color: #333; font-size: 16px; font-weight: 500;">Evaluation Scores</h3>
                        <p style="font-size: 12px; color: #777; margin-top: 10px; line-height: 1.5;">Visualize a breakdown of industry partner evaluations, self-assessments, and final grades.</p>
                        <button class="add-btn" id="btn-generate-evaluation" style="width: 100%; margin-top: 20px; color: #1e3b99; font-weight: bold;">View Chart &rarr;</button>
                    </div>
                </div>
                <div class="header-links">
                    <span>You previously logged in on <span id="login-date"></span> ⟲</span>
                    <a href="#" id="last-visited-link" style="display: none;">Go to your last visited page: ↩</a>
                </div>
            </div>
        </main>
    </div>

    <!-- Chart Modal -->
    <div class="modal-overlay" id="chartModal">
        <div class="task-modal" style="max-width: 700px; width: 90%;">
            <span class="modal-close" id="closeChartModal">&times;</span>
            <h2 style="font-weight: 500; color: #1e3b99; margin-bottom: 5px;">Evaluation Scores Visualization</h2>
            <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Average scores across all interns based on the 10 criteria.</p>
            
            <div style="position: relative; height: 350px; width: 100%;">
                <canvas id="evaluationChart"></canvas>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button class="add-btn" id="btn-download-excel">Download Excel (with Chart)</button>
                <button class="save-btn" id="btn-download-img" style="margin-top: 0; width: auto; padding: 8px 12px; font-size: 13px;">Download Image</button>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-links"><a href="#">ABOUT US</a> <a href="#">PRIVACY POLICY</a> <a href="#">TERMS OF USE</a></div>
        <div class="footer-copy">Copyright © <span id="current-year"></span> <a href="#">Asia Pacific College</a>. All rights reserved.</div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="adminscript.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnDeployment = document.getElementById('btn-generate-deployment');
            const btnEvaluation = document.getElementById('btn-generate-evaluation');
            const chartModal = document.getElementById('chartModal');
            const closeChartModal = document.getElementById('closeChartModal');
            const btnDownloadExcel = document.getElementById('btn-download-excel');
            const btnDownloadImg = document.getElementById('btn-download-img');
            let evalChart = null;

            function downloadCSV(filename, content) {
                const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                link.click();
            }

            if (btnDeployment) {
                btnDeployment.addEventListener('click', () => {
                    const csvContent = "Student ID,Last Name,First Name,Email,Contact Number,Program,Partner Company,Supervisor,Status\n2021-10001,Doe,John,john.doe@student.apc.edu.ph,09171234567,BSIT,TechCorp,Sarah Connor,Deployed\n2021-10002,Smith,Jane,jane.s@student.apc.edu.ph,09181234567,BSCS,Innovate Inc,Alan Turing,Waiting\n2021-10003,Johnson,Mark,mark.j@student.apc.edu.ph,09191234567,BSMMA,Creative Studio,Mark Johnson,Undeployed";
                    downloadCSV('Deployment_Status_Report.csv', csvContent);
                });
            }

            const evalCsvContent = "Student ID,Last Name,First Name,Technical Skills (1-10),Problem Solving (1-10),Communication Skills (1-10),Teamwork (1-10),Time Management (1-10),Professionalism (1-10),Adaptability (1-10),Initiative (1-10),Quality of Work (1-10),Attendance (1-10),Total Evaluation Score,Final Grade\n2021-10001,Doe,John,9,8,9,10,8,10,9,8,9,10,90,93\n2021-10002,Smith,Jane,8,9,8,8,9,9,8,9,8,10,86,87\n2021-10004,Davis,Emily,9,9,9,9,10,10,9,10,9,10,94,93";

            if (btnEvaluation) {
                btnEvaluation.addEventListener('click', () => {
                    chartModal.classList.add('active');
                    
                    if (evalChart) evalChart.destroy();
                    
                    const ctx = document.getElementById('evaluationChart').getContext('2d');
                    
                    // Sample average scores derived from the CSV above
                    const criteria = ['Technical', 'Problem Solving', 'Communication', 'Teamwork', 'Time Mgt', 'Professionalism', 'Adaptability', 'Initiative', 'Quality', 'Attendance'];
                    const averages = [8.67, 8.67, 8.67, 9.00, 9.00, 9.67, 8.67, 9.00, 8.67, 10.00];

                    evalChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: criteria,
                            datasets: [{
                                label: 'Average Score (1-10)',
                                data: averages,
                                backgroundColor: 'rgba(46, 150, 255, 0.7)',
                                borderColor: 'rgba(46, 150, 255, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 10
                                }
                            }
                        }
                    });
                });
            }

            if (closeChartModal) closeChartModal.addEventListener('click', () => chartModal.classList.remove('active'));
            if (chartModal) chartModal.addEventListener('click', (e) => { if (e.target === chartModal) chartModal.classList.remove('active'); });

            if (btnDownloadExcel) {
                btnDownloadExcel.addEventListener('click', async () => {
                    if (typeof ExcelJS === 'undefined') {
                        alert("ExcelJS library failed to load. Please check your internet connection.");
                        return;
                    }

                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet('Evaluation Scores');

                    // 1. Convert CSV string to an array of arrays and add to Excel
                    const rows = evalCsvContent.split('\n').map(row => row.split(','));
                    worksheet.addRows(rows);

                    // 2. Get the Chart image as Base64 and attach it below the data
                    const canvas = document.getElementById('evaluationChart');
                    const base64Image = canvas.toDataURL('image/png');
                    const imageId = workbook.addImage({ base64: base64Image, extension: 'png' });
                    
                    worksheet.addImage(imageId, { tl: { col: 0, row: rows.length + 2 }, ext: { width: 700, height: 350 } });

                    // 3. Export as .xlsx
                    const buffer = await workbook.xlsx.writeBuffer();
                    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'Evaluation_Scores_Report_with_Chart.xlsx';
                    link.click();
                });
            }

            if (btnDownloadImg) {
                btnDownloadImg.addEventListener('click', () => {
                    const canvas = document.getElementById('evaluationChart');
                    const link = document.createElement('a');
                    link.href = canvas.toDataURL('image/png');
                    link.download = 'Evaluation_Scores_Chart.png';
                    link.click();
                });
            }
        });
    </script>
</body>
</html>