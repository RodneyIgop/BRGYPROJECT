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

/**
 * Normalizes a name string by removing punctuation, trimming spaces, and converting to lowercase
 * @param string $name The name to normalize
 * @return string The normalized name
 */
function normalizeName($name) {
    // Remove commas, periods, and extra spaces, then convert to lowercase
    $normalized = preg_replace('/[,.]/', '', $name);
    $normalized = preg_replace('/\s+/', ' ', $normalized);
    return strtolower(trim($normalized));
}

/**
 * Splits a normalized name into individual tokens (name parts)
 * @param string $name The normalized name string
 * @return array Array of name tokens
 */
function getNameTokens($name) {
    $normalized = normalizeName($name);
    return array_filter(explode(' ', $normalized));
}

/**
 * Checks if an initial matches a full name (first letter comparison)
 * @param string $initial The single character initial
 * @param string $fullName The full name to compare against
 * @return bool True if initial matches the first letter of full name
 */
function matchesInitial($initial, $fullName) {
    if (strlen($initial) === 1 && strlen($fullName) > 0) {
        return strtolower(substr($initial, 0, 1)) === strtolower(substr($fullName, 0, 1));
    }
    return false;
}

/**
 * Advanced name matching function that supports order-insensitive comparison
 * Matches names regardless of word order and handles initials vs full names
 * @param string $requestName The name from the request (any format)
 * @param string $residentName The name from the residents database
 * @return bool True if names match according to flexible rules
 */
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

/**
 * Fetches all resident names from the residents table for validation
 * @param mysqli $conn Database connection
 * @return array Array of resident names
 */
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

/**
 * Checks if a request name matches any resident name using advanced matching
 * @param string $requestFullName The full name from the request
 * @param array $residentNames Array of resident names to check against
 * @return bool True if a match is found
 */
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

// Fetch resident requests from database (userrequest table)
$requests = [];
try {
    $query = "SELECT RequestID, LastName, FirstName, MiddleName, Suffix, birthdate, Age, email, ContactNumber, address, CensusNumber, dateRequested FROM userrequest ORDER BY dateRequested ASC";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = [
                'requestId' => $row['RequestID'],
                'FirstName' => $row['FirstName'],
                'MiddleName' => $row['MiddleName'] ?? '',
                'Suffix' => $row['Suffix'] ?? '',
                'LastName' => $row['LastName'],
                'Email' => $row['email'],
                'ContactNumber' => $row['ContactNumber'],
                'Address' => $row['address'] ?? '',
                'birthdate' => $row['birthdate'],
                'Age' => $row['Age'],
                'CensusNumber' => $row['CensusNumber'],
                'dateRequested' => $row['dateRequested'] ?? '',
                'status' => 'pending'
            ];
        }
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error fetching resident requests: ' . $e->getMessage()]);
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
        'requestId' => $request['requestId'],
        'fullName' => $fullname,
        'validationBadge' => $validationBadge,
        'validationClass' => $validationClass,
        'CensusNumber' => $request['CensusNumber'],
        'Email' => $request['Email'],
        'ContactNumber' => $request['ContactNumber'],
        'dateRequested' => $request['dateRequested'],
        'FirstName' => $request['FirstName'],
        'MiddleName' => $request['MiddleName'],
        'LastName' => $request['LastName'],
        'Suffix' => $request['Suffix'],
        'Address' => $request['Address'],
        'birthdate' => $request['birthdate'],
        'Age' => $request['Age'],
        'profile_picture' => $request['profile_picture'] ?? '',
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
