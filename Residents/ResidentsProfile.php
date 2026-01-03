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
    <link rel="stylesheet" href="ResidentProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="logo">
                <img src="../images/brgylogo.png" alt="Barangay Logo" style="width:80px;margin-bottom:10px;">
                <h2>BARANGAY&nbsp;NEW&nbsp;ERA</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="ResidentsIndex.php"><i class="fas fa-home"></i> Home</a></li>
                    <li class="active"><a href="#"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="ResidentsRequestDocu.php"><i class="fas fa-file-alt"></i> Document Request</a></li>
                    <li><a href="residentstrackrequest.php"><i class="fas fa-list"></i> My Request</a></li>
                    <li><a href="ResidentsArchive.php"><i class="fas fa-archive"></i> Archive</a></li>
                    <li><a href="residentlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="profile-container">
                <h1>My Profile</h1>
                
                <div class="profile-card">
                    <div class="profile-header" style="margin-bottom: 8px; margin-top: -9px;">
                        <div class="profile-image" style="position: relative; cursor: pointer;">
                            <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : '../images/tao.png'; ?>" 
                            alt="Profile Picture"
                            style="width: 138px; height: 140px; border-radius: 50%; object-fit: cover; border: 3px solid #00386b;">
                        </div>
                        <h2><?php echo $formattedName; ?></h2>
                    </div>

                    <div class="profile-details">
    <div class="form-row" style="display: flex; gap: 10px; align-items: flex-start; margin-bottom: -2px;">
        <div class="form-group" style="flex: 3;">
            <label for="fullname">FULL NAME</label>
            <input type="text" id="fullname" value="<?php echo $formattedName; ?>" style="width: 25em; max-width: 100%; border: 1px solid rgba(14, 13, 13, 0.38);" 
            readonly>
        </div>

        <div class="form-group" style="flex: 1; min-width: 150px;">
            <label for="birthdate" style = "margin-left: -8.8em;">BIRTHDATE</label>
            <input type="text" id="birthdate" value="<?php echo $formattedBirthdate; ?>" style="width: 100%; margin-left: -8em; border: 1px solid rgba(14, 13, 13, 0.38);" 
            readonly>
        </div>

        <div class="form-group" style="width: 150px;">
            <label for="age" style = "margin-left: -8.8em;">AGE</label>
            <input type="text" id="age" value="<?php echo $age; ?>" style="width: 5em; border: 1px solid rgba(14, 13, 13, 0.38); margin-left: -8em;" readonly>
        </div>
    </div>


        
  

    <div class="form-group" style="margin-bottom: 20px;">
        <label for="address">HOME ADDRESS/STREET NUMBER</label>
        <input type="text" id="address" value="<?php echo $address; ?>" style="width: 50em; border: 1px solid rgba(14, 13, 13, 0.38);" readonly>
    </div>

    <div class="form-row" style="display: flex; gap: 20px; margin-bottom: -2px;">
        <div class="form-group" style="flex: 1;">
            <label for="census">CENSUS NUMBER</label>
            <input type="text" id="census" value="<?php echo $censusNumber; ?>" style="width: 100%; border: 1px solid rgba(14, 13, 13, 0.38);" readonly>
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="contact">CONTACT NUMBER</label>
            <input type="text" id="contact" value="<?php echo $contactNumber; ?>" style="width: 15em; border: 1px solid rgba(14, 13, 13, 0.38);" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="email">EMAIL</label>
        <input type="email" id="email" value="<?php echo $email; ?>" style="width: 50em; border: 1px solid rgba(14, 13, 13, 0.38);" readonly>
    </div>
</div>  

                    <div class="profile-actions">
                        <button id="editProfile" class="btn-edit">Edit Profile</button>
                    </div>
                </div>
            </div>
        
            <!-- Edit Profile Modal -->
            <div id="editProfileModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal" id="closeEditModal">&times;</span>
                    <h2>Edit Profile</h2>
                    <form id="editProfileForm">
                        <div class="form-row" style="display:flex;gap:15px;flex-wrap:wrap;">
                            <div class="form-group" style="flex:1;min-width:180px;">
                                <label for="editLastName">Last Name</label>
                                <input type="text" id="editLastName" name="last_name" value="<?php echo htmlspecialchars($user['LastName']); ?>" required>
                            </div>
                            <div class="form-group" style="flex:1;min-width:180px;">
                                <label for="editFirstName">First Name</label>
                                <input type="text" id="editFirstName" name="first_name" value="<?php echo htmlspecialchars($user['FirstName']); ?>" required>
                            </div>
                            <div class="form-group" style="flex:1;min-width:180px;">
                                <label for="editMiddleName">Middle Name (optional)</label>
                                <input type="text" id="editMiddleName" name="middle_name" value="<?php echo htmlspecialchars($user['MiddleName']); ?>">
                            </div>
                            <div class="form-group" style="flex:1;min-width:120px;">
                                <label for="editSuffix">Suffix (optional)</label>
                                <input type="text" id="editSuffix" name="suffix" value="<?php echo htmlspecialchars($user['Suffix']); ?>">
                            </div>
                        </div>
                        <div class="form-row" style="display:flex;gap:15px;flex-wrap:wrap;">
                            <div class="form-group" style="flex:1;min-width:180px;">
                                <label for="editBirthdate">Birthdate</label>
                                <input type="text" id="editBirthdate" name="birthdate" value="<?php echo htmlspecialchars($editBirthdate ?? ''); ?>" placeholder="mm/dd/yyyy" 
                                autocomplete="off" required>
                            </div>
                            <div class="form-group" style="flex:1;min-width:100px;">
                                <label for="editAge">Age</label>
                                <input type="number" id="editAge" name="age" value="<?php echo $age; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="editAddress">Address</label>
                            <input type="text" id="editAddress" name="address" value="<?php echo $address; ?>" required>
                        </div>
                        <div class="form-row" style="display:flex;gap:15px;">
                            <div class="form-group" style="flex:1;">
                                <label for="editContact">Contact Number</label>
                                <input type="text" id="editContact" name="contact" value="<?php echo $contactNumber; ?>" required>
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label for="editEmail">Email</label>
                                <input type="email" id="editEmail" name="email" value="<?php echo $email; ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-save">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="editprofile.js"></script>
</body>
</html>