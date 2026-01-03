<?php
session_start();
require_once '../Connection/conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id = $_POST['id'] ?? '';
$type = $_POST['type'] ?? '';

if (empty($id) || empty($type)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    if ($type === 'admin') {
        $stmt = $conn->prepare("UPDATE adminrequests SET status = 'deleted' WHERE RequestID = ?");
    } elseif ($type === 'resident') {
        $stmt = $conn->prepare("UPDATE userrequest SET status = 'deleted' WHERE RequestID = ?");
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid notification type']);
        exit;
    }

    $stmt->bind_param('i', $id);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Notification deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
