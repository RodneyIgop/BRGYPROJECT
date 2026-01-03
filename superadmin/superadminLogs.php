<?php
session_start();

if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminLogin.php');
    exit;
}

$superadmin_name = $_SESSION['superadmin_name'] ?? 'Super Admin';

// Include database connection
include '../Connection/conn.php';
include '../Connection/log_activity.php';

// Get filter parameters
$roleFilter = $_GET['role'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';
$search = $_GET['search'] ?? '';

// Build comprehensive query with better filtering
$query = "SELECT * FROM activity_logs WHERE 1=1";
$params = [];
$types = '';

if (!empty($roleFilter)) {
    $query .= " AND user_role = ?";
    $params[] = $roleFilter;
    $types .= 's';
}

if (!empty($fromDate)) {
    $query .= " AND DATE(created_at) >= ?";
    $params[] = $fromDate;
    $types .= 's';
}

if (!empty($toDate)) {
    $query .= " AND DATE(created_at) <= ?";
    $params[] = $toDate;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (user_name LIKE ? OR action LIKE ? OR description LIKE ? OR page LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssss';
}

$query .= " ORDER BY created_at DESC LIMIT 200";

// Execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Activity Logs</title>

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

/* LOG CARD */
.log-container {
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
}

/* FILTERS */
.filters {
    display:flex;
    gap:15px;
    flex-wrap:wrap;
    margin-bottom:15px;
    align-items: center;
}
.filters select,
.filters input {
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #ced4da;
    min-width: 150px;
}
.clear-btn {
    padding:10px 16px;
    background:#014A7F;
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-size:14px;
    transition:background 0.3s;
}
.clear-btn:hover {
    background:#5a6268;
}

/* TABLE */
table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
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

/* ROLE BADGES */
.badge {
    padding:6px 10px;
    border-radius:20px;
    font-weight:600;
    font-size:12px;
}
.user { background:#e3f2fd; color:#1565c0;}
.admin { background:#fff3e0; color:#e65100;}
.superadmin { background:#fce4ec; color:#c2185b;}

/* STATUS BADGES */
.status-badge {
    padding:4px 8px;
    border-radius:12px;
    font-size:11px;
    font-weight:600;
}
.successful { background:#e8f5e8; color:#2e7d32;}
.failed { background:#ffebee; color:#c62828;}
.pending { background:#fff3e0; color:#f57c00;}

/* TABLE STYLING */
table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    font-size:13px;
}

th {
    background:#014A7F;
    color:white;
    padding:12px 8px;
    font-size:13px;
    font-weight:600;
    white-space: nowrap;
}

td {
    padding:10px 8px;
    border-bottom:1px solid #ddd;
    font-size:12px;
    vertical-align: top;
}

/* TABLE RESPONSIVE */
.log-container {
    overflow-x: auto;
}

/* RESPONSIVE DESIGN */
@media(max-width:900px){
    .main-content{margin-left:0}
    .filters {flex-direction:column;}
}

@media(max-width:768px){
    .main-content{padding:20px}
    .header h1{font-size:24px}
    .filters{gap:10px}
    .filters select, .filters input{width:100%}
}
</style>
</head>

<body>

<!-- ================= SIDEBAR ================= -->
<!-- <div class="sidebar">
    <div class="sidebar-logo">
        <img src="../images/brgylogo.png">
        <h2>BARANGAY NEW ERA</h2>
    </div>

    <nav class="sidebar-nav">
        <a href="superadmindashboard.php">
            <img src="../images/home.png"> Home
        </a>

        <a href="superadminProfile.php">
            <img src="../images/user.png"> Profile
        </a>

        <details class="sidebar-dropdown">
            <summary>
                <img src="../images/list.png"> Account Management
                <img src="../images/down.png">
            </summary>

            <a href="superadminUsers.php?tab=promote" class="submenu-link">
                <img src="../images/approved.png"> Accept / Promote Admin
            </a>

            <a href="superadminUsers.php?tab=access" class="submenu-link">
                <img src="../images/pending.png"> Block / Unblock Accounts
            </a>
        </details>

        <a href="superadminLogs.php" class="active">
            <img src="../images/monitor.png"> Activity Logs
        </a>

        <a href="superadminResidents.php">
            <img src="../images/residents.png"> Resident Information
        </a>

        <a href="superadminarchive.php">
            <img src="../images/archive.png"> Archives
        </a>

        <button onclick="logout()" style="margin-top:auto;">
            <img src="../images/logout.png"> Logout
        </button>
    </nav>
</div> -->
<?php include 'superadminSidebar.php' ;?>
<!-- =============== END SIDEBAR ================= -->

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="header">
        <h1>Activity Logs</h1>

        <div class="header-date">
            <div id="currentDate"></div>
            <div id="currentTime"></div>
        </div>
    </div>

    <div class="log-container">

        <!-- FILTERS -->
        <div class="filters">
            <select id="roleFilter">
                <option value="">All Roles</option>
                <!-- <option value="User">User</option> -->
                <option value="Admin">Admin</option>
                <option value="Superadmin">Superadmin</option>
            </select>

            <input type="date" id="fromDate" placeholder="From Date">
            <input type="date" id="toDate" placeholder="To Date">

            <input type="text" id="searchLog" placeholder="Search actions...">
            
            <button onclick="clearFilters()" class="clear-btn">Clear Filters</button>
        </div>

        <!-- LOG TABLE -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Page</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['id']) ?></td>
                            <td><?= htmlspecialchars($log['user_name']) ?></td>
                            <td><span class="badge <?= strtolower($log['user_role']) ?>"><?= htmlspecialchars($log['user_role']) ?></span></td>
                            <td>
                                <strong><?= htmlspecialchars($log['action']) ?></strong>
                            </td>
                            <td>
                                <?= !empty($log['description']) ? htmlspecialchars($log['description']) : '<em style="color: #999;">No description</em>' ?>
                            </td>
                            <td>
                                <?= !empty($log['page']) ? htmlspecialchars($log['page']) : '<em style="color: #999;">N/A</em>' ?>
                            </td>
                            <td><?= date('M d, Y • h:i A', strtotime($log['created_at'])) ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($log['status']) ?>">
                                    <?= htmlspecialchars($log['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px; color: #666;">
                            No activity logs found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

<script>
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

// Filter functionality
function applyFilters() {
    const role = document.getElementById('roleFilter').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    const search = document.getElementById('searchLog').value;
    
    const params = new URLSearchParams();
    if (role) params.append('role', role);
    if (fromDate) params.append('from_date', fromDate);
    if (toDate) params.append('to_date', toDate);
    if (search) params.append('search', search);
    
    window.location.href = 'superadminLogs.php?' + params.toString();
}

// Clear all filters
function clearFilters() {
    window.location.href = 'superadminLogs.php';
}

// Add event listeners to filters
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('roleFilter').addEventListener('change', applyFilters);
    document.getElementById('fromDate').addEventListener('change', applyFilters);
    
    // Add search on Enter key
    document.getElementById('searchLog').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });
    
    // Set current filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('role')) document.getElementById('roleFilter').value = urlParams.get('role');
    if (urlParams.get('from_date')) document.getElementById('fromDate').value = urlParams.get('from_date');
    if (urlParams.get('to_date')) document.getElementById('toDate').value = urlParams.get('to_date');
    if (urlParams.get('search')) document.getElementById('searchLog').value = urlParams.get('search');
    
    // Add event listener for toDate only if element exists
    const toDateElement = document.getElementById('toDate');
    if (toDateElement) {
        toDateElement.addEventListener('change', applyFilters);
    }
});
</script>

</body>
</html>
