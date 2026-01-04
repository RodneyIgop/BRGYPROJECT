<?php
session_start();
require_once '../Connection/conn.php';

if (!isset($_SESSION['pending_superadmin'])) {
    header('Location: superadminregister.php');
    exit;
}

$pending = $_SESSION['pending_superadmin'];

// Check for session expiration (10 minutes after employee ID creation)
$expiresSeconds = 600;
if (!empty($pending['employee_created_at']) && (time() - (int)$pending['employee_created_at']) > $expiresSeconds) {
    unset($_SESSION['pending_superadmin']);
    $error_message = 'Your session has expired. Please restart the registration process.';
}

if (empty($pending['employee_id'])) {
    header('Location: superadminVerify.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = trim($_POST['employee_id'] ?? '');
    $expected = (string)$pending['employee_id'];

    if ($submitted !== $expected) {
        $error_message = 'The employee ID you entered is incorrect. Please check your email and try again.';
    } else {

    $data = $pending['data'];

    // Check current number of superadmin accounts before final insertion
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM superadmin');
    $stmt->execute();
    $result = $stmt->get_result();
    $current_count = $result->fetch_assoc()['count'];
    $stmt->close();

    if ($current_count >= 2) {
        unset($_SESSION['pending_superadmin']);
        $error_message = 'The maximum number of superadmin accounts has been reached. No new accounts can be created at this time.';
    } else {

    $stmt = $conn->prepare('SELECT id FROM superadmin WHERE Email = ?');
    $stmt->bind_param('s', $data['Email']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        unset($_SESSION['pending_superadmin']);
        $error_message = 'This email is already registered as a superadmin account. Please use a different email or contact support.';
    } else {
    $stmt->close();

    $stmt = $conn->prepare('SELECT id FROM superadmin WHERE employeeID = ?');
    $stmt->bind_param('s', $expected);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        unset($_SESSION['pending_superadmin']);
        $error_message = 'This employee ID is already registered as a superadmin account. Please contact support if you believe this is an error.';
    } else {
    $stmt->close();

    $stmt = $conn->prepare('INSERT INTO superadmin (employeeID, LastName, FirstName, MiddleName, Suffix, Email, Password, verificationcode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param(
        'ssssssss',
        $expected,
        $data['LastName'],
        $data['FirstName'],
        $data['MiddleName'],
        $data['Suffix'],
        $data['Email'],
        $data['Password'],
        $data['verificationcode']
    );

    if ($stmt->execute()) {
        unset($_SESSION['pending_superadmin']);
        header('Location: superadminlogin.php?registered=success');
        exit;
    } else {
        $error_message = 'A database error occurred while processing your registration. Please try again later.';
    }
    }
    }
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Employee ID - Superadmin Register</title>

    <!-- Use unified theme design -->
    <link rel="stylesheet" href="./superadminEmployeeId.css">
</head>
<style>
    input {
    width: 94%;
    padding: 11px 12px;
    font-size: 1rem;
    border: 1px solid #bbb;
    border-radius: 6px;
    margin-bottom: 18px;
}
.error-message {
    background-color: #fee;
    border: 1px solid #fcc;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 20px;
    color: #c33;
    font-size: 14px;
    text-align: center;
}
</style>
<body>

    <nav class="navbar">
        <div class="nav-items">
            <img src="../images/brgylogo.png" alt="Logo" />
            <h1 class="title" style="color: white">BARANGAY NEW ERA </h1>
        </div>
        <div class="nav-links">
            
        </div>
    </nav>

    <main>
        <div class="card">

            <h2>Enter Employee ID</h2>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <p class="info-text">
                Your employee ID was sent to 
                <strong><?php echo htmlspecialchars($pending['data']['Email']); ?></strong>
            </p>

            <form method="POST">
                <label for="employee_id">Employee ID</label>
                <input type="text" id="employee_id" name="employee_id" maxlength="8" pattern="SPA[0-9]{5}" placeholder="SPA00000" required>

                <button type="submit" class="btn-primary">Submit</button>
            </form>

            <p class="back-link">
                Wrong page? <a href="superadminVerify.php">Go Back</a>
            </p>

        </div>
    </main>

</body>
</html>
