<?php
session_start();
require_once '../Connection/conn.php';
require_once '../Connection/log_activity.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeID = trim($_POST['employeeID'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($employeeID) || empty($password)) {
        $error = 'empty';
    } else {
        $stmt = $conn->prepare('SELECT AdminID, firstname, lastname, password FROM admintbl WHERE employeeID = ?');
        $stmt->bind_param('s', $employeeID);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($adminID, $firstname, $lastname, $stored_password);
            $stmt->fetch();

            if ($password === $stored_password) {
                $_SESSION['admin_id'] = $adminID;
                $_SESSION['admin_name'] = $firstname . ' ' . $lastname;
                
                // Log successful login
                $userName = $firstname . ' ' . $lastname;
                logActivity($conn, $adminID, $userName, 'Admin', ACTION_LOGIN, 'Admin logged in successfully', 'adminLogin.php', 'Successful');
                
                header('Location: adminIndex.php');
                exit;
            } else {
                $error = 'invalid';
                
                // Log failed login attempt
                logActivity($conn, 0, $employeeID, 'Admin', ACTION_LOGIN, 'Failed login attempt - invalid password', 'adminLogin.php', 'Failed');
            }
        } else {
            $error = 'invalid';
            
            // Log failed login attempt
            logActivity($conn, 0, $employeeID, 'Admin', ACTION_LOGIN, 'Failed login attempt - user not found', 'adminLogin.php', 'Failed');
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="adminLogin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            background-image: url('../images/barangay hall_background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            height: 100vh;
            overflow: hidden;
        }

        /* Navbar */
        .navbar {
            background-color: #014A7F;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 50px;
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

        /* ================== Section Home ================== */
        #Home {
            padding-top: 100px;
            /* space for fixed navbar */
        }

        /* ================== Login Container ================== */
        .login-admin {
            background-color: #fff;
            max-width: 450px;
            margin: 0 auto 50px;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        .login-admin h1 {
            font-size: 2rem;
            color: #014A7F;
            margin-bottom: 25px;
        }

        .login-admin .logo img {
            width: 100px;
            margin-bottom: 20px;
            display: inline-block;
        }

        .login-admin .logo {
            text-align: center;
        }

        .login-admin label {
            display: block;
            text-align: left;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .login-admin input[type="text"],
        .login-admin input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 1rem;
        }

        .checkrow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: -6px;
            font-size: 0.95rem;
        }

        .checkrow input[type="checkbox"] {
            margin-right: 5px;
        }

        .checkrow a {
            color: #014A7F;
        }

        .checkrow a:hover {
            text-decoration: underline;
        }

        button.Login {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            font-weight: bold;
            background-color: #014A7F;
            color: #fff;
            border: none;
            border-radius: 8px;
            transition: 0.3s;
        }

        button.Login:hover {
            background-color: #01365d;
        }

        .error-msg {
            color: #d9534f;
            margin-bottom: 15px;
            font-weight: 500;
            background-color: #f8d7da;
            padding: 10px 15px;
            border-radius: 6px;
        }

        .register-line {
            margin-top: 15px;
        }

        .register-line a {
            color: #014A7F;
            font-weight: 600;
            text-decoration: none;
        }

        .register-line a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 0.95rem;
            color: #014A7F;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-admin {
                margin: 120px 20px 50px;
                padding: 30px 20px;
            }

            .login-admin h1 {
                font-size: 1.6rem;
            }

            .login-admin input[type="text"],
            .login-admin input[type="password"] {
                font-size: 0.95rem;
                padding: 10px;
            }

            button.Login {
                font-size: 0.95rem;
                padding: 10px;
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
    </style>
</head>

<body>

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
            <a href="adminLogin.php">LOGIN</a>
            <a href="adminregister.php">REGISTER</a>
        </div>
    </nav>


    
        <div class="login-admin">
        <div class="logo">
            <img src="../images/taosaharap.png" alt="Logo">
        </div>
        <h1>Login Now</h1>

        <?php if ($error == 'invalid'): ?>
            <div class="error-msg">Invalid Employee ID or password.</div>
        <?php elseif ($error == 'empty'): ?>
            <div class="error-msg">Please fill in all fields.</div>
        <?php elseif (isset($_GET['success']) && $_GET['success'] == 'password_reset'): ?>
            <div class="success-msg" style="color: green;">Password reset successfully! Please login with your new password.</div>
        <?php endif; ?>

        <form method="POST" action="adminLogin.php">
            <label>Employee ID</label>
            <input type="text" name="employeeID" placeholder="Enter Employee ID (e.g., ADM-1234)" required>

            <div class="form-group password-group">
                <label>Password</label>
                <input type="password" name="password" id="password2" placeholder="Enter Password" required>
                <span class="toggle-password" data-target="password2">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            <div class="checkrow">
                
                <a href="adminForgotPassword.php">Forgot Password?</a>
            </div>

            <button type="submit" class="Login">LOGIN</button>
        </form>

        <div class="register-line">
            <span>Don't have an account? </span>
            <a href="adminregister.php">Register here</a>
        </div>
        <p class="donthave" style="margin-top: 20px;">Login as Administrator?</p>
  <!-- <a href="../Admin/adminlogin.php" class="register">Admin Login</a> -->
    <a href="../SuperAdmin/superadminlogin.php" class="register">Super Admin Login</a>

        <a href="../index.php" class="back-link">← Back to Home</a>
    


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
<script src="./adminLogin.js"></script>
</html>