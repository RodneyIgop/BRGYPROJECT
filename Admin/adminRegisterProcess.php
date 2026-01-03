<?php
session_start();
require_once '../Connection/conn.php';
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Validate required POST fields
$required = ['lastname','firstname','contact','birthdate','age','email','password','confirm_password'];
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
// Expecting mm/dd/yyyy from the datepicker; convert to Y-m-d
$dt = DateTime::createFromFormat('m/d/Y', $birthdateRaw);
$birthdate = $dt ? $dt->format('Y-m-d') : date('Y-m-d', strtotime($birthdateRaw));
$age       = (int)$_POST['age'];
$email     = trim($_POST['email']);
$password  = $_POST['password'];
$confirm   = $_POST['confirm_password'];

if ($password !== $confirm) {
    die('Passwords do not match');
}
// Password strength checked on client but double-check server-side
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};:\\|,.<>\/?]).{8,25}$/', $password)) {
    die('Password does not meet complexity requirements.');
}

// Check if email already exists in admintbl
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

// Check if email already exists in superadmin
$stmt = $conn->prepare('SELECT id FROM superadmin WHERE Email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die('Email already registered as a superadmin account');
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

// Generate verification code
$code = random_int(100000, 999999);

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
    $mail->Body    = 'Your verification code is <strong>' . $code . '</strong>. The code expires in 10 minutes.';

    $mail->send();
} catch (Exception $e) {
    unset($_SESSION['pending_admin']);
    die('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
}

header('Location: adminVerify.php');
exit;
