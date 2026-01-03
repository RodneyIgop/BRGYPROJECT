<?php
session_start();
require_once '../Connection/conn.php';
require_once '../Connection/PHPMailer/src/Exception.php';
require_once '../Connection/PHPMailer/src/PHPMailer.php';
require_once '../Connection/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['pending_superadmin'])) {
    header('Location: superadminregister.php');
    exit;
}

$pending = $_SESSION['pending_superadmin'];

$expiresSeconds = 600;
if (!empty($pending['created_at']) && (time() - (int)$pending['created_at']) > $expiresSeconds) {
    unset($_SESSION['pending_superadmin']);
    die('Verification code expired. Please register again.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = trim($_POST['verificationcode'] ?? '');

    if ($submitted_code !== (string)$pending['code']) {
        die('Invalid verification code.');
    }

    $maxAttempts = 10;
    $employeeId = '';
    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $candidate = 'SPA' . str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare('SELECT id FROM superadmin WHERE employeeID = ?');
        $stmt->bind_param('s', $candidate);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if (!$exists) {
            $employeeId = $candidate;
            break;
        }
    }

    if ($employeeId === '') {
        die('Could not generate employee ID. Please try again.');
    }
    $_SESSION['pending_superadmin']['employee_id'] = $employeeId;
    $_SESSION['pending_superadmin']['employee_created_at'] = time();

    $email = $pending['data']['Email'];
    $firstname = $pending['data']['FirstName'];
    $lastname = $pending['data']['LastName'];

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
        $mail->Subject = 'Barangay New Era - Superadmin Employee ID';
        $mail->Body    = 'Your superadmin employee ID is <strong>' . htmlspecialchars($employeeId) . '</strong>.';

        $mail->send();
    } catch (Exception $e) {
        unset($_SESSION['pending_superadmin']);
        die('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
    }

    header('Location: superadminEmployeeId.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Superadmin Register</title>
    <link rel="stylesheet" href="verifySuperAdminEmail.css">
</head>

<body>

    <nav class="navbar">
        <div class="nav-items">
            <img src="../images/brgylogo.png" alt="Logo" />
            <h1 class="title" style="color: white">BARANGAY NEW ERA </h1>
        </div>
        
    </nav>

    <main>
        <div class="card">
            <h2>Verify Your Email</h2>

            <p class="info-text">
                A verification code has been sent to <strong><?php echo htmlspecialchars($pending['data']['Email']); ?></strong>
            </p>

            <form method="POST">
                <label for="code">Verification Code</label>
                <input class="verify-label" type="text" id="code" name="verificationcode" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required>

                <button type="submit" class="btn-primary">Verify</button>
            </form>

            <p class="back-link">
                Didn't register yet? <a href="superadminregister.php">Go Back</a>
            </p>
        </div>
    </main>

</body>
</html>
