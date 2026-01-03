<?php
session_start();
require_once __DIR__ . '/../Connection/conn.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Redirect to login if no active resident session
if (!isset($_SESSION['resident_id']) && !isset($_SESSION['UserID'])) {
    header('Location: residentlogin.php');
    exit();
}

// Debug: Log request method and POST data
error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);
error_log('POST data: ' . print_r($_POST, true));
error_log('Session data: ' . print_r($_SESSION, true));

// Accept only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('Not a POST request');
    header('Location: ResidentsRequestDocu.php');
    exit();
}

$user_id     = $_SESSION['resident_id'] ?? $_SESSION['UserID'];

// Extract numeric part if user_id contains "UID-" prefix
if (strpos($user_id, 'UID-') === 0) {
    $user_id = substr($user_id, 4); // Remove "UID-" prefix
}

// Debug: Log all session data
error_log('All session data: ' . print_r($_SESSION, true));

// Check if we have session data with user info
$session_resident_name = $_SESSION['resident_name'] ?? '';
$session_firstname = $_SESSION['FirstName'] ?? '';
$session_lastname = $_SESSION['LastName'] ?? '';

error_log('Session resident_name: ' . $session_resident_name);
error_log('Session firstname: ' . $session_firstname);
error_log('Session lastname: ' . $session_lastname);

// If session has user info, use it directly
if ($session_resident_name) {
    $fullname = $session_resident_name;
    error_log('Using session resident_name: ' . $fullname);
} elseif ($session_firstname || $session_lastname) {
    $fullname = trim($session_firstname . ' ' . $session_lastname);
    error_log('Using session first/last name: ' . $fullname);
} else {
    // Fallback: try to find user in database or create basic record
    error_log('No session user data, trying database lookup for user ID: ' . $user_id);
}
$requestType = sanitize($_POST['requestType'] ?? '');
$purpose     = sanitize($_POST['purpose'] ?? '');
$notes       = sanitize($_POST['notes'] ?? ''); // currently unused but stored for possible future use

// Debug: Log extracted values
error_log('User ID: ' . $user_id);
error_log('Request Type: ' . $requestType);
error_log('Purpose: ' . $purpose);
error_log('Notes: ' . $notes);

// Basic validation
if ($requestType === '' || $purpose === '') {
    $_SESSION['request_error'] = 'Please fill in all required fields.';
    header('Location: ResidentsRequestDocu.php');
    exit();
}

try {
    // Enable error reporting for debugging
    ini_set('display_errors',1);
    ini_set('display_startup_errors',1);
    error_reporting(E_ALL);
    
    // Check if pending_requests table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'pending_requests'");
    if ($table_check->num_rows == 0) {
        throw new Exception('pending_requests table does not exist. Please create the table first.');
    }
    
    // Debug: Check what users exist in the table
    $debug_result = $conn->query("SELECT UserID, FirstName, LastName FROM usertbl LIMIT 5");
    $debug_users = [];
    while ($row = $debug_result->fetch_assoc()) {
        $debug_users[] = $row;
    }
    error_log('Available users: ' . print_r($debug_users, true));
    
    // Always use session name if available, no database fallback
    if ($session_resident_name) {
        $fullname = $session_resident_name;
        error_log('Using session resident_name for database insert: ' . $fullname);
    } else {
        throw new Exception('No session resident_name available for user ID: ' . $user_id);
    }

    // Insert into pending_requests table
    $sql = "INSERT INTO pending_requests (user_id, fullname, document_type, purpose, notes, date_requested, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Insert prepare failed: ' . $conn->error);
    }
    $nowSql = date('Y-m-d H:i:s');
    $stmt->bind_param('ssssss', $user_id, $fullname, $requestType, $purpose, $notes, $nowSql);
    
    if (!$stmt->execute()) {
        throw new Exception('Insert execute failed: ' . $stmt->error);
    }
    
    $inserted_id = $conn->insert_id;
    $stmt->close();

    $_SESSION['request_success'] = 'Your document request has been submitted (ID: ' . $inserted_id . ').';
    header('Location: residentstrackrequest.php');
    exit();
} catch (Exception $e) {
    error_log('Document request error: ' . $e->getMessage());
    $_SESSION['request_error'] = 'Error: ' . $e->getMessage();
    header('Location: ResidentsRequestDocu.php');
    exit();
}
?>
