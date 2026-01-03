<?php
session_start();
require_once '../Connection/conn.php';

// Check current number of superadmin accounts
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM superadmin');
$stmt->execute();
$result = $stmt->get_result();
$superadmin_count = $result->fetch_assoc()['count'];
$stmt->close();

$registration_disabled = $superadmin_count >= 2;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Superadmin Login</title>

    <style>
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        /* PAGE BACKGROUND */
        body {
            min-height: 100vh;
            background: #ffffff;
            background-image: url('../images/barangay hall_background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        /* Navbar */
        .navbar {
            background-color: #014A7F;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .nav-items {
            display: flex;
            align-items: center;
        }

        .navbar img {
            height: 40px;
            margin-right: 10px;
        }

        .navbar .title {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .nav-links a {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            margin-left: 20px;
        }

        /* Burger menu */
        .nav-toggle {
            display: none;
            font-size: 24px;
            color: #fff;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                margin-top: 10px;
                align-items: center;
                justify-content: center;
            }

            .nav-links a {
                margin: 10px 0;
                display: block;
                text-align: center;
            }

            .nav-toggle {
                display: block;
            }
        }

        /* LOGIN SECTION */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 120px 20px 50px;
        }

        /* LOGIN CARD */
        .login-card {
            background: #fff;
            width: 100%;
            max-width: 420px;
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
            text-align: center;
        }

        /* LOGO */
        .login-card img {
            width: 80px;
            margin-bottom: 10px;
        }

        /* TITLE */
        .login-card h2 {
            margin-bottom: 25px;
            color: #0f4c75;
        }

        /* FORM */
        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1b6ca8;
        }

        /* OPTIONS */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .form-options a {
            color: #014A7F;
            text-decoration: none;
        }

        .form-options a:hover {
            text-decoration: underline;
        }

        /* BUTTON */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #014A7F;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: rgba(24, 92, 141, 1);
        }

        /* LINKS */
        .register {
            margin-top: 20px;
            font-size: 14px;
        }

        .register a {
            color: #014A7F;
            text-decoration: none;
            font-weight: 500;
        }

        .register a:hover {
            text-decoration: underline;
        }

        .back-home {
            display: block;
            margin-top: 15px;
            font-size: 13px;
            color: #014A7F;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
        }

        .back-home:hover {
            text-decoration: underline;
        }

        /* DISABLED REGISTER LINK */
        .register-disabled {
            color: #999;
            text-decoration: none;
            cursor: not-allowed;
            font-weight: 500;
        }

        .register-disabled:hover {
            text-decoration: none;
        }

        .nav-disabled {
            color: #999 !important;
            text-decoration: none !important;
            cursor: not-allowed !important;
            font-weight: 600 !important;
        }

        .nav-disabled:hover {
            text-decoration: none !important;
        }

        /* NOTIFICATION */
        .limit-notification {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }

        .limit-notification i {
            margin-right: 8px;
        }

        /* RESPONSIVE */
        @media (max-width: 480px) {
            .login-card {
                padding: 25px 20px;
            }
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
            top: 67%;
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
    </style>

</head>

<body>
    <section id="Home">
        <!-- ================= NAVBAR ================= -->
        <nav class="navbar">
            <div class="nav-items">
                <img src="../images/brgylogo.png" alt="Logo" />
                <h1 class="title">BARANGAY NEW ERA</h1>
            </div>
            <div class="nav-toggle" id="navToggle">
                &#9776; <!-- burger icon -->
            </div>
            <div class="nav-links" id="navLinks">
                <a href="superadminlogin.php">LOGIN</a>
                <?php if ($registration_disabled): ?>
                    <span class="nav-disabled">REGISTER</span>
                <?php else: ?>
                    <a href="superadminregister.php">REGISTER</a>
                <?php endif; ?>
            </div>
        </nav>


        <!-- LOGIN -->
        <div class="login-container">
            <div class="login-card">

                <img src="../images/brgylogo.png" alt="Logo">

                <?php if ($registration_disabled): ?>
                    <div class="limit-notification">
                        <i class="fas fa-exclamation-triangle"></i>
                        Maximum superadmin accounts (2) reached. Registration is currently disabled.
                    </div>
                <?php endif; ?>

                <form method="POST" action="superadmindashboard.php">
                    <h2>Superadmin Login</h2>

                    <div class="form-group">
                        <label>Employee ID</label>
                        <input type="text" name="employee_id" placeholder="SPA00000" pattern="SPA[0-9]{5}" maxlength="8"
                            required>
                    </div>

                    <div class="form-group password-group">
                        <label>Password</label>
                        <input type="password" name="password" id="password4" placeholder="Enter password" required>
                        <span class="toggle-password" data-target="password4">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>

                    <div class="form-options">
                        <label>
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="#">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login">LOGIN</button>
                </form>

                <div class="register">
                    Don’t have an account?
                    <?php if ($registration_disabled): ?>
                        <span class="register-disabled">Register here</span>
                    <?php else: ?>
                        <a href="superadminregister.php">Register here</a>
                    <?php endif; ?>
                </div>

                <a href="../index.php" class="back-home">← Back to Home</a>

            </div>
        </div>
    </section>
</body>
<script>
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');

    navToggle.addEventListener('click', () => {
        if (navLinks.style.display === 'flex') {
            navLinks.style.display = 'none';
        } else {
            navLinks.style.display = 'flex';
        }
    });
</script>
<script src="./superadminlogin.js"></script>
</html>