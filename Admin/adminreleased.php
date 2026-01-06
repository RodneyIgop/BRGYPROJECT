
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
    </head>
    <style>
        @page {
    size: A4;
    margin: 8mm;
}

body {
    font-family: 'Georgia', 'Times New Roman', serif;
    background: #fff;
    color: #000;
    margin: 0;
    padding: 0;
}

.page {
    width: 100%;
    max-width: 190mm;
    margin: 0 auto;
    padding: 25px 30px;
    background: #fff;
    border: 1px solid #2c5aa0;
    position: relative;
    min-height: 240mm;
    box-sizing: border-box;
}

.page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #2c5aa0 0%, #1e3a6f 50%, #2c5aa0 100%);
}

.header {
    text-align: center;
    margin-bottom: 25px;
    position: relative;
}

.logo-container {
    margin-bottom: 18px;
}

.logo-container img {
    max-width: 70px;
    height: auto;
    border-radius: 50%;
    border: 2px solid #2c5aa0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.header h1 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #1e3a6f;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.header h2 {
    margin: 4px 0 0;
    font-size: 13px;
    font-weight: 500;
    color: #2c5aa0;
}

.header h3 {
    margin: 3px 0 0;
    font-size: 11px;
    font-weight: 400;
    color: #666;
}

.official-document {
    margin: 20px 0;
    padding: 10px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    text-align: center;
}

.official-document .doc-title {
    font-size: 12px;
    font-weight: 700;
    color: #1e3a6f;
    text-transform: uppercase;
    margin-bottom: 3px;
}

.official-document .doc-number {
    font-size: 11px;
    color: #495057;
}

.title {
    margin: 30px 0 20px;
    text-align: center;
    font-weight: 700;
    font-size: 15px;
    color: #1e3a6f;
    text-transform: uppercase;
    border-bottom: 2px solid #2c5aa0;
    padding-bottom: 8px;
}

.content {
    margin-top: 25px;
    font-size: 11px;
    line-height: 1.6;
    text-align: justify;
    background: #fff;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 3px;
}

.content strong {
    color: #1e3a6f;
}

.content p {
    margin: 10px 0;
}

.certification-text {
    margin: 18px 0;
    padding: 12px;
    background: #e3f2fd;
    border-left: 3px solid #2196f3;
    border-radius: 2px;
    font-style: italic;
}

.sig-container {
    margin-top: 80px;
    display: flex;
    justify-content: center;
    gap: 50px;
    padding: 20px;
}

.sig {
    flex: 1;
    text-align: center;
    max-width: 300px;
}

.sig .line {
    margin-top: 60px;
    border-top: 2px solid #1e3a6f;
    padding-top: 8px;
    font-weight: 600;
    color: #1e3a6f;
    font-size: 12px;
    letter-spacing: 1px;
}

.sig .title {
    margin-bottom: 50px;
    font-size: 16px;
    color: #1e3a6f;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.sig .box div {
    font-size: 14px;
    color: #495057;
    margin: 3px 0;
    font-weight: 500;
}

.footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #dee2e6;
    text-align: center;
    font-size: 8px;
    color: #6c757d;
}

.footer p {
    margin: 4px 0;
}

.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 70px;
    color: rgba(44, 90, 160, 0.06);
    font-weight: 700;
    text-transform: uppercase;
    pointer-events: none;
    z-index: 1;
}

