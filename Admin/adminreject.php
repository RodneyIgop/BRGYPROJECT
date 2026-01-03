<?php
session_start();
// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}

require_once __DIR__ . '/../Connection/conn.php';
require_once __DIR__ . '/../Connection/log_activity.php';

// Accept only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: adminpending.php');
    exit;
}

$reason = sanitize($_POST['reason'] ?? '');
if ($reason === '') {
    header('Location: adminpending.php');
    exit;
}

// Identify source table based on provided id field
$pendingId   = isset($_POST['pendingId']) && is_numeric($_POST['pendingId']) ? (int)$_POST['pendingId'] : null;
$requestId   = isset($_POST['RequestID'])  && is_numeric($_POST['RequestID'])  ? (int)$_POST['RequestID']  : null;

if ($pendingId === null && $requestId === null) {
    header('Location: adminpending.php');
    exit;
}

try {
    if ($pendingId !== null) {
        // Fetch record from pending_requests
        $stmt = $conn->prepare('SELECT * FROM pending_requests WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $pendingId);
    } else {
        // Fetch record from approved
        $stmt = $conn->prepare('SELECT * FROM approved WHERE RequestID = ? LIMIT 1');
        $stmt->bind_param('i', $requestId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Request not found.');
    }
    $row = $result->fetch_assoc();
    $stmt->close();

    // Map column names to archive table schema
    $fullname      = $row['fullname'];
    $dateRequested = isset($row['date_requested']) ? $row['date_requested'] : $row['dateRequested'];
    $documentType  = isset($row['document_type']) ? $row['document_type'] : (isset($row['documenttype']) ? $row['documenttype'] : $row['document']);
    $status        = 'declined';

    // Insert into archive table
    $ins = $conn->prepare('INSERT INTO archivetbl (fullname, daterequested, documenttype, status, reason) VALUES (?,?,?,?,?)');
    $ins->bind_param('sssss', $fullname, $dateRequested, $documentType, $status, $reason);
    $ins->execute();
    $ins->close();

    // Log the rejection activity
    $adminId = $_SESSION['admin_id'];
    $adminName = $_SESSION['admin_name'];
    $description = "Rejected document request for $fullname - Document: $documentType - Reason: $reason";
    logActivity($conn, $adminId, $adminName, 'Admin', ACTION_REJECT_REQUEST, $description, 'adminpending.php', 'Successful');

    // Delete from source table
    if ($pendingId !== null) {
        $del = $conn->prepare('DELETE FROM pending_requests WHERE id = ?');
        $del->bind_param('i', $pendingId);
    } else {
        $del = $conn->prepare('DELETE FROM approved WHERE RequestID = ?');
        $del->bind_param('i', $requestId);
    }
    $del->execute();
    $del->close();

} catch (Exception $e) {
    error_log('Reject error: '.$e->getMessage());
}

// Redirect back appropriately
if ($pendingId !== null) {
    header('Location: adminpending.php');
} else {
    header('Location: adminapproved.php');
}
exit;
?>
