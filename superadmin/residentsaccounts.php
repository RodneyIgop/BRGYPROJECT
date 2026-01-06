<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

// Database connection
require_once '../Connection/conn.php';
$db = $conn; // Use the global connection variable from Conn.php

// Fetch resident accounts from database
$residents = [];
try {
    $stmt = $db->prepare("SELECT UID, LastName, FirstName, MiddleName, Suffix, birthdate, Age, ContactNumber, Address, CensusNumber, Email, profile_picture, status FROM usertbl ORDER BY LastName, FirstName");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $residents[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // Handle database error
    $error_message = "Error fetching resident accounts: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Residents Accounts</title>
    <link rel="stylesheet" href="residentsaccounts.css">
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
                <a href="superadminUserAccs.php" class="submenu-link active"> <img src="../images/addUser.png" alt="">Manage Residents Accounts</a>
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
            <h1>View Residents Accounts</h1>
        </div>

        <div class="container">
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!isset($error_message)): ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Resident ID</th>
                                <th>Full Name</th>
                                <th>Census Number</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($residents)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px;">No resident accounts found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($residents as $resident): ?>
                                    <?php 
                                    $fullname = trim($resident['FirstName'] . ' ' . $resident['MiddleName'] . ' ' . $resident['LastName'] . ' ' . $resident['Suffix']);
                                    
                                    // Get actual status from database
                                    $status = $resident['status'] ?? 'active';
                                    $statusClass = $status === 'blocked' ? 'blocked' : 'active';
                                    $statusText = ucfirst($status);
                                    $isBlocked = $status === 'blocked';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($resident['UID'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($fullname); ?></td>
                                        <td><?php echo htmlspecialchars($resident['CensusNumber'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($resident['Email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($resident['ContactNumber'] ?? 'N/A'); ?></td>
                                        <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                        <td>
                                            <button class="action-btn view-btn" 
                                                    data-user-id="<?php echo htmlspecialchars($resident['UID']); ?>"
                                                    data-census-number="<?php echo htmlspecialchars($resident['CensusNumber'] ?? ''); ?>"
                                                    data-birthdate="<?php echo htmlspecialchars($resident['birthdate'] ?? ''); ?>"
                                                    data-age="<?php echo htmlspecialchars($resident['Age'] ?? ''); ?>"
                                                    data-address="<?php echo htmlspecialchars($resident['Address'] ?? ''); ?>"
                                                    data-profile-picture="<?php echo htmlspecialchars($resident['profile_picture'] ?? ''); ?>"
                                                    onclick="viewResident(this)">View</button>
                                            <button class="action-btn <?php echo $isBlocked ? 'unblock-btn' : 'block-btn'; ?>" 
                                                    data-uid="<?php echo htmlspecialchars($resident['UID']); ?>"
                                                    data-name="<?php echo htmlspecialchars($fullname); ?>"
                                                    data-current-status="<?php echo $status; ?>"
                                                    onclick="toggleBlockResident(this)">
                                                <?php echo $isBlocked ? 'Unblock' : 'Block'; ?>
                                            </button>
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

    <!-- Resident Details Modal -->
    <div id="residentDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Resident Profile</h2>
                <span class="close" onclick="closeResidentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="profile-modal">
                    <div class="profile-header">
                        <div class="profile-picture-container">
                            <img id="modalProfilePicture" src="../images/tao.png" alt="Profile Picture" class="profile-picture">
                        </div>
                        <div class="profile-basic-info">
                            <h3 id="modalFullName">-</h3>
                            <p id="modalUserID">-</p>
                            <p id="modalCensusNumber">-</p>
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
                            <div class="detail-label">Date Requested:</div>
                            <div class="detail-value" id="modalDateRequested">-</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Status:</div>
                            <div class="detail-value" id="modalStatus">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="residentsaccounts.js"></script>
</body>
</html>