<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<form id="loginForm">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Login with Password</button>
</form>

<p id="message"></p>

<script>
document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault(); // stop page redirect
	document.getElementById("message").innerText = "";

    const formData = new FormData(this);
	formData.append('action', 'login');

    try {
        const response = await fetch("handler.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        const msg = document.getElementById("message");

        if (data.status === "success") {
            msg.style.color = "green";
            msg.innerText = "Login successful! Redirecting...";

            // role-based redirect from backend
            if (data.redirect) {
                window.location.replace(data.redirect);
            }
        } else {
            msg.style.color = "red";
            msg.innerText = data.message;
        }

    } catch (error) {
        document.getElementById("message").innerText = "Server error";
    }
});
</script>

<h2>Send OTP</h2>

<form id="otpForm">
  <input type="email" id="email" placeholder="Enter your email" required />
  <button type="submit">Send OTP</button>
</form>

<script>
document.getElementById("otpForm").addEventListener("submit", async function (e) {
  e.preventDefault();

  const email = document.getElementById("email").value;

  const res = await fetch("handler.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({ email, action: 'sendotp' })
  });

  const data = await res.json();
  
	alert(data.message);
});
</script>

<h2>Enter OTP</h2>

<input type="text" id="otp_code" placeholder="Enter OTP"><br><br>
<button onclick="submitOTP()">Verify OTP</button>

<p id="response"></p>

<script>
function submitOTP() {
	document.getElementById("response").innerText = "";
    const otp = document.getElementById("otp_code").value;

    fetch("handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ otp, action: 'useotp' })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("response").innerText = data.message;
		if (data.status == "success") window.location.replace(data.redirect);
    })
    .catch(err => {
        document.getElementById("response").innerText = "Something broke.";
    });
}
</script>

<h2>Reset Password</h2>

<input type="email" id="emailr" placeholder="Enter your email"><br><br>
<button onclick="requestReset()">Send Reset Link</button>

<p id="response2"></p>

<script>
function requestReset() {
    const email = document.getElementById("emailr").value;
        document.getElementById("response2").innerText = "";

    fetch("handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ email, action: 'resetp' })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("response2").innerText = data.message;
    })
    .catch(() => {
        document.getElementById("response2").innerText = "Request failed";
    });
}
</script>

</body>
</html>