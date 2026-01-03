<?php
session_start();

if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminLogin.php');
    exit;
}

$superadmin_name = $_SESSION['superadmin_name'] ?? 'Super Admin';

// Include database connection
require_once '../Connection/conn.php';

// Fetch all account requests from adminrequests table
$all_requests = [];
$requests_query = $conn->prepare("SELECT * FROM adminrequests ORDER BY requestDate DESC");
if ($requests_query) {
    $requests_query->execute();
    $requests_result = $requests_query->get_result();
    while ($row = $requests_result->fetch_assoc()) {
        $all_requests[] = $row;
    }
    $requests_query->close();
}

// Separate residents and admins based on whether they exist in admintbl
$declined_residents = [];
$declined_admins = [];

foreach ($all_requests as $request) {
    // Check if this email exists in admintbl (meaning it's an admin request)
    $admin_check = $conn->prepare("SELECT email FROM admintbl WHERE email = ?");
    $admin_check->bind_param('s', $request['email']);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    
    if ($admin_result && $admin_result->num_rows > 0) {
        // This is an admin request
        $declined_admins[] = $request;
    } else {
        // This is a resident request
        $declined_residents[] = $request;
    }
    $admin_check->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archives</title>

<link rel="stylesheet" href="superadmindashboard.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>

.main-content {
    margin-left: 250px;
    padding:40px 50px;
    background:#f8f9fa;
    min-height:100vh;
    font-family:'Segoe UI', Tahoma;
}

/* Header */
.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:35px;
}
.header h1 { font-size:28px; font-weight:700; }

/* CARD */
.archive-container {
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
}

/* TABS */
.tabs {
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.tab-btn {
    padding:10px 18px;
    border-radius:10px;
    border:none;
    cursor:pointer;
    font-weight:600;
    font-size:14px;
    transition:.3s;
}

.active-tab {
    background:#014A7F;
    color:white;
}

.inactive-tab {
    background:#e9ecef;
}

/* TABLE */
table {
    width:100%;
    border-collapse:collapse;
}

th {
    background:#014A7F;
    color:white;
    padding:12px;
    font-size:15px;
}

td {
    padding:10px;
    border-bottom:1px solid #ddd;
    font-size:14px;
}

.status-finished {
    background:#d4edda;
    color:#155724;
    padding:6px 10px;
    border-radius:20px;
    font-weight:600;
}

.status-declined {
    background:#f8d7da;
    color:#721c24;
    padding:6px 10px;
    border-radius:20px;
    font-weight:600;
}

/* Responsive */
@media(max-width:900px){
    .main-content{margin-left:0}
}
</style>
</head>

<body>

<!-- ================= SIDEBAR ================= -->
<?php include 'superadminSidebar.php' ;?>
<!-- =============== END SIDEBAR ================= -->

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="header">
        <h1>Document Archives</h1>

        <div class="header-date">
            <div id="currentDate"></div>
            <div id="currentTime"></div>
        </div>
    </div>

    <div class="archive-container">

        <!-- TABS -->
        <div class="tabs">
            <button id="residentsTab" class="tab-btn active-tab" onclick="showResidents()">Declined Residents</button>
            <button id="adminTab" class="tab-btn inactive-tab" onclick="showAdmin()">Declined Admins</button>
        </div>

        <!-- RESIDENTS TABLE -->
        <div id="residentsTable">
            <table>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Date Requested</th>
                    <th>Status</th>
                </tr>

                <?php if (!empty($declined_residents)): ?>
                    <?php foreach ($declined_residents as $index => $resident): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($resident['lastname'] . ', ' . $resident['firstname'] . ' ' . $resident['middlename'] . ' ' . $resident['suffix']); ?></td>
                            <td><?php echo htmlspecialchars($resident['email']); ?></td>
                            <td><?php echo htmlspecialchars($resident['contactnumber']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($resident['requestDate'])); ?></td>
                            <td><span class="status-declined">Rejected</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <i class="bi bi-person-x" style="font-size: 48px; color: #ccc;"></i>
                            <p style="color: #666; margin-top: 10px;">No declined resident accounts found.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- ADMIN TABLE -->
        <div id="adminTable" style="display:none;">
            <table>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Date Requested</th>
                    <th>Status</th>
                </tr>

                <?php if (!empty($declined_admins)): ?>
                    <?php foreach ($declined_admins as $index => $admin): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($admin['lastname'] . ', ' . $admin['firstname'] . ' ' . $admin['middlename'] . ' ' . $admin['suffix']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo htmlspecialchars($admin['contactnumber']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($admin['requestDate'])); ?></td>
                            <td><span class="status-declined">Rejected</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <i class="bi bi-person-x" style="font-size: 48px; color: #ccc;"></i>
                            <p style="color: #666; margin-top: 10px;">No declined admin accounts found.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

    </div>

</div>

<script>
// Tabs
function showResidents(){
    document.getElementById("residentsTable").style.display="block";
    document.getElementById("adminTable").style.display="none";

    document.getElementById("residentsTab").classList.add("active-tab");
    document.getElementById("residentsTab").classList.remove("inactive-tab");

    document.getElementById("adminTab").classList.remove("active-tab");
    document.getElementById("adminTab").classList.add("inactive-tab");
}

function showAdmin(){
    document.getElementById("residentsTable").style.display="none";
    document.getElementById("adminTable").style.display="block";

    document.getElementById("adminTab").classList.add("active-tab");
    document.getElementById("adminTab").classList.remove("inactive-tab");

    document.getElementById("residentsTab").classList.remove("active-tab");
    document.getElementById("residentsTab").classList.add("inactive-tab");
}

// Date Time
function updateDateTime(){
    const now=new Date();
    document.getElementById('currentDate').innerText =
        now.toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
    document.getElementById('currentTime').innerText =
        now.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'});
}
updateDateTime();
setInterval(updateDateTime,1000);

function logout(){
    window.location.href='superadminLogout.php';
}
</script>

</body>
</html>
