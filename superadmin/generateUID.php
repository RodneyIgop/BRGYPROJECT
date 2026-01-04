<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

// Include PHPMailer at the top
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get request data from URL parameters
$requestId = $_GET['requestId'] ?? '';
$email = $_GET['email'] ?? '';
$firstName = $_GET['firstName'] ?? '';
$middleName = $_GET['middleName'] ?? '';
$lastName = $_GET['lastName'] ?? '';
$suffix = $_GET['suffix'] ?? '';
$censusNumber = $_GET['censusNumber'] ?? '';

// Generate a unique User ID
function generateUniqueUserID($conn) {
    // First, get all existing User IDs to avoid database queries in loop
    $stmt = $conn->prepare("SELECT Userid FROM usertbl WHERE Userid LIKE 'UID-%'");
    $stmt->execute();
    $result = $stmt->get_result();
    $existingIDs = [];
    while ($row = $result->fetch_assoc()) {
        $existingIDs[] = $row['Userid'];
    }
    $stmt->close();
    
    // Generate random ID until we find one that doesn't exist
    $attempts = 0;
    $maxAttempts = 100; // Prevent infinite loop
    
    do {
        $randomNumber = mt_rand(1000, 9999);
        $userID = 'UID-' . $randomNumber;
        $exists = in_array($userID, $existingIDs);
        $attempts++;
        
        // If we've tried too many times, expand the range
        if ($attempts > 50 && $attempts <= 60) {
            $randomNumber = mt_rand(10000, 99999);
            $userID = 'UID-' . $randomNumber;
            $exists = in_array($userID, $existingIDs);
        }
    } while ($exists && $attempts < $maxAttempts);
    
    return $userID;
}

// Database connection
require_once '../Connection/Conn.php';
$db = $conn;

// Generate User ID when requested
$generatedUserID = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $generatedUserID = generateUniqueUserID($db);
}

