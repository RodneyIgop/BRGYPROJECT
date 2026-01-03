<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Database connection
require_once '../Connection/Conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Check if request exists
        $checkStmt = $conn->prepare("SELECT RequestID FROM userrequest WHERE RequestID = ?");
        $checkStmt->bind_param("s", $requestId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Request not found']);
            exit;
        }
        
        $checkStmt->close();
        
        // Delete from userrequest
        $deleteStmt = $conn->prepare("DELETE FROM userrequest WHERE RequestID = ?");
        $deleteStmt->bind_param("s", $requestId);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Request rejected successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error rejecting request: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
