<?php
require_once 'Connection/conn.php';

// Create notifications table to store all notifications
echo "Creating notifications table...\n";
$create_notifications_table = "
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    email VARCHAR(255) NOT NULL,
    notification_type ENUM('resident', 'admin') NOT NULL,
    original_request_id INT NOT NULL,
    link VARCHAR(255) NOT NULL,
    status ENUM('unread', 'read', 'deleted') DEFAULT 'unread',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_type (notification_type),
    INDEX idx_created_at (created_at),
    INDEX idx_request_id (original_request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($create_notifications_table)) {
    echo "✓ Notifications table created\n";
} else {
    echo "✗ Error creating notifications table: " . $conn->error . "\n";
}

// Migrate existing pending requests to notifications table
echo "Migrating existing pending requests...\n";

// Migrate resident requests
$migrate_resident = "
INSERT INTO notifications (title, message, email, notification_type, original_request_id, link, status, created_at)
SELECT 
    'New Resident Account Request' as title,
    CONCAT(firstName, ' ', lastName, ' requested an account') as message,
    email,
    'resident' as notification_type,
    RequestID as original_request_id,
    'superadminUserAccs.php' as link,
    'unread' as status,
    dateRequested as created_at
FROM userrequest 
WHERE status = 'pending' OR status IS NULL
ON DUPLICATE KEY UPDATE 
    title = VALUES(title),
    message = VALUES(message),
    status = VALUES(status)";

if ($conn->query($migrate_resident)) {
    echo "✓ Resident requests migrated\n";
} else {
    echo "✗ Error migrating resident requests: " . $conn->error . "\n";
}

// Migrate admin requests
$migrate_admin = "
INSERT INTO notifications (title, message, email, notification_type, original_request_id, link, status, created_at)
SELECT 
    'New Admin Account Request' as title,
    CONCAT(firstname, ' ', lastname, ' requested an admin account') as message,
    email,
    'admin' as notification_type,
    RequestID as original_request_id,
    'adminrequests.php' as link,
    'unread' as status,
    requestDate as created_at
FROM adminrequests 
WHERE status = 'pending' OR status IS NULL
ON DUPLICATE KEY UPDATE 
    title = VALUES(title),
    message = VALUES(message),
    status = VALUES(status)";

if ($conn->query($migrate_admin)) {
    echo "✓ Admin requests migrated\n";
} else {
    echo "✗ Error migrating admin requests: " . $conn->error . "\n";
}

echo "\nSetup complete!\n";
echo "All notifications will now be stored in the notifications table.\n";
echo "Existing pending requests have been migrated.\n";
?>
