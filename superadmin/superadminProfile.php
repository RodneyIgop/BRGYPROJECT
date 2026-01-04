<?php
session_start();

require_once '../Connection/conn.php';

if (!isset($_SESSION['superadmin_id'])) {
    header('Location: superadminlogin.php');
    exit;
}

$superadmin_id = (int)$_SESSION['superadmin_id'];

$hasProfilePicture = false;
$colStmt = $conn->prepare("SHOW COLUMNS FROM superadmin LIKE 'profile_picture'");
if ($colStmt) {
    $colStmt->execute();
    $colStmt->store_result();
    $hasProfilePicture = $colStmt->num_rows > 0;
    $colStmt->close();
}

if ($hasProfilePicture) {
    $stmt = $conn->prepare('SELECT employeeID, LastName, FirstName, MiddleName, Suffix, Email, birthdate, age, Password, verificationcode, profile_picture FROM superadmin WHERE id = ?');
} else {
    $stmt = $conn->prepare('SELECT employeeID, LastName, FirstName, MiddleName, Suffix, Email, birthdate, age, Password, verificationcode FROM superadmin WHERE id = ?');
}
$stmt->bind_param('i', $superadmin_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    die('Superadmin not found');
}

$profile_picture = null;
$birthdate = null;
$age = null;
if ($hasProfilePicture) {
    $stmt->bind_result($employeeID, $lastname, $firstname, $middlename, $suffix, $email, $birthdate, $age, $password, $verificationcode, $profile_picture);
} else {
    $stmt->bind_result($employeeID, $lastname, $firstname, $middlename, $suffix, $email, $birthdate, $age, $password, $verificationcode);
}
$stmt->fetch();
$stmt->close();