// Handle sending email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    error_log("Send button clicked - processing email send");
    
    $userID = $_POST['userID'] ?? '';
    $recipientEmail = $_POST['email'] ?? '';
    
    error_log("User ID: " . $userID . ", Recipient: " . $recipientEmail);
    
    if (!empty($userID) && !empty($recipientEmail)) {
        try {
            error_log("Starting email sending process");
            
            // Send email via PHPMailer
            $mail = new PHPMailer(true);
            
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'kirstenkhatemiral@gmail.com';
            $mail->Password   = 'swke nhwm gnav omfs';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('no-reply@barangaynewera.local', 'Barangay New Era');
            $mail->addAddress($recipientEmail, $firstName . ' ' . $lastName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Barangay New Era - Your Resident User ID';
            $mail->Body    = 'Your resident User ID is <strong>' . $userID . '</strong>. You can now use this ID to login to the resident system.';
            $mail->AltBody = 'Your resident User ID is: ' . $userID . '. You can now use this ID to login to the resident system.';

            $mail->send();
            
            error_log("Email sent successfully to: " . $recipientEmail);
            
            // Get the password from userrequest table
            $password = '';
            $stmt = $db->prepare("SELECT Password FROM userrequest WHERE RequestID = ?");
            $stmt->bind_param("s", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $password = $row['Password'];
                error_log("Retrieved password from userrequest");
            }
            $stmt->close();
            
            // Move the request data to usertbl with the generated User ID and password
            $sql = "INSERT INTO usertbl (LastName, FirstName, MiddleName, Suffix, birthdate, Age, ContactNumber, Address, CensusNumber, email, UID, Password, profile_picture) SELECT LastName, FirstName, MiddleName, Suffix, birthdate, Age, ContactNumber, address, CensusNumber, email, ?, ?, profile_picture FROM userrequest WHERE RequestID = ?";
            error_log("SQL: " . $sql);
            error_log("Params: UserID='" . $userID . "', Password length=" . strlen($password) . ", RequestID=" . $requestId);
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sss", $userID, $password, $requestId);
            
            if ($stmt->execute()) {
                error_log("Moved data to usertbl with User ID");
            } else {
                error_log("Failed to move data to usertbl: " . $stmt->error);
            }
            $stmt->close();
            
            // Delete from userrequest after successful transfer
            $stmt = $db->prepare("DELETE FROM userrequest WHERE RequestID = ?");
            $stmt->bind_param("s", $requestId);
            $stmt->execute();
            $stmt->close();
            
            error_log("Deleted from userrequest");
            
            $successMessage = "User ID has been sent to $recipientEmail";
            error_log("Success message set: " . $successMessage);
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            $errorMessage = "Error sending email: " . $mail->ErrorInfo;
        }
    } else {
        error_log("Missing User ID or email");
        $errorMessage = "User ID or email is missing";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate User ID</title>
    <link rel="stylesheet" href="residentrequest.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }
        .sidebar {
            height: 100vh;
            overflow-y: auto;
        }
        .main-content {
            height: 100vh;
            overflow-y: auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .container {
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }
        .generate-id-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
        }
        .card-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #014A7F;
        }
        .card-header h3 {
            color: #014A7F;
            margin: 0;
            font-size: 24px;
        }
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .info-section p {
            margin: 10px 0;
            color: #333;
        }
        .info-section strong {
            color: #014A7F;
        }
        .generate-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-group label {
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            background: #f8f9fa;
        }
        .form-group input[readonly] {
            background: #e9ecef;
            cursor: not-allowed;
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .generate-btn, .send-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .generate-btn {
            background: #014A7F;
            color: white;
        }
        .generate-btn:hover {
            background: #013a6b;
        }
        .send-btn {
            background: #28a745;
            color: white;
        }
        .send-btn:hover:not(:disabled) {
            background: #218838;
        }
        .send-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .success-message, .error-message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success-popup {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .success-popup-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
        }
        .success-popup-btn {
            background: #014A7F;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../images/brgylogo.png" alt="Logo">
            <h2>BARANGAY NEW ERA</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="superadmindashboard.php"> <img src="../images/home.png" alt="">Home</a>
            <a href="superadminProfile.php"> <img src="../images/user.png" alt="">Profile</a>
            <details class="sidebar-dropdown">
                <summary><img src="../images/list.png" alt="">Account Management <img src="../images/down.png" alt=""></summary>
                <a href="superadminAdminAccs.php" class="submenu-link"> <img src="../images/addAdmin.png" alt="">Manage Admin Accounts</a>
                <a href="superadminUserAccs.php" class="submenu-link"> <img src="../images/addUser.png" alt="">Manage Residents Accounts</a>
                <a href="superadminAccounts.php" class="submenu-link"> <img src="../images/addUser.png" alt="">Manage Superadmin Accounts</a>
                <a href="superadminUsers.php" class="submenu-link"> <img src="../images/pending.png" alt="">Block / Unblock Accounts</a>
            </details>
            <a href="superadminLogs.php"> <img src="../images/monitor.png" alt="">Activity Logs</a>
            <a href="superadminResidents.php"> <img src="../images/residents.png" alt="">Resident Information</a>
            <a href="superadminarchive.php"> <img src="../images/archive.png" alt="">Archives</a>
            <a href="#" onclick="logout()"> <img src="../images/logout.png" alt="">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Generate User ID</h1>
            <a href="residentrequest.php" class="back-btn" style="text-decoration: none; display: inline-block; margin-left: auto;">
                <button style="background: #014A7F; margin-bottom: -1em; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
                    ← Back to Resident Requests
                </button>
            </a>
        </div>

        <div class="container">
            <?php if (isset($successMessage)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errorMessage)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <div class="generate-id-card">
                <div class="card-header">
                    <h3>User ID Generation</h3>
                </div>
                
                <div class="card-content">
                    <div class="info-section">
                        <p><strong>The User ID will be sent to:</strong> <?php echo htmlspecialchars($email); ?></p>
                        <p><strong>Requester Name:</strong> <?php echo htmlspecialchars(trim($firstName . ' ' . $middleName . ' ' . $lastName . ' ' . $suffix)); ?></p>
                        <p><strong>Census Number:</strong> <?php echo htmlspecialchars($censusNumber); ?></p>
                    </div>

                    <form method="POST" class="generate-form">
                        <div class="form-group">
                            <label for="userID">User ID:</label>
                            <input type="text" id="userID" name="userID" 
                                   value="<?php echo htmlspecialchars($generatedUserID); ?>" 
                                   readonly placeholder="Click 'Generate User ID' button">
                        </div>
                        
                        <!-- Hidden fields for email and request ID -->
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <input type="hidden" name="requestId" value="<?php echo htmlspecialchars($requestId); ?>">

                        <div class="button-group">
                            <button type="submit" name="generate" class="generate-btn">Generate User ID</button>
                            <button type="submit" name="send" class="send-btn" 
                                    <?php echo empty($generatedUserID) ? 'disabled' : ''; ?>>
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Popup -->
    <div id="successPopup" class="success-popup" style="display: none;">
        <div class="success-popup-content">
            <div class="success-popup-header">
                <h3>Success</h3>
            </div>
            <div class="success-popup-body">
                <p>User ID sent successfully!</p>
            </div>
            <div class="success-popup-footer">
                <button class="success-popup-btn" onclick="closeSuccessPopup()">OK</button>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            if(confirm('Are you sure you want to logout?')) {
                window.location.href = 'superadminlogin.php?logout=true';
            }
        }
        
        function closeSuccessPopup() {
            document.getElementById('successPopup').style.display = 'none';
            // Redirect back to resident requests page
            window.location.href = 'residentrequest.php';
        }
        
        // Show success popup if success message exists
        <?php if (isset($successMessage)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('successPopup').style.display = 'block';
            });
        <?php endif; ?>
    </script>
</body>
</html>
