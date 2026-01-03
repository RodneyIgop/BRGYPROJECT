<?php
session_start();
require_once '../Connection/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['superadmin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Mark all resident requests as read
    $resident_stmt = $conn->prepare("UPDATE userrequest SET status = 'read' WHERE status = 'pending'");
    if ($resident_stmt) {
        $resident_stmt->execute();
    }
    
    // Mark all admin requests as read
    $admin_stmt = $conn->prepare("UPDATE adminrequests SET status = 'read' WHERE status = 'pending'");
    if ($admin_stmt) {
        $admin_stmt->execute();
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error clearing all notifications: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
