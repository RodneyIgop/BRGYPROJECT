<?php
session_start();
require_once '../Connection/conn.php';

// Check if password reset session exists and is verified
if (!isset($_SESSION['password_reset']) || !$_SESSION['password_reset']['verified']) {
    header('Location: forgotPassword.php?error=session_expired');
    exit;
}

// Check if verification was done within the last 30 minutes
$verified_at = $_SESSION['password_reset']['verified_at'];
if (strtotime($verified_at) < (time() - 1800)) { // 30 minutes
    unset($_SESSION['password_reset']);
    header('Location: forgotPassword.php?error=session_expired');
    exit;
}

$email = $_SESSION['password_reset']['email'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay New Era | Reset Password</title>

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

    /* ================== Reset Password Container ================== */
    .reset-section {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 80px 20px 0;
      box-sizing: border-box;
    }

    .reset {
      background-color: #fff;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 420px;
      text-align: center;
    }

    @media (max-width: 576px) {
      .reset {
        padding: 20px 15px;
        margin: 0;
      }
    }

    .reset .logo img {
      width: 70px;
      margin-bottom: 12px;
    }

    .reset h1 {
      font-size: 1.5rem;
      margin-bottom: 18px;
      color: #014A7F;
      font-weight: 700;
    }

    .reset p {
      font-size: 0.9rem;
      color: #666;
      margin-bottom: 25px;
      line-height: 1.5;
    }

    .reset .email-display {
      background-color: #f8f9fa;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
      font-weight: 600;
      color: #014A7F;
    }

    .reset label {
      display: block;
      text-align: left;
      font-weight: 600;
      margin-bottom: 5px;
      color: #333;
    }

    .reset input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 8px;
      border: 1px solid #ced4da;
      font-size: 0.9rem;
    }

    .password-group {
      position: relative;
    }

    .password-group input {
      padding-right: 40px;
    }

    .toggle-password {
      position: absolute;
      right: 12px;
      top: 57%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #014A7F;
    }

    /* Remove browser password reveal icon (Chrome / Edge) */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
      display: none;
    }

    input[type="password"]::-webkit-credentials-auto-fill-button {
      visibility: hidden;
      position: absolute;
      right: 0;
    }

    .password-strength {
      height: 5px;
      border-radius: 3px;
      margin-top: 5px;
      margin-bottom: 15px;
      transition: all 0.3s;
    }

    .password-strength.weak {
      background-color: #dc3545;
      width: 33%;
    }

    .password-strength.medium {
      background-color: #ffc107;
      width: 66%;
    }

    .password-strength.strong {
      background-color: #28a745;
      width: 100%;
    }

    .password-requirements {
      font-size: 0.75rem;
      color: #666;
      text-align: left;
      margin-bottom: 15px;
    }

    .password-requirements ul {
      margin: 5px 0;
      padding-left: 20px;
    }

    .password-requirements li {
      margin: 2px 0;
    }

    .password-requirements li.valid {
      color: #28a745;
    }

    .password-requirements li.invalid {
      color: #dc3545;
    }

    .reset button {
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

    .reset button:hover {
      background-color: #01365d;
    }

    .reset button:disabled {
      background-color: #6c757d;
      cursor: not-allowed;
    }

    .reset .back-login {
      display: block;
      font-weight: 600;
      color: #014A7F;
      margin-top: 15px;
      text-align: center;
    }

    .reset .back-login:hover {
      text-decoration: underline;
    }

    .reset .back-home {
      display: block;
      font-weight: 600;
      color: #014A7F;
      margin-top: 15px;
      text-align: center;
    }

    .reset .back-home:hover {
      text-decoration: underline;
    }

    .reset .error-msg {
      color: red;
      font-size: 0.85rem;
      margin-bottom: 8px;
    }

    .reset p.error-msg {
      color: red;
      font-size: 0.85rem;
      margin-bottom: 8px;
    }

    .reset p.success-msg {
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

    /* ================== Responsive ================== */
    @media (max-width: 576px) {
      .reset {
        padding: 30px 20px;
      }

      .reset h1 {
        font-size: 1.5rem;
      }

      .reset input[type="password"] {
        font-size: 0.95rem;
        padding: 10px;
      }

      .reset button {
        font-size: 0.95rem;
        padding: 10px;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow custom-bg">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../images/brgylogo.png" class="mr-2">
        <strong>Barangay New Era</strong>
      </a>

      <!-- Hamburger -->
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Collapsible links -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <div class="nav-links ml-auto">
          <a href="residentlogin.php">LOGIN</a>
          <a href="residentregister.php">REGISTER</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- ================= RESET PASSWORD ================= -->
  <section class="reset-section">
    <div class="reset">
      <div class="logo">
        <img src="../images/brgylogo.png" alt="Barangay Logo">
      </div>

      <form id="resetPasswordForm">
        <h1>Reset Password</h1>
        <p>Create a new password for your account:</p>
        
        <div class="email-display"><?php echo htmlspecialchars($email); ?></div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'password_mismatch'): ?>
          <p class="error-msg">Passwords do not match.</p>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'weak_password'): ?>
          <p class="error-msg">Password does not meet security requirements.</p>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'update_failed'): ?>
          <p class="error-msg">Failed to update password. Please try again.</p>
        <?php endif; ?>

        <div class="form-group password-group">
          <label class="label">New Password</label>
          <input type="password" name="new_password" id="new_password" class="password" placeholder="Enter new password" required>
          <span class="toggle-password" data-target="new_password">
            <i class="fa-solid fa-eye"></i>
          </span>
        </div>

        <div class="password-strength" id="passwordStrength"></div>

        <div class="password-requirements">
          <strong>Password Requirements:</strong>
          <ul>
            <li id="length">8-10 characters</li>
            <li id="uppercase">One uppercase letter</li>
            <li id="lowercase">One lowercase letter</li>
            <li id="number">One number</li>
            <li id="special">One special character</li>
          </ul>
        </div>

        <div class="form-group password-group">
          <label class="label">Confirm New Password</label>
          <input type="password" name="confirm_password" id="confirm_password" class="password" placeholder="Confirm new password" required>
          <span class="toggle-password" data-target="confirm_password">
            <i class="fa-solid fa-eye"></i>
          </span>
        </div>

        <button type="submit" id="resetBtn">Reset Password</button>
      </form>

      <div class="loading" id="loading">
        <div class="spinner"></div>
        <span>Resetting...</span>
      </div>

      <a href="residentlogin.php" class="back-login">← Back to Login</a>
      
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(toggle => {
      toggle.addEventListener('click', () => {
        const inputId = toggle.getAttribute('data-target');
        const input = document.getElementById(inputId);
        const icon = toggle.querySelector('i');

        if (!input) return;

        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      });
    });

    // Password strength checker
    function checkPasswordStrength(password) {
      const requirements = {
        length: password.length >= 8 && password.length <= 10,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
      };

      // Update requirement indicators
      Object.keys(requirements).forEach(req => {
        const element = document.getElementById(req);
        if (requirements[req]) {
          element.classList.add('valid');
          element.classList.remove('invalid');
        } else {
          element.classList.add('invalid');
          element.classList.remove('valid');
        }
      });

      // Calculate strength
      const metRequirements = Object.values(requirements).filter(Boolean).length;
      const strengthBar = document.getElementById('passwordStrength');
      
      strengthBar.className = 'password-strength';
      
      if (metRequirements <= 2) {
        strengthBar.classList.add('weak');
      } else if (metRequirements <= 4) {
        strengthBar.classList.add('medium');
      } else {
        strengthBar.classList.add('strong');
      }

      return metRequirements === 5; // Return true if all requirements met
    }

    document.getElementById('new_password').addEventListener('input', function(e) {
      checkPasswordStrength(e.target.value);
    });

    // Form submission
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const resetBtn = document.getElementById('resetBtn');
      const loading = document.getElementById('loading');
      
      if (!newPassword || !confirmPassword) {
        alert('Please fill in all fields');
        return;
      }
      
      if (newPassword !== confirmPassword) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-msg';
        errorDiv.textContent = 'Passwords do not match';
        
        // Remove existing error messages
        const existingErrors = document.querySelectorAll('.error-msg');
        existingErrors.forEach(err => err.remove());
        
        // Insert error message after the form
        document.getElementById('resetPasswordForm').appendChild(errorDiv);
        return;
      }
      
      if (!checkPasswordStrength(newPassword)) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-msg';
        errorDiv.textContent = 'Password does not meet all security requirements';
        
        // Remove existing error messages
        const existingErrors = document.querySelectorAll('.error-msg');
        existingErrors.forEach(err => err.remove());
        
        // Insert error message after the form
        document.getElementById('resetPasswordForm').appendChild(errorDiv);
        return;
      }
      
      // Show loading state
      resetBtn.disabled = true;
      loading.style.display = 'block';
      
      // Create form data
      const formData = new FormData();
      formData.append('new_password', newPassword);
      formData.append('email', '<?php echo htmlspecialchars($email); ?>');
      
      // Send request
      fetch('resetPasswordProcess.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        loading.style.display = 'none';
        resetBtn.disabled = false;
        
        if (data.success) {
          // Clear session and redirect to login
          alert('Password reset successfully! Please login with your new password.');
          window.location.href = 'residentlogin.php?success=password_reset';
        } else {
          // Show error message
          const errorDiv = document.createElement('div');
          errorDiv.className = 'error-msg';
          errorDiv.textContent = data.message;
          
          // Remove existing error messages
          const existingErrors = document.querySelectorAll('.error-msg');
          existingErrors.forEach(err => err.remove());
          
          // Insert error message after the form
          document.getElementById('resetPasswordForm').appendChild(errorDiv);
        }
      })
      .catch(error => {
        loading.style.display = 'none';
        resetBtn.disabled = false;
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
    });
  </script>
</body>
</html>
