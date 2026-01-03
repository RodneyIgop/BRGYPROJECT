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

// Validate input
if (empty($requestId) || !is_numeric($requestId)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
    exit;
}

// Get user's fullname for security check
$user_id = $_SESSION['resident_id'] ?? $_SESSION['UserID'];
if (strpos($user_id, 'UID-') === 0) {
    $user_id = substr($user_id, 4);
}

$fullname = $_SESSION['resident_name'] ?? '';

// First, get the request details before moving to archive
$stmt = $conn->prepare('SELECT * FROM pending_requests WHERE id = ? AND fullname = ? AND status = "pending"');
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('is', $requestId, $fullname);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $request = $result->fetch_assoc();
    
    // Move to archive table
    $archiveStmt = $conn->prepare('INSERT INTO archivetbl (fullname, documenttype, daterequested, status, reason) VALUES (?, ?, ?, ?, ?)');
    if ($archiveStmt) {
        $cancelledStatus = 'cancelled';
        $cancelReason = 'Cancelled by user';
        $archiveStmt->bind_param('sssss', $request['fullname'], $request['document_type'], $request['date_requested'], $cancelledStatus, $cancelReason);
        $archiveStmt->execute();
        $archiveStmt->close();
    }
    
    // Delete from pending_requests
    $deleteStmt = $conn->prepare('DELETE FROM pending_requests WHERE id = ?');
    if ($deleteStmt) {
        $deleteStmt->bind_param('i', $requestId);
        $deleteStmt->execute();
        $deleteStmt->close();
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Request cancelled successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Request not found, already processed, or cannot be cancelled']);
}

$stmt->close();
$conn->close();
?>
