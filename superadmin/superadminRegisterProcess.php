<?php
session_start();
require_once '../Connection/conn.php';
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$required = ['lastname','firstname','contact','birthdate','email','password','confirm_password'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die('Missing field: ' . htmlspecialchars($field));
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
    die('Invalid birthdate');
}
$today = new DateTime('today');
if ($birthdateObj > $today) {
    die('Birthdate cannot be in the future');
}
$age = $birthdateObj->diff($today)->y;
$email     = trim($_POST['email']);
$password  = $_POST['password'];
$confirm   = $_POST['confirm_password'];

if ($password !== $confirm) {
    die('Passwords do not match');
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};:\\|,.<>\/?]).{8,25}$/', $password)) {
    die('Password does not meet complexity requirements.');
}

// Check current number of superadmin accounts
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM superadmin');
$stmt->execute();
$result = $stmt->get_result();
$current_count = $result->fetch_assoc()['count'];
$stmt->close();

if ($current_count >= 2) {
    die('Maximum number of superadmin accounts (2) has been reached. No new superadmin accounts can be created at this time.');
}

$stmt = $conn->prepare('SELECT id FROM superadmin WHERE Email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die('Email already registered as a superadmin account');
}
$stmt->close();

// Check if email already exists in admintbl (admin accounts)
$stmt = $conn->prepare('SELECT AdminID FROM admintbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die('Email already registered as an admin account');
}
$stmt->close();

// Check if email already exists in usertbl (resident accounts)
$stmt = $conn->prepare('SELECT UserID FROM usertbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die('Email already registered as a resident account');
}
$stmt->close();

// Check if email already exists in userrequest (pending resident requests)
$stmt = $conn->prepare('SELECT RequestID FROM userrequest WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die('Email already has a pending resident request');
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
    die('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
}

header('Location: superadminVerify.php');
exit;
