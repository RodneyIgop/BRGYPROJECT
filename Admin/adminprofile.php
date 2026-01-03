<?php
session_start();
require_once '../Connection/conn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin profile data
$stmt = $conn->prepare('SELECT profile_picture, employeeID, lastname, firstname, middlename, suffix, contactnumber, birthdate, age, email, password FROM admintbl WHERE AdminID = ?');
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    die('Admin not found');
}

$stmt->bind_result($profile_picture, $employeeID, $lastname, $firstname, $middlename, $suffix, $contactnumber, $birthdate, $age, $email, $password);
$stmt->fetch();
$stmt->close();

$fullname = trim($firstname . ' ' . $lastname);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="adminprofile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
     <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../images/brgylogo.png" alt="Logo">
            <h2>BARANGAY NEW ERA</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="adminIndex.php" > <img src="../images/home.png" alt="">Home</a>
            <a href="adminProfile.php" class="active"> <img src="../images/user.png" alt="">Profile</a>
            <details class="sidebar-dropdown">
                <summary><img src="../images/list.png" alt="">Request Lists <img src="../images/down.png" alt=""></summary>
                <a href="adminpending.php" class="submenu-link"> <img src="../images/pending.png" alt="">Pending and for review</a>
                <a href="adminapproved.php" class="submenu-link"> <img src="../images/approved.png" alt="">Approved and For Pick Up</a>
                <a href="adminreleased.php" class="submenu-link"> <img src="../images/complete.png" alt="">Signed and released</a>
            </details>
            <a href="adminarchive.php"> <img src="../images/archive.png" alt="">Archive</a>
            <a href="adminAnnouncements.php"> <img src="../images/marketing.png" alt=""> Announcements</a>
            <a href="adminMessages.php"> <img src="../images/email.png" alt="">Messages</a>
            <a href="adminResidents.php"> <img src="../images/residents.png" alt="">Residents</a>
            <button onclick="logout()" style="margin-top:auto;"> <img src="../images/logout.png" alt="">Logout</button>
        </nav>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>My Profile</h1>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-image" style="position: relative; cursor: pointer;">
                    <img src="<?php echo !empty($profile_picture) ? $profile_picture : '../images/tao.png'; ?>" alt="Profile Picture" 
                    style="width: 9em; margin-left:-3px; margin-top: -2px; height: 9em; border-radius: 50%; object-fit: cover; border: 3px solid #00386b;">
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($fullname); ?></div>
            </div>

            <form class="profile-form">
                <div class="form-group" >
                    <label>FULL NAME</label>
                    <input type="text" id="fullname" value="<?php echo htmlspecialchars($fullname); ?>" readonly>
                </div>
                <div class="form-group">
                    <label id="BIRTHDATE-LABEL">BIRTHDATE</label>
                    <input type="text" id="birthdate" value="<?php echo htmlspecialchars($birthdate); ?>" readonly>
                </div>
                <div class="form-group age">
                    <label id="age-label">AGE</label>
                    <input type="text" value="<?php echo htmlspecialchars($age); ?>" readonly>
                </div>
                <div class="form-group" style="grid-column: 1 / 2;">
                    <label>EMPLOYEE ID</label>
                    <input type="text" id="IDNUM"value="<?php echo htmlspecialchars($employeeID); ?>" readonly>
                </div>
                <div class="form-group" style="grid-column: 2 / 3;">
                    <label id="contact-label">CONTACT NUMBER</label>
                    <input type="text" id= "contact"value="<?php echo htmlspecialchars($contactnumber); ?>" readonly>
                </div>
                <div class="form-group" style="grid-column: 1 / 2;">
                    <label>EMAIL</label>
                    <input type="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                </div>
                <div class="form-group" style="grid-column: 2 / 3;">
                    <label>PASSWORD</label>
                    <input type="text" id="pass" value="<?php echo htmlspecialchars($password); ?>" readonly>
                </div>
                <button type="button" class="edit-btn" style="grid-column: 1 / -1;" onclick="editProfile()">Edit Profile</button>
            </form>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Profile</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editProfileForm" class="edit-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_lastname">Last Name</label>
                        <input type="text" id="edit_lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_firstname">First Name</label>
                        <input type="text" id="edit_firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_middlename">Middle Name</label>
                        <input type="text" id="edit_middlename" name="middlename" value="<?php echo htmlspecialchars($middlename); ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit_suffix">Suffix</label>
                        <input type="text" id="edit_suffix" name="suffix" value="<?php echo htmlspecialchars($suffix); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password">Password</label>
                        <input type="password" id="edit_password" name="password" placeholder="Leave blank to keep current password">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_birthdate">Birthdate</label>
                        <input type="text" id="edit_birthdate" name="birthdate" placeholder="mm/dd/yyyy" autocomplete="off" value="<?php echo $birthdate ? date('m/d/Y', strtotime($birthdate)) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit_contactnumber">Contact Number</label>
                        <input type="text" id="edit_contactnumber" name="contactnumber" value="<?php echo htmlspecialchars($contactnumber); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Employee ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($employeeID); ?>" readonly>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Verification Modal -->
    <div id="verificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Verify Employee ID</h2>
            </div>
            <form id="verificationForm" class="verification-form">
                <p>Please enter your Employee ID to confirm the changes:</p>
                <div class="form-group">
                    <label for="verify_id">Employee ID</label>
                    <input type="text" id="verify_id" name="verify_id" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeVerificationModal()">Cancel</button>
                    <button type="submit" class="verify-btn">Verify & Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src ="adminprofile.js"></script>
    <script>
        // Pass the AdminID from PHP to JavaScript
        const actualAdminID = '<?php echo htmlspecialchars($employeeID); ?>';
    </script>
</body>
</html>
