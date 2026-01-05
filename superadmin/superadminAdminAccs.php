<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Accounts Management</title>
    <link rel="stylesheet" href="superadminAdminAccs.css">
</head>
<body>
    <div class="sidebar">
    <div class="sidebar-logo">
        <img src="../images/brgylogo.png">
        <h2>BARANGAY NEW ERA</h2>
    </div>

    <nav class="sidebar-nav">
        <a href="superadmindashboard.php" class="active">
            <img src="../images/home.png"> Home
        </a>

        <a href="superadminProfile.php">
            <img src="../images/user.png"> Profile
        </a>

        <!-- ACCOUNT MANAGEMENT -->
        <details class="sidebar-dropdown">
            <summary>
                <img src="../images/list.png"> Account Management
                <img src="../images/down.png">
            </summary>
            <a href="superadminUserAccs.php" class="submenu-link">
                <img src="../images/addUser.png"> Manage Residents Accounts
            </a>
            <a href="superadminAdminAccs.php" class="submenu-link">
                <img src="../images/addAdmin.png"> Manage Admin Accounts
            </a>
            <a href="superadminAccounts.php" class="submenu-link"> 
                <img src="../images/addUser.png" alt="">Manage Superadmin Accounts</a>
        </details>

        <!-- ACTIVITY LOGS -->
        <a href="superadminLogs.php">
            <img src="../images/monitor.png"> Activity Logs
        </a>

        <!-- ATTENDANCE -->
        <a href="superadminAttendance.php">
            <img src="../images/attendance.png"> Attendance
        </a>

        <!-- RESIDENT INFO -->
        <a href="superadminResidents.php">
            <img src="../images/residents.png"> Resident Information
        </a>

        <!-- ARCHIVES -->
        <a href="superadminarchive.php">
            <img src="../images/archive.png"> Archives
        </a>

        <button onclick="showLogoutPopup()" style="margin-top:auto;">
            <img src="../images/logout.png"> Logout
        </button>
    </nav>
</div>
    <!-- <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../images/brgylogo.png" alt="Logo">
            <h2>BARANGAY NEW ERA</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="superadmindashboard.php"> <img src="../images/home.png" alt="">Home</a>
            <a href="superadminProfile.php"> <img src="../images/user.png" alt="">Profile</a>
            <details class="sidebar-dropdown">
                <summary><img src="../images/list.png" alt="">Account Management <img src="../images/down.png" alt=""></summary>
                <a href="superadminAdminAccs.php" class="submenu-link active"> <img src="../images/addAdmin.png" alt="">Manage Admin Accounts</a>
                <a href="superadminUsers.php" class="submenu-link"> <img src="../images/addUser.png" alt="">Manage Residents Accounts</a>
                <a href="superadminUsers.php" class="submenu-link"> <img src="../images/pending.png" alt="">Block / Unblock Accounts</a>
            </details>
            <a href="superadminLogs.php"> <img src="../images/monitor.png" alt="">Activity Logs</a>
            <a href="superadminResidents.php"> <img src="../images/residents.png" alt="">Resident Information</a>
            <a href="superadminarchive.php"> <img src="../images/archive.png" alt="">Archives</a>
            <a href="#" onclick="logout()"> <img src="../images/logout.png" alt="">Logout</a>
        </nav>
    </div> -->

    <div class="main-content">
        <div class="page-header">
            <h1>Admin Accounts Management</h1>
        </div>
<div class="container">
        <div class="cards-container">
            <!-- View Admin Accounts Card -->
            <div class="card">
                <div class="card-header">
                    <h3>View Admin Accounts</h3>
                    <a href="viewadminaccs.php" class="view-all-link">
                        View All
                        <img src="../images/arrow.png" alt="Arrow" class="arrow-icon">
                    </a>
                </div>
                <div class="card-content">
                    <p>Manage and monitor all existing admin accounts in the system</p>
                </div>
            </div>

            <!-- View Admin Requests Card -->
            <div class="card">
                <div class="card-header">
                    <h3>View Admin Requests</h3>
                    <a href="adminrequests.php" class="view-all-link">
                        View All
                        <img src="../images/arrow.png" alt="Arrow" class="arrow-icon">
                    </a>
                </div>
                <div class="card-content">
                    <p>Review and approve pending admin account requests</p>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>

    <!-- Logout Confirmation Popup -->
    <div class="logout-overlay" id="logoutOverlay" onclick="closeLogoutPopup()"></div>
    <div class="logout-popup" id="logoutPopup">
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to log out?</p>
        <div class="logout-popup-buttons">
            <button class="logout-btn logout-btn-yes" onclick="confirmLogout()">Yes</button>
            <button class="logout-btn logout-btn-no" onclick="closeLogoutPopup()">No</button>
        </div>
    </div>

    <script>
        function showLogoutPopup() {
            const overlay = document.getElementById('logoutOverlay');
            const popup = document.getElementById('logoutPopup');
            
            overlay.classList.add('show');
            popup.classList.add('show');
            
            // Prevent body scroll when popup is open
            document.body.style.overflow = 'hidden';
        }

        function closeLogoutPopup() {
            const overlay = document.getElementById('logoutOverlay');
            const popup = document.getElementById('logoutPopup');
            
            overlay.classList.remove('show');
            popup.classList.remove('show');
            
            // Restore body scroll
            document.body.style.overflow = 'auto';
        }

        function confirmLogout() {
            // Redirect to superadmin login page (this will end the session)
            window.location.href = 'superadminlogin.php?logout=true';
        }

        // Close popup on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLogoutPopup();
            }
        });
    </script>

    <script src="superadminAdminAccs.js"></script>
</body>
</html>