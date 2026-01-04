<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

// Database connection
require_once '../Connection/Conn.php';
$db = $conn; // Use the global connection variable from Conn.php

// Add status column if it doesn't exist
try {
    $checkColumn = $db->query("SHOW COLUMNS FROM admintbl LIKE 'status'");
    if ($checkColumn->num_rows == 0) {
        $sql = "ALTER TABLE admintbl ADD COLUMN status ENUM('active', 'blocked') NOT NULL DEFAULT 'active'";
        $db->query($sql);
    }
} catch (Exception $e) {
    // Handle column addition error silently
}

// Add last_activity column if it doesn't exist
try {
    $checkColumn = $db->query("SHOW COLUMNS FROM admintbl LIKE 'last_activity'");
    if ($checkColumn->num_rows == 0) {
        $sql = "ALTER TABLE admintbl ADD COLUMN last_activity TIMESTAMP NULL DEFAULT NULL";
        $db->query($sql);
    }
} catch (Exception $e) {
    // Handle column addition error silently
}

// Handle block/unblock actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $adminId = $_POST['admin_id'] ?? '';
    $action = $_POST['action'];
    
    if ($action === 'block' || $action === 'unblock') {
        try {
            $newStatus = $action === 'block' ? 'blocked' : 'active';
            $stmt = $db->prepare("UPDATE admintbl SET status = ? WHERE AdminID = ?");
            $stmt->bind_param('si', $newStatus, $adminId);
            $stmt->execute();
            $stmt->close();
            
            // Log the action
            $actionText = $action === 'block' ? 'blocked' : 'unblocked';
            error_log("Admin ID $adminId has been $actionText by superadmin");
            
            // Redirect to prevent form resubmission
            header('Location: viewadminaccs.php?status=' . $action . '&success=1');
            exit;
        } catch (Exception $e) {
            $error_message = "Error updating admin status: " . $e->getMessage();
        }
    }
}

// Fetch admin accounts from database
$admins = [];
try {
    $stmt = $db->prepare("SELECT adminID, employeeID, LastName, FirstName, MiddleName, Suffix, Email, ContactNumber, birthdate, age, profile_picture, status FROM admintbl ORDER BY LastName, FirstName");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // Handle database error
    $error_message = "Error fetching admin accounts: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Admin Accounts</title>
    <link rel="stylesheet" href="viewadminaccs.css">
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
                <a href="superadminUserAccs.php" class="submenu-link"> <img src="../images/addUser.png" alt="">Manage Residents Accounts</a>
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
            <h1>View Admin Accounts</h1>
        </div>

        <div class="container">
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <?php if (isset($_GET['status']) && $_GET['status'] == 'block'): ?>
                    <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px;">
                        Admin account has been successfully blocked.
                    </div>
                <?php elseif (isset($_GET['status']) && $_GET['status'] == 'unblock'): ?>
                    <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px;">
                        Admin account has been successfully unblocked.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (!isset($error_message)): ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($admins)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px;">No admin accounts found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($admins as $admin): ?>
                                    <?php 
                                    $fullname = trim($admin['FirstName'] . ' ' . $admin['MiddleName'] . ' ' . $admin['LastName'] . ' ' . $admin['Suffix']);
                                    // Use actual status from database
                                    $status = isset($admin['status']) ? $admin['status'] : 'active';
                                    $statusClass = $status === 'blocked' ? 'blocked' : 'active';
                                    $statusText = $status === 'blocked' ? 'Blocked' : 'Active';
                                    ?>
                                    <tr>
                                        <td><?php 
                                    $employeeID = htmlspecialchars($admin['employeeID'] ?? 'N/A');
                                    if ($employeeID !== 'N/A' && strlen($employeeID) > 4) {
                                        $masked = substr($employeeID, 0, -4) . str_repeat('*', 4);
                                        echo $masked;
                                    } elseif ($employeeID !== 'N/A') {
                                        echo str_repeat('*', strlen($employeeID));
                                    } else {
                                        echo $employeeID;
                                    }
                                    ?></td>
                                        <td><?php echo htmlspecialchars($fullname); ?></td>
                                        <td><?php echo htmlspecialchars($admin['Email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($admin['ContactNumber'] ?? 'N/A'); ?></td>
                                        <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                        <td>
                                            <button class="action-btn view-btn" 
                                                    data-admin-id="<?php echo htmlspecialchars($admin['adminID']); ?>"
                                                    data-employee-id="<?php echo htmlspecialchars($admin['employeeID'] ?? ''); ?>"
                                                    data-birthdate="<?php echo htmlspecialchars($admin['birthdate'] ?? ''); ?>"
                                                    data-age="<?php echo htmlspecialchars($admin['age'] ?? ''); ?>"
                                                    data-profile-picture="<?php echo htmlspecialchars($admin['profile_picture'] ?? ''); ?>"
                                                    onclick="viewAdmin(this)">View</button>
                                            
                                            <?php if ($status === 'active'): ?>
                                                <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to block this admin account?')">
                                                    <input type="hidden" name="action" value="block">
                                                    <input type="hidden" name="admin_id" value="<?php echo htmlspecialchars($admin['adminID']); ?>">
                                                    <button type="submit" class="action-btn block-btn">Block</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to unblock this admin account?')">
                                                    <input type="hidden" name="action" value="unblock">
                                                    <input type="hidden" name="admin_id" value="<?php echo htmlspecialchars($admin['adminID']); ?>">
                                                    <button type="submit" class="action-btn unblock-btn">Unblock</button>
                                                </form>
                                            <?php endif; ?>
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

    <!-- Admin Details Modal -->
    <div id="adminDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Admin Profile</h2>
                <span class="close" onclick="closeAdminModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="profile-modal">
                    <div class="profile-header">
                        <div class="profile-picture-container">
                            <img id="modalProfilePicture" src="../images/tao.png" alt="Profile Picture" class="profile-picture">
                        </div>
                        <div class="profile-basic-info">
                            <h3 id="modalFullName">-</h3>
                            <p id="modalAdminID">-</p>
                            <p id="modalEmployeeID">-</p>
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
                            <div class="detail-value" id="modalStatus">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="viewadminaccs.js"></script>
</body>
</html>