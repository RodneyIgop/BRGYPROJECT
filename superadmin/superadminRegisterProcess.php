<?php
session_start();
require_once '../Connection/conn.php';
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set content type to JSON
header('Content-Type: application/json');

$required = ['lastname','firstname','contact','birthdate','email','password','confirm_password'];
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
$dt = DateTime::createFromFormat('m/d/Y', $birthdateRaw);
$birthdate = $dt ? $dt->format('Y-m-d') : date('Y-m-d', strtotime($birthdateRaw));
$birthdateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
if (!$birthdateObj) {
    echo json_encode(['success' => false, 'message' => 'Invalid birthdate']);
    exit;
}
$today = new DateTime('today');
if ($birthdateObj > $today) {
    echo json_encode(['success' => false, 'message' => 'Birthdate cannot be in the future']);
    exit;
}
$age = $birthdateObj->diff($today)->y;
$email     = trim($_POST['email']);
$password  = $_POST['password'];
$confirm   = $_POST['confirm_password'];

if ($password !== $confirm) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};:\\|,.<>\/?]).{8,25}$/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password does not meet complexity requirements.']);
    exit;
}

// Check current number of superadmin accounts
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM superadmin');
$stmt->execute();
$result = $stmt->get_result();
$current_count = $result->fetch_assoc()['count'];
$stmt->close();

if ($current_count >= 2) {
    echo json_encode(['success' => false, 'message' => 'Maximum number of superadmin accounts (2) has been reached. No new superadmin accounts can be created at this time.']);
    exit;
}

$stmt = $conn->prepare('SELECT id FROM superadmin WHERE Email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered as a superadmin account']);
    exit;
}
$stmt->close();

// Check if email already exists in admintbl (admin accounts)
$stmt = $conn->prepare('SELECT AdminID FROM admintbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered as an admin account']);
    exit;
}
$stmt->close();

// Check if email already exists in usertbl (resident accounts)
$stmt = $conn->prepare('SELECT UserID FROM usertbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered as a resident account']);
    exit;
}
$stmt->close();

// Check if email already exists in userrequest (pending resident requests)
$stmt = $conn->prepare('SELECT RequestID FROM userrequest WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already has a pending resident request']);
    exit;
}
$stmt->close();

$code = random_int(100000, 999999);

$_SESSION['pending_superadmin'] = [
    'data' => [
        'LastName' => $lastname,
        'FirstName' => $firstname,
        'MiddleName' => $middlename,
        'Suffix' => $suffix,
        'ContactNumber' => $contact,
        'Birthdate' => $birthdate,
        'Age' => $age,
        'Email' => $email,
        'Password' => $password,
        'verificationcode' => (string)$code
    ],
    'code' => (string)$code,
    'created_at' => time()
];

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'kirstenkhatemiral@gmail.com';
    $mail->Password   = 'swke nhwm gnav omfs';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('no-reply@barangaynewera.local', 'Barangay New Era');
    $mail->addAddress($email, $firstname . ' ' . $lastname);

    $mail->isHTML(true);
    $mail->Subject = 'Barangay New Era - Superadmin Verification Code';
    $mail->Body    = 'Your verification code is <strong>' . $code . '</strong>. The code expires in 10 minutes.';

    $mail->send();
} catch (Exception $e) {
    unset($_SESSION['pending_superadmin']);
    echo json_encode(['success' => false, 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Registration successful. Please check your email for verification code.']);
?>
