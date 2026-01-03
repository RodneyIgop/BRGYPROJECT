<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Super Admin Register - Test Version</title>
<link rel="stylesheet" href="superadminregister.css" />
<style>
.register-box {
    max-width: 700px;
    margin: 80px auto;
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}
.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
button {
    background: #014A7F;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background: #01365d;
}
.debug-info {
    background: #f0f0f0;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    font-family: monospace;
    font-size: 12px;
}
</style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-items">
            <img src="../images/brgylogo.png" alt="Logo" />
            <h1 class="title">BARANGAY NEW ERA</h1>
        </div>
        <div class="nav-links">
            <a href="superadminlogin.php">LOGIN</a>
        </div>
    </nav>

    <div class="register-box">
        <h1>Super Admin Register - Test Version</h1>
        <p style="color: red; font-weight: bold;">This is a simplified test version for debugging</p>
        
        <div id="debugInfo" class="debug-info">Debug info will appear here...</div>
        
        <form id="registerForm" method="POST" action="superadminRegisterProcess.php">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="firstname" required />
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="lastname" required />
            </div>
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="middlename" required />
            </div>
            <div class="form-group">
                <label>Suffix</label>
                <input type="text" name="suffix" />
            </div>
            <div class="form-group">
                <label>Birthdate</label>
                <input type="date" name="birthdate" required />
            </div>
            <div class="form-group">
                <label>Contact No.</label>
                <input type="tel" name="contact" pattern="[0-9]{11}" maxlength="11" required />
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required />
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="password" required />
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required />
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="terms" required /> I agree to the Terms of Service</label>
            </div>
            
            <button type="submit">REGISTER</button>
        </form>
        
        <div class="register-line">
            Already have an account? <a href="superadminlogin.php">Login here</a>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPwd = document.getElementById('confirm_password');
    const debugInfo = document.getElementById('debugInfo');
    
    function updateDebug() {
        debugInfo.innerHTML = `
            Form valid: ${form.checkValidity()}<br>
            Password length: ${password.value.length}<br>
            Passwords match: ${password.value === confirmPwd.value}<br>
            Terms checked: ${form.terms.checked}<br>
            Password: "${password.value}"
        `;
    }
    
    form.addEventListener('submit', function(e) {
        if (password.value !== confirmPwd.value) {
            e.preventDefault();
            alert('Passwords do not match');
            return false;
        }
        
        if (password.value.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters');
            return false;
        }
        
        debugInfo.innerHTML += '<br><strong>Form submitted successfully!</strong>';
    });
    
    // Update debug info on input
    form.addEventListener('input', updateDebug);
    updateDebug();
});
</script>
</body>
</html>
