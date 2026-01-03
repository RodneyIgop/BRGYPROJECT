<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Include database connection
require_once '../Connection/conn.php';

// Fetch all residents from residents table
$residents = [];
$columns = [];
$query = $conn->prepare("SELECT * FROM residents");
if ($query) {
    $query->execute();
    $result = $query->get_result();
    if ($result && $result->num_rows > 0) {
        // Get columns from first row
        $first_row = $result->fetch_assoc();
        $columns = array_keys($first_row);
        $residents[] = $first_row;
        
        // Get remaining residents
        while ($row = $result->fetch_assoc()) {
            $residents[] = $row;
        }
    }
    $query->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents</title>
    <!-- Use the dedicated CSS for residents page -->
    <link rel="stylesheet" href="adminresidents.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <!-- SIDEBAR (exact markup copied from adminapproved.php) -->
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
            <a href="adminArchive.php"> <img src="../images/archive.png" alt="">Archive</a>
            <a href="adminAnnouncements.php"> <img src="../images/marketing.png" alt=""> Announcements</a>
            <a href="adminMessages.php"> <img src="../images/email.png" alt="">Messages</a>
            <a href="adminResidents.php" class="active"> <img src="../images/residents.png" alt="">Residents</a>
            <!-- <a href="adminRegister.php" class="active"> <img src="../images/residents.png" alt="">Logout</a> -->
            <!-- <button onclick="logout()" style="margin-top:auto;"> <img src="../images/logout.png" alt="">Logout</button> -->
             <button onclick="window.location.href='adminRegister.php'" style="margin-top:auto;">
                <img src="../images/logout.png" alt=""> Logout
            </button>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="header">
            <h1>Residents Information</h1>
        </div>

        <div class="table-filters">
            <input type="text" id="residentsSearch" placeholder="Search">
            <input type="text" id="residentsAgeFilter" placeholder="Age">
            <input type="text" id="residentsDateFilter" placeholder="Date">
        </div>
        <div class="requests-table">
            <table>
                <thead>
                    <tr>
                        <?php if (!empty($columns)): ?>
                            <?php foreach ($columns as $column): ?>
                                <th><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $column))); ?></th>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <th>No Data</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="residentBody">
                    <?php if (!empty($residents)): ?>
                        <?php foreach ($residents as $resident): ?>
                            <tr>
                                <?php if (!empty($columns)): ?>
                                    <?php foreach ($columns as $column): ?>
                                        <td>
                                            <?php 
                                            $value = $resident[$column] ?? '';
                                            
                                            // Format date columns
                                            if (strpos(strtolower($column), 'date') !== false && !empty($value)) {
                                                echo date('M d, Y', strtotime($value));
                                            }
                                            // Format UID columns with code style
                                            elseif (strpos(strtolower($column), 'uid') !== false && !empty($value)) {
                                                echo '<code>' . htmlspecialchars($value) . '</code>';
                                            }
                                            // Format name columns with special handling
                                            elseif (in_array(strtolower($column), ['lastname', 'firstname', 'middlename', 'suffix']) && !empty($value)) {
                                                if (strtolower($column) === 'lastname') {
                                                    echo '<strong>' . htmlspecialchars($value) . '</strong>';
                                                } else {
                                                    echo htmlspecialchars($value);
                                                }
                                            }
                                            // Default display
                                            else {
                                                echo htmlspecialchars($value ?: 'N/A');
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <td>No data available</td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo count($columns); ?>" style="text-align: center; padding: 40px;">
                                <i class="bi bi-person-x" style="font-size: 48px; color: #ccc;"></i>
                                <p style="color: #666; margin-top: 10px;">No residents found in the database.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Page-specific JS -->
    <script src="adminresidents.js"></script>
</body>
</html>