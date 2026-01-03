// Store resident data for modal
let residentData = {};

function logout() {
    if(confirm('Are you sure you want to logout?')) {
        window.location.href = 'superadminlogin.php?logout=true';
    }
}

function viewResident(button) {
    // Get the table row from the button
    const row = button.closest('tr');
    const cells = row.getElementsByTagName('td');
    
    // Extract resident data from table cells and data attributes
    const residentData = {
        userID: cells[0].textContent,
        fullName: cells[1].textContent,
        censusNumber: cells[2].textContent,
        email: cells[3].textContent,
        contactNumber: cells[4].textContent,
        status: cells[5].textContent.trim(),
        address: button.dataset.address || 'N/A',
        birthdate: button.dataset.birthdate || 'N/A',
        age: button.dataset.age || 'N/A',
        dateRequested: button.dataset.dateRequested || 'N/A',
        profilePicture: button.dataset.profilePicture || '../images/tao.png'
    };
    
    // Populate modal with resident data
    populateModal(residentData);
    
    // Show modal
    openResidentModal();
}

function trim(str) {
    return str.replace(/\s+/g, ' ').trim();
}

function populateModal(residentData) {
    // Set profile picture
    const profileImg = document.getElementById('modalProfilePicture');
    // Adjust path for resident profile pictures (assuming they're in Residents/uploads/)
    const profilePath = residentData.profilePicture && residentData.profilePicture !== '' ? 
    '../Residents/' + residentData.profilePicture : 
    '../images/tao.png';
profileImg.src = profilePath;

// Add error handling
profileImg.onerror = function() {
    this.src = '../images/tao.png';
};
    
    // Set basic info
    document.getElementById('modalFullName').textContent = residentData.fullName;
    document.getElementById('modalUserID').textContent = 'Resident ID: ' + residentData.userID;
    document.getElementById('modalCensusNumber').textContent = 'Census Number: ' + (residentData.censusNumber || 'N/A');
    
    // Set detailed info
    document.getElementById('modalEmail').textContent = residentData.email;
    document.getElementById('modalContactNumber').textContent = residentData.contactNumber;
    document.getElementById('modalAddress').textContent = residentData.address;
    document.getElementById('modalBirthdate').textContent = formatDate(residentData.birthdate) || 'N/A';
    document.getElementById('modalAge').textContent = residentData.age || 'N/A';
    document.getElementById('modalDateRequested').textContent = formatDate(residentData.dateRequested) || 'N/A';
    document.getElementById('modalStatus').textContent = residentData.status;
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

function openResidentModal() {
    const modal = document.getElementById('residentDetailsModal');
    modal.classList.add('show');
}

function closeResidentModal() {
    const modal = document.getElementById('residentDetailsModal');
    modal.classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('residentDetailsModal');
    if (event.target === modal) {
        closeResidentModal();
    }
}

function toggleBlockResident(button) {
    const uid = button.dataset.uid;
    const name = button.dataset.name;
    const currentStatus = button.dataset.currentStatus;
    const isCurrentlyBlocked = currentStatus === 'blocked';
    const action = isCurrentlyBlocked ? 'unblock' : 'block';
    const actionText = isCurrentlyBlocked ? 'unblock' : 'block';
    
    if (confirm(`Are you sure you want to ${actionText} this resident account?\n\nName: ${name}\nUID: ${uid}`)) {
        // Disable button to prevent multiple clicks
        button.disabled = true;
        button.textContent = 'Processing...';
        
        // Send AJAX request
        fetch('block_resident.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `resident_uid=${encodeURIComponent(uid)}&action=${encodeURIComponent(action)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button and status in the table
                const row = button.closest('tr');
                const statusCell = row.cells[5]; // Status column
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
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while processing your request', 'error');
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
        });
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

function blockResident(userId) {
    if(confirm('Are you sure you want to block this resident account: ' + userId + '?')) {
        // TODO: Implement block resident functionality via AJAX
        alert('Resident ' + userId + ' has been blocked');
        // TODO: Update table to show blocked status
    }
}

function unblockResident(userId) {
    if(confirm('Are you sure you want to unblock this resident account: ' + userId + '?')) {
        // TODO: Implement unblock resident functionality via AJAX
        alert('Resident ' + userId + ' has been unblocked');
        // TODO: Update table to show active status
    }
}