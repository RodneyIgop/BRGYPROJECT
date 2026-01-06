<?php
session_start();
require_once __DIR__ . '/../Connection/conn.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['resident_id']) && !isset($_SESSION['UserID'])) {
    header('Location: residentlogin.php');
    exit();
}
$user_id = $_SESSION['resident_id'] ?? $_SESSION['UserID'];

// Extract numeric part if user_id contains "UID-" prefix
if (strpos($user_id, 'UID-') === 0) {
    $user_id = substr($user_id, 4); // Remove "UID-" prefix
}

// The session contains the UID (like 'UID-2757'), but pending_requests uses numeric user_id
// We need to get the numeric user_id from the UID
$uid_string = $_SESSION['resident_id'] ?? '';
$numeric_user_id = null;

if ($uid_string && strpos($uid_string, 'UID-') === 0) {
    // Extract numeric part from UID
    $uid_numeric = substr($uid_string, 4); // Remove 'UID-' prefix
    
    // Get the actual numeric user_id from usertbl using UID
    $stmt = $conn->prepare('SELECT UserID FROM usertbl WHERE UID = ?');
    if ($stmt) {
        $stmt->bind_param('s', $uid_string);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $u = $res->fetch_assoc();
            $numeric_user_id = $u['UserID'];
            $_SESSION['numeric_user_id'] = $numeric_user_id; // Store for future use
        }
        $stmt->close();
    }
}

