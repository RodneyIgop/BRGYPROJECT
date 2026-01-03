<?php
session_start();
require_once '../Connection/conn.php';
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Validate required POST fields (Age is not required as it can be calculated)
$required = ['LastName','FirstName','ContactNumber','Birthdate','email','Password','Address','CensusNumber'];

// Debug: Show all POST data
error_log("POST data received: " . print_r($_POST, true));

// Validate password confirmation
if ($_POST['Password'] !== $_POST['confirm_password']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Passwords do not match. Please confirm your password.']);
    exit;
}

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        error_log("Missing field: $field. POST data for this field: '" . ($_POST[$field] ?? 'NOT SET') . "'");
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing field: ' . htmlspecialchars($field) . '. Please fill in all required fields.']);
        exit;
    }
}

// Calculate Age if not provided
if (empty($_POST['Age'])) {
    $BirthdateRaw = trim($_POST['Birthdate']);
    $dt = DateTime::createFromFormat('m/d/Y', $BirthdateRaw);
    $Birthdate = $dt ? $dt->format('Y-m-d') : date('Y-m-d', strtotime($BirthdateRaw));
    $birthDate = new DateTime($Birthdate);
    $today = new DateTime();
    $Age = $today->diff($birthDate)->y;
} else {
    $Age = (int)$_POST['Age'];
}

$LastName  = trim($_POST['LastName']);
$FirstName = trim($_POST['FirstName']);
$MiddleName= trim($_POST['MiddleName'] ?? '');
$Suffix    = trim($_POST['Suffix'] ?? '');
$ContactNumber   = trim($_POST['ContactNumber']);
$Birthdate  = trim($_POST['Birthdate']);
$Address   = trim($_POST['Address']);
$CensusNumber = trim($_POST['CensusNumber']);
$email     = trim($_POST['email']);
$Password  = $_POST['Password'];



// Password strength checked on client but double-check server-side (8-10 chars with complexity)
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};:\\|,.<>\/?]).{8,10}$/', $Password)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Password does not meet complexity requirements.']);
    exit;
}

// Check if email already exists in usertbl
$stmt = $conn->prepare('SELECT UserID FROM usertbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Email already registered as a resident account']);
    exit;
}
$stmt->close();

// Check if email already exists in userrequest
$stmt = $conn->prepare('SELECT RequestID FROM userrequest WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Email already has a pending resident request']);
    exit;
}
$stmt->close();

// Check if email already exists in admintbl (admin accounts)
$stmt = $conn->prepare('SELECT AdminID FROM admintbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Email already registered as an admin account']);
    exit;
}
$stmt->close();

// Check if email already exists in superadmin
$stmt = $conn->prepare('SELECT id FROM superadmin WHERE Email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Email already registered as a superadmin account']);
    exit;
}
$stmt->close();

// Generate verification code
$code = random_int(100000, 999999);

// Store user data temporarily in session
$_SESSION['pending_resident'] = [
  'data' => [
      'LastName'   => $LastName,
      'FirstName'  => $FirstName,
      'MiddleName' => $MiddleName,
      'Suffix'     => $Suffix,
      'ContactNumber' => $ContactNumber,
      'Birthdate'  => $Birthdate,
      'Age'        => $Age,
      'Address'    => $Address,
      'CensusNumber' => $CensusNumber,
      'email'      => $email,
      'Password'   => $Password,
      'verificationcode' => $code
  ],
  'code' => $code
];

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
    $mail->addAddress($email, $FirstName . ' ' . $LastName);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Barangay New Era - Resident Verification Code';
    $mail->Body    = 'Your verification code is <strong>' . $code . '</strong>. The code expires in 10 minutes.';

    $mail->send();
} catch (Exception $e) {
    unset($_SESSION['pending_resident']);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Registration successful']);
exit;
?>
