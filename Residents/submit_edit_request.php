<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../Connection/conn.php';

header('Content-Type: application/json');

// Validate session
if (!isset($_SESSION['resident_id']) && !isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}
$user_id = $_SESSION['resident_id'] ?? $_SESSION['UserID'];

// Collect and sanitize input
$address = trim($_POST['address'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$email   = trim($_POST['email'] ?? '');

if ($address === '' || $contact === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

try {
    // Insert request into table (admin will review)
    $stmt = $conn->prepare("INSERT INTO profile_edit_requests (UserId, address, contact, email, status, requested_at) VALUES (?,?,?,?, 'pending', NOW())");
    if (!$stmt) throw new Exception($conn->error);

    $stmt->bind_param('isss', $user_id, $address, $contact, $email);
    if (!$stmt->execute()) throw new Exception($stmt->error);
    $stmt->close();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Submit edit request error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
