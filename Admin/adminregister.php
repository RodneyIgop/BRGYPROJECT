<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Register</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <!-- jQuery UI (for datepicker if needed) -->
    <link rel="stylesheet" href="adminregister.css" />
    <style>
        /* ================== Global ================== */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
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

        /* Password field with toggle icon */
        .password-group {
            position: relative;
        }

        .password-group input {
            padding-right: 40px;
            /* space for the eye icon */
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 69%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #014A7F;
            font-size: 1rem;
        }

        .toggle-password:hover {
            color: #014A7F;
        }

        /* Remove browser default password reveal */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        input[type="password"]::-webkit-credentials-auto-fill-button {
            visibility: hidden;
        }

        /* Navbar */
        .navbar {
            background-color: #014A7F;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 10px;
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
            font-size: 17px;
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

        /* ================== Register Box ================== */
        .register-box {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            max-height: calc(100vh - 100px);
            overflow: visible;
        }

        .register-box h1 {
            text-align: center;
            color: #014A7F;
            margin-bottom: 15px;
            font-weight: 700;
        }

        @media (max-width: 576px) {
            .register-box {
                padding: 30px 20px;
                margin: 30px 20px;
            }
        }

        /* ================== Form Grid ================== */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="number"],
        .form-group input[type="tel"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 1rem;
        }

        /* Terms checkbox */
        .checkbox {
            display: flex;
            align-items: center;
            grid-column: 1 / -1;
        }

        .checkbox input {
            margin-right: 10px;
        }

        /* Buttons */
        button.Register {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            font-weight: bold;
            background-color: #014A7F;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            position: relative;
            transition: all 0.3s ease;
        }

        button.Register:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
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

        button.Register.loading .btn-text {
            visibility: hidden;
        }

        button.Register.loading .loading-spinner {
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

        /* Links */
        .register-line {
            text-align: center;
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
            color: #014A7F;
            font-weight: 600;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* ================== Modal ================== */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .modal-content {
            background: #ffffff;
            width: 90%;
            max-width: 650px;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin: 10% auto;
            max-height: 70vh;

        }

        .modal-content h2 {
            margin-bottom: 15px;
            text-align: center;
            color: #2c3e50;
            font-size: 22px;
            font-weight: bold;
        }

        .terms-content {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .term-item {
            margin-bottom: 16px;
        }

        .term-item h4 {
            margin: 0 0 4px;
            font-size: 15px;
            color: #014A7F;
            font-weight: 600;
        }

        .term-item p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            color: #555;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-decline {
            background: #e5e7eb;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-accept {
            background: #014A7F;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-accept:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }


        /* ================== Success Popup ================== */
        .success-popup {
            display: none;
            position: fixed;
            z-index: 2500;
            left: 0;
            top: 0;
        }

        .success-popup-btn {
            padding: 10px 20px;
            background-color: #014A7F;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .required {
            color: #e03131;
            /* Mantine red */
            margin-left: 4px;
            font-weight: 600;
        }

        #termsModal {
            display: none;
            /* hide by default */
        }

        /* ================== Responsive ================== */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- <section id="Home"> -->

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



    <!-- ==================== REGISTER CARD ==================== -->
    <div class="register-box">
        <h1>Admin Register</h1>
        <form id="registerForm" method="POST" action="adminRegisterProcess.php" class="form-grid">
            <!-- Row 1 -->
            <div class="form-group"><label>First Name<span class="required">*</span> </label><input type="text"
                    name="firstname" required /></div>
            <div class="form-group"><label>Middle Name <span class="required">*</span></label><input type="text" name="middlename" /></div>
            <div class="form-group"><label>Last Name <span class="required">*</span> </label><input type="text" name="lastname" required /></div>
            <div class="form-group"><label>Suffix</label><input type="text" name="suffix" /></div>

            <!-- Row 2 -->
            <div class="form-group"><label>Birthdate <span class="required">*</span></label><input type="text" name="birthdate" id="birthdate"
                    placeholder="mm/dd/yyyy" autocomplete="off" required /></div>
            <div class="form-group"><label>Age<span class="required">*</span> </label><input type="number" name="age" id="age" readonly /></div>

            <!-- Row 3: Age + Email -->
            <div class="form-group"><label>Contact No.<span class="required">*</span></label><input type="tel" name="contact" id="contact"
                    pattern="[0-9]{11}" maxlength="11" required /></div>
            <div class="form-group"><label>Email <span class="required">*</span> </label><input type="email" id="email" name="email" required /></div>

            <!-- Row 4: Password -->
            <div class="form-group password-group">
                <label>Password <span class="required">*</span></label>
                <input type="password" name="password" id="password" required>
                <span class="toggle-password" data-target="password">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            <div class="form-group password-group">
                <label>Confirm Password<span class="required">*</span></label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <span class="toggle-password" data-target="confirm_password">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            <!-- Terms -->
            <div class="form-group full checkbox">
                <label><input type="checkbox" name="terms" id="accept_terms" required /> I agree to the <a href="#"
                        id="openTerms">Terms of Service</a></label>
            </div>

            <!-- Submit -->
            <div class="form-group full">
                <button type="submit" class="Register" id="registerBtn" disabled>
                    <span class="btn-text">REGISTER</span>
                    <div class="loading-spinner"></div>
                </button>
            </div>
        </form>

        <div class="register-line">
            <span class="donthave">Already have an account?&nbsp;</span>
            <a href="adminLogin.php" class="login-link">Login here</a>
        </div>
        <div class="register-line">
            <a href="../index.php" class="back-link">← Back to Home</a>
        </div>
    </div>
    <!-- Terms Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <h2 class="terms">Terms and Conditions</h2>
            <div class="terms-content" style="max-height: 400px; overflow-y: auto;">
                <div class="term-item">
                    <h4>Admin Account Responsibilities</h4>
                    <p>
                        Admin accounts are strictly limited to authorized personnel designated by the
                        Barangay New Era Administration.
                    </p>
                </div>

                <div class="term-item">
                    <h4>Acceptable Use</h4>
                    <p>
                        The system must be used solely for official barangay operations and legitimate
                        administrative purposes.
                    </p>
                </div>

                <div class="term-item">
                    <h4>Data Privacy and Security</h4>
                    <p>
                        Administrators are required to protect all sensitive resident and barangay data
                        in accordance with applicable privacy laws and policies.
                    </p>
                </div>

                <div class="term-item">
                    <h4>System Modifications</h4>
                    <p>
                        The Barangay Administration reserves the right to update, modify, or restrict
                        system features and permissions at any time.
                    </p>
                </div>

                <div class="term-item">
                    <h4>Prohibited Activities</h4>
                    <p>
                        Unauthorized access, misuse of information, personal gain, or system tampering
                        is strictly prohibited.
                    </p>
                </div>

                <div class="term-item">
                    <h4>Activity Monitoring</h4>
                    <p>
                        All administrative actions within the system are logged and may be reviewed
                        for security and compliance purposes.
                    </p>
                </div>

                <div class="term-item">
                    <h4>Account Suspension</h4>
                    <p>
                        Any violation of these terms may result in account suspension or permanent
                        termination without prior notice.
                    </p>
                </div>

                <div class="term-item">
                    <h4>Limitation of Liability</h4>
                    <p>
                        The Barangay Administration shall not be held liable for damages resulting
                        from account misuse due to negligence or unauthorized access.
                    </p>
                </div>

                <div class="term-item">
                    <h4>Reporting Obligations</h4>
                    <p>
                        Administrators must immediately report any suspicious activity, security
                        breach, or system irregularity.
                    </p>
                </div>

                <div class="term-item">
                    <h4>Acceptance of Terms</h4>
                    <p>
                        Continued use of the system signifies full understanding and acceptance
                        of all terms and conditions stated above.
                    </p>
                </div>
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
            <p style="font-size: 12px; color: #666;">  Thanks for registering. We’ll now take you 
                to the verification page to complete your setup.
            </p>
            <button class="success-popup-btn" onclick="closeSuccessPopup()">OK</button>
        </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>




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
    <script>
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.dataset.target);
                const icon = toggle.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

    </script>

</body>
<script src="adminregister.js"></script>

</html>