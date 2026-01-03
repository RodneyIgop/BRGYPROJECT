<?php
session_start();
require_once '../Connection/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['superadmin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Superadmin not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'update_profile') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$superadmin_id = (int)$_SESSION['superadmin_id'];
$response = ['success' => false, 'message' => ''];

try {
    // Get form data
    $lastname = sanitize($_POST['lastname'] ?? '');
    $firstname = sanitize($_POST['firstname'] ?? '');
    $middlename = sanitize($_POST['middlename'] ?? '');
    $suffix = sanitize($_POST['suffix'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $birthdate_input = sanitize($_POST['birthdate'] ?? '');
    
    // Convert birthdate from mm/dd/yyyy to YYYY-MM-DD for database
    $birthdate = null;
    $age = null;
    if (!empty($birthdate_input)) {
        $date_parts = explode('/', $birthdate_input);
        if (count($date_parts) === 3) {
            $month = str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
            $day = str_pad($date_parts[1], 2, '0', STR_PAD_LEFT);
            $year = $date_parts[2];
            $birthdate = "$year-$month-$day";
            
            // Calculate age from birthdate
            $birthDate = new DateTime($birthdate);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
        }
    }
    
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($lastname) || empty($firstname) || empty($email)) {
        throw new Exception('Last name, first name, and email are required');
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Build update query
    $update_fields = "LastName = ?, FirstName = ?, MiddleName = ?, Suffix = ?, Email = ?, birthdate = ?, age = ?";
    $params = [$lastname, $firstname, $middlename, $suffix, $email, $birthdate, $age];
    $types = "ssssssi";

    // Add password if provided
    if (!empty($password)) {
        $update_fields .= ", Password = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
        $types .= "s";
    }

    $params[] = $superadmin_id;
    $types .= "i";

    $sql = "UPDATE superadmin SET $update_fields WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception('Database update failed: ' . $stmt->error);
    }

    $stmt->close();

    // Update session data
    $_SESSION['firstname'] = $firstname;
    $_SESSION['lastname'] = $lastname;
    $_SESSION['email'] = $email;

    $response = [
        'success' => true,
        'message' => 'Profile updated successfully!'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
