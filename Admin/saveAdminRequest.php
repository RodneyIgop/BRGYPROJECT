<?php
session_start();
require_once '../Connection/conn.php';
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Validate required POST fields
$required = ['lastname','firstname','contact','birthdate','age','email','password','confirm_password'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Missing field: ' . htmlspecialchars($field)]);
        exit;
    }
}

$lastname  = trim($_POST['lastname']);
$firstname = trim($_POST['firstname']);
$middlename= trim($_POST['middlename'] ?? '');
$suffix    = trim($_POST['suffix'] ?? '');
$contact   = trim($_POST['contact']);
$birthdateRaw = trim($_POST['birthdate']);
// Expecting mm/dd/yyyy from the datepicker; convert to Y-m-d
$dt = DateTime::createFromFormat('m/d/Y', $birthdateRaw);
$birthdate = $dt ? $dt->format('Y-m-d') : date('Y-m-d', strtotime($birthdateRaw));
$age       = (int)$_POST['age'];
$email     = trim($_POST['email']);
$password  = $_POST['password'];
$confirm   = $_POST['confirm_password'];

if ($password !== $confirm) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

// Password strength checked on client but double-check server-side
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};:\\|,.<>\/?]).{8,25}$/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password does not meet complexity requirements.']);
    exit;
}

// Check if email already exists in admintbl
$stmt = $conn->prepare('SELECT AdminID FROM admintbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered as an admin account']);
    exit;
}
$stmt->close();

// Check if email already exists in adminrequests
$stmt = $conn->prepare('SELECT RequestID FROM adminrequests WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already has a pending admin request']);
    exit;
}
$stmt->close();

// Generate verification code
$code = random_int(100000, 999999);

// Debug: Log code generation
error_log("Generated verification code: " . $code . " for email: " . $email);

// Store user data temporarily in session
$_SESSION['pending_admin'] = [
  'data' => [
      'lastname'   => $lastname,
      'firstname'  => $firstname,
      'middlename' => $middlename,
      'suffix'     => $suffix,
      'contactnumber' => $contact,
      'birthdate'  => $birthdate,
      'age'        => $age,
      'email'      => $email,
      'password'   => $password,
      'verificationcode' => $code
  ],
  'code' => $code
];

// Debug: Log session storage
error_log("Stored pending admin data in session for: " . $email);

// Send email via PHPMailer
$mail = new PHPMailer(true);
try {
    //Server settings
    $mail-> isSMTP();
   $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'kirstenkhatemiral@gmail.com';
    $mail->Password   = 'swke nhwm gnav omfs';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom('no-reply@barangaynewera.local', 'Barangay New Era');
    $mail->addAddress($email, $firstname . ' ' . $lastname);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Barangay New Era - Admin Verification Code';
    $mail->Body    = 'Your verification code is <strong>' . $code . '</strong>. The code expires in 10 minutes.<br><br><strong style="color: #dc3545;">⚠️ Don\'t share this code to anyone.</strong><br><br>If you didn\'t request this code, please ignore this email.';

    $mail->send();
    
    // Debug: Log successful email send
    error_log("Verification code sent successfully to: " . $email . " Code: " . $code);
    
} catch (Exception $e) {
    // Debug: Log email error
    error_log("Email sending failed: " . $mail->ErrorInfo);
    unset($_SESSION['pending_admin']);
    echo json_encode(['success' => false, 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Verification code sent successfully']);
?>
