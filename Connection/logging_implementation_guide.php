<?php
/**
 * Comprehensive Activity Logging Implementation Guide
 * 
 * This file demonstrates how to implement activity logging across all user levels:
 * - Users (residents)
 * - Admins (barangay staff)
 * - Superadmins (system administrators)
 */

// Include the logging function
include_once 'log_activity.php';

/**
 * Example: User Login Logging
 */
function logUserLogin($conn, $userId, $userName, $status = 'Successful') {
    $description = $status === 'Successful' ? 'User logged in successfully' : 'Failed login attempt';
    return logActivity($conn, $userId, $userName, 'User', ACTION_LOGIN, $description, 'userLogin.php', $status);
}

/**
 * Example: User Request Submission
 */
function logUserRequestSubmission($conn, $userId, $userName, $requestType, $requestId) {
    $description = "Submitted {$requestType} request (ID: {$requestId})";
    return logActivity($conn, $userId, $userName, 'User', ACTION_SUBMIT_REQUEST, $description, 'userRequests.php');
}

/**
 * Example: Admin Request Approval
 */
function logAdminRequestApproval($conn, $adminId, $adminName, $requestId, $residentName) {
    $description = "Approved request #{$requestId} for {$residentName}";
    return logActivity($conn, $adminId, $adminName, 'Admin', ACTION_APPROVE_REQUEST, $description, 'adminRequests.php');
}

/**
 * Example: Admin Account Creation
 */
function logAdminAccountCreation($conn, $superadminId, $superadminName, $newAdminName, $newAdminRole) {
    $description = "Created admin account for {$newAdminName} with role: {$newAdminRole}";
    return logActivity($conn, $superadminId, $superadminName, 'Superadmin', ACTION_CREATE_ADMIN, $description, 'superadminAdminAccs.php');
}

/**
 * Example: User Profile Update
 */
function logProfileUpdate($conn, $userId, $userName, $userRole, $updatedFields) {
    $description = "Updated profile: " . implode(', ', $updatedFields);
    $page = $userRole === 'User' ? 'userProfile.php' : ($userRole === 'Admin' ? 'adminProfile.php' : 'superadminProfile.php');
    return logActivity($conn, $userId, $userName, $userRole, ACTION_UPDATE_PROFILE, $description, $page);
}

/**
 * Example: Document Upload
 */
function logDocumentUpload($conn, $userId, $userName, $userRole, $documentType, $fileName) {
    $description = "Uploaded {$documentType}: {$fileName}";
    $page = $userRole === 'User' ? 'userDocuments.php' : 'adminDocuments.php';
    return logActivity($conn, $userId, $userName, $userRole, ACTION_UPLOAD_DOCUMENT, $description, $page);
}

/**
 * Example: Account Blocking/Unblocking
 */
function logAccountStatusChange($conn, $adminId, $adminName, $targetUserId, $targetUserName, $action) {
    $description = "{$action} account for {$targetUserName} (ID: {$targetUserId})";
    $status = $action === 'Blocked' ? 'Successful' : 'Successful';
    return logActivity($conn, $adminId, $adminName, 'Admin', $action === 'Blocked' ? ACTION_BLOCK_USER : ACTION_UNBLOCK_USER, $description, 'adminUserManagement.php', $status);
}

/**
 * Example: Resident Information Management
 */
function logResidentAction($conn, $userId, $userName, $userRole, $action, $residentName, $details = '') {
    $description = "{$action} resident: {$residentName}";
    if (!empty($details)) {
        $description .= " - {$details}";
    }
    return logActivity($conn, $userId, $userName, $userRole, $action, $description, 'superadminResidents.php');
}

/**
 * Example: Announcement Management
 */
function logAnnouncementAction($conn, $userId, $userName, $userRole, $action, $announcementTitle) {
    $description = "{$action} announcement: {$announcementTitle}";
    return logActivity($conn, $userId, $userName, $userRole, $action, $description, 'adminAnnouncements.php');
}

/**
 * Example: System Access Tracking
 */
function logSystemAccess($conn, $userId, $userName, $userRole, $pageAccessed) {
    $description = "Accessed {$pageAccessed}";
    return logActivity($conn, $userId, $userName, $userRole, 'Page Access', $description, $pageAccessed);
}

/**
 * Example: Failed Operations
 */
function logFailedOperation($conn, $userId, $userName, $userRole, $operation, $reason) {
    $description = "Failed {$operation}: {$reason}";
    return logActivity($conn, $userId, $userName, $userRole, $operation, $description, '', 'Failed');
}

/**
 * Example: Bulk Operations
 */
function logBulkOperation($conn, $userId, $userName, $userRole, $operation, $affectedCount, $details = '') {
    $description = "Performed bulk {$operation} affecting {$affectedCount} records";
    if (!empty($details)) {
        $description .= " - {$details}";
    }
    return logActivity($conn, $userId, $userName, $userRole, $operation, $description, '');
}

/**
 * Usage Examples in Different Files:
 * 
 * In userLogin.php:
 * ```php
 * include_once '../Connection/log_activity.php';
 * 
 * if (login_successful) {
 *     logUserLogin($conn, $user_id, $user_name, 'Successful');
 * } else {
 *     logUserLogin($conn, $user_id, $user_name, 'Failed');
 * }
 * ```
 * 
 * In adminRequests.php:
 * ```php
 * include_once '../Connection/log_activity.php';
 * 
 * if (approve_request) {
 *     logAdminRequestApproval($conn, $admin_id, $admin_name, $request_id, $resident_name);
 * }
 * ```
 * 
 * In superadminAdminAccs.php:
 * ```php
 * include_once '../Connection/log_activity.php';
 * 
 * if (create_admin_account) {
 *     logAdminAccountCreation($conn, $superadmin_id, $superadmin_name, $new_admin_name, $role);
 * }
 * ```
 */

?>
