<?php
session_start();
// verify admin login if needed
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

require_once '../Connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'release_print') {
    $requestId = isset($_POST['RequestID']) && is_numeric($_POST['RequestID']) ? (int)$_POST['RequestID'] : 0;
    $validId = '';
    $file = $_FILES['valid_id'] ?? null;

    if (is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $originalName = (string)($file['name'] ?? '');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed, true)) {
            $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'valid_ids';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $filename = 'valid_id_' . $requestId . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            if (is_uploaded_file($file['tmp_name'] ?? '') && @move_uploaded_file($file['tmp_name'], $targetPath)) {
                $validId = 'uploads/valid_ids/' . $filename;
            }
        }
    }

    if ($requestId > 0 && $validId !== '') {
        $status = 'completed';
        $action = 'Completed';
        $stmt = $conn->prepare('UPDATE approved SET status = ?, action = ? WHERE RequestID = ?');
        if ($stmt) {
            $stmt->bind_param('ssi', $status, $action, $requestId);

            $stmt->execute();
            $stmt->close();
        }

        header('Location: adminreleased.php?print=1&id=' . $requestId . '&valid_id=' . rawurlencode($validId));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Requests</title>
    <link rel="stylesheet" href="adminapproved.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
   <div class="sidebar">
        <div class="sidebar-header">
            <img src="../images/brgylogo.png" alt="Logo">
            <h2>BARANGAY NEW ERA</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="adminIndex.php" class="sidebar-link"> <img src="../images/home.png" alt="">Home</a>
            <a href="adminProfile.php" class="sidebar-link"> <img src="../images/user.png" alt="">Profile</a>
            <details class="sidebar-dropdown" open>
                <summary><img src="../images/list.png" alt="">Request Lists <img src="../images/down.png" alt=""></summary>
                <a href="adminpending.php" class="submenu-link"> <img src="../images/pending.png" alt="">Pending and for review</a>
                <a href="adminapproved.php" class="submenu-link active"> <img src="../images/approved.png" alt="">Approved and For Pick Up</a>
                <a href="adminreleased.php" class="submenu-link"> <img src="../images/complete.png" alt="">Signed and released</a>
            </details>
            <a href="adminArchive.php" class="sidebar-link"> <img src="../images/archive.png" alt="">Archive</a>
            <a href="adminAnnouncements.php" class="sidebar-link"> <img src="../images/marketing.png" alt=""> Announcements</a>
            <a href="adminMessages.php" class="sidebar-link"> <img src="../images/email.png" alt="">Messages</a>
            <a href="adminResidents.php" class="sidebar-link"> <img src="../images/residents.png" alt="residents"> Residents</a>
            <button onclick="showLogoutPopup()" class="sidebar-link"> <img src="../images/logout.png" alt="">Logout</button>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Document Request</h1>
        </div>
        <h3>Manage Pending Request</h3>
        <div class="table-filters">
            <input type="text" id="approvedSearch" placeholder="Search">
            <input type="text" id="approvedDateFilter" placeholder="Date">
        </div>
        <div class="requests-table">
            <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Fullname</th>
                    <th>Document Type</th>
                    <th>Purpose</th>
                    <th>Date Requested</th>
                            <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="pendingBody">
                <?php
                $sql = "SELECT * FROM approved WHERE status IN ('approved','under_review')";
                $result = $conn->query($sql);
                if($result && $result->num_rows>0){
                    while($row = $result->fetch_assoc()){
                        // try fetch user details
                        $details = ['Address'=>'','Birthdate'=>'','Age'=>'','ContactNumber'=>'','email'=>''];
                        $userIdValue = (int)($row['userId'] ?? ($row['UserID'] ?? ($row['userID'] ?? ($row['userid'] ?? 0))));

                        if ($userIdValue > 0) {
                            $detailsStmt0 = $conn->prepare("SELECT Address,Birthdate,Age,ContactNumber,email FROM usertbl WHERE UserID = ? LIMIT 1");
                            if ($detailsStmt0) {
                                $detailsStmt0->bind_param('i', $userIdValue);
                                $detailsStmt0->execute();
                                $detailRes0 = $detailsStmt0->get_result();
                                if ($detailRes0 && $detailRes0->num_rows > 0) {
                                    $details = $detailRes0->fetch_assoc() ?: $details;
                                }
                                $detailsStmt0->close();
                            }
                        }

                        if (!array_filter($details)) {
                            $parts = explode(' ', (string)($row['fullname'] ?? ''));
                            $firstName = $parts[0] ?? '';
                            $lastName = $parts[count($parts)-1] ?? '';
                            $detailsStmt = $conn->prepare("SELECT Address,Birthdate,Age,ContactNumber,email FROM usertbl WHERE FirstName = ? AND LastName = ? LIMIT 1");
                            if ($detailsStmt) {
                                $detailsStmt->bind_param('ss',$firstName,$lastName);
                                $detailsStmt->execute();
                                $detailRes = $detailsStmt->get_result();
                                if ($detailRes && $detailRes->num_rows > 0) {
                                    $details = $detailRes->fetch_assoc() ?: $details;
                                }
                                $detailsStmt->close();
                            }
                        }

                        if (!array_filter($details)) {
                            $fullNameNormalized = preg_replace('/\s+/', ' ', trim((string)($row['fullname'] ?? '')));
                            $detailsStmt2 = $conn->prepare("SELECT Address,Birthdate,Age,ContactNumber,email FROM usertbl WHERE TRIM(CONCAT_WS(' ', FirstName, NULLIF(MiddleName,''), LastName, NULLIF(Suffix,''))) = ? LIMIT 1");
                            if ($detailsStmt2) {
                                $detailsStmt2->bind_param('s', $fullNameNormalized);
                                $detailsStmt2->execute();
                                $detailRes2 = $detailsStmt2->get_result();
                                if ($detailRes2 && $detailRes2->num_rows > 0) {
                                    $details = $detailRes2->fetch_assoc() ?: $details;
                                }
                                $detailsStmt2->close();
                            }
                        }

                        echo '<tr>'; 
                        echo '<td>'.htmlspecialchars($row["RequestID"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["fullname"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["documenttype"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["purpose"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["dateRequested"]).'</td>';
                        echo '<td><span style="background:#28a745;color:#fff;padding:4px 10px;border-radius:12px;font-size:13px;">For Pick Up</span></td>';
                        echo '<td>';
                        echo '<a href="#requestModal" class="action-icon view-btn" data-id="'.$row["RequestID"].'" data-fullname="'.htmlspecialchars($row["fullname"],ENT_QUOTES).'" data-date="'.htmlspecialchars($row["dateRequested"],ENT_QUOTES).'" data-doc="'.htmlspecialchars($row["documenttype"],ENT_QUOTES).'" data-purpose="'.htmlspecialchars($row["purpose"],ENT_QUOTES).'" data-address="'.htmlspecialchars($details['Address'],ENT_QUOTES).'" data-birthdate="'.htmlspecialchars($details['Birthdate'],ENT_QUOTES).'" data-age="'.htmlspecialchars($details['Age'],ENT_QUOTES).'" data-contact="'.htmlspecialchars($details['ContactNumber'],ENT_QUOTES).'" data-email="'.htmlspecialchars($details['email'],ENT_QUOTES).'" data-label="View file"><img src="../images/view.png" alt="view" style="width:28px;height:28px;"></a>';
                        echo '<a href="#" class="action-icon print-btn" data-id="'.$row["RequestID"].'" data-fullname="'.htmlspecialchars($row["fullname"],ENT_QUOTES).'" data-date="'.htmlspecialchars($row["dateRequested"],ENT_QUOTES).'" data-doc="'.htmlspecialchars($row["documenttype"],ENT_QUOTES).'" data-purpose="'.htmlspecialchars($row["purpose"],ENT_QUOTES).'" data-address="'.htmlspecialchars($details['Address'],ENT_QUOTES).'" data-birthdate="'.htmlspecialchars($details['Birthdate'],ENT_QUOTES).'" data-age="'.htmlspecialchars($details['Age'],ENT_QUOTES).'" data-contact="'.htmlspecialchars($details['ContactNumber'],ENT_QUOTES).'" data-email="'.htmlspecialchars($details['email'],ENT_QUOTES).'" data-label="Print file"><img src="../images/print.png" alt="view" style="width:28px;height:28px; margin-left: 1em;"></a>';
                        echo '<a href="#declineModal" class="action-icon reject-btn" data-id="'.$row["RequestID"].'" data-label="Reject"><img src="../images/reject.png" alt="reject" style="width:28px;height:28px; margin-left: 1em;"></a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7" style="text-align:center;">No pending requests</td></tr>';
                }?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Decline Modal -->
    <div id="declineModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close decline-close">&times;</span>
            <div style="text-align:center;">
                <img src="../images/decline.png" alt="decline" style="width:60px;height:60px;margin-bottom:10px;">
                <h3>This document request is declined because</h3>
            </div>
            <form id="declineForm" method="POST" action="adminreject.php" style="margin-top:15px;">
                <input type="hidden" name="RequestID" id="declinePendingId">
                <select name="reason" id="declineReason" required style="width:100%;padding:8px 10px;">
                    <option value="" disabled selected>Select a reason for declining the request</option>
                    <option value="Incomplete requirements">Incomplete requirements</option>
                    <option value="Invalid information">Invalid information</option>
                    <option value="Duplicate request">Duplicate request</option>
                    <option value="Other">Other</option>
                </select>
                <div style="margin-top:20px;text-align:right;">
                    <button type="submit" id="declineBtn" class="btn-ok" style="background:#00386b;color:#fff;padding:8px 20px;border:none;border-radius:4px;cursor:pointer;">OK</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div id="requestModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Request Details</h2>
            <p><strong>Request ID:</strong> <span id="m-requestId"></span></p>
            <p><strong>Full name:</strong> <span id="m-fullname"></span></p>
            <p><strong>Date requested:</strong> <span id="m-date"></span></p>
            <p><strong>Document request:</strong> <span id="m-doc"></span></p>
            <p><strong>Purpose:</strong> <span id="m-purpose"></span></p>
            <p><strong>Notes:</strong> <span id="m-notes"></span></p>
            <hr style = "height:3px; background-color: gray;">
            <br>
            <H2>User Details</H2>
            <p><strong>Resident:</strong> <span id="m-ufullname"></span></p>
            <p><strong>Address:</strong> <span id="m-address"></span></p>
            <p><strong>Birthdate:</strong> <span id="m-birthdate"></span></p>
            <p><strong>Age:</strong> <span id="m-age"></span></p>
            <p><strong>Contact Number:</strong> <span id="m-contact"></span></p>
            <p><strong>Email:</strong></p> <span id="m-email"></span>

        </div>
    </div>

    <div id="printModal" class="modal" style="display:none;">
        <div class="modal-content print-modal-content">
            <span class="close" id="printClose">&times;</span>
            <h2>Print Document</h2>

            <div class="print-preview-wrap">
                <iframe id="printPreviewFrame" title="Print preview" class="print-preview-frame"></iframe>
            </div>

            <form id="printForm" method="POST" action="adminapproved.php" enctype="multipart/form-data" style="margin-top:15px;">
                <input type="hidden" name="action" value="release_print">
                <input type="hidden" name="RequestID" id="printRequestId">

                <label for="validIdInput" style="display:block;font-weight:600;color:#00386b;margin:12px 0 6px;">Valid ID</label>
                <input type="file" name="valid_id" id="validIdInput" accept="image/*" required style="width:100%;padding:10px 12px;border:1px solid #d9d9d9;border-radius:6px;">

                <hr style = "height:3px; background-color: gray; margin-top:16px;">
                <br>
                <H2>User Details</H2>
                <p><strong>Resident:</strong> <span id="p-ufullname"></span></p>
                <p><strong>Address:</strong> <span id="p-address"></span></p>
                <p><strong>Birthdate:</strong> <span id="p-birthdate"></span></p>
                <p><strong>Age:</strong> <span id="p-age"></span></p>
                <p><strong>Contact Number:</strong> <span id="p-contact"></span></p>
                <p><strong>Email:</strong></p> <span id="p-email"></span>

                <div style="margin-top:20px; text-align:right;">
                    <button type="button" id="printCancelBtn" style="background:#6c757d;color:#fff;border:none;padding:8px 16px;border-radius:4px;margin-right:8px;cursor:pointer;">Cancel</button>
                    <button type="submit" id="printConfirmBtn" style="background:#28a745;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Print</button>
                </div>
            </form>
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
            // Redirect to admin login page (this will end the session)
            window.location.href = 'adminLogin.php';
        }

        // Close popup on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLogoutPopup();
            }
        });
    </script>

    <script src="adminapproved.js"> </script>
</body>
</html>