<?php
session_start();

require_once '../Connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = trim($_POST['employee_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($employeeId === '' || $password === '') {
        header('Location: superadminlogin.php?error=empty');
        exit;
    }

    $stmt = $conn->prepare('SELECT id, FirstName, LastName, Password FROM superadmin WHERE employeeID = ? LIMIT 1');
    $stmt->bind_param('s', $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === (string)$row['Password']) {
            $_SESSION['superadmin_id'] = $row['id'];
            $_SESSION['superadmin_name'] = trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? ''));
            header('Location: superadmindashboard.php');
            exit;
        }
    }

    header('Location: superadminlogin.php?error=invalid');
    exit;
}

if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

$superadmin_name = $_SESSION['superadmin_name'] ?? 'Super Admin';
$current_date = date('l, F j, Y');
$current_time = date('g:i A');

// Fetch all notifications for dropdown
$notifications = [];
$total_notifications = 0;

// Get the last time notifications were viewed
$last_view_time = $_SESSION['last_view_time'] ?? 0;
$badge_dismissed = $_SESSION['badge_dismissed'] ?? false;

// Check if there are new notifications that arrived after last view time
$new_notifications_exist = false;

try {
    // Fetch pending user requests
    $user_query = "SELECT RequestID, FirstName, LastName, email, dateRequested as requestDate, 'user' as notification_type, 'superadminUserAccs.php' as link
                   FROM userrequest 
                   WHERE status = 'pending' 
                   ORDER BY requestDate DESC";
    $user_result = $conn->query($user_query);
    
    if ($user_result && $user_result->num_rows > 0) {
        while ($row = $user_result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['RequestID'],
                'title' => 'New User Registration Request',
                'message' => 'A new resident registration request from ' . $row['FirstName'] . ' ' . $row['LastName'],
                'email' => $row['email'],
                'date' => $row['requestDate'],
                'type' => $row['notification_type'],
                'link' => $row['link']
            ];
            
            // Check if this is a new notification
            $notification_time = strtotime($row['requestDate']);
            if ($notification_time > $last_view_time) {
                $new_notifications_exist = true;
                // Reset badge dismissed flag if new notifications exist
                $badge_dismissed = false;
                $_SESSION['badge_dismissed'] = false;
            }
            
            // Only count for badge if notification is newer than last view time AND badge wasn't dismissed
            if ($notification_time > $last_view_time && !$badge_dismissed) {
                $total_notifications++;
            }
        }
    }
    
    // Fetch pending admin requests
    $admin_query = "SELECT RequestID, firstname, lastname, email, requestDate, 'admin' as notification_type, 'adminrequests.php' as link
                    FROM adminrequests 
                    ORDER BY requestDate DESC";
    $admin_result = $conn->query($admin_query);
    
    if ($admin_result && $admin_result->num_rows > 0) {
        while ($row = $admin_result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['RequestID'],
                'title' => 'New Admin Registration Request',
                'message' => 'A new admin registration request from ' . $row['firstname'] . ' ' . $row['lastname'],
                'email' => $row['email'],
                'date' => $row['requestDate'],
                'type' => $row['notification_type'],
                'link' => $row['link']
            ];
            
            // Check if this is a new notification
            $notification_time = strtotime($row['requestDate']);
            if ($notification_time > $last_view_time) {
                $new_notifications_exist = true;
                // Reset badge dismissed flag if new notifications exist
                $badge_dismissed = false;
                $_SESSION['badge_dismissed'] = false;
            }
            
            // Only count for badge if notification is newer than last view time AND badge wasn't dismissed
            if ($notification_time > $last_view_time && !$badge_dismissed) {
                $total_notifications++;
            }
        }
    }
    
    // Sort all notifications by date (newest first)
    usort($notifications, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // Limit to 5 most recent notifications
    $notifications = array_slice($notifications, 0, 5);
    
} catch (Exception $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
}

// Helper function to calculate time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin Dashboard</title>
    <link rel="stylesheet" href="superadmindashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<style>
    
</style>
<body>

