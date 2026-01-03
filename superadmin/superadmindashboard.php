<?php
session_start();

require_once '../Connection/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = trim($_POST['employee_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($employeeId === '' || $password === '') {
        header('Location: superadminlogin.php?error=empty');
        exit;
    }

    $stmt = $conn->prepare('SELECT id, FirstName, LastName, Password FROM superadmin WHERE employeeID = ? LIMIT 1');
    $stmt->bind_param('s', $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === (string)$row['Password']) {
            $_SESSION['superadmin_id'] = $row['id'];
            $_SESSION['superadmin_name'] = trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? ''));
            header('Location: superadmindashboard.php');
            exit;
        }
    }

    header('Location: superadminlogin.php?error=invalid');
    exit;
}

if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

$superadmin_name = $_SESSION['superadmin_name'] ?? 'Super Admin';
$current_date = date('l, F j, Y');
$current_time = date('g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin Dashboard</title>
    <link rel="stylesheet" href="superadmindashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>

<!-- SIDEBAR (UNCHANGED STRUCTURE) -->
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="../images/brgylogo.png">
        <h2>BARANGAY NEW ERA</h2>
    </div>

    <nav class="sidebar-nav">
        <a href="superadmindashboard.php" class="active">
            <img src="../images/home.png"> Home
        </a>

        <a href="superadminProfile.php">
            <img src="../images/user.png"> Profile
        </a>

        <!-- ACCOUNT MANAGEMENT -->
        <details class="sidebar-dropdown">
            <summary>
                <img src="../images/list.png"> Account Management
                <img src="../images/down.png">
            </summary>
            <a href="superadminAdminAccs.php" class="submenu-link">
                <img src="../images/addAdmin.png"> Manage Admin Accounts
            </a>
            <a href="superadminUserAccs.php" class="submenu-link">
                <img src="../images/addUser.png"> Manage Residents Accounts
            </a>
            <a href="superadminAccounts.php" class="submenu-link">
                <img src="../images/addAdmin.png"> Manage Superadmin Accounts
            </a>
           
        </details>

        <!-- ACTIVITY LOGS -->
        <a href="superadminLogs.php">
            <img src="../images/monitor.png"> Activity Logs
        </a>

        <!-- ATTENDANCE -->
        <a href="superadminAttendance.php">
            <img src="../images/attendance.png"> Attendance
        </a>

        <!-- RESIDENT INFO -->
        <a href="superadminResidents.php">
            <img src="../images/residents.png"> Resident Information
        </a>

        <!-- ARCHIVES -->
        <a href="superadminarchive.php">
            <img src="../images/archive.png"> Archives
        </a>

        <button onclick="logout()" style="margin-top:auto;">
            <img src="../images/logout.png"> Logout
        </button>
    </nav>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="header">
    <h1>Hello, <?php echo htmlspecialchars($superadmin_name); ?></h1>

    <div class="header-date">
        <div class="date" id="currentDate"></div>
        <div class="time" id="currentTime"></div>
    </div>
</div>

    <!-- DASHBOARD CARDS -->
    <div class="dashboard-grid">

        <div class="card">
            <div class="card-header">
                <span>👥</span>
                <span>Account Management</span>
            </div>
            <div class="card-description">
                Manage admin approvals, promotions, and account access restrictions.
            </div>
            <a href="superadminAdminAccs.php" class="card-link">Open →</a>
        </div>

        <div class="card">
            <div class="card-header">
                <span>📜</span>
                <span>Activity Logs</span>
            </div>
            <div class="card-description">
                Track system activities and user actions for transparency.
            </div>
            <a href="superadminLogs.php" class="card-link">View →</a>
        </div>

        <div class="card">
            <div class="card-header">
                <span>🏠</span>
                <span>Resident Information</span>
            </div>
            <div class="card-description">
                View and manage registered barangay residents.
            </div>
            <a href="superadminResidents.php" class="card-link">Open →</a>
        </div>

        <div class="card">
            <div class="card-header">
                <span>🗄️</span>
                <span>Archives</span>
            </div>
            <div class="card-description">
                Access archived requests and historical records.
            </div>
            <a href="superadminarchive.php" class="card-link">View →</a>
        </div>

    </div>
</div>



<script src="superadmindashboard.js"></script>
<script>
function updateDateTime() {
    const now = new Date();

    const optionsDate = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };

    const optionsTime = {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    };

    document.getElementById('currentDate').textContent =
        now.toLocaleDateString('en-US', optionsDate);

    document.getElementById('currentTime').textContent =
        now.toLocaleTimeString('en-US', optionsTime);
}

// Initial load
updateDateTime();

// Update every second
setInterval(updateDateTime, 1000);
</script>

</body>
</html>
