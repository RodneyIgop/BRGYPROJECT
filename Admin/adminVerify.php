<?php
session_start();
require_once '../Connection/conn.php';

// Check if pending admin data exists
if (!isset($_SESSION['pending_admin'])) {
    header('Location: adminregister.php');
    exit;
}

$pending = $_SESSION['pending_admin'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = trim($_POST['verificationcode'] ?? '');
    
    if ($submitted_code !== (string)$pending['code']) {
        $error_message = 'The verification code you entered is incorrect. Please check your email and try again.';
    } else {
    
    // Code is valid, insert admin request into database
    $data = $pending['data'];
    
    $stmt = $conn->prepare(
        'INSERT INTO adminrequests 
        (lastname, firstname, middlename, suffix, birthdate, age, email, contactnumber, password, requestDate) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );

    $stmt->bind_param(
        'sssssisss',
        $data['lastname'],
        $data['firstname'],
        $data['middlename'],
        $data['suffix'],
        $data['birthdate'],
        $data['age'],
        $data['email'],
        $data['contactnumber'],
        $data['password']
    );

    if ($stmt->execute()) {
        $_SESSION['pending_admin_password'] = $data['password'];
        unset($_SESSION['pending_admin']);
        $verification_success = true;
    } else {
        $error_message = 'A database error occurred while processing your registration. Please try again later.';
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
<title>Verify Email - Admin Register</title>
<link rel="stylesheet" href="adminregister.css">
<style>
    /* Professional UI Styles */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f2f5;
        margin: 0;
    }
    .navbar {
        display: flex;
        align-items: center;
        padding: 1rem 2rem;
        background-color: #014A7F;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .navbar img {
        height: 50px;
        margin-right: 1rem;
    }
    .navbar .title {
        color: #fff;
        font-size: 1.6rem;
    }
    .navbar .title span {
        display: block;
        font-size: 1rem;
        font-weight: normal;
        color: #cbd5e1;
    }
    .register-box {
        max-width: 450px;
        margin: 4rem auto;
        background: #fff;
        padding: 2.5rem 2rem;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        text-align: center;
    }
    .register-box h1 {
        margin-bottom: 1rem;
        color: #111827;
    }
    .register-box p {
        font-size: 0.95rem;
        color: #4b5563;
        margin-bottom: 2rem;
    }

    /* Form Styling */
    .form-grid {
        display: flex;
        flex-direction: column;
        gap: 1.2rem; /* Even spacing between fields */
    }
    .form-grid .form-group {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    .form-grid label {
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
    }
    .form-grid input {
        width: 100%;
        padding: 0.65rem 0.8rem;
        font-size: 1rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .form-grid input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59,130,246,0.2);
    }

    /* Button Styling */
    .Register {
        width: 100%;
        padding: 0.75rem;
        background-color: #014A7F;
        color: #fff;
        font-size: 1rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
    }
    .Register:hover {
        background-color: #0760a0ff;
    }
    .error-message {
        background-color: #fee;
        border: 1px solid #fcc;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 20px;
        color: #c33;
        font-size: 14px;
        text-align: center;
    }

    .register-line {
        margin-top: 1.5rem;
        font-size: 0.9rem;
        color: #6b7280;
    }
    .register-line .login-link {
        color: #014A7F;
        text-decoration: none;
        font-weight: 500;
    }
    .register-line .login-link:hover {
        text-decoration: underline;
    }

    /* Success Popup */
    .success-popup {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(31, 41, 55, 0.7);
        z-index: 1000;
    }
    .success-popup-content {
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        text-align: center;
    }
    .success-popup-header h3 {
        color: #10b981;
        margin-bottom: 1rem;
    }
    .success-popup-body p {
        font-size: 0.95rem;
        color: #374151;
        margin-bottom: 0.8rem;
    }
    .success-popup-btn {
        margin-top: 1.2rem;
        padding: 0.6rem 1.5rem;
        background-color: #10b981;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.3s;
    }
    .success-popup-btn:hover {
        background-color: #059669;
    }
    /* --- Mobile Responsive Tweaks (Small change only) --- */
@media (max-width: 768px) {

  .navbar {
    padding: 0.8rem 1rem;
  }

  .navbar img {
    height: 40px;
  }

  .navbar .title {
    font-size: 1.2rem;
  }

  .register-box {
    margin: 2rem 1rem;
    padding: 1.5rem 1rem;
    max-width: 100%;
    margin: 30px 15px;
  }

  .form-grid {
    gap: 1rem;
  }

  .Register {
    font-size: 0.95rem;
  }

  .success-popup-content {
    width: 95%;
    padding: 1.5rem 1rem;
  }
}

</style>
</head>
<body>
<section id="Home">
    <nav class="navbar">
        <img src="../images/brgylogo.png" alt="Logo" />
        <h1 class="title">BARANGAY NEW ERA </h1>
    </nav>

    <div class="register-box">
        <h1>Verify Email</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <p>A verification code has been sent to <strong><?php echo htmlspecialchars($pending['data']['email']); ?></strong></p>
        
        <form method="POST" class="form-grid">
            <div class="form-group full">
                <label>Enter Verification Code*</label>
                <input type="text" name="verificationcode" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required />
            </div>
            <div class="form-group full">
                <button type="submit" class="Register">VERIFY & COMPLETE REGISTRATION</button>
            </div>
        </form>

        <div class="register-line">
            <span>Don't have a registration code?&nbsp;</span>
            <a href="adminregister.php" class="login-link">Back to Register</a>
        </div>
    </div>

    <!-- Success Popup -->
    <div id="successPopup" class="success-popup" style="display: <?php echo isset($verification_success) && $verification_success ? 'flex' : 'none'; ?>;">
        <div class="success-popup-content">
            <div class="success-popup-header">
                <h3>Verification Successful</h3>
            </div>
            <div class="success-popup-body">
                <p>Your verification was successful!</p>
                <p>Your admin request has been submitted and is now under review by the superadmin.</p>
                <p>Your Account ID will be provided after approval.</p>
            </div>
            <div class="success-popup-footer">
                <button class="success-popup-btn" onclick="closeSuccessPopup()">OK</button>
            </div>
        </div>
    </div>
</section>

<script>
function closeSuccessPopup() {
    window.location.href = 'adminLogin.php';
}
</script>
</body>
</html>
