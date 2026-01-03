<?php
session_start();
require_once '../Connection/conn.php';

// Enable exceptions for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set JSON response header
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => ''];

// Check session
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit();
}

$admin_id = (int)$_SESSION['admin_id'];

try {
    // Check request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'update_profile') {
        throw new Exception('Invalid request');
    }

    // Get form data
    $lastname = sanitize($_POST['lastname'] ?? '');
    $firstname = sanitize($_POST['firstname'] ?? '');
    $middlename = sanitize($_POST['middlename'] ?? '');
    $suffix = sanitize($_POST['suffix'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $birthdate_input = sanitize($_POST['birthdate'] ?? '');
    $contactnumber = sanitize($_POST['contactnumber'] ?? '');
    $password_raw = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($lastname) || empty($firstname) || empty($email) || empty($contactnumber)) {
        throw new Exception('Last name, first name, email, and contact number are required');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check email uniqueness
    $stmt = $conn->prepare("SELECT AdminID FROM admintbl WHERE email = ? AND AdminID != ?");
    $stmt->bind_param('si', $email, $admin_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        throw new Exception('Email is already used by another admin account');
    }
    $stmt->close();

    // Convert birthdate to YYYY-MM-DD
    $birthdate = null;
    $age = null;
    if (!empty($birthdate_input)) {
        $parts = explode('/', $birthdate_input);
        if (count($parts) === 3) {
            $month = (int)$parts[0];
            $day = (int)$parts[1];
            $year = (int)$parts[2];
            if (checkdate($month, $day, $year)) {
                $birthdate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $age = (new DateTime())->diff(new DateTime($birthdate))->y;
            }
        }
    }

    // Build update query dynamically
    $fields = ['lastname = ?', 'firstname = ?', 'middlename = ?', 'suffix = ?', 'email = ?', 'contactnumber = ?'];
    $params = [$lastname, $firstname, $middlename, $suffix, $email, $contactnumber];
    $types = 'ssssss';

    if ($birthdate) {
        $fields[] = 'birthdate = ?';
        $fields[] = 'age = ?';
        $params[] = $birthdate;
        $params[] = $age;
        $types .= 'si';
    }

    if (!empty($password_raw)) {
        $fields[] = 'password = ?';
        $hashed = password_hash($password_raw, PASSWORD_DEFAULT);
        $params[] = $hashed;
        $types .= 's';
    }

    // Add AdminID for WHERE
    $params[] = $admin_id;
    $types .= 'i';

    $sql = "UPDATE admintbl SET " . implode(', ', $fields) . " WHERE AdminID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    $response['success'] = true;
    $response['message'] = 'Profile updated successfully!';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
?>
