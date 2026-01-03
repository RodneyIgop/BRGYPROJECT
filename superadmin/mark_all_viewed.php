<?php
session_start();
require_once '../Connection/conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Just mark as viewed in session, don't change database status
    // This keeps notifications as pending so they stay visible
    $_SESSION['notifications_viewed'] = true;
    
    echo json_encode(['success' => true, 'message' => 'Notifications marked as viewed in session']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Session error: ' . $e->getMessage()]);
}

$conn->close();
?>
