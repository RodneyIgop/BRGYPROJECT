<?php
session_start();
require_once '../Connection/conn.php';

// sanitize inputs
$userID = isset($_POST['userID']) ? trim($_POST['userID']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if($userID === '' || $password === ''){
    header('Location: residentlogin.php?error=empty');
    exit;
}

$stmt = $conn->prepare("SELECT UID, FirstName, LastName, Password, status FROM usertbl WHERE UID = ? LIMIT 1");
$stmt->bind_param('s', $userID);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    // Check if account is blocked
    if($row['status'] === 'blocked') {
        header('Location: residentlogin.php?error=blocked');
        exit;
    }
    
    // Passwords stored in plain text per earlier requirement
    if($password === $row['Password']){
        $_SESSION['resident_id'] = $row['UID'];
        $_SESSION['resident_name'] = $row['FirstName'].' '.$row['LastName'];
        header('Location: residentlogin.php?login_success=true');
        exit;
    }
}
// invalid credentials
header('Location: residentlogin.php?error=invalid');
exit;
