<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

// Database connection
require_once '../Connection/Conn.php';
$db = $conn; // Use the global connection variable from Conn.php

// Fetch resident requests from database (userrequest table)
$requests = [];
try {
   $query = "SELECT RequestID, LastName, FirstName, MiddleName, Suffix, birthdate, Age, email, ContactNumber, address, CensusNumber, dateRequested FROM userrequest ORDER BY dateRequested ASC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
    $requests[] = [
        'RequestID' => $row['RequestID'],
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
        'dateRequested' => $row['dateRequested']
    ];
}
    }
} catch (Exception $e) {
    // Handle database error
    $error_message = "Error fetching resident requests: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Requests</title>
    <link rel="stylesheet" href="residentrequest.css">
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
                <a href="superadminAdminAccs.php" class="submenu-link"> <img src="../images/addAdmin.png" alt="">Manage Admin Accounts</a>
                <a href="superadminUserAccs.php" class="submenu-link active"> <img src="../images/addUser.png" alt="">Manage Residents Accounts</a>
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
            <h1>Resident Requests</h1>
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
                                <th>Census Number</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requests)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px;">No resident requests found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($requests as $request): ?>
                                    <?php 
                                    $fullname = trim($request['FirstName'] . ' ' . $request['MiddleName'] . ' ' . $request['LastName'] . ' ' . $request['Suffix']);
                                    $statusClass = 'pending';
                                    $statusText = 'Pending';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['RequestID']); ?></td>
                                        <td><?php echo htmlspecialchars($fullname); ?></td>
                                        <td><?php echo htmlspecialchars($request['CensusNumber']); ?></td>
                                        <td><?php echo htmlspecialchars($request['Email']); ?></td>
                                        <td><?php echo htmlspecialchars($request['ContactNumber']); ?></td>
                                        <td><?php echo htmlspecialchars($request['dateRequested']); ?></td>
                                        <td>
                                            <button class="action-btn view-btn" 
                                                data-request-id="<?php echo htmlspecialchars($request['RequestID']); ?>"
                                                data-census-number="<?php echo htmlspecialchars($request['CensusNumber']); ?>"
                                                data-first-name="<?php echo htmlspecialchars($request['FirstName']); ?>"
                                                data-middle-name="<?php echo htmlspecialchars($request['MiddleName']); ?>"
                                                data-last-name="<?php echo htmlspecialchars($request['LastName']); ?>"
                                                data-suffix="<?php echo htmlspecialchars($request['Suffix']); ?>"
                                                data-email="<?php echo htmlspecialchars($request['Email']); ?>"
                                                data-contact-number="<?php echo htmlspecialchars($request['ContactNumber']); ?>"
                                                data-address="<?php echo htmlspecialchars($request['Address']); ?>"
                                                data-birthdate="<?php echo htmlspecialchars($request['birthdate']); ?>"
                                                data-age="<?php echo htmlspecialchars($request['Age']); ?>"
                                                data-profile-picture="<?php echo htmlspecialchars($request['profile_picture'] ?? ''); ?>"
                                                data-request-date="<?php echo htmlspecialchars($request['dateRequested']); ?>"
                                                onclick="viewRequest(this)">View</button>
                                           
                                            <button class="action-btn reject-btn" onclick="rejectRequest('<?php echo htmlspecialchars($request['RequestID']); ?>', this)">Reject</button>
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

    <!-- Resident Request Details Modal -->
    <div id="requestDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Resident Request Profile</h2>
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
                            <p id="modalCensusNumber">-</p>
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
                            <div class="detail-label">Address:</div>
                            <div class="detail-value" id="modalAddress">-</div>
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
                <a href="generateUID.php?requestId=<?php echo urlencode($request['RequestID']); ?>&email=<?php echo urlencode($request['Email']); ?>&firstName=<?php echo urlencode($request['FirstName']); ?>&middleName=<?php echo urlencode($request['MiddleName']); ?>&lastName=<?php echo urlencode($request['LastName']); ?>&suffix=<?php echo urlencode($request['Suffix']); ?>&censusNumber=<?php echo urlencode($request['CensusNumber']); ?>" class="action-btn accept-btn" style="text-decoration: none; display: inline-block;">Accept</a>
                <button type="button" class="cancel-btn" onclick="closeRequestModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script src="residentrequest.js"></script>
</body>
</html>