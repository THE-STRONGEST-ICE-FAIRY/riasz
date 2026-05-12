<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intern Feedback - RIAS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="studentStyle.css">
</head>
<body>

    <header class="top-nav">
        <div class="logo-section">
            <div class="logo-rias">RIAS</div>
            <div class="logo-text">
                <span>Rams</span><span>Internship</span><span>Assessment System</span>
            </div>
        </div>
        <div class="top-nav-right">
            <span class="role-text">STUDENT INTERN</span>
            <div class="profile-circle"></div>
        </div>
    </header>

    <div class="layout-container">
        <aside class="sidebar" id="sidebar">
            <div class="nav-item" id="menuToggle">
                <svg viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18" /></svg>
                <span class="nav-label">Menu</span>
            </div>
            <div class="nav-item" onclick="window.location.href='student.php'">
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
            <div class="page-header">
                <div class="huge-date">Intern Feedback</div>
            </div>

            <div class="panel" style="padding: 30px;">
                <div class="welcome-section">
                    <h1>Share Your <span class="supervisor-name">Experience</span></h1>
                    <p>Your feedback helps us improve the internship program for future Rams.</p>
                </div>

                <hr style="margin: 25px 0; border: 0; border-top: 1px solid #eee;">

                <form id="feedbackForm" class="edit-form-container" style="padding: 0;">
                    <div class="form-section">
                        <h3>General Assessment</h3>
                        <div class="input-group">
                            <label>What is your concern regarding?</label>
                            <select name="category" class="select-box" style="padding: 12px; width: 100%;" required>
                                <option value="" disabled selected>Select a category</option>
                                <option value="company">Company/Work Environment</option>
                                <option value="learning">Learning & Skill Development</option>
                                <option value="system">RIAS Website/Platform</option>
                                <option value="supervisor">Supervision & Mentorship</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Your Comments</h3>
                        <div class="input-group">
                            <label>Describe your overall internship experience:</label>
                            <textarea name="experience" class="input-box" rows="5" style="resize: vertical; min-height: 100px;" placeholder="What did you enjoy most?" required></textarea>
                        </div>
                        <div class="input-group">
                            <label>Specific Concerns or Suggestions:</label>
                            <textarea name="concerns" class="input-box" rows="4" style="resize: vertical; min-height: 80px;" placeholder="Is there anything you would like to see changed?"></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-action btn-edit" style="background-color: #e0e0e0; color: #333;" onclick="window.location.href='student.php'">CANCEL</button>
                        <button type="submit" class="btn-action btn-portfolio">SUBMIT FEEDBACK</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="successModal">
        <div class="task-modal" style="text-align: center; max-width: 400px;">
            <div style="font-size: 50px; color: #1eb972; margin-bottom: 15px;">✔</div>
            <h2 style="color: #1e3b99; margin-bottom: 10px;">Feedback Sent!</h2>
            <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Thank you for your response. Your feedback has been successfully recorded.</p>
            <button class="btn-action btn-portfolio" style="width: 100%;" onclick="window.location.href='student.php'">BACK TO DASHBOARD</button>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-copy">
            Copyright © 2026 <a href="#">Asia Pacific College</a>. All rights reserved.
        </div>
    </footer>

    <script>
        // Intercept form submission
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent actual page reload
            
            // Show the modal
            document.getElementById('successModal').classList.add('active');
            
            // Note: In a real capstone project, you would use fetch() here 
            // to send the data to a PHP script in the background before showing the modal.
        });
    </script>
</body>
</html>