<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay New Era | Resident Login</title>

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

    /* ================== Login Container ================== */
    .login-section {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 80px 20px 0;
      box-sizing: border-box;
    }

    .login {
      background-color: #fff;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 420px;
      text-align: center;
    }

    @media (max-width: 576px) {
      .login {
        padding: 20px 15px;
        margin: 0;
      }
    }

    .login .logo img {
      width: 70px;
      margin-bottom: 12px;
    }

    .login h1 {
      font-size: 1.5rem;
      margin-bottom: 18px;
      color: #014A7F;
      font-weight: 700;
    }

    .login label {
      display: block;
      text-align: left;
      font-weight: 600;
      margin-bottom: 5px;
      color: #333;
    }

    .login input[type="text"],
    .login input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 8px;
      border: 1px solid #ced4da;
      font-size: 0.9rem;
    }

    .login .checkrow {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      font-size: 0.85rem;
      margin-top: -15px;
    }

    .login .checkrow input[type="checkbox"] {
      margin-right: 5px;
    }

    .login .forgot-password {
      color: #014A7F;
    }

    .login .forgot-password:hover {
      text-decoration: underline;
    }

    .login button.Login {
      width: 100%;
      padding: 10px;
      font-size: 0.9rem;
      font-weight: bold;
      background-color: #014A7F;
      color: #fff;
      border: none;
      border-radius: 8px;
      transition: background 0.3s;
    }

    .login button.Login:hover {
      background-color: #01365d;
    }

    .login .donthave {
      margin-top: 10px;
      font-size: 0.85rem;
      color: #555;
    }

    .login .register {
      display: block;
      font-weight: 600;
      color: #014A7F;
      margin-bottom: 6px;
    }

    .login .register:hover {
      text-decoration: underline;
    }

    .login .back-home {
      display: block;
      font-weight: 600;
      color: #014A7F;
      margin-top: 15px;
      text-align: center;
    }

    .login .back-home:hover {
      text-decoration: underline;
    }

    .login p.error-msg {
      color: red;
      font-size: 0.85rem;
      margin-bottom: 8px;
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

    /* ================== Responsive ================== */
    @media (max-width: 576px) {
      .login {
        padding: 30px 20px;
      }

      .login h1 {
        font-size: 1.5rem;
      }

      .login input[type="email"],
      .login input[type="password"] {
        font-size: 0.95rem;
        padding: 10px;
      }

      .login button.Login {
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
  </nav>


  <!-- ================= LOGIN ================= -->
  <section class="login-section">
    <div class="login">
      <div class="logo">
        <img src="../images/brgylogo.png" alt="Barangay Logo">
      </div>

      <form method="POST" action="residentLoginProcess.php">
        <h1>Login Now</h1>

        <label class="label">User ID</label>
        <span><input type="text" name="userID" class="userID" placeholder="Enter User ID" required></span>

        <div class="form-group password-group">
          <label class="label">Password</label>
          <input type="password" name="password" id="password3" class="password" placeholder="Enter Password" required>

          <span class="toggle-password" data-target="password3">
            <i class="fa-solid fa-eye"></i>
          </span>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
          <p class="error-msg">Invalid email or password.</p>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'empty'): ?>
          <p class="error-msg">Please fill in all fields.</p>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'blocked'): ?>
          <p class="error-msg">Your account has been blocked. Please contact the barangay administrator for assistance.</p>
        <?php elseif (isset($_GET['success']) && $_GET['success'] == 'password_reset'): ?>
          <p class="success-msg" style="color: green;">Password reset successfully! Please login with your new password.</p>
        <?php endif; ?>

        <div class="checkrow">
          <div>
            <a href="forgotPassword.php" class="forgot-password">Forgot Password?</a>
            
          </div>
        </div>

        <button type="submit" class="Login">LOGIN</button>
      </form>

      <p class="donthave">Don't have an account?</p>
      <a href="residentregister.php" class="register">Register here</a>
      
      <p class="donthave" style="margin-top: 20px;">Login as Administrator?</p>
      <a href="../Admin/adminlogin.php" class="register">Admin Login</a>
      <!-- <a href="../SuperAdmin/superadminlogin.php" class="register">Super Admin Login</a> -->
      
      <a href="../index.php" class="back-home">← Back to Home</a>
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script src="./residentlogin.js"></script>
<!-- <script>
  document.addEventListener('DOMContentLoaded', () => {
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
});
</script> -->
</html>