@media print {
    @page {
        size: A4;
        margin: 5mm;
    }

    body {
        background: #fff;
        padding: 0;
    }

    .page {
        padding: 20px 25px;
        margin: 0;
        max-width: none;
        border: 1px solid #000;
        box-shadow: none;
        min-height: 0;
    }

    .watermark {
        color: rgba(44, 90, 160, 0.03);
        font-size: 50px;
    }

    .logo-container img {
        max-width: 100px;
    }

    .header h1 {
        font-size: 16px;
    }

    .header h2 {
        font-size: 11px;
    }

    .header h3 {
        font-size: 9px;
    }

    .title {
        font-size: 13px;
    }

    .content {
        font-size: 9px;
        padding: 12px;
    }

    .certification-text {
        padding: 10px;
    }

    .sig .line {
        font-size: 8px;
        margin-top: 45px;
    }

    .sig .box div {
        font-size: 15px;
    }

    .sig .title {
        font-size: 14px;
    }

    .validity-note {
        font-size: 10px;
    }

    .footer {
        font-size: 6px;
    }
}
    </style>
    <body>
        <div class="page">
            <div class="watermark">OFFICIAL</div>
            <div class="header">
                <div class="logo-container">
                    <img src="../images/brgylogo.png" alt="Barangay New Era Logo">
                </div>
                
                <h2>Province of Cavite</h2>
                <h3>CITY OF DASMARIÑAS</h3>
                <h1>BARANGAY NEW ERA</h1>
                <h2>Dasmariñas City, Cavite</h2>
            </div>

            <div class="official-document">
                <div class="doc-title">OFFICE OF THE BARANGAY CAPTAIN</div>
            </div>

            <div class="title"><?php echo htmlspecialchars($documenttype); ?></div>
            <div class="content">
                <div class="certification-text">
                    <strong>TO WHOM IT MAY CONCERN:</strong>
                </div>
                <p style="text-indent: 40px; font-size: 15px; font-family:Arial, 'Times New Roman', serif">
                    This is to certify that <strong><?php echo htmlspecialchars($fullname); ?></strong>, <?php echo htmlspecialchars((string)($details['Age'] ?? '___')); ?> years of age,
                    born on <?php echo htmlspecialchars((string)($details['Birthdate'] ?? '_______________')); ?>,
                    and a resident of <strong><?php echo htmlspecialchars((string)($details['Address'] ?? 'Barangay New Era, Dasmariñas City, Cavite')); ?></strong>, 
                    has personally appeared before this office on this day and requested a 
                    <strong><?php echo htmlspecialchars($documenttype); ?></strong> for the purpose of 
                    <strong><?php echo htmlspecialchars($purpose); ?></strong>.
                </p>
                <p style="text-indent: 40px; font-size: 15px; font-family:Arial, 'Times New Roman', serif">
                    This certification is being issued upon the request of the aforementioned person for whatever legal purpose it may serve.
                </p>
                <p style="text-indent: 40px; font-size: 15px; font-family:Arial, 'Times New Roman', serif">
                    Issued this <?php echo date('d') ?> day of <?php echo date('F') ?>, <?php echo date('Y') ?> at Barangay New Era, Dasmariñas City, Cavite.
                </p>
            </div>

            <div class="sig-container">
                <div class="sig">
                    <div class="title">Certified by:</div>
                    <div class="box">
                        <div class="line">HON. _____________________</div>
                        <div>Barangay Captain</div>
                        <div>Barangay New Era</div>
                    </div>
                </div>
            </div>

            <div class="validity-note">
                <p style="text-align: center; font-size: 12px; font-weight: 300; margin-top: 30px;">
                    <strong>NOTE:</strong> Not valid without dry seal. Valid for 3 months from date of issuance.
                </p>
            </div>

            <div class="footer">
                <p><strong>BARANGAY NEW ERA</strong> | Dasmariñas City, Cavite 4114</p>
                <p>Tel: (046) 416-XXXX | Email: barangaynewera@gmail.com</p>
                <p>This document is valid and authentic.</p>
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
            <button onclick="showLogoutPopup()" class="sidebar-link"> <img src="../images/logout.png" alt="">Logout</button>
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
                        echo '<a href="#" class="action-icon view-btn" data-id="'.$row["RequestID"].'" data-fullname="'.htmlspecialchars($row["fullname"],ENT_QUOTES).'" data-date="'.htmlspecialchars($row["dateRequested"],ENT_QUOTES).'" data-doc="'.htmlspecialchars($row["documenttype"],ENT_QUOTES).'" data-label="Print file"><img src="../images/print.png" alt="view" style="width:28px;height:28px; "></a>';
                        echo '<a href="#declineModal" class="action-icon reject-btn" data-id="'.$row["RequestID"].'" data-label="Reject"><img src="../images/reject.png" alt="reject" style="width:28px;height:28px; "></a>';
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

    <script src="adminreleased.js"> </script>
</body>
</html>

