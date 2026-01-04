<?php
// Admin authentication helper functions

/**
 * Validates admin session and checks if account is still active
 * Redirects to login if session is invalid or account is blocked
 */
function validateAdminSession($conn) {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        header('Location: adminLogin.php');
        exit;
    }
    
    $admin_id = $_SESSION['admin_id'];
    
    // Check if admin account still exists and is not blocked
    try {
        $stmt = $conn->prepare("SELECT status FROM admintbl WHERE AdminID = ?");
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Admin account no longer exists
            session_destroy();
            header('Location: adminLogin.php?error=account_not_found');
            exit;
        }
        
        $admin = $result->fetch_assoc();
        $stmt->close();
        
        // Check if account is blocked
        if (isset($admin['status']) && $admin['status'] === 'blocked') {
            // Clear session and redirect to login with blocked message
            session_destroy();
            header('Location: adminLogin.php?error=blocked');
            exit;
        }
        
    } catch (Exception $e) {
        // Database error - destroy session for security
        session_destroy();
        header('Location: adminLogin.php?error=system_error');
        exit;
    }
}

/**
 * Updates the last activity timestamp for the admin
 */
function updateAdminActivity($conn, $admin_id) {
    try {
        $stmt = $conn->prepare("UPDATE admintbl SET last_activity = NOW() WHERE AdminID = ?");
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // Log error but don't interrupt user experience
        error_log("Failed to update admin activity: " . $e->getMessage());
    }
}
?>
