<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

include '../Connection/conn.php';

// Fetch superadmin accounts from database
$query = "SELECT id, employeeID, LastName, FirstName, MiddleName, Suffix, Email, profile_picture, birthdate, age FROM superadmin ORDER BY id ASC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Accounts Management</title>
    <link rel="stylesheet" href="superadminUserAccs.css">
    <style>
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px;
        }
        
        .table-container h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .accounts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .accounts-table th {
            background-color: #4a90e2;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .accounts-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }
        
        .accounts-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
        }
        
        .accounts-table tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>
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
            <a href="superadminAccounts.php" class="submenu-link active">
                <img src="../images/addUser.png"> Manage Superadmin Accounts
            </a>
           
        </details>

        <!-- ACTIVITY LOGS -->
        <a href="superadminLogs.php">
            <img src="../images/monitor.png"> Activity Logs
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
    <!-- <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../images/brgylogo.png" alt="Logo">
            <h2>BARANGAY NEW ERA</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="superadmindashboard.php"> <img src="../images/home.png" alt="">Home</a>
            <a href="superadminProfile.php"> <img src="../images/user.png" alt="">Profile</a>
            <details class="sidebar-dropdown">
                <summary><img src="../images/list.png" alt="">Account Management <img src="../images/down.png" alt=""></summary>
                <a href="superadminAdminAccs.php" class="submenu-link"> <img src="../images/addAdmin.png" alt="">Manage Admin Accounts</a>
                <a href="superadminUserAccs.php" class="submenu-link active"> <img src="../images/addUser.png" alt="">Manage Residents Accounts</a>
                <a href="superadminUsers.php" class="submenu-link"> <img src="../images/pending.png" alt="">Block / Unblock Accounts</a>
            </details>
            <a href="superadminLogs.php"> <img src="../images/monitor.png" alt="">Activity Logs</a>
            <a href="superadminResidents.php"> <img src="../images/residents.png" alt="">Resident Information</a>
            <a href="superadminarchive.php"> <img src="../images/archive.png" alt="">Archives</a>
            <a href="#" onclick="logout()"> <img src="../images/logout.png" alt="">Logout</a>
        </nav>
    </div> -->

    <div class="main-content">
        <div class="page-header">
            <h1>Superadmin Accounts Management</h1>
        </div>
        <div class="container">
            <div class="table-container">
                <h2>Superadmin Accounts</h2>
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Age</th>
                            <th>Profile Picture</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $fullName = $row['LastName'] . ', ' . $row['FirstName'] . ' ' . $row['MiddleName'];
                                if (!empty($row['Suffix'])) {
                                    $fullName .= ' ' . $row['Suffix'];
                                }
                                
                                $profilePicPath = !empty($row['profile_picture']) ? $row['profile_picture'] : 'images/tao.png';
                                $birthdate = $row['birthdate'] ? date('M d, Y', strtotime($row['birthdate'])) : 'Not set';
                                ?>
                                <tr>
                                    <td><?php 
                                    $employeeID = htmlspecialchars($row['employeeID']);
                                    if (strlen($employeeID) > 4) {
                                        $masked = substr($employeeID, 0, -4) . str_repeat('*', 4);
                                        echo $masked;
                                    } else {
                                        echo str_repeat('*', strlen($employeeID));
                                    }
                                    ?></td>
                                    <td><?php echo htmlspecialchars($fullName); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['age']); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($row['profile_picture'])): 
                                            // Check if it's already a full path or just a filename
                                            if (strpos($row['profile_picture'], 'uploads/') === 0) {
                                                $imagePath = $row['profile_picture'];
                                            } else {
                                                $imagePath = 'uploads/superadminprofiles/' . basename($row['profile_picture']);
                                            }
                                        ?>
                                            <img src="../<?php echo htmlspecialchars($imagePath); ?>" alt="Profile" class="profile-pic">
                                        <?php else: ?>
                                            <img src="../<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile" class="profile-pic">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No superadmin accounts found.</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="superadminUserAccs.js"></script>
</body>
</html>
