<?php
session_start();
require_once '../Connection/conn.php';

header('Content-Type: application/json');

// Check if password reset session exists
if (!isset($_SESSION['password_reset'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please start over.']);
    exit;
}

$reset_session = $_SESSION['password_reset'];
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$verification_code = isset($_POST['verification_code']) ? trim($_POST['verification_code']) : '';

// Validate inputs
if (empty($email) || empty($token) || empty($verification_code)) {
    echo json_encode(['success' => false, 'message' => 'Missing required information']);
    exit;
}

// Verify session matches request
if ($reset_session['email'] !== $email || $reset_session['token'] !== $token) {
    echo json_encode(['success' => false, 'message' => 'Invalid session. Please start over.']);
    exit;
}

// Verify the reset request and code
$stmt = $conn->prepare('SELECT id, expires_at FROM password_reset_requests WHERE email = ? AND reset_token = ? AND verification_code = ? AND is_used = 0');
$stmt->bind_param('sss', $email, $token, $verification_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
    exit;
}

$reset_request = $result->fetch_assoc();
$stmt->close();

// Check if token has expired
if (strtotime($reset_request['expires_at']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Verification code has expired']);
    exit;
}

// Mark the reset request as used
$stmt = $conn->prepare('UPDATE password_reset_requests SET is_used = 1 WHERE id = ?');
$stmt->bind_param('i', $reset_request['id']);
$stmt->execute();
$stmt->close();

// Update session to indicate verification is complete
$_SESSION['password_reset']['verified'] = true;
$_SESSION['password_reset']['verified_at'] = date('Y-m-d H:i:s');

echo json_encode(['success' => true, 'message' => 'Verification successful']);
?>
