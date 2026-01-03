<?php
session_start();

if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminLogin.php');
    exit;
}

$superadmin_name = $_SESSION['superadmin_name'] ?? 'Super Admin';

// Include database connection
require_once '../Connection/Conn.php';

// Fetch rejected admin requests from the rejected_admin_requests table
$rejected_admins = [];
$rejected_query = $conn->prepare("SELECT * FROM rejected_admin_requests ORDER BY rejectionDate DESC");
if ($rejected_query) {
    $rejected_query->execute();
    $rejected_result = $rejected_query->get_result();
    while ($row = $rejected_result->fetch_assoc()) {
        $rejected_admins[] = $row;
    }
    $rejected_query->close();
}

// Fetch rejected resident requests from the rejected_resident_requests table
$rejected_residents = [];
$resident_query = $conn->prepare("SELECT * FROM rejected_resident_requests ORDER BY rejectionDate DESC");
if ($resident_query) {
    $resident_query->execute();
    $resident_result = $resident_query->get_result();
    while ($row = $resident_result->fetch_assoc()) {
        $rejected_residents[] = $row;
    }
    $resident_query->close();
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
                    <th>Date Rejected</th>
                    <th>Rejected By</th>
                    <th>Status</th>
                </tr>

                <?php if (!empty($rejected_residents)): ?>
                    <?php foreach ($rejected_residents as $index => $resident): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($resident['lastname'] . ', ' . $resident['firstname'] . ' ' . $resident['middlename'] . ' ' . $resident['suffix']); ?></td>
                            <td><?php echo htmlspecialchars($resident['email']); ?></td>
                            <td><?php echo htmlspecialchars($resident['contactnumber']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($resident['requestDate'])); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($resident['rejectionDate'])); ?></td>
                            <td><?php echo htmlspecialchars($resident['rejected_by']); ?></td>
                            <td><span class="status-declined">Rejected</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <i class="bi bi-person-x" style="font-size: 48px; color: #ccc;"></i>
                            <p style="color: #666; margin-top: 10px;">No rejected resident accounts found.</p>
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
                    <th>Date Rejected</th>
                    <th>Rejected By</th>
                    <th>Status</th>
                </tr>

                <?php if (!empty($rejected_admins)): ?>
                    <?php foreach ($rejected_admins as $index => $admin): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($admin['lastname'] . ', ' . $admin['firstname'] . ' ' . $admin['middlename'] . ' ' . $admin['suffix']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo htmlspecialchars($admin['contactnumber']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($admin['requestDate'])); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($admin['rejectionDate'])); ?></td>
                            <td><?php echo htmlspecialchars($admin['rejected_by']); ?></td>
                            <td><span class="status-declined">Rejected</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <i class="bi bi-person-x" style="font-size: 48px; color: #ccc;"></i>
                            <p style="color: #666; margin-top: 10px;">No rejected admin accounts found.</p>
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
