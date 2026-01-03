<?php
session_start();
// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}

require_once __DIR__ . '/../Connection/conn.php';
require_once __DIR__ . '/../Connection/log_activity.php';

// Validate incoming request id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: adminpending.php');
    exit;
}

$pendingId = (int) $_GET['id'];

// Fetch the pending request details
$stmt = $conn->prepare('SELECT * FROM pending_requests WHERE id = ? LIMIT 1');
if (!$stmt) {
    die('Database error.');
}
$stmt->bind_param('i', $pendingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Prepare data for insertion into approved table
    $fullname       = $row['fullname'];
    $documenttype   = $row['document_type'];
    $purpose        = $row['purpose'];
    $dateRequested  = $row['date_requested'];
    $status         = 'approved'; // initial status once approved by admin
    $action         = 'For Pick Up';

    // Insert into approved table
    $ins = $conn->prepare('INSERT INTO approved (fullname, documenttype, purpose, dateRequested, status, action) VALUES (?,?,?,?,?,?)');
    if ($ins) {
        $ins->bind_param('ssssss', $fullname, $documenttype, $purpose, $dateRequested, $status, $action);
        $ins->execute();
        $ins->close();
        
        // Log the approval activity
        $adminId = $_SESSION['admin_id'];
        $adminName = $_SESSION['admin_name'];
        $description = "Approved document request for $fullname - Document: $documenttype";
        logActivity($conn, $adminId, $adminName, 'Admin', ACTION_APPROVE_REQUEST, $description, 'adminpending.php', 'Successful');
        
        // Optionally delete the record from pending_requests table
        $del = $conn->prepare('DELETE FROM pending_requests WHERE id = ?');
        if ($del) {
            $del->bind_param('i', $pendingId);
            $del->execute();
            $del->close();
        }
    }
}
$stmt->close();

header('Location: adminapproved.php');
exit;
?>
