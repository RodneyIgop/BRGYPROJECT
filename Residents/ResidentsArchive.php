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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive - Barangay New Era</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="ResidentsTrackRequest.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="logo">
                <img src="../images/brgylogo.png" alt="Barangay Logo" class="logo-img">
                <div class="logo-text">
                    <h2>BARANGAY NEW ERA</h2>   
                </div>
            </div>
            <nav>
                 <ul>
                    <li><a href="ResidentsIndex.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="ResidentsProfile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="ResidentsRequestDocu.php"><i class="fas fa-file-alt"></i> Document Request</a></li>
                    <li><a href="residentstrackrequest.php"><i class="fas fa-list"></i> My Request</a></li>
                    <li class="active"><a href="#"><i class="fas fa-archive"></i> Archive</a></li>
                    <li><a href="residentlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Archive</h1>
            <p>View your cancelled and declined document requests</p>
            
            <div class="requests-table">
                <table>
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Status</th>
                            <th>Date Requested</th>
                            <th>Document Type</th>
                            <th>Purpose</th>
                            <th>Reason</th>
                            <th style = "margin-left: 2em;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
// Debug: Show current session values
error_log("DEBUG: uid_string = " . var_export($uid_string, true));
error_log("DEBUG: numeric_user_id = " . var_export($numeric_user_id, true));
error_log("DEBUG: fullname = " . var_export($fullname, true));

// Use the numeric user_id for filtering
$effective_user_id = $numeric_user_id ?? $user_id;

// Query to get archived/declined/cancelled requests
$sql = "SELECT ArchiveID AS reqId, '' AS user_id, fullname, documenttype AS document, daterequested AS dateRequested, '' AS purpose, status, reason, '' AS notes FROM archivetbl WHERE fullname = ? AND (status = 'declined' OR status = 'cancelled')
        UNION ALL
        SELECT id AS reqId, user_id, fullname, document_type AS document, date_requested AS dateRequested, purpose, status, notes, '' AS reason FROM pending_requests WHERE (fullname = ? OR user_id = ?) AND status = 'declined'
        ORDER BY dateRequested DESC";

// Debug: Show the SQL query
error_log("DEBUG: SQL = " . $sql);

$query = $conn->prepare($sql);
$query->bind_param('sss', $fullname, $fullname, $effective_user_id);

// Debug: Show bound parameters
error_log("DEBUG: Bound params - fullname: $fullname, effective_user_id: $effective_user_id");

$query->execute();
$rows = $query->get_result();

// Debug: Show number of results
error_log("DEBUG: Number of rows found: " . ($rows ? $rows->num_rows : 0));
if ($rows && $rows->num_rows > 0) {
    while ($row = $rows->fetch_assoc()) {
        echo '<tr>';
        echo '<td>'.htmlspecialchars($row['fullname'] ?? $fullname).'</td>';
        $displayStatus = $row['status'];
        if($row['status']==='declined') $displayStatus = 'declined';
        elseif($row['status']==='cancelled') $displayStatus = 'cancelled';
        echo '<td><span class="status '.htmlspecialchars($row['status']).'">'.ucfirst($displayStatus).'</span></td>';
        echo '<td>'.htmlspecialchars($row['dateRequested']).'</td>';
        echo '<td>'.htmlspecialchars($row['document']).'</td>';
        echo '<td>'.htmlspecialchars($row['purpose'] ?? 'N/A').'</td>';
        echo '<td>'.htmlspecialchars($row['reason'] ?? 'N/A').'</td>';
        echo '<td class="action-buttons">';
        if(($row['status']==='declined' || $row['status']==='cancelled') && !empty($row['reason'])){
            echo '<button class="edit-btn reason-btn" data-reason="'.htmlspecialchars($row['reason'],ENT_QUOTES).'" data-id="'.htmlspecialchars($row['reqId']).'">View Reason</button>';
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
    <div id="reasonModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);align-items:center;justify-content:center;z-index:3000;">
        <div class="modal-content" style="background:#fff;padding:30px 40px;border-radius:8px;max-width:500px;width:90%;position:relative;box-shadow:0 4px 10px rgba(0,0,0,.1);">
            <span class="close" id="reasonClose" style="position:absolute;top:14px;right:18px;font-size:24px;font-weight:bold;color:#00386b;cursor:pointer;">&times;</span>
            <h2 style="color:#dc3545;">Request Declined</h2>
            <p><strong>Request ID:</strong> <span id="r-requestId"></span></p>
            <p><strong>Document:</strong> <span id="r-document"></span></p>
            <p><strong>Purpose:</strong> <span id="r-purpose"></span></p>
            <p><strong>Date Requested:</strong> <span id="r-date"></span></p>
            <hr style="margin:15px 0;">
            <p><strong>Reason for Declination:</strong></p>
            <p id="r-reason" style="font-style:italic; font-weight: bold; color:#dc3545; background:#f8d7da; padding:10px; border-radius:4px;"></p>
        </div>
    </div>

    <script>
    // Reason Modal functionality
    document.addEventListener('click',function(ev){
        const btn = ev.target.closest('.reason-btn');
        if(!btn) return;
        ev.preventDefault();
        document.getElementById('r-requestId').textContent = btn.dataset.id;
        const row = btn.closest('tr');
        document.getElementById('r-document').textContent = row ? row.children[3].textContent : '';
        document.getElementById('r-purpose').textContent = row ? row.children[4].textContent : '';
        document.getElementById('r-date').textContent = row ? row.children[2].textContent : '';
        document.getElementById('r-reason').textContent = btn.dataset.reason;
        document.getElementById('reasonModal').style.display='flex';
    });
    document.getElementById('reasonClose').addEventListener('click',()=>document.getElementById('reasonModal').style.display='none');

    // Close modal when clicking outside
    window.addEventListener('click',e=>{
        if(e.target===document.getElementById('reasonModal')) document.getElementById('reasonModal').style.display='none';
    });
    </script>
</body>
</html>
