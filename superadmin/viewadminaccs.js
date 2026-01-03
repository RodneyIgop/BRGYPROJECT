// Store admin data for modal
let adminData = {};

function logout() {
    if(confirm('Are you sure you want to logout?')) {
        window.location.href = 'superadminlogin.php?logout=true';
    }
}

function viewAdmin(button) {
    // Get the table row from the button
    const row = button.closest('tr');
    const cells = row.getElementsByTagName('td');
    
    // Extract admin data from table cells and data attributes
    const adminData = {
        adminID: cells[0].textContent,
        fullName: cells[1].textContent,
        email: cells[2].textContent,
        contactNumber: cells[3].textContent,
        status: cells[4].textContent.trim(),
        employeeID: button.dataset.employeeId || 'N/A',
        birthdate: button.dataset.birthdate || 'N/A',
        age: button.dataset.age || 'N/A',
        profilePicture: button.dataset.profilePicture || '../images/tao.png'
    };
    
    // Populate modal with admin data
    populateModal(adminData);
    
    // Show modal
    openAdminModal();
}

function trim(str) {
    return str.replace(/\s+/g, ' ').trim();
}

function populateModal(adminData) {
    // Set profile picture
    const profileImg = document.getElementById('modalProfilePicture');
    // Adjust path for admin profile pictures from Admin directory
    const profilePath = adminData.profilePicture ? 
        (adminData.profilePicture.startsWith('uploads/') ? '../Admin/' + adminData.profilePicture : adminData.profilePicture) : 
        '../images/tao.png';
    profileImg.src = profilePath;
    
    // Set basic info
    document.getElementById('modalFullName').textContent = adminData.fullName;
    document.getElementById('modalAdminID').textContent = 'Admin ID: ' + adminData.adminID;
    document.getElementById('modalEmployeeID').textContent = 'Employee ID: ' + (adminData.employeeID || 'N/A');
    
    // Set detailed info
    document.getElementById('modalEmail').textContent = adminData.email;
    document.getElementById('modalContactNumber').textContent = adminData.contactNumber;
    document.getElementById('modalBirthdate').textContent = formatDate(adminData.birthdate) || 'N/A';
    document.getElementById('modalAge').textContent = adminData.age || 'N/A';
    document.getElementById('modalStatus').textContent = adminData.status;
}

function formatDate(dateString) {
    if (!dateString || dateString === 'N/A') return dateString;
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        // Format as mm/dd/yyyy
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const year = date.getFullYear();
        
        return `${month}/${day}/${year}`;
    } catch (e) {
        return dateString;
    }
}

function openAdminModal() {
    const modal = document.getElementById('adminDetailsModal');
    modal.classList.add('show');
}

function closeAdminModal() {
    const modal = document.getElementById('adminDetailsModal');
    modal.classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('adminDetailsModal');
    if (event.target === modal) {
        closeAdminModal();
    }
}

function blockAdmin(adminId) {
    if(confirm('Are you sure you want to block this admin account: ' + adminId + '?')) {
        // TODO: Implement block admin functionality via AJAX
        alert('Admin ' + adminId + ' has been blocked');
        // TODO: Update table to show blocked status
    }
}

function unblockAdmin(adminId) {
    if(confirm('Are you sure you want to unblock this admin account: ' + adminId + '?')) {
        // TODO: Implement unblock admin functionality via AJAX
        alert('Admin ' + adminId + ' has been unblocked');
        // TODO: Update table to show active status
    }
}