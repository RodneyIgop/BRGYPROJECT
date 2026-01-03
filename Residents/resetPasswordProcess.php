<?php
session_start();
require_once '../Connection/conn.php';

header('Content-Type: application/json');

// Check if password reset session exists and is verified
if (!isset($_SESSION['password_reset']) || !$_SESSION['password_reset']['verified']) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please start over.']);
    exit;
}

// Check if verification was done within the last 30 minutes
$verified_at = $_SESSION['password_reset']['verified_at'];
if (strtotime($verified_at) < (time() - 1800)) { // 30 minutes
    unset($_SESSION['password_reset']);
    echo json_encode(['success' => false, 'message' => 'Session expired. Please start over.']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

// Validate inputs
if (empty($email) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Missing required information']);
    exit;
}

// Verify session matches request
if ($_SESSION['password_reset']['email'] !== $email) {
    echo json_encode(['success' => false, 'message' => 'Invalid session. Please start over.']);
    exit;
}

// Password strength validation (same as registration)
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};:\\|,.<>\/?]).{8,10}$/', $new_password)) {
    echo json_encode(['success' => false, 'message' => 'Password does not meet security requirements']);
    exit;
}

// Check if new password is the same as current password
$stmt = $conn->prepare('SELECT Password FROM usertbl WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row && password_verify($new_password, $row['Password'])) {
    echo json_encode(['success' => false, 'message' => 'New password cannot be the same as your current password']);
    exit;
}

// Update password in database
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('UPDATE usertbl SET Password = ? WHERE email = ?');
$stmt->bind_param('ss', $hashed_password, $email);

if ($stmt->execute()) {
    $stmt->close();
    
    // Clear the password reset session
    unset($_SESSION['password_reset']);
    
    echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
} else {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again.']);
}
?>
