<?php
session_start();
// verify admin login if needed
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Requests</title>
    <link rel="stylesheet" href="adminpending.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
   <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../images/brgylogo.png" alt="Logo">
            <h2>BARANGAY NEW ERA</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="adminIndex.php"> <img src="../images/home.png" alt="">Home</a>
            <a href="adminProfile.php"> <img src="../images/user.png" alt="">Profile</a>
            <details class="sidebar-dropdown">
                <summary><img src="../images/list.png" alt="">Request Lists <img src="../images/down.png" alt=""></summary>
                <a href="adminpending.php" class="submenu-link active"> <img src="../images/pending.png" alt="">Pending and for review</a>
                <a href="adminapproved.php" class="submenu-link"> <img src="../images/approved.png" alt="">Approved and For Pick Up</a>
                <a href="adminreleased.php" class="submenu-link"> <img src="../images/complete.png" alt="">Signed and released</a>
            </details>
            <a href="adminArchive.php"> <img src="../images/archive.png" alt="">Archive</a>
            <a href="adminAnnouncements.php"> <img src="../images/marketing.png" alt=""> Announcements</a>
            <a href="adminMessages.php"> <img src="../images/email.png" alt="">Messages</a>
            <a href="adminResidents.php"> <img src="../images/residents.png" alt="">Residents</a>
            <button onclick="logout()" style="margin-top:auto;"> <img src="../images/logout.png" alt="">Logout</button>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Document Request</h1>
        </div>
        <h3>Manage Pending Request</h3>
        <div class="table-filters">
            <input type="text" id="pendingSearch" placeholder="Search">
            <input type="text" id="pendingDateFilter" placeholder="Date">
        </div>
        <div class="requests-table">
            <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Fullname</th>
                    <th>Document Type</th>
                    <th>Date Requested</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="pendingBody">
                <?php
                require_once '../Connection/conn.php';
                $sql = "SELECT * FROM pending_requests WHERE status='pending'";
                $result = $conn->query($sql);
                if($result && $result->num_rows>0){
                    while($row = $result->fetch_assoc()){
                        // fetch user details
                        $details = ['Address'=>'','Birthdate'=>'','Age'=>'','ContactNumber'=>'','email'=>''];
                        $userIdValue = (int)($row['userId'] ?? ($row['UserID'] ?? ($row['userID'] ?? ($row['userid'] ?? 0))));

                        $userStmt = $conn->prepare("SELECT Address, Birthdate, Age, ContactNumber, email FROM usertbl WHERE UserID = ? LIMIT 1");
                        if ($userStmt && $userIdValue > 0) {
                            $userStmt->bind_param('i', $userIdValue);
                            $userStmt->execute();
                            $uRes = $userStmt->get_result();
                            if ($uRes && $uRes->num_rows > 0) {
                                $details = $uRes->fetch_assoc() ?: $details;
                            }
                            $userStmt->close();
                        }

                        if (!array_filter($details)) {
                            // second fallback by splitting firstname lastname
                            $parts = explode(' ', (string)($row['fullname'] ?? ''));
                            $firstName = $parts[0] ?? '';
                            $lastName  = $parts[count($parts)-1] ?? '';
                            $userStmt2 = $conn->prepare("SELECT Address, Birthdate, Age, ContactNumber, email FROM usertbl WHERE FirstName = ? AND LastName = ? LIMIT 1");
                            if ($userStmt2) {
                                $userStmt2->bind_param('ss', $firstName, $lastName);
                                $userStmt2->execute();
                                $uRes2 = $userStmt2->get_result();
                                if ($uRes2 && $uRes2->num_rows > 0) {
                                    $details = $uRes2->fetch_assoc() ?: $details;
                                }
                                $userStmt2->close();
                            }
                        }

                        if (!array_filter($details)) {
                            $fullNameNormalized = preg_replace('/\s+/', ' ', trim((string)($row['fullname'] ?? '')));
                            $userStmt3 = $conn->prepare("SELECT Address, Birthdate, Age, ContactNumber, email FROM usertbl WHERE TRIM(CONCAT_WS(' ', FirstName, NULLIF(MiddleName,''), LastName, NULLIF(Suffix,''))) = ? LIMIT 1");
                            if ($userStmt3) {
                                $userStmt3->bind_param('s', $fullNameNormalized);
                                $userStmt3->execute();
                                $uRes3 = $userStmt3->get_result();
                                if ($uRes3 && $uRes3->num_rows > 0) {
                                    $details = $uRes3->fetch_assoc() ?: $details;
                                }
                                $userStmt3->close();
                            }
                        }
                        echo '<tr>';
                        echo '<td>'.htmlspecialchars($row["id"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["fullname"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["document_type"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["date_requested"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["purpose"]).'</td>';
                        echo '<td><span style="background:#ffc107;color:#000;padding:4px 10px;border-radius:12px;font-size:13px;">Pending</span></td>';
                        echo '<td>';
                        echo '<a href="#requestModal" class="action-icon view-btn" data-id="'.$row["id"].'" data-fullname="'.htmlspecialchars($row["fullname"],ENT_QUOTES).'" data-date="'.htmlspecialchars($row["date_requested"],ENT_QUOTES).'" data-doc="'.htmlspecialchars($row["document_type"],ENT_QUOTES).'" data-purpose="'.htmlspecialchars($row["purpose"],ENT_QUOTES).'" data-notes="'.htmlspecialchars($row["notes"] ?? '',ENT_QUOTES).'" data-address="'.htmlspecialchars($details['Address'],ENT_QUOTES).'" data-birthdate="'.htmlspecialchars($details['Birthdate'],ENT_QUOTES).'" data-age="'.htmlspecialchars($details['Age'],ENT_QUOTES).'" data-contact="'.htmlspecialchars($details['ContactNumber'],ENT_QUOTES).'" data-email="'.htmlspecialchars($details['email'],ENT_QUOTES).'" data-label="View file"><img src="../images/view.png" alt="view" style="width:28px;height:28px;"></a> ';
                        echo '<a href="#" class="action-icon reject-btn" data-id="'.$row["id"].'" data-label="Reject"><img src="../images/reject.png" alt="reject" style="width:28px;height:28px; margin-left: 1em;"></a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                }else{
                    echo '<tr><td colspan="7" style="text-align:center;">No pending requests</td></tr>';
                }
                ?>
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
                <input type="hidden" name="pendingId" id="declinePendingId">
                <select name="reason" id="declineReason" required style="width:100%;padding:8px 10px;">
                    <option value="" disabled selected>Select a reason for declining the request</option>
                    <option value="Incomplete requirements">Incomplete requirements</option>
                    <option value="Invalid information">Invalid information</option>
                    <option value="Duplicate request">Duplicate request</option>
                    <option value="Other">Other</option>
                </select>
                <div style="margin-top:20px;text-align:right;">
                    <button type="submit" class="btn-ok" style="background:#00386b;color:#fff;padding:8px 20px;border:none;border-radius:4px;cursor:pointer;">OK</button>
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

            <div style="margin-top:20px; text-align:right;">
                <button id="approveBtn" style="background:#28a745;color:#fff;border:none;padding:8px 16px;border-radius:4px;margin-right:8px;cursor:pointer;">Approve</button>
                <button id="cancelBtn" style="background:#6c757d;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Cancel</button>
            </div>
        </div>
    </div>

    <script src="adminpending.js"> </script>
</body>
</html>