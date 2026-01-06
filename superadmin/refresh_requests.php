<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Database connection
require_once '../Connection/Conn.php';

/**
 * Advanced Name Normalization and Comparison Functions
 * These functions handle order-insensitive name matching with flexible formats
 */

function normalizeName($name) {
    // Remove commas, periods, and extra spaces, then convert to lowercase
    $normalized = preg_replace('/[,.]/', '', $name);
    $normalized = preg_replace('/\s+/', ' ', $normalized);
    return strtolower(trim($normalized));
}

function getNameTokens($name) {
    $normalized = normalizeName($name);
    return array_filter(explode(' ', $normalized));
}

function matchesInitial($initial, $fullName) {
    if (strlen($initial) === 1 && strlen($fullName) > 0) {
        return strtolower(substr($initial, 0, 1)) === strtolower(substr($fullName, 0, 1));
    }
    return false;
}

function advancedNameMatch($requestName, $residentName) {
    // Get tokens for both names
    $requestTokens = getNameTokens($requestName);
    $residentTokens = getNameTokens($residentName);
    
    // If either name has no tokens, no match possible
    if (empty($requestTokens) || empty($residentTokens)) {
        return false;
    }
    
    // Count of matched tokens
    $matchedTokens = 0;
    $usedResidentTokens = array_fill(0, count($residentTokens), false);
    
    // Try to match each request token with resident tokens
    foreach ($requestTokens as $reqToken) {
        $foundMatch = false;
        
        for ($i = 0; $i < count($residentTokens); $i++) {
            if ($usedResidentTokens[$i]) continue; // Skip already used tokens
            
            $resToken = $residentTokens[$i];
            
            // Exact match
            if ($reqToken === $resToken) {
                $foundMatch = true;
                $usedResidentTokens[$i] = true;
                break;
            }
            
            // Initial to full name match
            if (strlen($reqToken) === 1 && strlen($resToken) > 1) {
                if (matchesInitial($reqToken, $resToken)) {
                    $foundMatch = true;
                    $usedResidentTokens[$i] = true;
                    break;
                }
            }
            
            // Full name to initial match
            if (strlen($resToken) === 1 && strlen($reqToken) > 1) {
                if (matchesInitial($resToken, $reqToken)) {
                    $foundMatch = true;
                    $usedResidentTokens[$i] = true;
                    break;
                }
            }
        }
        
        if ($foundMatch) {
            $matchedTokens++;
        }
    }
    
    // Calculate match percentage
    $matchPercentage = $matchedTokens / count($requestTokens);
    
    // Require at least 80% of tokens to match for a valid match
    // This allows for minor differences but ensures strong similarity
    return $matchPercentage >= 0.8;
}

function getResidentsNames($conn) {
    $residentNames = [];
    try {
        // Fetch all full names from residents table using prepared statement
        $stmt = $conn->prepare("SELECT full_name FROM residents ORDER BY full_name");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $residentNames[] = $row['full_name'];
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error fetching residents names: " . $e->getMessage());
    }
    return $residentNames;
}

function checkNameMatch($requestFullName, $residentNames) {
    foreach ($residentNames as $residentName) {
        if (advancedNameMatch($requestFullName, $residentName)) {
            return true;
        }
    }
    return false;
}

// Get all resident names for validation
$residentNames = getResidentsNames($conn);

// Fetch admin requests from database
$requests = [];
try {
    $query = "SELECT RequestID, lastname, firstname, middlename, suffix, birthdate, age, email, contactnumber, requestDate FROM adminrequests ORDER BY RequestID ASC";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = [
                'adminID' => $row['RequestID'],
                'employeeID' => '',
                'LastName' => $row['lastname'],
                'FirstName' => $row['firstname'],
                'MiddleName' => $row['middlename'] ?? '',
                'Suffix' => $row['suffix'] ?? '',
                'Email' => $row['email'],
                'ContactNumber' => $row['contactnumber'],
                'birthdate' => $row['birthdate'],
                'age' => $row['age'],
                'profile_picture' => '',
                'requestDate' => $row['requestDate'] ?? '',
                'status' => 'pending'
            ];
        }
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error fetching admin requests: ' . $e->getMessage()]);
    exit;
}

// Process requests with validation
$processedRequests = [];
foreach ($requests as $request) {
    // Build full name from components (FIRST MIDDLE LAST format)
    $fullname = trim($request['FirstName'] . ' ' . $request['MiddleName'] . ' ' . $request['LastName'] . ' ' . $request['Suffix']);
    
    // Advanced name matching: checks if request name matches any resident name
    // regardless of word order (supports LAST FIRST MIDDLE, FIRST MIDDLE LAST, etc.)
    $nameMatch = checkNameMatch($fullname, $residentNames);
    
    // Determine validation status and styling based on match result
    if ($nameMatch) {
        $validationBadge = '✓ match in the residents list';
        $validationClass = 'success';
        $canApprove = true;
    } else {
        $validationBadge = '❌ doesnt match any in the residents list';
        $validationClass = 'danger';
        $canApprove = false;
    }
    
    $processedRequests[] = [
        'adminID' => $request['adminID'],
        'fullName' => $fullname,
        'validationBadge' => $validationBadge,
        'validationClass' => $validationClass,
        'Email' => $request['Email'],
        'ContactNumber' => $request['ContactNumber'],
        'requestDate' => $request['requestDate'],
        'FirstName' => $request['FirstName'],
        'MiddleName' => $request['MiddleName'],
        'LastName' => $request['LastName'],
        'Suffix' => $request['Suffix'],
        'birthdate' => $request['birthdate'],
        'age' => $request['age'],
        'profile_picture' => $request['profile_picture'],
        'canApprove' => $canApprove
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'requests' => $processedRequests,
    'count' => count($processedRequests)
]);
?>
