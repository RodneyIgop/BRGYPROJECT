<?php
session_start();

// Include authentication helper
require_once __DIR__ . '/../Connection/admin_auth.php';
require_once __DIR__ . '/../Connection/conn.php';

// Validate admin session and check account status
validateAdminSession($conn);

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_id = $_SESSION['admin_id'];
$current_date = date('l, F j, Y');
$current_time = date('g:i A');

// Update admin activity
updateAdminActivity($conn, $admin_id);

$countPending = 0;
$countApprovedPickup = 0;
$countReleased = 0;
$countResidents = 0;
$countMessages = 0;

if ($res = $conn->query("SELECT COUNT(*) AS c FROM pending_requests WHERE status='pending'")) {
    $countPending = (int)($res->fetch_assoc()['c'] ?? 0);
    $res->free();
}
if ($res = $conn->query("SELECT COUNT(*) AS c FROM approved WHERE status IN ('approved','under_review')")) {
    $countApprovedPickup = (int)($res->fetch_assoc()['c'] ?? 0);
    $res->free();
}
if ($res = $conn->query("SELECT COUNT(*) AS c FROM approved WHERE status='completed'")) {
    $countReleased = (int)($res->fetch_assoc()['c'] ?? 0);
    $res->free();
}
if ($res = $conn->query("SELECT COUNT(*) AS c FROM residents")) {
    $countResidents = (int)($res->fetch_assoc()['c'] ?? 0);
    $res->free();
}
if ($res = $conn->query("SELECT COUNT(*) AS c FROM messages")) {
    $countMessages = (int)($res->fetch_assoc()['c'] ?? 0);
    $res->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="adminIndex.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../images/brgylogo.png" alt="Logo">
            <h2>BARANGAY NEW ERA</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="adminIndex.php" class="active"> <img src="../images/home.png" alt="">Home</a>
            <a href="adminProfile.php"> <img src="../images/user.png" alt="">Profile</a>
            <details class="sidebar-dropdown">
                <summary><img src="../images/list.png" alt="">Request Lists <img src="../images/down.png" alt=""></summary>
                <a href="adminpending.php" class="submenu-link"> <img src="../images/pending.png" alt="" >Pending and for review</a>
                <a href="adminapproved.php" class="submenu-link"> <img src="../images/approved.png" alt="">Approved and For Pick Up</a>
                <a href="adminreleased.php" class="submenu-link"> <img src="../images/complete.png" alt="">Signed and released</a>
            </details>
            <a href="adminarchive.php"> <img src="../images/archive.png" alt="">Archive</a>
            <a href="adminAnnouncements.php"> <img src="../images/marketing.png" alt=""> Announcements</a>
            <a href="adminMessages.php"> <img src="../images/email.png" alt="">Messages</a>
            <a href="adminResidents.php"> <img src="../images/residents.png" alt="">Residents</a>
            <button onclick="showLogoutPopup()" style="margin-top:auto;"> <img src="../images/logout.png" alt="">Logout</button>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Hello, <?php echo htmlspecialchars($admin_name); ?></h1>
            <div class="header-date" id="dateTimeCard">
                <div class="date" id="currentDate"><?php echo $current_date; ?></div>
                <div class="time" id="currentTime"><?php echo $current_time; ?></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <span>📋</span>
                    <span>Request Lists</span>
                </div>
                <div class="card-description">View all requests from residents</div>
                <div class="card-total"><a href="adminpending.php" style="color: #014A7F;text-decoration:none;">Pending and for review: <?php echo (int)$countPending; ?></a><br><a href="adminapproved.php" style="color: #014A7F;text-decoration:none;">approved and for pickup: <?php echo (int)$countApprovedPickup; ?></a><br><a href="adminreleased.php" style="color: #014A7F;text-decoration:none;">signed and released: <?php echo (int)$countReleased; ?></a></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span>📦</span>
                    <span>Archive</span>
                </div>
                <div class="card-description">See all the deleted and completed requested documents.</div>
                <a href="adminarchive.php" class="card-link">View All →</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <span>📢</span>
                    <span>Announcements</span>
                </div>
                <div class="card-description">Write and upload announcements for the users to see.</div>
                <a href="adminAnnouncements.php" class="card-link">View All →</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <span>👤</span>
                    <span>Profile</span>
                </div>
                <div class="card-description">Manage your personal informations.</div>
                <a href="adminprofile.php" class="card-link">View all →</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <span>💬</span>
                    <span>Messages</span>
                </div>
                <div class="card-description">See all message from the users and visitors.</div>
                <div class="card-total">TOTAL MESSAGES: <?php echo (int)$countMessages; ?></div>
                <a href="adminMessages.php" class="card-link">View all →</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <span>👥</span>
                    <span>Residents</span>
                </div>
                <div class="card-description">View residents' information.</div>
                <div class="card-total">TOTAL RESIDENTS: <?php echo (int)$countResidents; ?></div>
                <a href="adminResidents.php" class="card-link">View all →</a>
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
            // Redirect to login page (this will end the session)
            window.location.href = 'adminLogin.php?logout=true';
        }

        // Close popup on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLogoutPopup();
            }
        });
    </script>

    <script src="adminIndex.js"></script>
</body>
</html>
