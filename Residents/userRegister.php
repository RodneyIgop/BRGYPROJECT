<?php 
include '../Connection/conn.php';

// Initialize variables
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $first_name = sanitize($_POST['FirstName'] ?? '');
    $middle_name = isset($_POST['MiddleName']) && trim($_POST['MiddleName']) !== '' ? sanitize($_POST['MiddleName']) : '';
    $last_name = sanitize($_POST['LastName'] ?? '');
    $suffix = isset($_POST['Suffix']) && trim($_POST['Suffix']) !== '' ? sanitize($_POST['Suffix']) : '';
    $birthdate = date('Y-m-d', strtotime(sanitize($_POST['Birthdate'] ?? '')));
    $age = (int)($_POST['Age'] ?? 0);
    $contact_number = sanitize($_POST['ContactNumber'] ?? '');
    $address = sanitize($_POST['Address'] ?? '');
    $census_number = sanitize($_POST['CensusNumber'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = sanitize($_POST['Password'] ?? '');

    // Basic validation
    if (empty($first_name) || empty($lastname_name) || empty($birthdate) || 
        empty($contact_number) || empty($address) || 
        empty($census_number) || empty($email) || empty($_POST['Password'])) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{11}$/', $contact_number)) {
        $error = 'Please enter a valid 11-digit contact number.';
    } else {
        // Check if email already exists in usertbl
        $stmt = $conn->prepare("SELECT Userid FROM usertbl WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered as a resident account. Please use a different email.';
        } else {
            // Check if email already exists in admintbl (admin accounts)
            $stmt = $conn->prepare("SELECT AdminID FROM admintbl WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email already registered as an admin account. Please use a different email.';
            } else {
                // Check if email already exists in superadmin
                $stmt = $conn->prepare("SELECT id FROM superadmin WHERE Email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = 'Email already registered as a superadmin account. Please use a different email.';
                } else {
                    // Check if email already exists in userrequest (pending requests)
                    $stmt = $conn->prepare("SELECT RequestID FROM userrequest WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $error = 'Email already has a pending resident request. Please use a different email.';
                    }
                }
            }
        }
        
        if (empty($error)) {
            // Insert new user
            $stmt = $conn->prepare("
                INSERT INTO usertbl (
                    LastName, FirstName, MiddleName, Suffix, 
                    Birthdate, Age, ContactNumber, Address, 
                    CensusNUmber, email, Password
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param(
                "sssssssssss", 
                $last_name, $first_name, $middle_name, $suffix,
                $birthdate, $age, $contact_number, $address,
                $census_number, $email, $password
            );
            
            if ($stmt->execute()) {
                header('Location: residentregister.php?success=' . urlencode('Registration successful! You can now login.'));
                exit();
            } else {
                $error = 'Error: ' . $conn->error;
            }
        }
    }
    
    // If there was an error, redirect back with error message
    if (!empty($error)) {
        // Store form data in session to repopulate form
        $_SESSION['form_data'] = $_POST;
        header('Location: residentregister.php?error=' . urlencode($error));
        exit();
    }
} else {
    // If someone tries to access this file directly
    header('Location: residentregister.php');
    exit();
}
?>