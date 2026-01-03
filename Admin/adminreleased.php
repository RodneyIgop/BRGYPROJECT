
<?php
session_start();
// verify admin login if needed
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

require_once '../Connection/conn.php';

$isPreview = isset($_GET['preview']) && $_GET['preview'] == '1';
$isPrint = isset($_GET['print']) && $_GET['print'] == '1';

if ($isPreview || $isPrint) {
    $requestId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($requestId <= 0) {
        http_response_code(400);
        echo 'Invalid request.';
        exit;
    }

    $stmt = $conn->prepare('SELECT * FROM approved WHERE RequestID = ? LIMIT 1');
    if (!$stmt) {
        http_response_code(500);
        echo 'Database error.';
        exit;
    }
    $stmt->bind_param('i', $requestId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res && $res->num_rows > 0 ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        http_response_code(404);
        echo 'Request not found.';
        exit;
    }

    $validId = (string)($_GET['valid_id'] ?? '');
    if ($isPrint && trim($validId) === '') {
        header('Location: adminapproved.php');
        exit;
    }
    $fullname = (string)($row['fullname'] ?? '');
    $documenttype = (string)($row['documenttype'] ?? '');
    $purpose = (string)($row['purpose'] ?? '');
    $dateRequested = (string)($row['dateRequested'] ?? '');

    $details = ['Address'=>'','Birthdate'=>'','Age'=>'','ContactNumber'=>'','email'=>''];
    $parts = explode(' ', trim($fullname));
    $firstName = $parts[0] ?? '';
    $lastName = $parts[count($parts)-1] ?? '';
    if ($firstName !== '' && $lastName !== '') {
        $detailsStmt = $conn->prepare("SELECT Address,Birthdate,Age,ContactNumber,email FROM usertbl WHERE FirstName = ? AND LastName = ? LIMIT 1");
        if ($detailsStmt) {
            $detailsStmt->bind_param('ss', $firstName, $lastName);
            $detailsStmt->execute();
            $detailRes = $detailsStmt->get_result();
            if ($detailRes && $detailRes->num_rows > 0) {
                $details = $detailRes->fetch_assoc() ?: $details;
            }
            $detailsStmt->close();
        }
    }

    $autoPrintScript = $isPrint ? "<script>window.addEventListener('load',()=>{window.print();});</script>" : '';

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Print Document</title>
        <style>
            body{font-family:Arial,Helvetica,sans-serif;background:#fff;color:#000;margin:0;padding:0;}
            .page{max-width:800px;margin:0 auto;padding:40px 50px;}
            .header{text-align:center;}
            .header h1{margin:0;font-size:20px;letter-spacing:.5px;}
            .header h2{margin:8px 0 0;font-size:16px;font-weight:600;}
            .meta{margin-top:24px;font-size:14px;}
            .meta .row{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;}
            .meta .row div{min-width:240px;}
            .title{margin:26px 0 0;text-align:center;font-weight:700;font-size:18px;text-transform:uppercase;}
            .content{margin-top:24px;font-size:14px;line-height:1.8;text-align:justify;}
            .sig{margin-top:60px;display:flex;justify-content:flex-end;}
            .sig .box{text-align:center;min-width:260px;}
            .sig .line{margin-top:50px;border-top:1px solid #000;padding-top:6px;}
            @media print{body{background:#fff;} .page{padding:0 0 0 0; margin:0; max-width:none;}}
        </style>
    </head>
    <body>
        <div class="page">
            <div class="header">
                <h1>BARANGAY NEW ERA</h1>
                <h2>Dasmariñas City, Cavite</h2>
            </div>

            <div class="meta">
                <div class="row">
                    <div><strong>Request ID:</strong> <?php echo htmlspecialchars((string)$requestId); ?></div>
                    <div><strong>Date Requested:</strong> <?php echo htmlspecialchars($dateRequested); ?></div>
                </div>
                <div class="row" style="margin-top:8px;">
                    <div><strong>Resident:</strong> <?php echo htmlspecialchars($fullname); ?></div>
                    <div><strong>Valid ID:</strong> <?php echo htmlspecialchars($validId); ?></div>
                </div>
                <div class="row" style="margin-top:8px;">
                    <div><strong>Address:</strong> <?php echo htmlspecialchars((string)($details['Address'] ?? '')); ?></div>
                    <div><strong>Contact:</strong> <?php echo htmlspecialchars((string)($details['ContactNumber'] ?? '')); ?></div>
                </div>
            </div>

            <div class="title"><?php echo htmlspecialchars($documenttype); ?></div>
            <div class="content">
                This is to certify that <strong><?php echo htmlspecialchars($fullname); ?></strong>, a resident of Barangay New Era,
                requested a <strong><?php echo htmlspecialchars($documenttype); ?></strong> for the purpose of
                <strong><?php echo htmlspecialchars($purpose); ?></strong>.
                <br><br>
                Issued this day upon request for whatever legal purpose it may serve.
            </div>

            <div class="sig">
                <div class="box">
                    <div class="line">Authorized Signature</div>
                </div>
            </div>
        </div>
        <?php echo $autoPrintScript; ?>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signed and Released</title>
    <link rel="stylesheet" href="adminreleased.css">
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
                <a href="adminapproved.php" class="submenu-link"> <img src="../images/approved.png" alt="">Approved and For Pick Up</a>
                <a href="adminreleased.php" class="submenu-link active"> <img src="../images/complete.png" alt="">Signed and released</a>
            </details>
            <a href="adminArchive.php" class="sidebar-link"> <img src="../images/archive.png" alt="">Archive</a>
            <a href="adminAnnouncements.php" class="sidebar-link"> <img src="../images/marketing.png" alt=""> Announcements</a>
            <a href="adminMessages.php" class="sidebar-link"> <img src="../images/email.png" alt="">Messages</a>
            <a href="adminResidents.php" class="sidebar-link"> <img src="../images/residents.png" alt="residents"> Residents</a>
            <button onclick="logout()" class="sidebar-link"> <img src="../images/logout.png" alt="">Logout</button>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Document Request</h1>
        </div>
        <h3>Signed and Released</h3>
        <div class="table-filters">
            <input type="text" id="releasedSearch" placeholder="Search">
            <input type="text" id="releasedDateFilter" placeholder="Date">
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
                // NOTE: In your codebase, adminapprove_query.php inserts approved requests with status='approved'.
                // If you use a different status value for released records, update this WHERE clause.
                $sql = "SELECT * FROM approved WHERE status='completed'";
                $result = $conn->query($sql);
                if($result && $result->num_rows>0){
                    while($row = $result->fetch_assoc()){
                        $parts = explode(' ', $row['fullname']);
                        $firstName = $parts[0] ?? '';
                        $lastName = $parts[count($parts)-1] ?? '';
                        $detailsStmt = $conn->prepare("SELECT Address,Birthdate,Age,ContactNumber,email FROM usertbl WHERE FirstName = ? AND LastName = ? LIMIT 1");
                        $detailsStmt->bind_param('ss',$firstName,$lastName);
                        $detailsStmt->execute();
                        $detailRes = $detailsStmt->get_result();
                        $details = $detailRes && $detailRes->num_rows>0 ? $detailRes->fetch_assoc() : ['Address'=>'','Birthdate'=>'','Age'=>'','ContactNumber'=>'','email'=>''];
                        $detailsStmt->close();

                        echo '<tr>';
                        echo '<td>'.htmlspecialchars($row["RequestID"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["fullname"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["documenttype"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["purpose"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["dateRequested"]).'</td>';
                        echo '<td><span style="background:#6c757d;color:#fff;padding:4px 10px;border-radius:12px;font-size:13px;">Released</span></td>';
                        echo '<td>';
                        echo '<a href="#requestModal" class="action-icon view-btn" data-id="'.$row["RequestID"].'" data-fullname="'.htmlspecialchars($row["fullname"],ENT_QUOTES).'" data-date="'.htmlspecialchars($row["dateRequested"],ENT_QUOTES).'" data-doc="'.htmlspecialchars($row["documenttype"],ENT_QUOTES).'" data-purpose="'.htmlspecialchars($row["purpose"],ENT_QUOTES).'" data-address="'.htmlspecialchars($details['Address'],ENT_QUOTES).'" data-birthdate="'.htmlspecialchars($details['Birthdate'],ENT_QUOTES).'" data-age="'.htmlspecialchars($details['Age'],ENT_QUOTES).'" data-contact="'.htmlspecialchars($details['ContactNumber'],ENT_QUOTES).'" data-email="'.htmlspecialchars($details['email'],ENT_QUOTES).'" data-label="View file"><img src="../images/view.png" alt="view" style="width:28px;height:28px;"></a>';
                        echo '<a href="#" class="action-icon view-btn" data-id="'.$row["RequestID"].'" data-fullname="'.htmlspecialchars($row["fullname"],ENT_QUOTES).'" data-date="'.htmlspecialchars($row["dateRequested"],ENT_QUOTES).'" data-doc="'.htmlspecialchars($row["documenttype"],ENT_QUOTES).'" data-label="Print file"><img src="../images/print.png" alt="view" style="width:28px;height:28px; margin-left: 1em;"></a>';
                        echo '<a href="#declineModal" class="action-icon reject-btn" data-id="'.$row["RequestID"].'" data-label="Reject"><img src="../images/reject.png" alt="reject" style="width:28px;height:28px; margin-left: 1em;"></a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7" style="text-align:center;">No released requests</td></tr>';
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

    <script src="adminreleased.js"> </script>
</body>
</html>

