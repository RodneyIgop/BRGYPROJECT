<?php
session_start();
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
    <title>Archive</title>
    <link rel="stylesheet" href="adminarchive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
                <a href="adminpending.php" class="submenu-link"> <img src="../images/pending.png" alt="">Pending and for review</a>
                <a href="adminapproved.php" class="submenu-link"> <img src="../images/approved.png" alt="">Approved and For Pick Up</a>
                <a href="adminreleased.php" class="submenu-link"> <img src="../images/complete.png" alt="">Signed and released</a>
            </details>
            <a href="adminArchive.php" class="active"> <img src="../images/archive.png" alt="">Archive</a>
            <a href="adminAnnouncements.php"> <img src="../images/marketing.png" alt=""> Announcements</a>
            <a href="adminMessages.php"> <img src="../images/email.png" alt="">Messages</a>
            <a href="adminResidents.php"> <img src="../images/residents.png" alt="">Residents</a>
            <!-- <button onclick="logout()" style="margin-top:auto;"> <img src="../images/logout.png" alt="">Logout</button> -->
             <button onclick="window.location.href='adminRegister.php'" style="margin-top:auto;">
                <img src="../images/logout.png" alt=""> Logout
            </button>
        </nav>
    </div>

    <div class="main-content">
        <div class="header"><h1>Document Archive</h1></div>
        <h3>Archive Records</h3>
        <div class="table-filters">
            <input type="text" id="archiveSearch" placeholder="Search">
            <input type="text" id="archiveDateFilter" placeholder="Date">
        </div>
        <div class="requests-table">
        <table class="archive-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Date Requested</th>
                    <th>Document Type</th>
                    <th>Status</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once '../Connection/conn.php';
                $sql = "SELECT * FROM archivetbl ORDER BY daterequested DESC";
                $res = $conn->query($sql);
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>'.htmlspecialchars($row["ArchiveID"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["fullname"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["daterequested"]).'</td>';
                        echo '<td>'.htmlspecialchars($row["documenttype"]).'</td>';
                        echo '<td><span style="background:#dc3545;color:#fff;padding:4px 10px;border-radius:12px;font-size:13px;">Declined</span></td>';
                        echo '<td>'.htmlspecialchars($row["reason"]).'</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6" style="text-align:center;">No archive records</td></tr>';
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>

    <script src="adminarchive.js"></script>
</body>
</html>