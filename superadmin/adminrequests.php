<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

// Database connection
require_once '../Connection/Conn.php';
$db = $conn; // Use the global connection variable from Conn.php

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

// Fetch admin requests from database
$requests = [];
try {
    $query = "SELECT RequestID, lastname, firstname, middlename, suffix, birthdate, age, email, contactnumber, requestDate FROM adminrequests ORDER BY RequestID ASC";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Debug: Log the email from database
            error_log("Database email for RequestID " . $row['RequestID'] . ": " . $row['email']);
            
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
    // Handle database error
    $error_message = "Error fetching admin requests: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Requests</title>
    <link rel="stylesheet" href="adminrequests.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../images/brgylogo.png" alt="Logo">
            <h2>BARANGAY NEW ERA</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="superadmindashboard.php"> <img src="../images/home.png" alt="">Home</a>
            <a href="superadminProfile.php"> <img src="../images/user.png" alt="">Profile</a>
            <details class="sidebar-dropdown">
                <summary><img src="../images/list.png" alt="">Account Management <img src="../images/down.png" alt=""></summary>
                <a href="superadminUserAccs.php" class="submenu-link"> <img src="../images/addUser.png" alt="">Manage Residents Accounts</a>
                <a href="superadminAdminAccs.php" class="submenu-link"> <img src="../images/addAdmin.png" alt="">Manage Admin Accounts</a>
                <a href="superadminAccounts.php" class="submenu-link"> <img src="../images/addUser.png" alt="">Manage Superadmin Accounts</a>
                <!-- <a href="superadminUsers.php" class="submenu-link"> <img src="../images/pending.png" alt="">Block / Unblock Accounts</a> -->
            </details>
            <a href="superadminLogs.php"> <img src="../images/monitor.png" alt="">Activity Logs</a>
            <a href="superadminResidents.php"> <img src="../images/residents.png" alt="">Resident Information</a>
            <a href="superadminarchive.php"> <img src="../images/archive.png" alt="">Archives</a>
            <a href="#" onclick="logout()"> <img src="../images/logout.png" alt="">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Admin Requests</h1>
        </div>

        <div class="container">
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Full Name</th>
                                <th>Validation Status</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requests)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px;">No admin requests found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($requests as $request): ?>
                                    <?php 
                                    // Build full name from components (FIRST MIDDLE LAST format)
                                    $fullname = trim($request['FirstName'] . ' ' . $request['MiddleName'] . ' ' . $request['LastName'] . ' ' . $request['Suffix']);
                                    
                                    // Advanced name matching: checks if request name matches any resident name
                                    // regardless of word order (supports LAST FIRST MIDDLE, FIRST MIDDLE LAST, etc.)
                                    $nameMatch = checkNameMatch($fullname, $residentNames);
                                    
                                    // Determine validation status and styling based on match result
                                    if ($nameMatch) {
                                        $validationBadge = '<span class="badge bg-success">✓ match in the residents list</span>';
                                        $canApprove = true;
                                    } else {
                                        $validationBadge = '<span class="badge bg-danger">❌ doesnt match any in the residents list</span>';
                                        $canApprove = false;
                                    }
                                    
                                    $statusClass = 'pending';
                                    $statusText = 'Pending';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['adminID'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($fullname); ?></td>
                                        <td><?php echo $validationBadge; ?></td>
                                        <td><?php echo htmlspecialchars($request['Email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($request['ContactNumber'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($request['requestDate'] ?? 'N/A'); ?></td>
                                        <td>
                                            <button class="action-btn view-btn" 
                                                data-request-id="<?php echo htmlspecialchars($request['adminID']); ?>"
                                                data-first-name="<?php echo htmlspecialchars($request['FirstName'] ?? ''); ?>"
                                                data-middle-name="<?php echo htmlspecialchars($request['MiddleName'] ?? ''); ?>"
                                                data-last-name="<?php echo htmlspecialchars($request['LastName'] ?? ''); ?>"
                                                data-suffix="<?php echo htmlspecialchars($request['Suffix'] ?? ''); ?>"
                                                data-email="<?php echo htmlspecialchars($request['Email'] ?? ''); ?>"
                                                data-contact-number="<?php echo htmlspecialchars($request['ContactNumber'] ?? ''); ?>"
                                                data-birthdate="<?php echo htmlspecialchars($request['birthdate'] ?? ''); ?>"
                                                data-age="<?php echo htmlspecialchars($request['age'] ?? ''); ?>"
                                                data-profile-picture="<?php echo htmlspecialchars($request['profile_picture'] ?? ''); ?>"
                                                data-request-date="<?php echo htmlspecialchars($request['requestDate'] ?? ''); ?>"
                                                data-can-approve="<?php echo $canApprove ? 'true' : 'false'; ?>"
                                                onclick="viewRequest(this)">View</button>
                                           
                                            <button class="action-btn reject-btn" onclick="rejectRequest('<?php echo htmlspecialchars($request['adminID']); ?>', event)">Reject</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Request Details Modal -->
    <div id="requestDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Admin Request Profile</h2>
                <span class="close" onclick="closeRequestModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="profile-modal">
                    <div class="profile-header">
                        <div class="profile-picture-container">
                            <img id="modalProfilePicture" src="../images/tao.png" alt="Profile Picture" class="profile-picture">
                        </div>
                        <div class="profile-basic-info">
                            <h3 id="modalFullName">-</h3>
                            <p id="modalRequestID">-</p>
                            <p id="modalRequestDate">-</p>
                        </div>
                    </div>
                    <div class="profile-details">
                        <div class="detail-row">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value" id="modalEmail">-</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Contact Number:</div>
                            <div class="detail-value" id="modalContactNumber">-</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Birthdate:</div>
                            <div class="detail-value" id="modalBirthdate">-</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Age:</div>
                            <div class="detail-value" id="modalAge">-</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Status:</div>
                            <div class="detail-value" id="modalStatus">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="action-btn accept-btn" id="acceptButton" onclick="acceptRequest()">Accept</button>
                <button type="button" class="cancel-btn" onclick="closeRequestModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script src="adminrequests.js"></script>
    
    <!-- Bootstrap CSS for badges -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</body>
</html>