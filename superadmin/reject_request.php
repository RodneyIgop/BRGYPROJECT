<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Database connection
require_once '../Connection/Conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];
    $rejectedBy = $_SESSION['superadmin_name'] ?? 'Super Admin';
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Check if request exists in adminrequests table first
        $checkStmt = $conn->prepare("SELECT RequestID, lastname, firstname, middlename, suffix, birthdate, age, email, contactnumber, requestDate FROM adminrequests WHERE RequestID = ?");
        $checkStmt->bind_param("s", $requestId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            // This is an admin request
            $requestDetails = $result->fetch_assoc();
            $checkStmt->close();
            
            // Insert into rejected_admin_requests table
            $insertStmt = $conn->prepare("INSERT INTO rejected_admin_requests (RequestID, lastname, firstname, middlename, suffix, birthdate, age, email, contactnumber, requestDate, rejectionDate, rejected_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
            $insertStmt->bind_param("sssssisssss", 
                $requestDetails['RequestID'],
                $requestDetails['lastname'],
                $requestDetails['firstname'],
                $requestDetails['middlename'],
                $requestDetails['suffix'],
                $requestDetails['birthdate'],
                $requestDetails['age'],
                $requestDetails['email'],
                $requestDetails['contactnumber'],
                $requestDetails['requestDate'],
                $rejectedBy
            );
            $insertStmt->execute();
            $insertStmt->close();
            
            
            // Delete from adminrequests
            $deleteStmt = $conn->prepare("DELETE FROM adminrequests WHERE RequestID = ?");
            $deleteStmt->bind_param("s", $requestId);
            $deleteStmt->execute();
            $deleteStmt->close();
            
        } else {
            // Check if it's a resident request
            $checkStmt->close();
            $residentCheckStmt = $conn->prepare("SELECT RequestID, LastName, FirstName, MiddleName, Suffix, birthdate, Age, email, ContactNumber, address, CensusNumber, dateRequested FROM userrequest WHERE RequestID = ?");
            $residentCheckStmt->bind_param("s", $requestId);
            $residentCheckStmt->execute();
            $residentResult = $residentCheckStmt->get_result();
            
            if ($residentResult->num_rows > 0) {
                // This is a resident request
                $residentDetails = $residentResult->fetch_assoc();
                $residentCheckStmt->close();
                
                // Insert into rejected_resident_requests table
                $insertStmt = $conn->prepare("INSERT INTO rejected_resident_requests (RequestID, lastname, firstname, middlename, suffix, birthdate, age, email, contactnumber, requestDate, rejectionDate, rejected_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
                $insertStmt->bind_param("sssssisssss", 
                    $residentDetails['RequestID'],
                    $residentDetails['LastName'],
                    $residentDetails['FirstName'],
                    $residentDetails['MiddleName'],
                    $residentDetails['Suffix'],
                    $residentDetails['birthdate'],
                    $residentDetails['Age'],
                    $residentDetails['email'],
                    $residentDetails['ContactNumber'],
                    $residentDetails['dateRequested'],
                    $rejectedBy
                );
                $insertStmt->execute();
                $insertStmt->close();
                
                
                // Delete from userrequest
                $deleteStmt = $conn->prepare("DELETE FROM userrequest WHERE RequestID = ?");
                $deleteStmt->bind_param("s", $requestId);
                $deleteStmt->execute();
                $deleteStmt->close();
                
            } else {
                $residentCheckStmt->close();
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Request not found in admin or resident tables']);
                exit;
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Request rejected successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error rejecting request: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
