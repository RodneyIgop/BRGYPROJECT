<?php
session_start();
require_once '../Connection/conn.php';

// Get email and token from URL
$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

// Validate email and token
if (empty($email) || empty($token)) {
    header('Location: adminForgotPassword.php?error=invalid_request');
    exit;
}

// Verify reset token exists and is valid
$stmt = $conn->prepare('SELECT id, verification_code, expires_at FROM password_reset_requests WHERE email = ? AND reset_token = ? AND is_used = 0');
$stmt->bind_param('ss', $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header('Location: adminForgotPassword.php?error=invalid_token');
    exit;
}

$reset_request = $result->fetch_assoc();
$stmt->close();

// Check if token has expired
if (strtotime($reset_request['expires_at']) < time()) {
    header('Location: adminForgotPassword.php?error=expired_token');
    exit;
}

// Store reset info in session
$_SESSION['admin_password_reset'] = [
    'email' => $email,
    'token' => $token,
    'request_id' => $reset_request['id'],
    'verification_code' => $reset_request['verification_code']
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay New Era | Admin Verify Reset Code</title>

  <!-- Bootstrap 4 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* ================== Global ================== */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
      scroll-behavior: smooth;
      background-image: url('../images/barangay hall_background.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
      min-height: 100vh;
    }

    a {
      text-decoration: none;
    }

    /* ================== Navbar ================== */
    .navbar {
      background-color: #014A7F;
      padding: 0.75rem 5rem;
    }

    .navbar .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }

    .navbar .nav-links a {
      color: #fff;
      margin-left: 15px;
      font-weight: 500;
    }

    .navbar .nav-links a:hover {
      text-decoration: underline;
    }

    @media (max-width: 991px) {
      .nav-links {
        text-align: center;
      }

      .nav-links a {
        display: block;
        margin: 10px 0;
      }

      .navbar {
        padding: 0.75rem 1rem;
      }
    }

    /* ================== Verification Container ================== */
    .verify-section {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 80px 20px 0;
      box-sizing: border-box;
    }

    .verify {
      background-color: #fff;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 420px;
      text-align: center;
    }

    @media (max-width: 576px) {
      .verify {
        padding: 20px 15px;
        margin: 0;
      }
    }

    .verify .logo img {
      width: 70px;
      margin-bottom: 12px;
    }

    .verify h1 {
      font-size: 1.5rem;
      margin-bottom: 18px;
      color: #014A7F;
      font-weight: 700;
    }

    .verify p {
      font-size: 0.9rem;
      color: #666;
      margin-bottom: 25px;
      line-height: 1.5;
    }

    .verify .email-display {
      background-color: #f8f9fa;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
      font-weight: 600;
      color: #014A7F;
    }

    .verify .code-inputs {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-bottom: 20px;
    }

    .verify .code-input {
      width: 45px;
      height: 45px;
      text-align: center;
      font-size: 1.5rem;
      font-weight: bold;
      border: 2px solid #ced4da;
      border-radius: 8px;
      transition: border-color 0.3s;
    }

    .verify .code-input:focus {
      border-color: #014A7F;
      outline: none;
      box-shadow: 0 0 0 2px rgba(1, 74, 127, 0.2);
    }

    .verify button {
      width: 100%;
      padding: 10px;
      font-size: 0.9rem;
      font-weight: bold;
      background-color: #014A7F;
      color: #fff;
      border: none;
      border-radius: 8px;
      transition: background 0.3s;
      margin-bottom: 15px;
    }

    .verify button:hover {
      background-color: #01365d;
    }

    .verify button:disabled {
      background-color: #6c757d;
      cursor: not-allowed;
    }

    .verify .resend {
      display: block;
      color: #ffffff;
      font-size: 0.85rem;
      margin-bottom: 15px;
      cursor: pointer;
    }

    .verify .resend:hover {
      text-decoration: underline;
    }

    .verify .resend:disabled {
      color: #6c757d;
      cursor: not-allowed;
      text-decoration: none;
    }

    .verify .back-login {
      display: block;
      font-weight: 600;
      color: #014A7F;
      margin-top: 15px;
      text-align: center;
    }

    .verify .back-login:hover {
      text-decoration: underline;
    }

    .verify .back-home {
      display: block;
      font-weight: 600;
      color: #014A7F;
      margin-top: 15px;
      text-align: center;
    }

    .verify .back-home:hover {
      text-decoration: underline;
    }

    .verify .error-msg {
      color: red;
      font-size: 0.85rem;
      margin-bottom: 8px;
    }

    .verify p.success-msg {
      color: green;
      font-size: 0.85rem;
      margin-bottom: 8px;
    }

    .loading {
      display: none;
      text-align: center;
      margin-top: 10px;
    }

    .spinner {
      border: 3px solid #f3f3f3;
      border-top: 3px solid #014A7F;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      animation: spin 1s linear infinite;
      display: inline-block;
      margin-right: 8px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .timer {
      font-size: 0.85rem;
      color: #666;
      margin-bottom: 15px;
    }

    .timer.expired {
      color: red;
      font-weight: bold;
    }

    /* ================== Responsive ================== */
    @media (max-width: 576px) {
      .verify {
        padding: 30px 20px;
      }

      .verify h1 {
        font-size: 1.5rem;
      }

      .verify .code-input {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow custom-bg">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../images/taosaharap.png" class="mr-2">
        <strong>Barangay New Era</strong>
      </a>

      <!-- Hamburger -->
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Collapsible links -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <div class="nav-links ml-auto">
          <a href="adminLogin.php">LOGIN</a>
          <a href="adminregister.php">REGISTER</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- ================= VERIFICATION ================= -->
  <section class="verify-section">
    <div class="verify">
      <div class="logo">
        <img src="../images/taosaharap.png" alt="Admin Logo">
      </div>

      <form id="verifyCodeForm">
        <h1>Enter Verification Code</h1>
        <p>We've sent a 6-digit verification code to:</p>
        
        <div class="email-display"><?php echo htmlspecialchars($email); ?></div>
        
        <div class="timer" id="timer">Code expires in: <span id="countdown">10:00</span></div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_code'): ?>
          <p class="error-msg">Invalid verification code. Please try again.</p>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'expired'): ?>
          <p class="error-msg">Verification code has expired. Please request a new one.</p>
        <?php endif; ?>

        <div class="code-inputs">
          <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
          <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
          <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
          <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
          <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
          <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
        </div>

        <button type="submit" id="verifyBtn">Verify Code</button>
      </form>

      <button type="button" id="resendBtn" class="resend">Resend Code</button>

      <div class="loading" id="loading">
        <div class="spinner"></div>
        <span>Verifying...</span>
      </div>

      <a href="adminLogin.php" class="back-login">← Back to Login</a>
      
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Auto-focus next input
    const codeInputs = document.querySelectorAll('.code-input');
    codeInputs.forEach((input, index) => {
      input.addEventListener('input', function(e) {
        if (e.target.value.length === 1) {
          if (index < codeInputs.length - 1) {
            codeInputs[index + 1].focus();
          }
        }
      });

      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
          codeInputs[index - 1].focus();
        }
      });

      // Only allow numbers
      input.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
      });
    });

    // Countdown timer
    // Use a more reliable date parsing method
    const expiresAt = <?php echo strtotime($reset_request['expires_at']) * 1000; ?>;
    const countdownEl = document.getElementById('countdown');
    const timerEl = document.getElementById('timer');
    
    function updateCountdown() {
      const now = new Date().getTime();
      const distance = expiresAt - now;
      
      if (distance < 0) {
        countdownEl.textContent = '00:00';
        timerEl.classList.add('expired');
        document.getElementById('verifyBtn').disabled = true;
        clearInterval(countdownInterval);
        return;
      }
      
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);
      
      countdownEl.textContent = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
    }
    
    const countdownInterval = setInterval(updateCountdown, 1000);
    updateCountdown();

    // Form submission
    document.getElementById('verifyCodeForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get verification code
      let code = '';
      codeInputs.forEach(input => {
        code += input.value;
      });
      
      if (code.length !== 6) {
        alert('Please enter all 6 digits');
        return;
      }
      
      const verifyBtn = document.getElementById('verifyBtn');
      const loading = document.getElementById('loading');
      
      // Show loading state
      verifyBtn.disabled = true;
      loading.style.display = 'block';
      
      // Create form data
      const formData = new FormData();
      formData.append('verification_code', code);
      formData.append('email', '<?php echo htmlspecialchars($email); ?>');
      formData.append('token', '<?php echo htmlspecialchars($token); ?>');
      
      // Send request
      fetch('adminResetPasswordVerifyProcess.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        loading.style.display = 'none';
        verifyBtn.disabled = false;
        
        if (data.success) {
          // Redirect to reset password page
          window.location.href = 'adminResetPassword.php';
        } else {
          // Show error message
          const errorDiv = document.createElement('div');
          errorDiv.className = 'error-msg';
          errorDiv.textContent = data.message;
          
          // Remove existing error messages
          const existingErrors = document.querySelectorAll('.error-msg');
          existingErrors.forEach(err => err.remove());
          
          // Insert error message after the form
          document.getElementById('verifyCodeForm').appendChild(errorDiv);
          
          // Clear inputs
          codeInputs.forEach(input => input.value = '');
          codeInputs[0].focus();
        }
      })
      .catch(error => {
        loading.style.display = 'none';
        verifyBtn.disabled = false;
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
    });

    // Resend code
    document.getElementById('resendBtn').addEventListener('click', function() {
      const resendBtn = document.getElementById('resendBtn');
      
      if (resendBtn.disabled) return;
      
      resendBtn.disabled = true;
      resendBtn.textContent = 'Sending...';
      
      // Create form data
      const formData = new FormData();
      formData.append('email', '<?php echo htmlspecialchars($email); ?>');
      
      // Send request
      fetch('adminForgotPasswordProcess.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Reload page to get new token
          window.location.href = data.redirect_url;
        } else {
          alert('Failed to resend code. Please try again.');
          resendBtn.disabled = false;
          resendBtn.textContent = 'Resend Code';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend Code';
      });
    });
  </script>
</body>
</html>
