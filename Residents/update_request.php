<?php
session_start();
require_once __DIR__ . '/../Connection/conn.php';

// Check if user is logged in
if (!isset($_SESSION['resident_id']) && !isset($_SESSION['UserID'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$requestId = $_POST['requestId'] ?? '';
$documentType = $_POST['documentType'] ?? '';
$purpose = $_POST['purpose'] ?? '';
$notes = $_POST['notes'] ?? '';

// Validate inputs
if (empty($requestId) || empty($documentType) || empty($purpose)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Get user's fullname for security check
$user_id = $_SESSION['resident_id'] ?? $_SESSION['UserID'];
if (strpos($user_id, 'UID-') === 0) {
    $user_id = substr($user_id, 4);
}

$fullname = $_SESSION['resident_name'] ?? '';

// Update the request
$stmt = $conn->prepare('UPDATE pending_requests SET document_type = ?, purpose = ?, notes = ?, updated_at = NOW() WHERE id = ? AND fullname = ?');
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('sssss', $documentType, $purpose, $notes, $requestId, $fullname);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Request updated successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Request not found or no changes made']);
}

$stmt->close();
$conn->close();
?>
