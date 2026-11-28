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
            <div class="nav-item" onclick="window.location.href='requirements.php'">
               <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span class="nav-label">Intern Feedback and Submissions</span>
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
                        <p style="font-size: 12px; color: #777; margin-top: 10px; line-height: 1.5;">Visualize industry partner evaluation criteria, Internship Officer assessments, and final grades.</p>
                        <button class="add-btn" id="btn-generate-evaluation" style="width: 100%; margin-top: 20px; color: #1e3b99; font-weight: bold;">View Chart &rarr;</button>
                    </div>
                </div>
                <div class="header-links">
                    <span>You previously logged in on <span id="login-date">Loading...</span> ⟲</span>
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
            <p id="evaluation-chart-summary" style="color: #666; font-size: 13px; margin-bottom: 15px;">Average scores for submitted evaluation criteria.</p>
            
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
            let evaluationData = null;

            async function fetchReport(action) {
                const response = await fetch(`reports_api.php?action=${encodeURIComponent(action)}`, {
                    headers: { Accept: 'application/json' }
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'The report could not be loaded.');
                }
                return result;
            }

            function csvCell(value) {
                let text = value === null || value === undefined ? '' : String(value);
                if (/^[\u0000-\u0020]*[=+\-@]/.test(text)) text = `'${text}`;
                return `"${text.replace(/"/g, '""')}"`;
            }

            function downloadCSV(filename, rows) {
                const content = rows.map(row => row.map(csvCell).join(',')).join('\r\n');
                const blob = new Blob(['\ufeff', content], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                link.click();
                setTimeout(() => URL.revokeObjectURL(url), 0);
            }

            if (btnDeployment) {
                btnDeployment.addEventListener('click', async () => {
                    btnDeployment.disabled = true;
                    try {
                        const report = await fetchReport('deployment');
                        const rows = [
                            ['Student ID', 'Last Name', 'First Name', 'Email', 'Program', 'Partner Company', 'Supervisor', 'Status'],
                            ...report.rows.map(row => [
                                row.student_id,
                                row.last_name,
                                row.first_name,
                                row.email,
                                row.program_name,
                                row.company_name,
                                row.supervisor_name,
                                row.deployment_status
                            ])
                        ];
                        downloadCSV('Deployment_Status_Report.csv', rows);
                    } catch (error) {
                        alert(error.message || 'Unable to generate the deployment report.');
                    } finally {
                        btnDeployment.disabled = false;
                    }
                });
            }

            if (btnEvaluation) {
                btnEvaluation.addEventListener('click', async () => {
                    btnEvaluation.disabled = true;
                    try {
                        evaluationData = await fetchReport('evaluation');
                    } catch (error) {
                        alert(error.message || 'Unable to load evaluation scores.');
                        return;
                    } finally {
                        btnEvaluation.disabled = false;
                    }

                    if (typeof Chart === 'undefined') {
                        alert('The chart library failed to load. Please check your internet connection.');
                        return;
                    }

                    chartModal.classList.add('active');
                    if (evalChart) evalChart.destroy();
                    const criteria = evaluationData.criteria || [];
                    const summary = document.getElementById('evaluation-chart-summary');
                    summary.textContent = criteria.length
                        ? `Average scores across ${criteria.length} evaluation criteria with submitted ratings.`
                        : 'No submitted evaluation scores are available yet.';
                    const ctx = document.getElementById('evaluationChart').getContext('2d');
                    evalChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: criteria.map(item => item.label),
                            datasets: [{
                                label: 'Average Score',
                                data: criteria.map(item => item.average_score),
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
                                    beginAtZero: true
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
                    if (!evaluationData) {
                        alert('Load the evaluation report before downloading it.');
                        return;
                    }
                    if (typeof ExcelJS === 'undefined') {
                        alert("ExcelJS library failed to load. Please check your internet connection.");
                        return;
                    }

                    try {
                        const criteriaLabels = evaluationData.criteria.map(item => item.label);
                        const criteriaByInternship = new Map();
                        evaluationData.criterion_scores.forEach(item => {
                            if (!criteriaByInternship.has(item.internship_id)) {
                                criteriaByInternship.set(item.internship_id, new Map());
                            }
                            criteriaByInternship.get(item.internship_id).set(item.criterion, item.score);
                        });

                        const headers = [
                            'Student ID', 'First Name', 'Last Name', 'Program', 'Partner Company',
                            'Industry Partner Score', 'Internship Officer Score', 'Final Grade',
                            ...criteriaLabels
                        ];
                        const rows = evaluationData.rows.map(item => {
                            const scores = criteriaByInternship.get(item.internship_id) || new Map();
                            return [
                                item.student_id,
                                item.first_name,
                                item.last_name,
                                item.program_name,
                                item.company_name,
                                item.supervisor_grade,
                                item.io_grade,
                                item.total_grade,
                                ...criteriaLabels.map(label => scores.get(label) ?? '')
                            ];
                        });

                        const workbook = new ExcelJS.Workbook();
                        const worksheet = workbook.addWorksheet('Evaluation Scores');
                        worksheet.addRow(headers);
                        worksheet.addRows(rows);
                        worksheet.getRow(1).font = { bold: true };
                        worksheet.views = [{ state: 'frozen', ySplit: 1 }];

                        const canvas = document.getElementById('evaluationChart');
                        const imageId = workbook.addImage({
                            base64: canvas.toDataURL('image/png'),
                            extension: 'png'
                        });
                        worksheet.addImage(imageId, {
                            tl: { col: 0, row: rows.length + 3 },
                            ext: { width: 700, height: 350 }
                        });

                        const buffer = await workbook.xlsx.writeBuffer();
                        const blob = new Blob([buffer], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        });
                        const url = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'Evaluation_Scores_Report.xlsx';
                        link.click();
                        setTimeout(() => URL.revokeObjectURL(url), 0);
                    } catch (error) {
                        alert(error.message || 'Unable to create the evaluation spreadsheet.');
                    }
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
<script src="../login/login-date.js"></script>
</body>
</html>