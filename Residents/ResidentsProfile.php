<?php
// Include the query file that contains all PHP logic
require_once 'ResidentsProfileQuery.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Profile - BARANGAY NEW ERA</title>
    <link rel="stylesheet" href="ResidentProfile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu">
        <i class="bi bi-list"></i>
        <span class="sr-only">Menu</span>
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
                <li><a href="ResidentsIndex.php" aria-label="Home"><i class="fas fa-home"></i> <span>Home</span></a></li>
                <li class="active"><a href="ResidentsProfile.php" aria-label="Profile"><i class="fas fa-user"></i> <span>Profile</span></a></li>
                <li><a href="ResidentsRequestDocu.php" aria-label="Document Request"><i class="fas fa-file-alt"></i> <span>Document Request</span></a></li>
                <li><a href="residentstrackrequest.php" aria-label="My Request"><i class="fas fa-list"></i> <span>My Request</span></a></li>
                <li><a href="ResidentsArchive.php" aria-label="Archive"><i class="fas fa-archive"></i> <span>History</span></a></li>
                <li class="logout"><a href="residentlogin.php" aria-label="Logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </nav>
    </div>
    <div class="container">
        <!-- Sidebar Navigation -->


        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1>My Profile</h1>
            </div>
            <div class="profile-container">

                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-image-container">
                            <div class="profile-image" role="button" tabindex="0" aria-label="Profile picture options">
                                <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : '../images/tao.png'; ?>"
                                    alt="Profile Picture"
                                    loading="lazy">
                            </div>
                        </div>
                        <h2><?php echo $formattedName; ?></h2>
                    </div>

                    <div class="profile-details">
                        <div class="form-section">
                            <h3>Personal Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fullname">FULL NAME</label>
                                    <input type="text" id="fullname" value="<?php echo $formattedName; ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="form-row form-row-2">
                                <div class="form-group">
                                    <label for="birthdate">BIRTHDATE</label>
                                    <input type="text" id="birthdate" value="<?php echo $formattedBirthdate; ?>" readonly>
                                </div>
                                <div class="form-group form-group-small">
                                    <label for="age">AGE</label>
                                    <input type="text" id="age" value="<?php echo $age; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <!-- <h3>Contact Information</h3> -->
                            <div class="form-group">
                                <label for="address">HOME ADDRESS/STREET NUMBER</label>
                                <input type="text" id="address" value="<?php echo $address; ?>" readonly>
                            </div>
                            
                            <div class="form-row form-row-2">
                                <div class="form-group">
                                    <label for="census">CENSUS NUMBER</label>
                                    <input type="text" id="census" value="<?php echo $censusNumber; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="contact">CONTACT NUMBER</label>
                                    <input type="text" id="contact" value="<?php echo $contactNumber; ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">EMAIL</label>
                                <input type="email" id="email" value="<?php echo $email; ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button id="editProfile" class="btn-edit" aria-label="Edit profile">
                            <i class="fas fa-edit"></i>
                            <span>Edit Profile</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Modal -->
            <div id="editProfileModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="modalTitle">Edit Profile</h2>
                        <button class="close-modal" id="closeEditModal" aria-label="Close modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editProfileForm" novalidate>
                            <div class="form-section">
                                
                                <div class="form-row form-row-4">
                                    <div class="form-group">
                                        <label for="editLastName">Last Name</label>
                                        <input type="text" id="editLastName" name="last_name"
                                            value="<?php echo htmlspecialchars($user['LastName']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="editFirstName">First Name</label>
                                        <input type="text" id="editFirstName" name="first_name"
                                            value="<?php echo htmlspecialchars($user['FirstName']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="editMiddleName">Middle Name (optional)</label>
                                        <input type="text" id="editMiddleName" name="middle_name"
                                            value="<?php echo htmlspecialchars($user['MiddleName']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="editSuffix">Suffix (optional)</label>
                                        <input type="text" id="editSuffix" name="suffix"
                                            value="<?php echo htmlspecialchars($user['Suffix']); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row form-row-2">
                                    <div class="form-group">
                                        <label for="editBirthdate">Birthdate</label>
                                        <input type="text" id="editBirthdate" name="birthdate"
                                            value="<?php echo htmlspecialchars($editBirthdate ?? ''); ?>"
                                            placeholder="mm/dd/yyyy" autocomplete="off" required>
                                    </div>
                                    <div class="form-group form-group-small">
                                        <label for="editAge">Age</label>
                                        <input type="number" id="editAge" name="age" value="<?php echo $age; ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                
                                <div class="form-group">
                                    <label for="editAddress">Address</label>
                                    <input type="text" id="editAddress" name="address" value="<?php echo $address; ?>" required>
                                </div>
                                
                                <div class="form-row form-row-2">
                                    <div class="form-group">
                                        <label for="editContact">Contact Number</label>
                                        <input type="text" id="editContact" name="contact" value="<?php echo $contactNumber; ?>"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="editEmail">Email</label>
                                        <input type="email" id="editEmail" name="email" value="<?php echo $email; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" id="cancelEdit">Cancel</button>
                                <button type="submit" class="btn-save">
                                    <i class="fas fa-save"></i>
                                    <span>Save Changes</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="editprofile.js"></script>
</body>

</html>