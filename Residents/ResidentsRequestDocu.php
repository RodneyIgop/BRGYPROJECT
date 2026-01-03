<?php
// Include the query file that contains all PHP logic
require_once 'ResidentsProfileQuery.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BARANGAY NEW ERA</title>
    <link rel="stylesheet" href="ResidentsRequestDocu.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<style>
    .sidebar {
        width: 250px;
        background: #00386b;
        color: #fff;
        display: flex;
        flex-direction: column;
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        z-index: 1000;
    }

    /* Logo section */
    .sidebar .logo {
        width: 100%;
        padding: 25px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 10px;
        /* height: 178px; */
    }

    .sidebar .logo-img {
        width: 80px;
        height: auto;
        margin-bottom: 10px;
        border-radius: 50%;
        object-fit: cover;
    }

    .sidebar .logo-text {
        color: #fff;
    }

    .sidebar .logo h2 {
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin: 10px 0;
        text-transform: uppercase;
        /* height: 23px; */
    }

    .sidebar .logo p {
        font-size: 0.7rem;
        margin: 0;
        opacity: 0.9;
        letter-spacing: 0.5px;
    }

    .sidebar nav ul {
        list-style: none;
        margin: 0;
        padding: 0;

    }

    .sidebar nav ul li {
        width: 100%;
        position: relative;
    }

    /* Link styles */
    .sidebar nav a {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 25px;
        color: #fff;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .sidebar nav a:hover,
    .sidebar nav a.active {
        background: #0d4070;
        /* darker blue on hover/active */
    }

    /* Left indicator bar */
    .sidebar nav a.active::before,
    .sidebar nav a:hover::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 5px;
        background: #3b82f6;
        /* bright blue indicator */
    }

    .sidebar nav i {
        width: 20px;
        text-align: center;
        font-size: 1.1em;
    }

    /* Logout link at bottom */
    .logout {
        margin-top: auto;
    }

    .logout a {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 25px;
        color: #fff;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .logout a:hover {
        background: #0d4070;
    }

    /* Icons specific styles */
    .bi-house::before {
        content: "\f3e5";
    }

    .bi-person::before {
        content: "\f4e1";
    }

    .bi-file-earmark-text::before {
        content: "\f31c";
    }

    .bi-card-checklist::before {
        content: "\f2d9";
    }
</style>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>
    <div class="sidebar">
        <div class="logo">
            <img src="../images/brgylogo.png" alt="Barangay Logo" class="logo-img">
            <div class="logo-text">
                <h2>BARANGAY NEW ERA</h2>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="ResidentsIndex.php"><i class="fas fa-home"></i> Home</a></li>
                <li class="active"><a href="ResidentsProfile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="ResidentsRequestDocu.php"><i class="fas fa-file-alt"></i> Document Request</a></li>
                <li><a href="residentstrackrequest.php"><i class="fas fa-list"></i> My Request</a></li>
                <li><a href="ResidentsArchive.php"><i class="fas fa-archive"></i> Archive</a></li>
                <li><a href="residentlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>
    <div class="container">
        <!-- Sidebar -->


        <!-- Main content -->
        <div class="main-content">
            
            <?php if (isset($_SESSION['request_error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    echo $_SESSION['request_error'];
                    unset($_SESSION['request_error']);
                    ?>
                </div>
                <?php endif; ?>
                
                <!-- Success message hidden
                <?php if (isset($_SESSION['request_success'])): ?>
                    <div class="alert alert-success">
                        <?php
                    echo $_SESSION['request_success'];
                    unset($_SESSION['request_success']);
                    ?>
                </div>
                <?php endif; ?>
                -->
                
                <h1>Document Request</h1>
            <div class="card">
                <form id="docRequestForm" method="POST" action="submit_document_request.php">
                    <div class="form-group">
                        <label for="requestType">Document Type</label>
                        <select id="requestType" name="requestType" required>
                            <option value="" disabled selected>Select Document Type</option>
                            <option value="Barangay Clearance">Barangay Clearance</option>
                            <option value="Barangay Indigency">Barangay Indigency</option>
                            <option value="Certificate of Residency">Certificate of Residency</option>
                            <option value="Business Permit">Business Permit</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="purpose">Purpose</label>
                        <select id="purpose" name="purpose" required disabled>
                            <option value="" disabled selected>Select Purpose</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">Additional Notes <span class="optional">(Optional)</span></label>
                        <textarea id="notes" name="notes" rows="4" placeholder="Notes..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
    <script src="ResidentsRequestdocu.js"></script>
</body>

</html>