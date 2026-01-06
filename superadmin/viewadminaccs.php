<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

// Database connection
require_once '../Connection/Conn.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';
require_once '../Connection/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
$db = $conn; // Use the global connection variable from Conn.php

function sendBlockUnblockEmail($email, $fullName, $action, $accountType = 'Resident') {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kirstenkhatemiral@gmail.com';
        $mail->Password = 'swke nhwm gnav omfs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('no-reply@barangaynewera.local', 'Barangay New Era');
        $mail->addAddress($email, $fullName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Barangay New Era - Account ' . ucfirst($action);
        
        $accountTypeText = $accountType === 'Admin' ? 'Admin Account' : 'Resident Account';
        $actionText = $action === 'block' ? 'Blocked' : 'Unblocked';
        $actionLower = $action === 'block' ? 'blocked' : 'unblocked';
        
        if ($action === 'block') {
            $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <div style="text-align: center; margin-bottom: 30px;">
                        <img src="https://barangaynewera.local/images/brgylogo.png" alt="Barangay New Era Logo" style="width: 80px;">
                        <h2 style="color: #014A7F; margin: 10px 0;">Barangay New Era</h2>
                    </div>
                    
                    <h3 style="color: #333;">' . $accountTypeText . ' ' . $actionText . '</h3>
                    
                    <p>Dear ' . htmlspecialchars($fullName) . ',</p>
                    
                    <p>We regret to inform you that your ' . strtolower($accountTypeText) . ' has been <strong>' . $actionLower . '</strong>.</p>
                    
                    <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 20px 0;">
                        <h4 style="color: #721c24; margin: 0 0 10px 0;">Reason for Blocking:</h4>
                        <p style="color: #721c24; margin: 0;">Your account has been blocked due to violation of our terms of service or community guidelines.</p>
                    </div>
                    
                    <p><strong>Account Details:</strong></p>
                    <ul style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; list-style-type: none;">
                        <li><strong>Account Type:</strong> ' . $accountTypeText . '</li>
                        <li><strong>Action:</strong> ' . $actionText . '</li>
                        <li><strong>Date:</strong> ' . date('F j, Y, g:i a') . '</li>
                    </ul>
                    
                    <p>If you believe this is an error, please visit the Barangay New Era office with your valid identification documents for verification.</p>
                    
                    <div style="background-color: #f0f0f0; padding: 15px; border-radius: 5px; margin-top: 30px;">
                        <p style="margin: 0; font-size: 14px; color: #666;">
                            <strong>Important Notice:</strong> This action is based on our review process. 
                            You may contact the barangay office for more information about this action.
                        </p>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <p style="color: #666; font-size: 12px; margin: 0;">Barangay New Era Management System</p>
                        <p style="color: #666; font-size: 12px; margin: 0;">This is an automated message. Please do not reply to this email.</p>
                    </div>
                </div>';
        } else {
            $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <div style="text-align: center; margin-bottom: 30px;">
                        <img src="https://barangaynewera.local/images/brgylogo.png" alt="Barangay New Era Logo" style="width: 80px;">
                        <h2 style="color: #014A7F; margin: 10px 0;">Barangay New Era</h2>
                    </div>
                    
                    <h3 style="color: #333;">' . $accountTypeText . ' ' . $actionText . '</h3>
                    
                    <p>Dear ' . htmlspecialchars($fullName) . ',</p>
                    
                    <p>We are pleased to inform you that your ' . strtolower($accountTypeText) . ' has been <strong>' . $actionLower . '</strong>.</p>
                    
                    <div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 20px 0;">
                        <h4 style="color: #155724; margin: 0 0 10px 0;">Account Status:</h4>
                        <p style="color: #155724; margin: 0;">Your account has been reactivated and you can now access our services.</p>
                    </div>
                    
                    <p><strong>Account Details:</strong></p>
                    <ul style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; list-style-type: none;">
                        <li><strong>Account Type:</strong> ' . $accountTypeText . '</li>
                        <li><strong>Action:</strong> ' . $actionText . '</li>
                        <li><strong>Date:</strong> ' . date('F j, Y, g:i a') . '</li>
                    </ul>
                    
                    <p>You can now log in to your account and access all available services.</p>
                    
                    <div style="background-color: #f0f0f0; padding: 15px; border-radius: 5px; margin-top: 30px;">
                        <p style="margin: 0; font-size: 14px; color: #666;">
                            <strong>Welcome Back!</strong> We are glad to have you as part of our community again.
                        </p>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <p style="color: #666; font-size: 12px; margin: 0;">Barangay New Era Management System</p>
                        <p style="color: #666; font-size: 12px; margin: 0;">This is an automated message. Please do not reply to this email.</p>
                    </div>
                </div>';
        }
        
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<p>', '</p>'], ["\n", "\n", "\n\n", "\n"], $mail->Body));
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

// Add status column if it doesn't exist
try {
    $checkColumn = $db->query("SHOW COLUMNS FROM admintbl LIKE 'status'");
    if ($checkColumn->num_rows == 0) {
        $sql = "ALTER TABLE admintbl ADD COLUMN status ENUM('active', 'blocked') NOT NULL DEFAULT 'active'";
        $db->query($sql);
    }
} catch (Exception $e) {
    // Handle column addition error silently
}

// Add last_activity column if it doesn't exist
try {
    $checkColumn = $db->query("SHOW COLUMNS FROM admintbl LIKE 'last_activity'");
    if ($checkColumn->num_rows == 0) {
        $sql = "ALTER TABLE admintbl ADD COLUMN last_activity TIMESTAMP NULL DEFAULT NULL";
        $db->query($sql);
    }
} catch (Exception $e) {
    // Handle column addition error silently
}

// Handle block/unblock actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $adminId = $_POST['admin_id'] ?? '';
    $action = $_POST['action'];
    
    if ($action === 'block' || $action === 'unblock') {
        try {
            // Get admin info before updating
            $stmt = $db->prepare("SELECT adminID, FirstName, LastName, Email FROM admintbl WHERE adminID = ?");
            $stmt->bind_param('i', $adminId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Admin not found');
            }
            
            $admin = $result->fetch_assoc();
            $adminName = $admin['FirstName'] . ' ' . $admin['LastName'];
            $adminEmail = $admin['Email'];
            $stmt->close();
            
            $newStatus = $action === 'block' ? 'blocked' : 'active';
            $stmt = $db->prepare("UPDATE admintbl SET status = ? WHERE AdminID = ?");
            $stmt->bind_param('si', $newStatus, $adminId);
            $stmt->execute();
            $stmt->close();
            
            // Log the action
            $actionText = $action === 'block' ? 'blocked' : 'unblocked';
            error_log("Admin ID $adminId has been $actionText by superadmin");
            
            // Send email notification
            $emailSent = false;
            if (!empty($adminEmail)) {
                $emailSent = sendBlockUnblockEmail($adminEmail, $adminName, $action, 'Admin');
            }
            
            // Redirect to prevent form resubmission with email status
            $redirectUrl = 'viewadminaccs.php?status=' . $action . '&success=1';
            if ($emailSent) {
                $redirectUrl .= '&email=1';
            }
            header('Location: ' . $redirectUrl);
            exit;
        } catch (Exception $e) {
            $error_message = "Error updating admin status: " . $e->getMessage();
        }
    }
}

// Fetch admin accounts from database
$admins = [];
try {
    $stmt = $db->prepare("SELECT adminID, employeeID, LastName, FirstName, MiddleName, Suffix, Email, ContactNumber, birthdate, age, profile_picture, status FROM admintbl ORDER BY LastName, FirstName");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // Handle database error
    $error_message = "Error fetching admin accounts: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Admin Accounts</title>
    <link rel="stylesheet" href="viewadminaccs.css">
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
                <a href="superadminUserAccs.php" class="submenu-link"> <img src="../images/addUser.png" alt="">Manage Residents Accounts</a>
                <a href="superadminAdminAccs.php" class="submenu-link"> <img src="../images/addAdmin.png" alt="">Manage Admin Accounts</a>
                <a href="superadminAccounts.php" class="submenu-link"> <img src="../images/addUser.png" alt="">Manage Superadmin Accounts</a>
                <!-- <a href="superadminUsers.php" class="submenu-link"> <img src="../images/pending.png" alt="">Block / Unblock Accounts</a> -->
            </details>
            <a href="superadminLogs.php"> <img src="../images/monitor.png" alt="">Activity Logs</a>
            <a href="superadminResidents.php"> <img src="../images/residents.png" alt="">Resident Information</a>
            <a href="superadminarchive.php"> <img src="../images/archive.png" alt="">Archives</a>
            <a href="#" onclick="logout()"> <img src="../images/logout.png" alt="">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>View Admin Accounts</h1>
        </div>

        <div class="container">
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!isset($error_message)): ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($admins)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px;">No admin accounts found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($admins as $admin): ?>
                                    <?php 
                                    $fullname = trim($admin['FirstName'] . ' ' . $admin['MiddleName'] . ' ' . $admin['LastName'] . ' ' . $admin['Suffix']);
                                    // Use actual status from database
                                    $status = isset($admin['status']) ? $admin['status'] : 'active';
                                    $statusClass = $status === 'blocked' ? 'blocked' : 'active';
                                    $statusText = $status === 'blocked' ? 'Blocked' : 'Active';
                                    ?>
                                    <tr>
                                        <td><?php 
                                    $employeeID = htmlspecialchars($admin['employeeID'] ?? 'N/A');
                                    if ($employeeID !== 'N/A' && strlen($employeeID) > 4) {
                                        $masked = substr($employeeID, 0, -4) . str_repeat('*', 4);
                                        echo $masked;
                                    } elseif ($employeeID !== 'N/A') {
                                        echo str_repeat('*', strlen($employeeID));
                                    } else {
                                        echo $employeeID;
                                    }
                                    ?></td>
                                        <td><?php echo htmlspecialchars($fullname); ?></td>
                                        <td><?php echo htmlspecialchars($admin['Email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($admin['ContactNumber'] ?? 'N/A'); ?></td>
                                        <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                        <td>
                                            <button class="action-btn view-btn" 
                                                    data-admin-id="<?php echo htmlspecialchars($admin['adminID']); ?>"
                                                    data-employee-id="<?php echo htmlspecialchars($admin['employeeID'] ?? ''); ?>"
                                                    data-birthdate="<?php echo htmlspecialchars($admin['birthdate'] ?? ''); ?>"
                                                    data-age="<?php echo htmlspecialchars($admin['age'] ?? ''); ?>"
                                                    data-profile-picture="<?php echo htmlspecialchars($admin['profile_picture'] ?? ''); ?>"
                                                    onclick="viewAdmin(this)">View</button>
                                            
                                            <button class="action-btn <?php echo $status === 'blocked' ? 'unblock-btn' : 'block-btn'; ?>" 
                                                    data-admin-id="<?php echo htmlspecialchars($admin['adminID']); ?>"
                                                    data-admin-name="<?php echo htmlspecialchars($fullname); ?>"
                                                    data-current-status="<?php echo $status; ?>"
                                                    onclick="toggleBlockAdmin(this)">
                                                <?php echo $status === 'blocked' ? 'Unblock' : 'Block'; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Details Modal -->
    <div id="adminDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Admin Profile</h2>
                <span class="close" onclick="closeAdminModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="profile-modal">
                    <div class="profile-header">
                        <div class="profile-picture-container">
                            <img id="modalProfilePicture" src="../images/tao.png" alt="Profile Picture" class="profile-picture">
                        </div>
                        <div class="profile-basic-info">
                            <h3 id="modalFullName">-</h3>
                            <p id="modalAdminID">-</p>
                            <p id="modalEmployeeID">-</p>
                        </div>
                    </div>
                    <div class="profile-details">
                        <div class="detail-row">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value" id="modalEmail">-</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Contact Number:</div>
                            <div class="detail-value" id="modalContactNumber">-</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Birthdate:</div>
                            <div class="detail-value" id="modalBirthdate">-</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Age:</div>
                            <div class="detail-value" id="modalAge">-</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Status:</div>
                            <div class="detail-value" id="modalStatus">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '15px 20px',
        borderRadius: '5px',
        color: 'white',
        fontWeight: 'bold',
        zIndex: 10000,
        maxWidth: '300px',
        overflowWrap: 'break-word',
        backgroundColor: type === 'success' ? 'rgb(40, 167, 69)' :
                        type === 'error' ? 'rgb(220, 53, 69)' :
                        'rgb(23, 162, 184)'
    });

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// detect redirect parameters
const params = new URLSearchParams(window.location.search);

if (params.get('success') === '1') {
    const action = params.get('status');

    if (action === 'block') {
        showNotification(
            "Admin account has been blocked successfully. An email notification has been sent.",
            "success"
        );
    }

    if (action === 'unblock') {
        showNotification(
            "Admin account has been unblocked successfully. An email notification has been sent.",
            "success"
        );
    }
}

if (params.get('error')) {
    showNotification(params.get('error'), "error");
}
</script>
    <script src="viewadminaccs.js"></script>
    
</body>
</html>