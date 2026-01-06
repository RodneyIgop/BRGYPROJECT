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
    <title>My Request - Barangay New Era</title>
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
                    <li class="active"><a href="residentstrackrequest.php" aria-label="My Request"><i class="fas fa-list"></i> <span>My Request</span></a></li>
                    <li><a href="ResidentsArchive.php" aria-label="Archive"><i class="fas fa-archive"></i> <span>History</span></a></li>
                    <li class="logout"><a href="residentlogin.php" aria-label="Logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </div>
    <div class="container">
        <!-- Sidebar Navigation -->


        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1>My Request</h1>
                <p class="page-description">Track and manage your document requests</p>
            </div>
            
            <div class="requests-table">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Status</th>
                                <th>Date Requested</th>
                                <th>Document Type</th>
                                <th>Purpose</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
// Debug: Show current session values
error_log("DEBUG: uid_string = " . var_export($uid_string, true));
error_log("DEBUG: numeric_user_id = " . var_export($numeric_user_id, true));
error_log("DEBUG: fullname = " . var_export($fullname, true));

// Use the numeric user_id for pending_requests filtering
$effective_user_id = $numeric_user_id ?? $user_id;

// Modified query to show only active requests (exclude cancelled, declined, and completed)
$sql = "SELECT id AS reqId, user_id, fullname, document_type AS document, date_requested AS dateRequested, purpose, status, notes, '' AS reason FROM pending_requests WHERE (fullname = ? OR user_id = ?) AND status NOT IN ('cancelled', 'declined', 'completed')
        UNION ALL
        SELECT RequestID AS reqId, '' AS user_id, fullname, documenttype AS document, dateRequested, purpose, status, '' AS notes, '' AS reason FROM approved WHERE fullname = ? AND status != 'completed'
        ORDER BY dateRequested DESC";

// Debug: Show the SQL query
error_log("DEBUG: SQL = " . $sql);

$query = $conn->prepare($sql);
$query->bind_param('sss', $fullname, $effective_user_id, $fullname);

// Debug: Show bound parameters
error_log("DEBUG: Bound params - effective_user_id: $effective_user_id, fullname: $fullname");

$query->execute();
$rows = $query->get_result();

// Debug: Show number of results
error_log("DEBUG: Number of rows found: " . ($rows ? $rows->num_rows : 0));
if ($rows && $rows->num_rows > 0) {
    while ($row = $rows->fetch_assoc()) {
        echo '<tr>';
        echo '<td data-label="Full Name">'.htmlspecialchars($row['fullname'] ?? $fullname).'</td>';
        $displayStatus = $row['status'];
        if($row['status']==='pending') $displayStatus = 'in process';
        elseif($row['status']==='approved' || $row['status']==='under_review') $displayStatus = 'approved and ready for pick up';
        elseif($row['status']==='completed') $displayStatus = 'completed';
        elseif($row['status']==='declined') $displayStatus = 'declined';
        echo '<td data-label="Status"><span class="status '.htmlspecialchars($row['status']).'">'.ucfirst($displayStatus).'</span></td>';
        echo '<td data-label="Date Requested">'.htmlspecialchars($row['dateRequested']).'</td>';
        echo '<td data-label="Document Type">'.htmlspecialchars($row['document']).'</td>';
        echo '<td data-label="Purpose">'.htmlspecialchars($row['purpose']).'</td>';
        echo '<td data-label="Notes">'.htmlspecialchars($row['notes'] ?? '').'</td>';
        echo '<td class="action-buttons" data-label="Actions">';
        if($row['status']==='declined'){
            echo '<button class="edit-btn reason-btn" data-reason="'.htmlspecialchars($row['reason'],ENT_QUOTES).'" data-id="'.htmlspecialchars($row['reqId']).'">View Reason</button>';
        }elseif($row['status']==='pending'){
            echo '<button class="edit-btn edit-request-btn" data-id="'.htmlspecialchars($row['reqId']).'" data-document="'.htmlspecialchars($row['document'],ENT_QUOTES).'" data-purpose="'.htmlspecialchars($row['purpose'],ENT_QUOTES).'" data-notes="'.htmlspecialchars($row['notes'] ?? '',ENT_QUOTES).'">Edit Request</button> ';
            echo '<button class="cancel-btn cancel-request-btn" data-id="'.htmlspecialchars($row['reqId']).'">Cancel</button>';
        }else{
            echo '<button class="edit-btn" disabled>Edit Request</button> ';
            echo '<button class="cancel-btn" disabled>Cancel</button>';
        }
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7" style="text-align:center;">No requests found</td></tr>';
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
                <h2 id="reasonModalTitle">Request Declined</h2>
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
                </div>
                <div class="reason-section">
                    <h4>Reason for Declination:</h4>
                    <p id="r-reason"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Request Modal -->
    <div id="editModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="editModalTitle">Edit Request</h2>
                <button class="close-modal" id="editClose" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm" novalidate>
                    <input type="hidden" id="editRequestId">
                    <div class="form-group">
                        <label for="editDocumentType">Document Type</label>
                        <select id="editDocumentType" required>
                            <option value="">Select Document Type</option>
                            <option value="Barangay Clearance">Barangay Clearance</option>
                            <option value="Barangay Indigency">Barangay Indigency</option>
                            <option value="Certificate of Residency">Certificate of Residency</option>
                            <option value="Business Permit">Business Permit</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editPurpose">Purpose</label>
                        <select id="editPurpose" required>
                            <option value="">Select Purpose</option>
                            <option value="Employment">Employment</option>
                            <option value="Scholarship">Scholarship</option>
                            <option value="Business">Business</option>
                            <option value="Bank Requirement">Bank Requirement</option>
                            <option value="Government Requirement">Government Requirement</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editNotes">Notes</label>
                        <textarea id="editNotes" placeholder="Additional notes (optional)"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="editCancel">Cancel</button>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i>
                            <span>Update Request</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Request Modal -->
    <div id="cancelModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="cancelModalTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="cancelModalTitle">Cancel Request</h2>
                <button class="close-modal" id="cancelClose" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="cancel-message">
                    <p>Are you sure you want to cancel this request? This action cannot be undone.</p>
                    <p><strong>Request ID:</strong> <span id="cancelRequestId"></span></p>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelNo">No</button>
                    <button type="button" class="btn-danger" id="cancelYes">
                        <i class="fas fa-trash"></i>
                        <span>Yes, Cancel</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="trackrequest.js"></script>
</body>
</html>