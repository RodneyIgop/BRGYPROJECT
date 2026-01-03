<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

require_once '../Connection/conn.php';
require_once '../Connection/log_activity.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $residentUID = $_POST['resident_uid'] ?? '';
    $action = $_POST['action'] ?? ''; // 'block' or 'unblock'

    if (empty($residentUID) || !in_array($action, ['block', 'unblock'])) {
        throw new Exception('Invalid parameters');
    }

    // Get resident info before updating
    $stmt = $conn->prepare("SELECT UID, FirstName, LastName FROM usertbl WHERE UID = ?");
    $stmt->bind_param('s', $residentUID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Resident not found');
    }
    
    $resident = $result->fetch_assoc();
    $residentName = $resident['FirstName'] . ' ' . $resident['LastName'];
    $stmt->close();

    // Update resident status
    $newStatus = $action === 'block' ? 'blocked' : 'active';
    $stmt = $conn->prepare("UPDATE usertbl SET status = ? WHERE UID = ?");
    $stmt->bind_param('ss', $newStatus, $residentUID);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update resident status');
    }
    $stmt->close();

    // Log the activity
    $superadminId = $_SESSION['superadmin_id'];
    $superadminName = $_SESSION['superadmin_name'];
    $actionText = $action === 'block' ? ACTION_BLOCK_USER : ACTION_UNBLOCK_USER;
    $description = ucfirst($action) . " resident account - Resident: $residentName (UID: $residentUID)";
    $page = 'residentsaccounts.php';
    
    logActivity($conn, $superadminId, $superadminName, 'Superadmin', $actionText, $description, $page, 'Successful');

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "Resident account has been " . ($action === 'block' ? 'blocked' : 'unblocked') . " successfully.",
        'new_status' => $newStatus
    ]);

} catch (Exception $e) {
    // Log error if needed
    error_log("Block/unblock error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
