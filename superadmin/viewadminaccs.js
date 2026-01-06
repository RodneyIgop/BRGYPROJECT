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

function toggleBlockAdmin(button) {
    console.log('toggleBlockAdmin called');
    const adminId = button.dataset.adminId;
    const name = button.dataset.adminName;
    const currentStatus = button.dataset.currentStatus;
    
    console.log('Data:', { adminId, name, currentStatus });
    
    const isCurrentlyBlocked = currentStatus === 'blocked';
    const action = isCurrentlyBlocked ? 'unblock' : 'block';
    const actionText = isCurrentlyBlocked ? 'unblock' : 'block';
    
    console.log('Action:', { action, actionText, isCurrentlyBlocked });
    
    if (confirm(`Are you sure you want to ${actionText} this admin account?\n\nName: ${name}\nAdmin ID: ${adminId}`)) {
        console.log('User confirmed action');
        // Disable button to prevent multiple clicks
        button.disabled = true;
        button.textContent = 'Processing...';
        
        // Send AJAX request
        console.log('Sending AJAX request to viewadminaccs.php');
        fetch('viewadminaccs.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `admin_id=${encodeURIComponent(adminId)}&action=${encodeURIComponent(action)}`
        })
        .then(response => {
            console.log('Response received:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            console.log('Response text:', text);
            // Since viewadminaccs.php redirects on success, we need to handle this differently
            // Check if response contains HTML (indicating a redirect)
            if (text.includes('<!DOCTYPE html>') || text.includes('<html')) {
                // Success - redirect to show success message
                const actionText = action === 'block' ? 'block' : 'unblock';
                const redirectUrl = `viewadminaccs.php?status=${actionText}&success=1&email=1`;
                window.location.href = redirectUrl;
            } else {
                // Try to parse as JSON in case of error
                try {
                    const data = JSON.parse(text);
                    console.log('Data received:', data);
                    if (data.success) {
                        // Update button and status in the table
                        const row = button.closest('tr');
                        const statusCell = row.cells[4]; // Status column (index 4 for admin table)
                        const statusSpan = statusCell.querySelector('.status');
                        
                        if (action === 'block') {
                            // Update to blocked state
                            button.textContent = 'Unblock';
                            button.className = 'action-btn unblock-btn';
                            button.dataset.currentStatus = 'blocked';
                            statusSpan.className = 'status blocked';
                            statusSpan.textContent = 'Blocked';
                        } else {
                            // Update to active state
                            button.textContent = 'Block';
                            button.className = 'action-btn block-btn';
                            button.dataset.currentStatus = 'active';
                            statusSpan.className = 'status active';
                            statusSpan.textContent = 'Active';
                        }
                        
                        // Show success message
                        showNotification(data.message, 'success');
                    } else {
                        // Show error message
                        showNotification(data.message || 'An error occurred', 'error');
                    }
                } catch (e) {
                    console.error('Failed to parse response:', e);
                    // If we can't parse response, assume it was successful and redirect
                    const actionText = action === 'block' ? 'block' : 'unblock';
                    const redirectUrl = `viewadminaccs.php?status=${actionText}&success=1&email=1`;
                    window.location.href = redirectUrl;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while processing your request', 'error');
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
        });
    } else {
        console.log('User cancelled action');
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Style the notification
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '15px 20px',
        borderRadius: '5px',
        color: 'white',
        fontWeight: 'bold',
        zIndex: '10000',
        maxWidth: '300px',
        wordWrap: 'break-word'
    });
    
    // Set background color based on type
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#28a745';
            break;
        case 'error':
            notification.style.backgroundColor = '#dc3545';
            break;
        default:
            notification.style.backgroundColor = '#17a2b8';
    }
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}