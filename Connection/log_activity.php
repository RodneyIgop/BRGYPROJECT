<?php
/**
 * Activity Logger Helper Function
 * Logs admin activities to the activity_logs table
 */

function logActivity($conn, $userId, $userName, $userRole, $action, $description = '', $page = '', $status = 'Successful') {
    try {
        // Get client IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        // Prepare and execute the insert statement
        $stmt = $conn->prepare("
            INSERT INTO activity_logs (user_id, user_name, user_role, action, description, page, ip_address, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("isssssss", $userId, $userName, $userRole, $action, $description, $page, $ipAddress, $status);
        $stmt->execute();
        $stmt->close();
        
        return true;
    } catch (Exception $e) {
        // Log error if needed, but don't break the main functionality
        error_log("Activity logging failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Common activity descriptions
 */
define('ACTION_LOGIN', 'Login');
define('ACTION_LOGOUT', 'Logout');
define('ACTION_REGISTER', 'Account Registration');
define('ACTION_SUBMIT_REQUEST', 'Submit Request');
define('ACTION_CANCEL_REQUEST', 'Cancel Request');
define('ACTION_VIEW_REQUESTS', 'View Requests');
define('ACTION_VIEW_ANNOUNCEMENTS', 'View Announcements');
define('ACTION_UPDATE_PROFILE', 'Update Profile');
define('ACTION_CHANGE_PASSWORD', 'Change Password');
define('ACTION_UPLOAD_DOCUMENT', 'Upload Document');
define('ACTION_DOWNLOAD_DOCUMENT', 'Download Document');
define('ACTION_APPROVE_REQUEST', 'Approve Request');
define('ACTION_REJECT_REQUEST', 'Reject Request');
define('ACTION_DELETE_REQUEST', 'Delete Request');
define('ACTION_CREATE_ANNOUNCEMENT', 'Create Announcement');
define('ACTION_EDIT_ANNOUNCEMENT', 'Edit Announcement');
define('ACTION_DELETE_ANNOUNCEMENT', 'Delete Announcement');
define('ACTION_VIEW_ARCHIVE', 'View Archive');
define('ACTION_CREATE_ADMIN', 'Create Admin Account');
define('ACTION_UPDATE_ADMIN', 'Update Admin Account');
define('ACTION_DELETE_ADMIN', 'Delete Admin Account');
define('ACTION_BLOCK_USER', 'Block User');
define('ACTION_UNBLOCK_USER', 'Unblock User');
define('ACTION_VIEW_RESIDENTS', 'View Resident Information');
define('ACTION_ADD_RESIDENT', 'Add Resident');
define('ACTION_EDIT_RESIDENT', 'Edit Resident');
define('ACTION_DELETE_RESIDENT', 'Delete Resident');

?>
