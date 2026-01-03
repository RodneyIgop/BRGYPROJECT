<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../Connection/conn.php';

header('Content-Type: application/json');

// Validate session
if (!isset($_SESSION['resident_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['resident_id'];

// Collect and sanitize input
$last_name = trim($_POST['last_name'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$middle_name = trim($_POST['middle_name'] ?? '');
$suffix = trim($_POST['suffix'] ?? '');
$birthdate = trim($_POST['birthdate'] ?? '');
$address = trim($_POST['address'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($address) || empty($contact) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'First name, last name, address, contact, and email are required']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Validate and format birthdate
$formatted_birthdate = null;
if (!empty($birthdate)) {
    // Try to parse birthdate in various formats
    $date_formats = ['m/d/Y', 'Y-m-d', 'F j, Y'];
    foreach ($date_formats as $format) {
        $date = DateTime::createFromFormat($format, $birthdate);
        if ($date !== false) {
            $formatted_birthdate = $date->format('Y-m-d');
            break;
        }
    }
    
    if ($formatted_birthdate === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid birthdate format']);
        exit();
    }
}

try {
    // Update user profile directly
    $stmt = $conn->prepare("UPDATE usertbl SET LastName = ?, FirstName = ?, MiddleName = ?, Suffix = ?, birthdate = ?, Address = ?, ContactNumber = ?, email = ? WHERE UID = ?");
    if (!$stmt) throw new Exception($conn->error);

    $stmt->bind_param('sssssssss', $last_name, $first_name, $middle_name, $suffix, $formatted_birthdate, $address, $contact, $email, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();

    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully!'
    ]);
    
} catch (Exception $e) {
    error_log('Update profile error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
