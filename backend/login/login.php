<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/legal.php';

security_headers();
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e($csrf) ?>">
  <title>Rams Internship Assessment System</title>
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Inria+Serif:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="../../assets/rias_icon.png">
</head>

<body data-domains="<?= e(implode(',', ALLOWED_EMAIL_DOMAINS)) ?>">
  <div id="click-blocker" hidden></div>

  <!-- (1) Header -->
  <header class="header no-select">
    <div class="container header-content">
      <img src="../../assets/header_apclogo_orange.png" alt="RIAS Logo" class="rias-logo">
      <img src="../../assets/header_apclogo.png" alt="APC Logo" class="header-logo">
    </div>
  </header>

  <!-- (2) Navigation -->
  <nav class="nav no-select" aria-label="Main">
    <div class="container">
      <ul class="nav-list">
        <li><a href="#about">ABOUT</a></li>
        <li><a href="#privacy-policy">PRIVACY POLICY</a></li>
        <li><a href="#terms-of-use">TERMS OF USE</a></li>
        <li><a href="#faq">FAQ</a></li>
        <li class="nav-divider" aria-hidden="true"></li>
        <li class="sign-in-container">
          <a href="#" class="sign-in-btn" id="open-sign-in">SIGN IN
            <img src="../../assets/signin_arrow.png" alt="" class="signin-arrow">
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- (3) Landing -->
  <div class="hero no-select">
    <div class="container hero-content">
      <h1>RIAS</h1>
      <p class="subtitle">RAMS <br> INTERNSHIP <br> ASSESSMENT <br> SYSTEM</p>
    </div>
    <div class="scroll-indicator" aria-hidden="true"><div class="scroll-arrow"></div></div>
  </div>

  <noscript><p class="notice notice-error noscript">JavaScript is required to sign in.</p></noscript>

  <!-- (4) Collapsible content -->
  <div class="content-container">
    <section class="content" id="about">
      <h1>ABOUT US</h1>
      <p>Welcome to the Rams Internship Assessment System (RIAS) of Asia Pacific College (APC)!</p>
      <p>Our platform is designed to bridge the gap between academic learning and industry practice, providing students, faculty, and companies with an efficient, streamlined, and reliable system for managing internships and their assessments.</p>
      <p>Our mission is to empower students with hands-on experience in their chosen fields, ensure effective coordination between stakeholders, and maintain a high standard of internship assessment. Through RIAS, we aim to foster growth, skill development, and professional readiness for APC students.</p>
      <p>The Internship Assessment System is a digital platform that simplifies and enhances the entire internship process by offering:</p>
      <ul>
        <li>Streamlined Application Process: Students can easily apply for internships and track their progress.</li>
        <li>Real-Time Monitoring: Supervisors and mentors can monitor student performance and provide feedback.</li>
        <li>Comprehensive Assessment: Evaluations are conducted based on pre-defined criteria, ensuring fairness and consistency.</li>
        <li>Industry Collaboration: We collaborate with top-tier companies to provide diverse and meaningful internship opportunities.</li>
        <li>User-Friendly Interface: Designed with simplicity and accessibility in mind, the platform ensures a seamless experience for all users.</li>
        <li>Transparency: From application to assessment, every step is clear and trackable.</li>
      </ul>
      <p><strong>Who We Serve:</strong></p>
      <ul>
        <li><strong>Students:</strong> Enabling them to connect classroom knowledge with real-world applications.</li>
        <li><strong>Faculty:</strong> Assisting in managing and assessing student internships with ease.</li>
        <li><strong>Industry Partners:</strong> Providing access to a pool of talented, motivated individuals ready to contribute.</li>
      </ul>
      <p>Together, let’s build a future where academic excellence meets professional competence through the Internship Assessment System of APC!</p>
    </section>

    <section class="content" id="privacy-policy">
      <h1>PRIVACY POLICY</h1>
      <p><strong>Last Updated: March 2025</strong></p>
      <?= legal_privacy_body() ?>
      <p>For inquiries regarding this policy, students and other stakeholders may contact the APC Privacy Officer at @apc.edu.ph.</p>
    </section>

    <section class="content" id="terms-of-use">
      <h1>TERMS OF USE</h1>
      <p><strong>Last Updated: March 2025</strong></p>
      <?= legal_terms_body() ?>
      <p>For inquiries, contact APC IT Support Team at [support@apc.edu.ph].</p>
    </section>

    <section class="content" id="faq">
      <h1>FAQ</h1>
      <p><strong>What is the Internship Assessment System?</strong></p>
      <p>The Internship Assessment System is a platform designed to help companies and educational institutions track, manage, and evaluate intern performance, replacing manual assessments and scattered spreadsheets.</p>
      <p><strong>Where do I register?</strong></p>
      <p>There isn’t a registration feature for this system. All accounts are created only by the Internship Officer to ensure that only verified email addresses are allowed to access certain features.</p>
      <p><strong>It’s my first time here. How can I get my password?</strong></p>
      <p>An email containing a link is sent to newly created accounts for users to create their password. You can also use “Receive an OTP (One-time Password)” on the sign-in window to get a one-time code by email, or “Forgot Password?” to get a new password link.</p>
    </section>
  </div>

  <!-- Privacy Policy / Terms modal (shown before the first sign-in) -->
  <div id="privacy-policy-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="legal-title">
    <div class="modal-content">
      <button type="button" class="close-btn" aria-label="Close">&times;</button>
      <div class="modal-header">
        <h1 id="legal-title">Privacy Policy - Terms of Use</h1>
        <h2>Review Before Signing In:</h2>
      </div>
      <div class="modal-text scrollable-box" tabindex="0">
        <h3>Privacy Policy</h3>
        <?= legal_privacy_body() ?>
        <h3>Terms of Use</h3>
        <?= legal_terms_body() ?>
      </div>
      <div class="modal-footer">
        <label>
          <input type="checkbox" id="agree-checkbox" disabled>
          By proceeding and filling the checkbox, you agree with our Data Privacy and Terms of Use.
        </label>
        <p class="hint" id="scroll-hint">Scroll to the end of the text above to enable the checkbox.</p>
        <button type="button" id="proceed-btn" disabled>Proceed to Sign In</button>
      </div>
    </div>
  </div>

  <!-- Sign-in modal: its content is swapped between the <template>s below -->
  <div id="sign-in-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="auth-title">
    <div class="modal-content"></div>
  </div>

  <!-- Footer -->
  <footer class="footer-container">
    <div class="footer-left">
      <p>Made with the Magic of Friendship.</p>
      <p>The IAS or Internship Assessment System is only for authorized Asia Pacific College personnel.<br>Use may be monitored.</p>
      <p>This project was made by the group, My Little Project, of BSCPE 231.</p>
      <ul class="footer-links">
        <li><a href="#terms-of-use">TERMS OF USE</a></li>
        <li><a href="#privacy-policy">PRIVACY POLICY</a></li>
      </ul>
    </div>
    <div class="footer-divider"></div>
    <div class="footer-right">
      <p>Contact us:</p>
      <ul class="contact-links">
        <li><a href="https://www.apc.edu.ph/contact-us/" target="_blank" rel="noopener noreferrer">https://www.apc.edu.ph/contact-us/</a></li>
        <li><a href="https://www.apc.edu.ph/welcome-home-rams/" target="_blank" rel="noopener noreferrer">https://www.apc.edu.ph/welcome-home-rams/</a></li>
      </ul>
      <p class="copyright">Copyright © 2024 Asia Pacific College. All rights reserved.</p>
    </div>
  </footer>

  <!-- ================= View templates ================= -->

  <template id="tpl-signin">
    <button type="button" class="sign-in-close-btn" data-close aria-label="Close">&times;</button>
    <h1 class="modal-title" id="auth-title">Sign In</h1>
    <form data-form novalidate>
      <div class="input-group">
        <label for="email">Email</label>
        <div class="input-wrapper">
          <input type="email" id="email" name="email" placeholder="Enter your email" autocomplete="username" required>
          <img src="../../assets/email_logo.png" alt="" class="input-icon email-icon" tabindex="0">
          <span class="email-tooltip" data-tooltip></span>
        </div>
        <span class="error" data-error="email" aria-live="polite"></span>
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
          <button type="button" class="input-icon toggle-password" aria-label="Show password"
                  data-src-show="../../assets/login_password_show.png" data-src-hide="../../assets/login_password.png">
            <img src="../../assets/login_password.png" alt="">
          </button>
        </div>
        <span class="error" data-error="password" aria-live="polite"></span>
      </div>
      <button type="submit" class="btn btn-primary">Sign In</button>
      <span class="error" data-error="form" aria-live="polite"></span>
    </form>
    <p class="subtitle">By signing in, you agree to our
      <a href="#privacy-policy">Privacy Policy</a> and <a href="#terms-of-use">Terms of Use</a>.</p>
    <div class="links">
      <a href="#" data-goto="otp">Receive an OTP (One-time Password)</a>
      <a href="#" data-goto="forgot">Forgot Password?</a>
    </div>
  </template>

  <template id="tpl-otp-request">
    <button type="button" class="sign-in-close-btn" data-close aria-label="Close">&times;</button>
    <h1 class="modal-title" id="auth-title">Receive an OTP</h1>
    <p class="subtitle">If the email is registered, you will receive a 6-digit code.</p>
    <form data-form novalidate>
      <div class="input-group">
        <label for="otp-email">Email</label>
        <div class="input-wrapper">
          <input type="email" id="otp-email" name="email" placeholder="Enter your email" autocomplete="username" required>
          <img src="../../assets/email_logo.png" alt="" class="input-icon">
        </div>
        <span class="error" data-error="email" aria-live="polite"></span>
      </div>
      <button type="submit" class="btn btn-primary">SEND OTP</button>
      <span class="error" data-error="form" aria-live="polite"></span>
    </form>
    <p class="subtitle"><a href="#" class="back-link" data-goto="signin">Back to Sign In</a></p>
  </template>

  <template id="tpl-otp-verify">
    <button type="button" class="sign-in-close-btn" data-close aria-label="Close">&times;</button>
    <h1 class="modal-title" id="auth-title">Verify OTP</h1>
    <p class="notice" data-notice role="status"></p>
    <form data-form novalidate>
      <div class="input-group">
        <label for="otp-code">Enter OTP</label>
        <div class="input-wrapper">
          <input type="text" id="otp-code" name="otp" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                 autocomplete="one-time-code" placeholder="6-digit code" required>
          <img src="../../assets/lock_icon.png" alt="" class="input-icon">
        </div>
        <span class="error" data-error="otp" aria-live="polite"></span>
      </div>
      <button type="submit" class="btn btn-primary" data-verify>VERIFY OTP</button>
      <button type="button" class="link-btn" data-resend>Resend OTP</button>
      <span class="error" data-error="form" aria-live="polite"></span>
    </form>
    <p class="subtitle">
      <a href="#" class="back-link" data-action="change-email">Use a different email</a>
    </p>
    <p class="subtitle"><a href="#" class="back-link" data-goto="signin">Back to Sign In</a></p>
  </template>

  <template id="tpl-forgot">
    <button type="button" class="sign-in-close-btn" data-close aria-label="Close">&times;</button>
    <h1 class="modal-title" id="auth-title">Forgot Password</h1>
    <p class="subtitle" data-intro>Enter your registered email to reset your password.</p>
    <p class="notice notice-ok" data-sent role="status" hidden></p>
    <form data-form novalidate>
      <div class="input-group">
        <label for="forgot-email">Email</label>
        <div class="input-wrapper">
          <input type="email" id="forgot-email" name="email" placeholder="Enter your email" autocomplete="username" required>
          <img src="../../assets/email_logo.png" alt="" class="input-icon">
        </div>
        <span class="error" data-error="email" aria-live="polite"></span>
      </div>
      <button type="submit" class="btn btn-primary">RESET PASSWORD</button>
      <span class="error" data-error="form" aria-live="polite"></span>
    </form>
    <p class="subtitle"><a href="#" class="back-link" data-goto="signin">Back to Sign In</a></p>
  </template>

  <script src="app.js"></script>
</body>
</html>