$fullname = trim($firstname . ' ' . $middlename . ' ' . $lastname . ' ' . $suffix);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../superadmin/superadminprofile.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
	 <!-- <div class="sidebar">
		<div class="sidebar-logo">
			<img src="../images/brgylogo.png" alt="Logo">
			<h2>BARANGAY NEW ERA</h2>
		</div>
		<nav class="sidebar-nav">
			<a href="superadmindashboard.php"> <img src="../images/home.png" alt="">Home</a>
			<a href="superadminProfile.php" class="active"> <img src="../images/user.png" alt="">Profile</a>
			<details class="sidebar-dropdown">
				<summary><img src="../images/list.png" alt="">Account Management <img src="../images/down.png" alt=""></summary>
				<a href="superadminUsers.php?tab=promote" class="submenu-link"> <img src="../images/approved.png" alt="">Accept / Promote Admin</a>
				<a href="superadminUsers.php?tab=access" class="submenu-link"> <img src="../images/pending.png" alt="">Block / Unblock Accounts</a>
			</details>
			<a href="superadminLogs.php"> <img src="../images/monitor.png" alt="">Activity Logs</a>
			<a href="superadminResidents.php"> <img src="../images/residents.png" alt="">Resident Information</a>
			<a href="superadminarchive.php"> <img src="../images/archive.png" alt="">Archives</a>
			<button onclick="logout()" style="margin-top:auto;"> <img src="../images/logout.png" alt="">Logout</button>
		</nav>
	</div> -->
	<?php include 'superadminSidebar.php' ;?>

	<div class="main-content">
		<div class="page-header">
			<h1>My Profile</h1>
		</div>

		<div class="profile-card">
			<div class="profile-header">
				<div class="profile-image" style="position: relative; cursor: pointer;">
					<img src="<?php echo !empty($profile_picture) ? htmlspecialchars($profile_picture) : '../images/tao.png'; ?>" alt="Profile Picture" style="width: 9em; margin-left:-3px; margin-top: -2px; height: 9em; border-radius: 50%; object-fit: cover; border: 3px solid #00386b;">
				</div>
				<div class="profile-name"><?php echo htmlspecialchars($fullname); ?></div>
			</div>

			<form class="profile-form">
				<div class="form-group">
					<label>FULL NAME</label>
					<input type="text" id="fullname" value="<?php echo htmlspecialchars($fullname); ?>" readonly>
				</div>
				<div class="form-group">
					<label>BIRTHDATE</label>
					<input type="text" value="<?php echo $birthdate ? date('m/d/Y', strtotime($birthdate)) : 'Not Available'; ?>" class="birthdate"readonly>
				</div>
				<div class="form-group">
					<label class="AGE">AGE</label>
					<input type="text" value="<?php echo htmlspecialchars($age ?: 'Not Available'); ?>" class="age"readonly>
				</div>
				<div class="form-group" style="grid-column: 1 / 2;">
					<label>ACCOUNT IDENTIFICATION NUMBER</label>
					<input type="text" id="IDNUM" value="<?php echo htmlspecialchars($employeeID); ?>" readonly>
				</div>
				<div class="form-group" style="grid-column: 2 / 3;">
					<label>EMAIL</label>
					<input type="email" value="<?php echo htmlspecialchars($email); ?>" class="email" readonly>
				</div>
				
				<div class="form-group" style="grid-column: 1 / 2;">
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
						<label>Age</label>
						<input type="text" value="<?php echo htmlspecialchars($age ?: 'Not Available'); ?>" readonly>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label>Account Identification Number</label>
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

	<!-- Account ID Verification Modal -->
	<div id="verificationModal" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<h2>Verify Account Identification Number</h2>
			</div>
			<form id="verificationForm" class="verification-form">
				<p>Please enter your account identification number to confirm the changes:</p>
				<div class="form-group">
					<label for="verify_id">Account Identification Number</label>
					<input type="text" id="verify_id" name="verify_id" required>
				</div>
				<div class="modal-actions">
					<button type="button" class="cancel-btn" onclick="closeVerificationModal()">Cancel</button>
					<button type="submit" class="verify-btn">Verify & Save</button>
				</div>
			</form>
		</div>
	</div>

	<script>
	$(document).ready(function() {
		const birthdateInput = $('#edit_birthdate');
		
		if(birthdateInput.length) {
			birthdateInput.datepicker({
				changeMonth: true,
				changeYear: true,
				yearRange: "1900:+0",
				maxDate: new Date(),
				dateFormat: 'mm/dd/yy',
				onSelect: function(dateText) {
					updateAge();
				}
			});
		}

		function parseBirthdate(value) {
			if(!value) {
				return null;
			}

			try {
				return $.datepicker.parseDate('mm/dd/yy', value);
			} catch(e) {
				const d = new Date(value);
				return Number.isNaN(d.getTime()) ? null : d;
			}
		}

		function calculateAgeFromDate(birthDate) {
			const today = new Date();
			let age = today.getFullYear() - birthDate.getFullYear();
			const m = today.getMonth() - birthDate.getMonth();
			if(m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
				age--;
			}
			return age;
		}

		function updateAge() {
			const birthDate = parseBirthdate(birthdateInput.val());
			if(!birthDate) {
				// Update the age field in the modal
				birthdateInput.closest('.form-row').find('input[readonly]').val('Not Available');
				return;
			}

			const age = calculateAgeFromDate(birthDate);
			birthdateInput.closest('.form-row').find('input[readonly]').val(age >= 0 ? age : 'Not Available');
		}

		birthdateInput.on('change blur', updateAge);
		updateAge();
	});

	function editProfile(){
		document.getElementById('editProfileModal').classList.add('show');
	}
	
	function closeEditModal(){
		document.getElementById('editProfileModal').classList.remove('show');
	}
	
	function closeVerificationModal(){
		document.getElementById('verificationModal').classList.remove('show');
	}
	
	// Handle edit profile form submission
	document.getElementById('editProfileForm').addEventListener('submit', function(e) {
		e.preventDefault();
		closeEditModal();
		document.getElementById('verificationModal').classList.add('show');
	});
	
	// Handle verification form submission
	document.getElementById('verificationForm').addEventListener('submit', function(e) {
		e.preventDefault();
		const verifyId = document.getElementById('verify_id').value;
		const actualId = '<?php echo htmlspecialchars($employeeID); ?>';
		
		if (verifyId === actualId) {
			// Submit the form data
			const formData = new FormData(document.getElementById('editProfileForm'));
			formData.append('action', 'update_profile');
			
			fetch('superadminUpdateProfile.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					alert('Profile updated successfully!');
					location.reload();
				} else {
					alert('Error updating profile: ' + data.message);
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('Error updating profile');
			});
		} else {
			alert('Incorrect account identification number');
		}
	});
	
	// Close modals when clicking outside
	window.onclick = function(event) {
		const editModal = document.getElementById('editProfileModal');
		const verifyModal = document.getElementById('verificationModal');
		if (event.target == editModal) {
			closeEditModal();
		}
		if (event.target == verifyModal) {
			closeVerificationModal();
		}
	}
	</script>

    <script src="superadminprofile.js"></script>
</body>
</html>
