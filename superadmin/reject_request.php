<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Database connection
require_once '../Connection/Conn.php';
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send rejection email notification
 * @param string $email Recipient email
 * @param string $fullName Recipient full name
 * @param string $requestType Type of request (admin/resident)
 * @param string $requestId Request ID
 * @return bool True if email sent successfully, false otherwise
 */
function sendRejectionEmail($email, $fullName, $requestType, $requestId) {
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
        $mail->Subject = 'Barangay New Era - Account Request Rejected';
        
        $accountType = $requestType === 'admin' ? 'Admin Account' : 'Resident Account';
        
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <img src="/era/images/taosaharap.png" alt="Barangay New Era Logo" style="width: 80px;">
                    <h2 style="color: #014A7F; margin: 10px 0;">Barangay New Era</h2>
                </div>
                
                <h3 style="color: #333;">' . $accountType . ' Request Rejected</h3>
                
                <p>Dear ' . htmlspecialchars($fullName) . ',</p>
                
                <p>We regret to inform you that your request for a ' . strtolower($accountType) . ' has been <strong>rejected</strong>.</p>
                
                <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <h4 style="color: #721c24; margin: 0 0 10px 0;">Reason for Rejection:</h4>
                    <p style="color: #721c24; margin: 0;">Your name does not match any record in our official residents list.</p>
                </div>
                
                <p><strong>Request Details:</strong></p>
                <ul style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; list-style-type: none;">
                    <li><strong>Request ID:</strong> ' . htmlspecialchars($requestId) . '</li>
                    <li><strong>Request Type:</strong> ' . $accountType . '</li>
                    <li><strong>Rejection Date:</strong> ' . date('F j, Y, g:i a') . '</li>
                </ul>
                
                <p>If you believe this is an error, please visit the Barangay New Era office with your valid identification documents for verification.</p>
                
                <div style="background-color: #f0f0f0; padding: 15px; border-radius: 5px; margin-top: 30px;">
                    <p style="margin: 0; font-size: 14px; color: #666;">
                        <strong>Important Notice:</strong> This rejection is based on our verification process. 
                        Only registered residents of Barangay New Era are eligible for account creation.
                    </p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #888;">
                    <p>&copy; ' . date('Y') . ' Barangay New Era. All rights reserved.</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        ';

        $mail->AltBody = 'Your ' . strtolower($accountType) . ' request (ID: ' . $requestId . ') has been rejected because your name does not match any record in our residents list. If you believe this is an error, please visit the Barangay New Era office with your valid identification documents.';

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send rejection email: " . $mail->ErrorInfo);
        return false;
    }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];
    $rejectedBy = $_SESSION['superadmin_name'] ?? 'Super Admin';
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Check if request exists in adminrequests table first
        $checkStmt = $conn->prepare("SELECT RequestID, lastname, firstname, middlename, suffix, birthdate, age, email, contactnumber, requestDate FROM adminrequests WHERE RequestID = ?");
        $checkStmt->bind_param("s", $requestId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            // This is an admin request
            $requestDetails = $result->fetch_assoc();
            $checkStmt->close();
            
            // Send rejection email
            $fullName = trim($requestDetails['firstname'] . ' ' . $requestDetails['middlename'] . ' ' . $requestDetails['lastname'] . ' ' . $requestDetails['suffix']);
            $emailSent = sendRejectionEmail($requestDetails['email'], $fullName, 'admin', $requestId);
            
            // Insert into rejected_admin_requests table
            $insertStmt = $conn->prepare("INSERT INTO rejected_admin_requests (RequestID, lastname, firstname, middlename, suffix, birthdate, age, email, contactnumber, requestDate, rejectionDate, rejected_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
            $insertStmt->bind_param("sssssisssss", 
                $requestDetails['RequestID'],
                $requestDetails['lastname'],
                $requestDetails['firstname'],
                $requestDetails['middlename'],
                $requestDetails['suffix'],
                $requestDetails['birthdate'],
                $requestDetails['age'],
                $requestDetails['email'],
                $requestDetails['contactnumber'],
                $requestDetails['requestDate'],
                $rejectedBy
            );
            $insertStmt->execute();
            $insertStmt->close();
            
            // Delete from adminrequests
            $deleteStmt = $conn->prepare("DELETE FROM adminrequests WHERE RequestID = ?");
            $deleteStmt->bind_param("s", $requestId);
            $deleteStmt->execute();
            $deleteStmt->close();
            
        } else {
            // Check if it's a resident request
            $checkStmt->close();
            $residentCheckStmt = $conn->prepare("SELECT RequestID, LastName, FirstName, MiddleName, Suffix, birthdate, Age, email, ContactNumber, address, CensusNumber, dateRequested FROM userrequest WHERE RequestID = ?");
            $residentCheckStmt->bind_param("s", $requestId);
            $residentCheckStmt->execute();
            $residentResult = $residentCheckStmt->get_result();
            
            if ($residentResult->num_rows > 0) {
                // This is a resident request
                $residentDetails = $residentResult->fetch_assoc();
                $residentCheckStmt->close();
                
                // Send rejection email
                $fullName = trim($residentDetails['FirstName'] . ' ' . $residentDetails['MiddleName'] . ' ' . $residentDetails['LastName'] . ' ' . $residentDetails['Suffix']);
                $emailSent = sendRejectionEmail($residentDetails['email'], $fullName, 'resident', $requestId);
                
                // Insert into rejected_resident_requests table
                $insertStmt = $conn->prepare("INSERT INTO rejected_resident_requests (RequestID, lastname, firstname, middlename, suffix, birthdate, age, email, contactnumber, requestDate, rejectionDate, rejected_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
                $insertStmt->bind_param("sssssisssss", 
                    $residentDetails['RequestID'],
                    $residentDetails['LastName'],
                    $residentDetails['FirstName'],
                    $residentDetails['MiddleName'],
                    $residentDetails['Suffix'],
                    $residentDetails['birthdate'],
                    $residentDetails['Age'],
                    $residentDetails['email'],
                    $residentDetails['ContactNumber'],
                    $residentDetails['dateRequested'],
                    $rejectedBy
                );
                $insertStmt->execute();
                $insertStmt->close();
                
                // Delete from userrequest
                $deleteStmt = $conn->prepare("DELETE FROM userrequest WHERE RequestID = ?");
                $deleteStmt->bind_param("s", $requestId);
                $deleteStmt->execute();
                $deleteStmt->close();
                
            } else {
                $residentCheckStmt->close();
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Request not found in admin or resident tables']);
                exit;
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $message = 'Request rejected successfully';
        if (isset($emailSent) && $emailSent) {
            $message .= ' and email notification sent';
        } elseif (isset($emailSent) && !$emailSent) {
            $message .= ' but email notification failed';
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error rejecting request: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
