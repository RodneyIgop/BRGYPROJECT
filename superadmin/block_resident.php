<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

require_once '../Connection/conn.php';
require_once '../Connection/log_activity.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';
require_once '../Connection/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Debug: Log that the script was accessed
error_log("DEBUG: block_resident.php was accessed");

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

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $residentUID = $_POST['resident_uid'] ?? '';
    $action = $_POST['action'] ?? ''; // 'block' or 'unblock'

    if (empty($residentUID) || !in_array($action, ['block', 'unblock'])) {
        throw new Exception('Invalid parameters: UID=' . $residentUID . ', Action=' . $action);
    }

    // Debug: Log received parameters
    error_log("DEBUG: Received resident_uid: " . $residentUID . ", action: " . $action);

    // Get resident info before updating
    $stmt = $conn->prepare("SELECT UID, FirstName, LastName, Email FROM usertbl WHERE UID = ?");
    $stmt->bind_param('s', $residentUID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Resident not found');
    }
    
    $resident = $result->fetch_assoc();
    $residentName = $resident['FirstName'] . ' ' . $resident['LastName'];
    $residentEmail = $resident['Email'];
    $stmt->close();

    // Update resident status
    $newStatus = $action === 'block' ? 'blocked' : 'active';
    $stmt = $conn->prepare("UPDATE usertbl SET status = ? WHERE UID = ?");
    $stmt->bind_param('ss', $newStatus, $residentUID);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update resident status');
    }
    $stmt->close();

    // Log the activity
    $superadminId = $_SESSION['superadmin_id'];
    $superadminName = $_SESSION['superadmin_name'];
    $actionText = $action === 'block' ? ACTION_BLOCK_USER : ACTION_UNBLOCK_USER;
    $description = ucfirst($action) . " resident account - Resident: $residentName (UID: $residentUID)";
    $page = 'residentsaccounts.php';
    
    logActivity($conn, $superadminId, $superadminName, 'Superadmin', $actionText, $description, $page, 'Successful');
    
    // Send email notification
    $emailSent = false;
    if (!empty($residentEmail)) {
        $emailSent = sendBlockUnblockEmail($residentEmail, $residentName, $action, 'Resident');
    }

    // Return success response
    $message = "Resident account has been " . ($action === 'block' ? 'blocked' : 'unblocked') . " successfully.";
    if ($emailSent) {
        $message .= " An email notification has been sent to the resident.";
    } else if (!empty($residentEmail)) {
        $message .= " However, the email notification could not be sent.";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'new_status' => $newStatus,
        'email_sent' => $emailSent
    ]);

} catch (Exception $e) {
    // Log error if needed
    error_log("Block/unblock error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
