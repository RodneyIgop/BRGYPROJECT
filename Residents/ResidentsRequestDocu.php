<?php
session_start();
// Optionally check session, redirect to login if not logged in
if(!isset($_SESSION['resident_id']) && !isset($_SESSION['UserID'])){
    header('Location: residentlogin.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Request - BARANGAY NEW ERA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="ResidentsRequestDocu.css">
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>
    
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <img src="../images/brgylogo.png" alt="Barangay Logo" style="width: 6em; margin-bottom: 10px;">
    <h2>BARANGAY&nbsp;NEW&nbsp;ERA</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="ResidentsIndex.php"><i class="bi bi-house"></i> Home</a></li>
                    <li><a href="ResidentsProfile.php"><i class="bi bi-person"></i> Profile</a></li>
                    <li><a href="#" class="active"><i class="bi bi-file-earmark-text"></i> Document Request</a></li>
                    <li><a href="residentstrackrequest.php"><i class="bi bi-card-checklist"></i> My Request</a></li>
                    <li><a href="ResidentsArchive.php"><i class="fas fa-archive"></i> Archive</a></li>
                    <li><a href="residentlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <h1>Document Request</h1>
            
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
                        <textarea id="notes" name="notes" rows="4" placeholder = "Notes..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
     <script src = "ResidentsRequestdocu.js"></script>
</body>
</html>