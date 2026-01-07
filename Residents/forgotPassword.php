<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay New Era | Forgot Password</title>

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

    /* ================== Forgot Password Container ================== */
    .forgot-section {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 80px 20px 0;
      box-sizing: border-box;
    }

    .forgot {
      background-color: #fff;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 420px;
      text-align: center;
    }

    @media (max-width: 576px) {
      .forgot {
        padding: 20px 15px;
        margin: 0;
      }
    }

    .forgot .logo img {
      width: 70px;
      margin-bottom: 12px;
    }

    .forgot h1 {
      font-size: 1.5rem;
      margin-bottom: 18px;
      color: #014A7F;
      font-weight: 700;
    }

    .forgot p {
      font-size: 0.9rem;
      color: #666;
      margin-bottom: 25px;
      line-height: 1.5;
    }

    .forgot label {
      display: block;
      text-align: left;
      font-weight: 600;
      margin-bottom: 5px;
      color: #333;
    }

    .forgot input[type="email"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 8px;
      border: 1px solid #ced4da;
      font-size: 0.9rem;
    }

    .forgot button {
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

    .forgot button:hover {
      background-color: #01365d;
    }

    .forgot .back-login {
      display: block;
      font-weight: 600;
      color: #014A7F;
      margin-top: 15px;
      text-align: center;
    }

    .forgot .back-login:hover {
      text-decoration: underline;
    }

    .forgot .back-home {
      display: block;
      font-weight: 600;
      color: #014A7F;
      margin-top: 15px;
      text-align: center;
    }

    .forgot .back-home:hover {
      text-decoration: underline;
    }

    .forgot p.error-msg {
      font-size: 0.85rem;
      margin-bottom: 8px;
      font-weight: 500;
      background-color: #f8d7da;
      padding: 8px 12px;
      border-radius: 4px;
      border-left: 4px solid #dc3545;
      display: inline-block;
    }
    .forgot p.success-msg {
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
      .forgot {
        padding: 30px 20px;
      }

      .forgot h1 {
        font-size: 1.5rem;
      }

      .forgot input[type="email"] {
        font-size: 0.95rem;
        padding: 10px;
      }

      .forgot button {
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

  <!-- ================= FORGOT PASSWORD ================= -->
  <section class="forgot-section">
    <div class="forgot">
      <div class="logo">
        <img src="../images/brgylogo.png" alt="Barangay Logo">
      </div>

      <form id="forgotPasswordForm">
        <h1>Forgot Password</h1>
        <p>Enter your email address and we'll send you a verification code to reset your password.</p>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'email_not_found'): ?>
          <p class="error-msg" style="color: red;">Email address not found in our system.</p>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'email_sent_failed'): ?>
          <p class="error-msg" style="color: red;">Failed to send verification email. Please try again.</p>
        <?php elseif (isset($_GET['success']) && $_GET['success'] == 'email_sent'): ?>
          <p class="success-msg" style="color: green;">Verification code sent! Please check your email.</p>
        <?php endif; ?>

        <label class="label">Email Address</label>
        <input type="email" name="email" id="email" class="email" placeholder="Enter your registered email" required>

        <button type="submit" id="submitBtn">Send Verification Code</button>
      </form>

      <div class="loading" id="loading">
        <div class="spinner"></div>
        <span>Sending...</span>
      </div>

      <a href="residentlogin.php" class="back-login">← Back to Login</a>
      
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const email = document.getElementById('email').value;
      const submitBtn = document.getElementById('submitBtn');
      const loading = document.getElementById('loading');
      
      if (!email) {
        alert('Please enter your email address');
        return;
      }
      
      // Show loading state
      submitBtn.disabled = true;
      loading.style.display = 'block';
      
      // Create form data
      const formData = new FormData();
      formData.append('email', email);
      
      // Send request
      fetch('forgotPasswordProcess.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        loading.style.display = 'none';
        submitBtn.disabled = false;
        
        if (data.success) {
          // Redirect to verification page using the URL from response
          window.location.href = data.redirect_url;
        } else {
          // Show error message
          const errorDiv = document.createElement('div');
          errorDiv.className = 'error-msg';
          errorDiv.style.color = 'red';
          errorDiv.textContent = data.message;
          
          // Remove existing error messages
          const existingErrors = document.querySelectorAll('.error-msg');
          existingErrors.forEach(err => err.remove());
          
          // Insert error message after the form
          document.getElementById('forgotPasswordForm').appendChild(errorDiv);
        }
      })
      .catch(error => {
        loading.style.display = 'none';
        submitBtn.disabled = false;
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
    });
  </script>
</body>
</html>
