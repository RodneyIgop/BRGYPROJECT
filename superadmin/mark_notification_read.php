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

$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Missing notification ID']);
    exit;
}

try {
    // Update notification status to read in notifications table
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error marking notification as read: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
