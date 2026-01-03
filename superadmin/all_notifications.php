<?php
session_start();

require_once '../Connection/conn.php';

if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

$superadmin_name = $_SESSION['superadmin_name'] ?? 'Super Admin';

// Fetch all notifications with pagination
$notifications = [];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;
$total_notifications = 0;
$total_pages = 1;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

try {
    // Get total count based on filter
    $count_query = "SELECT COUNT(*) as total FROM notifications WHERE status != 'deleted'";
    if ($filter === 'unread') {
        $count_query .= " AND status = 'unread'";
    } elseif ($filter === 'read') {
        $count_query .= " AND status = 'read'";
    }
    
    $count_result = $conn->query($count_query);
    $total_notifications = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_notifications / $per_page);
    
    // Get notifications with pagination and filter
    $query = "SELECT id, title, message, email, notification_type, link, status, created_at 
              FROM notifications WHERE status != 'deleted'";
    
    if ($filter === 'unread') {
        $query .= " AND status = 'unread'";
    } elseif ($filter === 'read') {
        $query .= " AND status = 'read'";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
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
        return date('M j, Y g:i A', $time);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Notifications - SuperAdmin</title>
    <link rel="stylesheet" href="superadmindashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .notifications-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .notifications-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .notifications-title h1 {
            color: #333;
            font-size: 28px;
            margin: 0;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .filter-tab {
            padding: 10px 20px;
            border: 1px solid #ddd;
            background: white;
            text-decoration: none;
            color: #666;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .filter-tab:hover {
            background: #f8f9fa;
        }
        
        .filter-tab.active {
            background: #00386b;
            color: white;
            border-color: #00386b;
        }
        
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        
        .notification-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            /* overflow: hidden; */
        }
        
        .notification-item {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: background 0.3s;
            position: relative;
        }
        
        .notification-item:hover {
            background: #f8f9fa;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item.unread {
            background: #f0f8ff;
            border-left: 4px solid #007bff;
        }
        
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .notification-icon.admin {
            background: #28a745;
        }
        
        .notification-icon.resident {
            background: #007bff;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .notification-message {
            color: #666;
            margin-bottom: 5px;
        }
        
        .notification-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #999;
        }
        
        .notification-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .action-btn:hover {
            background: #5a6268;
        }
        
        .action-btn.mark-read {
            background: #28a745;
        }
        
        .action-btn.mark-read:hover {
            background: #218838;
        }
        
        .action-btn.delete {
            background: #dc3545;
        }
        
        .action-btn.delete:hover {
            background: #c82333;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a {
            padding: 10px 15px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .pagination a.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="../images/brgylogo.png">
        <h2>BARANGAY NEW ERA</h2>
    </div>

    <nav class="sidebar-nav">
        <a href="superadmindashboard.php">
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

<div class="main-content">
    <div class="notifications-container">
        <div class="notifications-header">
            <div class="notifications-title">
                <a href="superadmindashboard.php" class="back-btn">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
                <h1><i class="bi bi-bell"></i> All Notifications</h1>
            </div>
        </div>

        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All (<?php 
                    $all_count = 0;
                    try {
                        $all_result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE status != 'deleted'");
                        $all_count = $all_result->fetch_assoc()['total'];
                    } catch (Exception $e) {}
                    echo $all_count;
                ?>)
            </a>
            <a href="?filter=unread" class="filter-tab <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                Unread (<?php 
                    $unread_count = 0;
                    try {
                        $unread_result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE status = 'unread'");
                        $unread_count = $unread_result->fetch_assoc()['total'];
                    } catch (Exception $e) {}
                    echo $unread_count;
                ?>)
            </a>
            <a href="?filter=read" class="filter-tab <?php echo $filter === 'read' ? 'active' : ''; ?>">
                Read (<?php 
                    $read_count = 0;
                    try {
                        $read_result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE status = 'read'");
                        $read_count = $read_result->fetch_assoc()['total'];
                    } catch (Exception $e) {}
                    echo $read_count;
                ?>)
            </a>
        </div>

        <div class="stats-card">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $all_count; ?></div>
                    <div class="stat-label">Total Notifications</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        $admin_count = 0;
                        try {
                            $admin_result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE notification_type = 'admin' AND status != 'deleted'");
                            $admin_count = $admin_result->fetch_assoc()['total'];
                        } catch (Exception $e) {}
                        echo $admin_count;
                    ?></div>
                    <div class="stat-label">Admin Requests</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        $resident_count = 0;
                        try {
                            $resident_result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE notification_type = 'resident' AND status != 'deleted'");
                            $resident_count = $resident_result->fetch_assoc()['total'];
                        } catch (Exception $e) {}
                        echo $resident_count;
                    ?></div>
                    <div class="stat-label">Resident Requests</div>
                </div>
            </div>
        </div>

        <div class="notification-list">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <i class="bi bi-bell-slash"></i>
                    <h3>No Notifications</h3>
                    <p>No notifications found for the selected filter.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['status'] === 'unread' ? 'unread' : ''; ?>">
                        <div class="notification-icon <?php echo $notification['notification_type']; ?>">
                            <?php if ($notification['notification_type'] === 'admin'): ?>
                                <i class="bi bi-person-badge"></i>
                            <?php else: ?>
                                <i class="bi bi-person"></i>
                            <?php endif; ?>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                            <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                            <div class="notification-meta">
                                <span><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($notification['email']); ?></span>
                                <span><i class="bi bi-calendar"></i> <?php echo timeAgo($notification['created_at']); ?></span>
                                <span><i class="bi bi-circle"></i> <?php echo ucfirst($notification['status']); ?></span>
                            </div>
                        </div>
                        <div class="notification-actions">
                            <a href="<?php echo htmlspecialchars($notification['link']); ?>" class="action-btn">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <?php if ($notification['status'] === 'unread'): ?>
                                <button class="action-btn mark-read" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                    <i class="bi bi-check"></i> Mark Read
                                </button>
                            <?php endif; ?>
                            <button class="action-btn delete" onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>"><i class="bi bi-chevron-left"></i></a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>" class="active"><?php echo $i; ?></a>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>"><i class="bi bi-chevron-right"></i></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'superadminlogin.php?logout=true';
    }
}

function markAsRead(id) {
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to mark notification as read: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while marking notification as read');
    });
}

function deleteNotification(id) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }

    fetch('delete_notification_db.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to delete notification: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the notification');
    });
}
</script>
</body>
</html>
