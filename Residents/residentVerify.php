<?php
session_start();
include '../Connection/conn.php';

// Check if user has pending registration
if (!isset($_SESSION['pending_resident'])) {
    header('Location: residentregister.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = trim($_POST['verification_code'] ?? '');
    $stored_code = $_SESSION['pending_resident']['code'];
    
    if ($entered_code == $stored_code) {
        // Code is correct, save to userrequest table
        $data = $_SESSION['pending_resident']['data'];
        
        $stmt = $conn->prepare("
            INSERT INTO userrequest (
                LastName, FirstName, MiddleName, Suffix, 
                Birthdate, Age, ContactNumber, address, 
                censusnumber, email, Password, verificationCode, dateRequested
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param(
            "sssssissssss", 
            $data['LastName'], $data['FirstName'], $data['MiddleName'], $data['Suffix'],
            $data['Birthdate'], $data['Age'], $data['ContactNumber'], $data['Address'],
            $data['CensusNumber'], $data['email'], $data['Password'], $stored_code
        );
        
        if ($stmt->execute()) {
            unset($_SESSION['pending_resident']);
            $success = true;
        } else {
            $error = 'Error saving your request. Please try again.';
        }
    } else {
        $error = 'Invalid verification code. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Verification - Barangay New Era</title>

<!-- Bootstrap 4 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    padding: 0;
}

.navbar {
    background-color: #014A7F;
    padding: 1rem 2rem;
    color: #fff;
    display: flex;
    align-items: center;
}

.navbar img {
    height: 50px;
    margin-right: 15px;
}

.navbar h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.login {
    background-color: #fff;
    max-width: 500px;
    margin: 100px auto 50px;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    text-align: center;
}

.login h1 {
    font-size: 2rem;
    color: #014A7F;
    margin-bottom: 25px;
}

.login p {
    font-size: 1rem;
    color: #555;
    margin-bottom: 20px;
}

.field label {
    display: block;
    text-align: left;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.field input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ced4da;
    font-size: 1.2rem;
    text-align: center;
    letter-spacing: 4px;
    margin-bottom: 20px;
}

button.register {
    width: 100%;
    padding: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    background-color: #014A7F;
    color: #fff;
    border: none;
    border-radius: 8px;
    transition: 0.3s;
}

button.register:hover {
    background-color: #01365d;
}

button.ok-button {
    width: auto;
    min-width: 120px;
    padding: 12px 30px;
    font-size: 1rem;
    font-weight: 600;
    background-color: #28a745;
    color: #fff;
    border: none;
    border-radius: 8px;
    transition: 0.3s;
    cursor: pointer;
}

button.ok-button:hover {
    background-color: #218838;
}

.error-msg {
    color: #d9534f;
    margin-bottom: 20px;
    font-weight: 500;
    background-color: #f8d7da;
    padding: 10px 15px;
    border-radius: 6px;
}

.back-link {
    display: inline-block;
    margin-top: 15px;
    font-size: 0.95rem;
    color: #014A7F;
}

.back-link:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<nav class="navbar">
    <img src="../images/brgylogo.png" alt="Logo">
    <h1>BARANGAY NEW ERA </h1>
</nav>

<div class="login">
    <h1>Email Verification</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">
            Your account is under review by the SuperAdmin. They will send your User ID to your email.
        </div>
        <button onclick="window.location.href='residentlogin.php'" class="ok-button">OK</button>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <p>Please enter the 6-digit verification code sent to your email:</p>

        <form method="POST" action="">
            <div class="field">
                <label>Verification Code</label>
                <input type="text" name="verification_code" maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit code" required>
            </div>
            <button type="submit" class="register">Verify</button>
        </form>

        <a class="back-link" href="residentregister.php">← Back to Registration</a>
    <?php endif; ?>
</div>

</body>
</html>
