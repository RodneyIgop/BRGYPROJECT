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

// Generate a unique employee ID
function generateUniqueEmployeeID($conn) {
    // First, get all existing employee IDs to avoid database queries in loop
    $stmt = $conn->prepare("SELECT employeeID FROM admintbl WHERE employeeID LIKE 'ADM-%'");
    $stmt->execute();
    $result = $stmt->get_result();
    $existingIDs = [];
    while ($row = $result->fetch_assoc()) {
        $existingIDs[] = $row['employeeID'];
    }
    $stmt->close();
    
    // Generate random ID until we find one that doesn't exist
    $attempts = 0;
    $maxAttempts = 100; // Prevent infinite loop
    
    do {
        $randomNumber = mt_rand(1000, 9999);
        $employeeID = 'ADM-' . $randomNumber;
        $exists = in_array($employeeID, $existingIDs);
        $attempts++;
        
        // If we've tried too many times, expand the range
        if ($attempts > 50 && $attempts <= 60) {
            $randomNumber = mt_rand(10000, 99999);
            $employeeID = 'ADM-' . $randomNumber;
            $exists = in_array($employeeID, $existingIDs);
        }
    } while ($exists && $attempts < $maxAttempts);
    
    return $employeeID;
}

// Database connection
require_once '../Connection/Conn.php';
$db = $conn;

// Generate employee ID when requested
$generatedEmployeeID = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $generatedEmployeeID = generateUniqueEmployeeID($db);
}

// Handle sending email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    error_log("Send button clicked - processing email send");
    
    $employeeID = $_POST['employeeID'] ?? '';
    $recipientEmail = $_POST['email'] ?? '';
    
    error_log("Employee ID: " . $employeeID . ", Recipient: " . $recipientEmail);
    
    if (!empty($employeeID) && !empty($recipientEmail)) {
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
            $mail->addAddress($recipientEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Barangay New Era - Your Admin Employee ID';
            $mail->Body    = 'Your admin Employee ID is <strong>' . $employeeID . '</strong>. You can now use this ID to login to the admin system.';
            $mail->AltBody = 'Your admin Employee ID is: ' . $employeeID . '. You can now use this ID to login to the admin system.';

            $mail->send();
            
            error_log("Email sent successfully to: " . $recipientEmail);
            
            // Get the password from session (stored during registration)
            $password = '';
            if (isset($_SESSION['pending_admin_password'])) {
                $password = $_SESSION['pending_admin_password'];
                error_log("Retrieved password from session: " . (empty($password) ? "EMPTY" : "FOUND"));
            } else {
                error_log("No password found in session - session data: " . print_r(array_keys($_SESSION), true));
            }
            
            // Move the request data to admintbl with the generated employeeID and password
            $sql = "INSERT INTO admintbl (lastname, firstname, middlename, suffix, contactnumber, birthdate, age, email, employeeID, password) SELECT lastname, firstname, middlename, suffix, contactnumber, birthdate, age, email, ?, ? FROM adminrequests WHERE RequestID = ?";
            error_log("SQL: " . $sql);
            error_log("Params: EmployeeID='" . $employeeID . "', Password length=" . strlen($password) . ", RequestID=" . $requestId);
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sss", $employeeID, $password, $requestId);
            
            if ($stmt->execute()) {
                error_log("Moved data to admintbl with password (length: " . strlen($password) . ")");
            } else {
                error_log("Failed to move data to admintbl: " . $stmt->error);
            }
            $stmt->close();
            
            // Delete from adminrequests after successful transfer
            $stmt = $db->prepare("DELETE FROM adminrequests WHERE RequestID = ?");
            $stmt->bind_param("s", $requestId);
            $stmt->execute();
            $stmt->close();
            
            error_log("Deleted from adminrequests");
            
            // Clear password from session
            unset($_SESSION['pending_admin_password']);
            
            $successMessage = "Employee ID has been sent to $recipientEmail";
            error_log("Success message set: " . $successMessage);
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            $errorMessage = "Error sending email: " . $mail->ErrorInfo;
        }
    } else {
        error_log("Missing employee ID or email");
        $errorMessage = "Employee ID or email is missing";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Employee ID</title>
    <link rel="stylesheet" href="adminrequests.css">
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
            <h1>Generate Employee ID</h1>
            <a href="adminrequests.php" class="back-btn" style="text-decoration: none; display: inline-block; margin-left: auto;">
                <button style="background: #014A7F; margin-bottom: -1em; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
                    ← Back to Admin Requests
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
                    <h3>Employee ID Generation</h3>
                </div>
                
                <div class="card-content">
                    <div class="info-section">
                        <p><strong>The employee ID will be sent to:</strong> <?php echo htmlspecialchars($email); ?></p>
                        <p><strong>Requester Name:</strong> <?php echo htmlspecialchars(trim($firstName . ' ' . $middleName . ' ' . $lastName . ' ' . $suffix)); ?></p>
                    </div>

                    <form method="POST" class="generate-form">
                        <div class="form-group">
                            <label for="employeeID">Employee ID:</label>
                            <input type="text" id="employeeID" name="employeeID" 
                                   value="<?php echo htmlspecialchars($generatedEmployeeID); ?>" 
                                   readonly placeholder="Click 'Generate Employee ID' button">
                        </div>
                        
                        <!-- Hidden fields for email and request ID -->
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <input type="hidden" name="requestId" value="<?php echo htmlspecialchars($requestId); ?>">

                        <div class="button-group">
                            <button type="submit" name="generate" class="generate-btn">Generate Employee ID</button>
                            <button type="submit" name="send" class="send-btn" 
                                    <?php echo empty($generatedEmployeeID) ? 'disabled' : ''; ?>>
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
                <p>Employee ID sent successfully!</p>
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
            // Redirect back to admin requests page
            window.location.href = 'adminrequests.php';
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
