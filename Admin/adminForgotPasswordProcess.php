<?php
session_start();
require_once '../Connection/conn.php';
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Validate email input
if (empty($_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email address is required']);
    exit;
}

$email = trim($_POST['email']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check if email exists in admintbl (admin accounts)
$stmt = $conn->prepare('SELECT AdminID, firstname, lastname FROM admintbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Email address not found in our system']);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Clean up any existing unused reset requests for this email
$stmt = $conn->prepare('DELETE FROM password_reset_requests WHERE email = ? AND is_used = 0 AND expires_at < NOW()');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->close();

// Generate verification code and reset token
$verification_code = random_int(100000, 999999);
$reset_token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Store reset request in database
$stmt = $conn->prepare('INSERT INTO password_reset_requests (email, verification_code, reset_token, expires_at) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $email, $verification_code, $reset_token, $expires_at);

if (!$stmt->execute()) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Failed to process reset request. Please try again.']);
    exit;
}
$stmt->close();

// Send email via PHPMailer
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
    $mail->addAddress($email, $user['firstname'] . ' ' . $user['lastname']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Barangay New Era - Admin Password Reset Verification Code';
    
    $reset_link = "http://$_SERVER[HTTP_HOST]/Admin/adminResetPasswordVerify.php?email=" . urlencode($email) . "&token=" . urlencode($reset_token);
    
    $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            
            <div style="background-color: #014A7F; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
                <h1 style="margin: 0; font-size: 24px;">🔐 Barangay New Era</h1>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Admin Password Reset</p>
            </div>
            
            <div style="background-color: #f8f9fa; padding: 30px; border: 1px solid #ddd; border-top: none;">
                
                <p>Dear ' . htmlspecialchars($user['firstname'] . ' ' . htmlspecialchars($user['lastname'])) . ',</p>
                
                <p>We received a request to reset your password for your Barangay New Era admin account. Your verification code is:</p>
                
                <div style="background-color: #f8f9fa; border: 2px solid #014A7F; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
                    <h1 style="color: #014A7F; font-size: 32px; margin: 0; letter-spacing: 3px;">' . $verification_code . '</h1>
                </div>
                
                <p style="color: #dc3545; font-weight: bold; text-align: center; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin: 20px 0;">
                    ⚠️ <strong>Don\'t share this code to anyone.</strong>
                </p>
                
                <p><strong>Important:</strong></p>
                <ul>
                    <li>This code will expire in 10 minutes for security reasons</li>
                    <li>Never share this code with anyone</li>
                    <li>If you didn\'t request this password reset, please ignore this email</li>
                </ul>
                
                <p>If you have any questions, please contact the barangay office.</p>
                
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #888;">
                <p>&copy; ' . date('Y') . ' Barangay New Era. All rights reserved.</p>
                <p>This is an automated message. Please do not reply to this email.</p>
            </div>
        </div>
    ';

    $mail->AltBody = 'Your admin password reset verification code is: ' . $verification_code . '. This code expires in 10 minutes. ⚠️ Don\'t share this code to anyone. If you didn\'t request this, please ignore this email.';

    $mail->send();
    
    // Get the reset token for redirect
    $stmt = $conn->prepare('SELECT reset_token FROM password_reset_requests WHERE email = ? AND verification_code = ? ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('ss', $email, $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset_request = $result->fetch_assoc();
    $stmt->close();
    
    $redirect_url = 'adminResetPasswordVerify.php?email=' . urlencode($email) . '&token=' . urlencode($reset_request['reset_token']);
    echo json_encode(['success' => true, 'message' => 'Verification code sent successfully', 'redirect_url' => $redirect_url]);
    
} catch (Exception $e) {
    // Clean up failed reset request
    $stmt = $conn->prepare('DELETE FROM password_reset_requests WHERE email = ? AND verification_code = ?');
    $stmt->bind_param('ss', $email, $verification_code);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => false, 'message' => 'Failed to send verification email. Please try again. Error: ' . $mail->ErrorInfo]);
}
?>
