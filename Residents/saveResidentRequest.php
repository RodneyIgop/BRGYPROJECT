<?php
session_start();
require_once '../Connection/conn.php';
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// -------------------- 1. Validate POST fields --------------------
$required = ['FirstName','LastName','MiddleName','ContactNumber','Birthdate','Age','email','Password','Address','CensusNumber'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die('Missing field: ' . htmlspecialchars($field));
    }
}

// -------------------- 2. Assign variables --------------------
$FirstName   = trim($_POST['FirstName']);
$LastName    = trim($_POST['LastName']);
$MiddleName  = trim($_POST['MiddleName']);
$Suffix      = trim($_POST['Suffix'] ?? '');
$ContactNumber = trim($_POST['ContactNumber']);
$BirthdateRaw  = trim($_POST['Birthdate']);
$Age           = (int)$_POST['Age'];
$Address       = trim($_POST['Address']);
$CensusNumber  = trim($_POST['CensusNumber']);
$email         = trim($_POST['email']);
$Password      = $_POST['Password'];

// -------------------- 3. Birthdate formatting --------------------
$dt = DateTime::createFromFormat('m/d/Y', $BirthdateRaw);
if (!$dt) {
    die('Invalid birthdate format.');
}
$Birthdate = $dt->format('Y-m-d');

// -------------------- 4. Password validation --------------------
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};:\\|,.<>\/?]).{8,10}$/', $Password)) {
    die('Password must be 8-10 characters, include uppercase, lowercase, number, and special character.');
}

// -------------------- 5. Check for existing email --------------------
$stmt = $conn->prepare('SELECT UserID FROM usertbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die('Email already registered as a resident account.');
}
$stmt->close();

$stmt = $conn->prepare('SELECT RequestID FROM userrequest WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die('Email already has a pending resident request.');
}
$stmt->close();

// -------------------- 6. Generate verification code --------------------
$verificationCode = random_int(100000, 999999);

// -------------------- 7. Store pending resident in SESSION --------------------
$_SESSION['pending_resident'] = [
    'data' => [
        'FirstName' => $FirstName,
        'LastName' => $LastName,
        'MiddleName' => $MiddleName,
        'Suffix' => $Suffix,
        'ContactNumber' => $ContactNumber,
        'Birthdate' => $Birthdate,
        'Age' => $Age,
        'Address' => $Address,
        'CensusNumber' => $CensusNumber,
        'email' => $email,
        'Password' => password_hash($Password, PASSWORD_DEFAULT), // hashed password
    ],
    'code' => $verificationCode
];

// -------------------- 8. Send email via PHPMailer --------------------
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'kirstenkhatemiral@gmail.com'; // your Gmail
    $mail->Password   = 'your_app_password_here';      // use Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('no-reply@barangaynewera.local', 'Barangay New Era');
    $mail->addAddress($email, $FirstName . ' ' . $LastName);

    $mail->isHTML(true);
    $mail->Subject = 'Barangay New Era - Resident Verification Code';
    $mail->Body    = 'Your verification code is <strong>' . $verificationCode . '</strong>. The code expires in 10 minutes.<br><br><strong style="color: #dc3545;">⚠️ Don\'t share this code to anyone.</strong><br><br>If you didn\'t request this code, please ignore this email.';

    $mail->send();
} catch (Exception $e) {
    unset($_SESSION['pending_resident']);
    die('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
}

// -------------------- 9. Redirect to verification page --------------------
header('Location: residentVerify.php');
exit;
?>