<!-- SIDEBAR (UNCHANGED STRUCTURE) -->
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
            <a href="superadminAdminAccs.php" class="submenu-link">
                <img src="../images/addAdmin.png"> Manage Admin Accounts
            </a>
            <a href="superadminUserAccs.php" class="submenu-link">
                <img src="../images/addUser.png"> Manage Residents Accounts
            </a>
            <a href="superadminUsers.php" class="submenu-link">
                <img src="../images/pending.png"> Block / Unblock Accounts
            </a>
        </details>

        <!-- ACTIVITY LOGS -->
        <a href="superadminLogs.php">
            <img src="../images/monitor.png"> Activity Logs
        </a>

        <!-- RESIDENT INFO -->
        <a href="superadminResidents.php">
            <img src="../images/residents.png"> Resident Information
        </a>

        <!-- ARCHIVES -->
        <a href="superadminarchive.php">
            <img src="../images/archive.png"> Archives
        </a>

        <button onclick="logout()" style="margin-top:auto;">
            <img src="../images/logout.png"> Logout
        </button>
    </nav>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="header">
    <h1>Hello, <?php echo htmlspecialchars($superadmin_name); ?></h1>

    <div class="header-right">
        <!-- Notification Bell -->
        <div class="notification-container">
            <div class="notification-bell" onclick="toggleNotifications()">
                <i class="bi bi-bell"></i>
                <?php if ($total_notifications > 0): ?>
                    <span class="notification-badge"></span>
                <?php endif; ?>
            </div>
            
            <!-- Notification Dropdown -->
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <?php if ($total_notifications > 0): ?>
                        <a href="#" class="clear-all" onclick="clearAllNotifications(event)">Clear All</a>
                    <?php endif; ?>
                </div>
                <div class="notification-list">
                    <?php if (empty($notifications)): ?>
                        <div class="no-notifications">
                            <i class="bi bi-bell-slash"></i>
                            <p>No new notifications</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item" onclick="viewNotification(
                                '<?php echo $notification['link']; ?>',
                                '<?php echo $notification['id']; ?>',
                                '<?php echo $notification['type']; ?>'
                            )">
                                <div class="notification-icon">
                                    <?php if ($notification['type'] === 'admin'): ?>
                                        <i class="bi bi-person-badge"></i>
                                    <?php else: ?>
                                        <i class="bi bi-person"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                    <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                    <div class="notification-email"><?php echo htmlspecialchars($notification['email']); ?></div>
                                    <div class="notification-time"><?php echo timeAgo($notification['date']); ?></div>
                                </div>
                                <div class="notification-actions">
                                    <button class="notification-btn view-btn" onclick="viewNotification(
                                        '<?php echo $notification['link']; ?>',
                                        '<?php echo $notification['id']; ?>',
                                        '<?php echo $notification['type']; ?>'
                                    )" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="notification-btn delete-btn" onclick="deleteNotification(
                                        '<?php echo $notification['id']; ?>',
                                        '<?php echo $notification['type']; ?>'
                                    )" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="notification-footer">
                    <a href="#" class="view-all" onclick="viewAllNotifications(event)">View All Notifications</a>
                </div>
            </div>
        </div>

        <div class="header-date">
            <div class="date" id="currentDate"></div>
            <div class="time" id="currentTime"></div>
        </div>
    </div>
</div>

    <!-- DASHBOARD CARDS -->
    <div class="dashboard-grid">

        <div class="card">
            <div class="card-header">
                <span>👥</span>
                <span>Account Management</span>
            </div>
            <div class="card-description">
                Manage admin approvals, promotions, and account access restrictions.
            </div>
            <a href="superadminAdminAccs.php" class="card-link">Open →</a>
        </div>

        <div class="card">
            <div class="card-header">
                <span>📜</span>
                <span>Activity Logs</span>
            </div>
            <div class="card-description">
                Track system activities and user actions for transparency.
            </div>
            <a href="superadminLogs.php" class="card-link">View →</a>
        </div>

        <div class="card">
            <div class="card-header">
                <span>🏠</span>
                <span>Resident Information</span>
            </div>
            <div class="card-description">
                View and manage registered barangay residents.
            </div>
            <a href="superadminResidents.php" class="card-link">Open →</a>
        </div>

        <div class="card">
            <div class="card-header">
                <span>🗄️</span>
                <span>Archives</span>
            </div>
            <div class="card-description">
                Access archived requests and historical records.
            </div>
            <a href="superadminarchive.php" class="card-link">View →</a>
        </div>

    </div>
</div>



<script src="superadmindashboard.js"></script>
<script>
function updateDateTime() {
    const now = new Date();

    const optionsDate = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };

    const optionsTime = {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    };

    document.getElementById('currentDate').textContent =
        now.toLocaleDateString('en-US', optionsDate);

    document.getElementById('currentTime').textContent =
        now.toLocaleTimeString('en-US', optionsTime);
}

// Initial load
updateDateTime();

// Update every second
setInterval(updateDateTime, 1000);
</script>

</body>
</html>
