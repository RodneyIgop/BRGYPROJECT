<?php
session_start();
if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents Accounts Management</title>
    <link rel="stylesheet" href="superadminUserAccs.css">
</head>
<body>
    <?php include 'superadminSidebar.php' ;?>

    <div class="main-content">
        <div class="page-header">
            <h1>Residents Accounts Management</h1>
        </div>
<div class="container">
        <div class="cards-container">
            <!-- View Residents Accounts Card -->
            <div class="card">
                <div class="card-header">
                    <h3>View Residents Accounts</h3>
                    <a href="residentsaccounts.php" class="view-all-link">
                        View All
                        <img src="../images/arrow.png" alt="Arrow" class="arrow-icon">
                    </a>
                </div>
                <div class="card-content">
                    <p>Manage and monitor all existing resident accounts in the system</p>
                </div>
            </div>

            <!-- View Residents Request Card -->
            <div class="card">
                <div class="card-header">
                    <h3>View Residents Request</h3>
                    <a href="residentrequest.php" class="view-all-link">
                        View All
                        <img src="../images/arrow.png" alt="Arrow" class="arrow-icon">
                    </a>
                </div>
                <div class="card-content">
                    <p>Review and approve pending resident account requests</p>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>

    <script src="superadminUserAccs.js"></script>
</body>
</html>