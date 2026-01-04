<?php
include '../Connection/conn.php';
if (isset($_GET['success'])) {
    echo "<script>alert('Registered Successfully!');window.location.href='residentlogin.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Barangay New Era | Resident Registration</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="residentregister.css">
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

        a.loginhere, a.back-home {
            text-decoration: none;
            
            color: #014A7F;
            
        }

        a.loginhere:hover, a.back-home:hover {
            text-decoration: underline;
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

        /* ================== Registration Container ================== */
        .register-section {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding-top: 70px;
        }

        .register-box {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 600px;
            text-align: center;
            margin: 3rem 0 5rem 0;
        }

        .register-box .logo img {
            width: 100px;
            margin-bottom: 20px;
        }

        .register-box h1 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: #014A7F;
            font-weight: 700;
        }

        .register-box label {
            display: block;
            text-align: left;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .register-box input,
        .register-box select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 1rem;
        }

        .register-box .checkrow {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .register-box .checkrow input[type="checkbox"] {
            margin-right: 5px;
        }

        .register-box .terms-link {
            color: #014A7F;
            cursor: pointer;
        }

        .register-box .terms-link:hover {
            text-decoration: underline;
        }

        .register-box button.register {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            font-weight: bold;
            background-color: #014A7F;
            color: #fff;
            border: none;
            border-radius: 8px;
            transition: background 0.3s;
            position: relative;
            margin-bottom: 20px;
        }

        .register-box button.register:hover {
            background-color: #01365d;
        }

        /* Loading spinner */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        .register-box button.register.loading .btn-text {
            visibility: hidden;
        }

        .register-box button.register.loading .loading-spinner {
            display: block;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Success popup */
        .success-popup {
            display: none;
            position: fixed;
            z-index: 2500;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .success-popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .success-popup-content h3 {
            color: #014A7F;
            margin-bottom: 10px;
        }

        .success-popup-btn {
            padding: 10px 20px;
            background-color: #014A7F;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        /* Error message */
        .register-box p.error-msg {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        /* ================== Responsive ================== */
        @media (max-width: 576px) {
            .register-box {
                padding: 30px 20px;
            }

            .register-box h1 {
                font-size: 1.5rem;
            }

            .register-box input {
                font-size: 0.95rem;
                padding: 10px;
            }

            .register-box button.register {
                font-size: 0.95rem;
                padding: 10px;
            }
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow: auto;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            max-height: 70vh;
            border-radius: 8px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .terms-content {
            overflow-y: auto;
            flex: 1;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 10px 0;
        }

        #acceptBtn:disabled {
            background-color: #000;
            cursor: not-allowed;
        }

        #acceptBtn {
            color: white;
        }

        /* Add this to ensure the checkbox is properly aligned */
        .checkrow label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        /* residentregister.css */
        .register[disabled] {
            background: #888 !important;
            /* grey when disabled */
            cursor: not-allowed;
            opacity: .6;
        }

        .register {
            background: #014A7F;
            color: #fff;
        }

        @media (max-width: 576px) {
            .register-box {
                padding: 30px 20px;
                margin: 30px 20px;
            }

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

        .required {
            color: red;
        }
    </style>

</head>

<body>

    <!-- ================= NAVBAR ================= -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow custom-bg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="../images/brgylogo.png" class="mr-2">
                <strong>Barangay New Era</strong>
            </a>

            <!-- Hamburger -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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

    <!-- ================= REGISTRATION ================= -->
    <section class="register-section">
        <div class="register-box">
            <div class="logo">
                <img src="../images/brgylogo.png" alt="Barangay Logo">
            </div>

            <h1>Register Now</h1>

            <form method="POST" action="residentRegisterProcess.php" id="registrationForm">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>First Name <span class="required">*</span></label>
                        <input type="text" name="FirstName" placeholder="First Name" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Last Name <span class="required">*</span></label>
                        <input type="text" name="LastName" placeholder="Last Name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Middle Name <span class="required">*</span></label>
                        <input type="text" name="MiddleName" placeholder="Middle Name">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Suffix</label>
                        <input type="text" name="Suffix" placeholder="Suffix">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Birthdate <span class="required">*</span> </label>
                        <input type="text" name="Birthdate" id="birthdate" placeholder="mm/dd/yyyy" autocomplete="off"
                            required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Age <span class="required">*</span></label>
                        <input type="text" id="age_display" placeholder="Age" readonly>
                        <input type="hidden" name="Age" id="age">
                    </div>
                </div>

                <div class="form-group">
                    <label>Contact Number <span class="required">*</span></label>
                    <input type="tel" name="ContactNumber" placeholder="09XXXXXXXXX" maxlength="11" pattern="[0-9]{11}"
                        title="Please enter exactly 11 digits" required>
                </div>

                <div class="form-group">
                    <label>Home Address <span class="required">*</span></label>
                    <input type="text" name="Address" placeholder="Street / House No." required>
                </div>

                <div class="form-group">
                    <label>Census Number <span class="required">*</span></label>
                    <input type="text" name="CensusNumber" placeholder="Census Number" required>
                </div>

                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" placeholder="email@example.com" required
                        pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                </div>

                <div class="form-group" style="position: relative;">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="Password" id="password" placeholder="Password" required
                        style="padding-right: 40px;">

                    <span id="togglePassword" style="position: absolute; right: 12px; top: 59%; transform: translateY(-50%);
               cursor: pointer; color: #014A7F;">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                </div>
                <div class="form-group" style="position: relative;">
                    <label>Confirm Password <span class="required">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required
                        style="padding-right: 40px;">

                    <span id="toggleConfirmPassword" style="position: absolute; right: 12px; top: 59%; transform: translateY(-50%);
               cursor: pointer; color: #014A7F;">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                </div>


                <div class="checkrow" style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                    <input type="checkbox" name="accept_terms" id="accept_terms" style="width: 18px; height: 18px;">
                    <label for="accept_terms" style="margin: 0; font-size: 0.95rem; line-height: 1.3; cursor: pointer;">
                        I agree to the <span class="terms-link" id="showTerms" style="color: #014A7F;">terms and
                            conditions</span>.
                    </label>
                </div>



                <button type="submit" id="registerBtn" class="register" disabled>
                    <span class="btn-text">REGISTER</span>
                    <div class="loading-spinner"></div>
                </button>
            </form>

            <p class="already-account">Already have an account?</p>
            <a href="residentlogin.php" class="loginhere">Login here</a>
            <br>
            <a href="../index.php" class="back-home">← Back to Home</a>
        </div>
    </section>

    <!-- ================= TERMS MODAL ================= -->


    <div id="termsModal" class="modal">
        <div class="modal-content">
            <h2>Terms and Conditions</h2>
            <div class="terms-content">
            </div>
            <div class="modal-buttons">
                <button id="declineBtn" type="button">Decline</button>
                <button id="acceptBtn" type="button" disabled>Accept</button>
            </div>
        </div>
    </div>

    <!-- Success Popup -->
    <div id="successPopup" class="success-popup">
        <div class="success-popup-content">
            <h3>Registration Successful</h3>
            <p style="font-size: 12px; color: #666;">Thanks for registering. We’ll now take you 
                to the verification page to complete your setup.</p>
            <button class="success-popup-btn" onclick="closeSuccessPopup()">OK</button>
        </div>
    </div>

    <!-- ================= SCRIPTS ================= -->
</body>


<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="./residentregister.js"></script>
<script src="./residentlogin.js"></script>

<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const icon = togglePassword.querySelector('i');

    togglePassword.addEventListener('click', () => {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>


</html>