// Also get fullname for display in tables that don't have UID
$fullname = $_SESSION['resident_name'] ?? '';
if (!$fullname) {
    // Fallback: try to fetch from database
    $stmt = $conn->prepare('SELECT FirstName, MiddleName, LastName, Suffix FROM usertbl WHERE UserID = ?');
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $u = $res->fetch_assoc();
            $fullname = trim(sprintf('%s %s %s %s', $u['FirstName'], $u['MiddleName'], $u['LastName'], $u['Suffix']));
            $_SESSION['resident_name'] = $fullname; // Store in session for future use
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Archive - Barangay New Era</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="ResidentsTrackRequest.css">
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu">
        <i class="bi bi-list"></i>
        <span class="sr-only">Menu</span>
    </button>

    <div class="sidebar">
        <div class="logo">
            <img src="../images/brgylogo.png" alt="Barangay Logo" class="logo-img">
            <div class="logo-text">
                <h2>BARANGAY NEW ERA</h2>   
            </div>
        </div>
        <nav>
             <ul>
                <li><a href="ResidentsIndex.php" aria-label="Home"><i class="fas fa-home"></i> <span>Home</span></a></li>
                <li><a href="ResidentsProfile.php" aria-label="Profile"><i class="fas fa-user"></i> <span>Profile</span></a></li>
                <li><a href="ResidentsRequestDocu.php" aria-label="Document Request"><i class="fas fa-file-alt"></i> <span>Document Request</span></a></li>
                <li><a href="residentstrackrequest.php" aria-label="My Request"><i class="fas fa-list"></i> <span>My Request</span></a></li>
                <li class="active"><a href="#" aria-label="Archive"><i class="fas fa-archive"></i> <span>History</span></a></li>
                <li class="logout"><a href="residentlogin.php" aria-label="Logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </nav>
    </div>

    <div class="container">
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1>History</h1>
                <p class="page-description">View your completed, cancelled, and declined document requests</p>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-controls">
                    <label for="statusFilter">Filter by Status:</label>
                    <select id="statusFilter" class="form-select" style="width: 200px; display: inline-block;">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="declined">Declined</option>
                    </select>
                    <button id="clearFilter" class="btn btn-secondary btn-sm ms-2">Clear Filter</button>
                </div>
            </div>
            
            <div class="requests-table">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="vertical-align: middle; text-align: left;">Full Name</th>
                                <th style="vertical-align: middle; text-align: center;">Status</th>
                                <th style="vertical-align: middle; text-align: left;">Date Requested</th>
                                <th style="vertical-align: middle; text-align: left;">Document Type</th>
                                <th style="vertical-align: middle; text-align: left;">Purpose</th>
                                <th style="vertical-align: middle; text-align: left;">Reason</th>
                                <th style="vertical-align: middle; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="archiveTableBody">
<?php
// Debug: Show current session values
error_log("DEBUG: uid_string = " . var_export($uid_string, true));
error_log("DEBUG: numeric_user_id = " . var_export($numeric_user_id, true));
error_log("DEBUG: fullname = " . var_export($fullname, true));

// Use the numeric user_id for filtering
$effective_user_id = $numeric_user_id ?? $user_id;

// Get filter status from GET parameter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build WHERE clause for status filter
$status_where = "";
if (!empty($status_filter)) {
    $status_where = " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Query to get archived/declined/cancelled/completed requests
$sql = "SELECT ArchiveID AS reqId, '' AS user_id, fullname, documenttype AS document, daterequested AS dateRequested, '' AS purpose, status, reason, '' AS notes FROM archivetbl WHERE fullname = ? AND (status = 'declined' OR status = 'cancelled' OR status = 'completed')$status_where
        UNION ALL
        SELECT id AS reqId, user_id, fullname, document_type AS document, date_requested AS dateRequested, purpose, status, '' AS reason, notes FROM pending_requests WHERE (fullname = ? OR user_id = ?) AND status = 'declined'$status_where
        UNION ALL
        SELECT RequestID AS reqId, '' AS user_id, fullname, documenttype AS document, dateRequested, purpose, status, '' AS reason, '' AS notes FROM approved WHERE fullname = ? AND status = 'completed'$status_where
        ORDER BY dateRequested DESC";

// Debug: Show the SQL query
error_log("DEBUG: SQL = " . $sql);

$query = $conn->prepare($sql);
$query->bind_param('ssss', $fullname, $fullname, $effective_user_id, $fullname);

// Debug: Show bound parameters
error_log("DEBUG: Bound params - fullname: $fullname, effective_user_id: $effective_user_id");

$query->execute();
$rows = $query->get_result();

// Debug: Show number of results
error_log("DEBUG: Number of rows found: " . ($rows ? $rows->num_rows : 0));
if ($rows && $rows->num_rows > 0) {
    while ($row = $rows->fetch_assoc()) {
        echo '<tr>';
        echo '<td style="vertical-align: middle; text-align: left;">'.htmlspecialchars($row['fullname'] ?? $fullname).'</td>';
        $displayStatus = $row['status'];
        if($row['status']==='declined') $displayStatus = 'declined';
        elseif($row['status']==='cancelled') $displayStatus = 'cancelled';
        elseif($row['status']==='completed') $displayStatus = 'completed';
        echo '<td style="vertical-align: middle; text-align: center;"><span class="status '.htmlspecialchars($row['status']).'">'.ucfirst($displayStatus).'</span></td>';
        echo '<td style="vertical-align: middle; text-align: left;">'.htmlspecialchars($row['dateRequested']).'</td>';
        echo '<td style="vertical-align: middle; text-align: left;">'.htmlspecialchars($row['document']).'</td>';
        echo '<td style="vertical-align: middle; text-align: left;">'.htmlspecialchars($row['purpose'] ?? 'N/A').'</td>';
        echo '<td style="vertical-align: middle; text-align: left;">'.htmlspecialchars($row['reason'] ?? 'N/A').'</td>';
        echo '<td class="action-buttons" style="vertical-align: middle; text-align: center;">';
        if(($row['status']==='declined' || $row['status']==='cancelled') && !empty($row['reason'])){
            echo '<button class="edit-btn reason-btn" data-reason="'.htmlspecialchars($row['reason'],ENT_QUOTES).'" data-id="'.htmlspecialchars($row['reqId']).'">View Reason</button>';
        }elseif($row['status']==='completed'){
            echo '<button class="edit-btn" disabled>Completed</button>';
        }else{
            echo '<button class="edit-btn" disabled>No Actions</button>';
        }
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7" style="text-align:center;">No archived requests found</td></tr>';
}
?>
                </table>
            </div>
        </div>
    </div>

    <!-- Reason Modal -->
    <div id="reasonModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="reasonModalTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="reasonModalTitle">Request Details</h2>
                <button class="close-modal" id="reasonClose" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="request-details">
                    <p><strong>Request ID:</strong> <span id="r-requestId"></span></p>
                    <p><strong>Document:</strong> <span id="r-document"></span></p>
                    <p><strong>Purpose:</strong> <span id="r-purpose"></span></p>
                    <p><strong>Date Requested:</strong> <span id="r-date"></span></p>
                    <p><strong>Status:</strong> <span id="r-status"></span></p>
                </div>
                <div class="reason-section">
                    <h4>Reason:</h4>
                    <p id="r-reason"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="archive.js"></script>
    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const clearFilter = document.getElementById('clearFilter');
            
            // Set current filter value if exists
            const urlParams = new URLSearchParams(window.location.search);
            const currentStatus = urlParams.get('status');
            if (currentStatus) {
                statusFilter.value = currentStatus;
            }
            
            // Handle filter change
            statusFilter.addEventListener('change', function() {
                const currentUrl = new URL(window.location);
                if (this.value) {
                    currentUrl.searchParams.set('status', this.value);
                } else {
                    currentUrl.searchParams.delete('status');
                }
                window.location.href = currentUrl.toString();
            });
            
            // Handle clear filter
            clearFilter.addEventListener('click', function() {
                statusFilter.value = '';
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.delete('status');
                window.location.href = currentUrl.toString();
            });
        });
    </script>
</body>
</html>
