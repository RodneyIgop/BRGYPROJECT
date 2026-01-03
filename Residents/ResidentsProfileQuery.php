<?php
session_start();
include '../Connection/conn.php';

// Debug logging
error_log("Session Data: " . print_r($_SESSION, true));
error_log("User ID from session: " . ($_SESSION['resident_id'] ?? 'Not set'));

// Check if user is logged in
if (!isset($_SESSION['resident_id'])) {
    error_log("User not logged in, redirecting to login page");
    header('Location: residentlogin.php');
    exit();
}

$user_id = $_SESSION['resident_id'];
$user = [];

// Fetch user data from database
$stmt = $conn->prepare("SELECT * FROM usertbl WHERE UID = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    die("Database error. Please try again later.");
}

$stmt->bind_param('s', $user_id);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    die("Error fetching user data. Please try again.");
}

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    error_log("No user found with ID: " . $user_id);
    header('Location: ResidentsIndex.php');
    exit();
}

$user = $result->fetch_assoc();
error_log("User data from database: " . print_r($user, true));

// Calculate age from birthdate if it exists
$age = !empty($user['Age']) ? intval($user['Age']) : 'N/A';
if (!empty($user['birthdate'])) {
    $birthDate = new DateTime($user['birthdate']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
}

// Format full name
$nameParts = [];
if (!empty($user['LastName'])) {
    $lastName = $user['LastName'];
    if (!empty($user['Suffix'])) {
        $lastName .= ' ' . $user['Suffix'];
    }
    $nameParts[] = $lastName;
}

if (!empty($user['FirstName'])) {
    $nameParts[] = $user['FirstName'];
}

if (!empty($user['MiddleName'])) {
    $nameParts[] = $user['MiddleName'];
}

$formattedName = htmlspecialchars(implode(' ', $nameParts));

// Format birthdate
$formattedBirthdate = !empty($user['birthdate']) && $user['birthdate'] !== '0000-00-00' ? date('F j, Y', strtotime($user['birthdate'])) : 'N/A';
$editBirthdate = !empty($user['birthdate']) && $user['birthdate'] !== '0000-00-00' ? date('m/d/Y', strtotime($user['birthdate'])) : '';

// Escape other user data
$userData = [
    'user' => $user,  // Include the full user data including profile_picture
    'formattedName' => $formattedName,
    'formattedBirthdate' => $formattedBirthdate,
    'editBirthdate' => $editBirthdate,
    'age' => $age,
    'address' => htmlspecialchars($user['Address'] ?? ''),
    'censusNumber' => htmlspecialchars($user['CensusNumber'] ?? ''),
    'contactNumber' => htmlspecialchars($user['ContactNumber'] ?? ''),
    'email' => htmlspecialchars($user['email'] ?? '')
];

// Make user data available for the included file
extract($userData);
?>