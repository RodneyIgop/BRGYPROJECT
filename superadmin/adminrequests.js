// Store request data for modal
let currentRequestData = {};

function logout() {
    if(confirm('Are you sure you want to logout?')) {
        window.location.href = 'superadminlogin.php?logout=true';
    }
}

function viewRequest(button) {
    // Get the table row from the button
    const row = button.closest('tr');
    const cells = row.getElementsByTagName('td');
    
    // Extract request data from table cells and data attributes
    currentRequestData = {
        requestId: button.dataset.requestId || cells[0].textContent,
        fullName: cells[1].textContent,
        email: button.dataset.email || cells[2].textContent,
        contactNumber: button.dataset.contactNumber || cells[3].textContent,
        requestDate: button.dataset.requestDate || cells[4].textContent,
        firstName: button.dataset.firstName || '',
        middleName: button.dataset.middleName || '',
        lastName: button.dataset.lastName || '',
        suffix: button.dataset.suffix || '',
        birthdate: button.dataset.birthdate || 'N/A',
        age: button.dataset.age || 'N/A',
        profilePicture: button.dataset.profilePicture || '../images/tao.png',
        status: 'Pending',
        canApprove: button.dataset.canApprove === 'true'
    };
    
    // Populate modal with request data
    populateRequestModal(currentRequestData);
    
    // Show modal
    openRequestModal();
}

function populateRequestModal(requestData) {
    // Set profile picture
    const profileImg = document.getElementById('modalProfilePicture');
    profileImg.src = requestData.profilePicture || '../images/tao.png';
    
    // Set basic info
    document.getElementById('modalFullName').textContent = requestData.fullName;
    document.getElementById('modalRequestID').textContent = 'Request ID: ' + requestData.requestId;
    document.getElementById('modalRequestDate').textContent = 'Request Date: ' + requestData.requestDate;
    
    // Set detailed info
    document.getElementById('modalEmail').textContent = requestData.email;
    document.getElementById('modalContactNumber').textContent = requestData.contactNumber;
    document.getElementById('modalBirthdate').textContent = formatDate(requestData.birthdate) || 'N/A';
    document.getElementById('modalAge').textContent = requestData.age || 'N/A';
    document.getElementById('modalStatus').textContent = requestData.status;
    
    // Update accept button state based on validation
    updateAcceptButton(requestData.canApprove);
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

function openRequestModal() {
    const modal = document.getElementById('requestDetailsModal');
    modal.classList.add('show');
}

function closeRequestModal() {
    const modal = document.getElementById('requestDetailsModal');
    modal.classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('requestDetailsModal');
    if (event.target === modal) {
        closeRequestModal();
    }
}

function acceptRequest() {
    // Check if approval is allowed
    if (!currentRequestData.canApprove) {
        alert('Cannot approve request: Name does not match any resident in the residents list.');
        return false;
    }
    
    // Debug: Check current request data
    console.log('Current request data:', currentRequestData);
    console.log('Email from data:', currentRequestData.email);
    
    // Redirect to generate employee ID page with request data
    const params = new URLSearchParams({
        requestId: currentRequestData.requestId,
        email: currentRequestData.email,
        firstName: currentRequestData.firstName,
        middleName: currentRequestData.middleName,
        lastName: currentRequestData.lastName,
        suffix: currentRequestData.suffix
    });
    
    console.log('Generated params:', params.toString());
    window.location.href = `generateEmployeeID.php?${params.toString()}`;
}

function rejectRequest(requestId, event) {
    if(confirm('Are you sure you want to reject this admin request: ' + requestId + '?')) {
        // Create and send AJAX request
        const formData = new FormData();
        formData.append('requestId', requestId);
        
        fetch('reject_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Remove the row from the table
                const row = event.target.closest('tr');
                if (row) {
                    row.remove();
                }
                // Check if table is empty and show message
                const tbody = document.querySelector('.admin-table tbody');
                if (tbody && tbody.children.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px;">No admin requests found.</td></tr>';
                }
            } else {
                alert('Error rejecting request: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while rejecting the request');
        });
    }
}

// Update accept button state based on validation
function updateAcceptButton(canApprove) {
    const acceptBtn = document.getElementById('acceptButton');
    if (acceptBtn) {
        acceptBtn.setAttribute('data-can-approve', canApprove ? 'true' : 'false');
        if (canApprove === false) {
            acceptBtn.style.opacity = '0.5';
            acceptBtn.style.pointerEvents = 'none';
            acceptBtn.style.cursor = 'not-allowed';
            acceptBtn.title = 'Cannot approve: Name does not match any resident in the residents list';
        } else {
            acceptBtn.style.opacity = '1';
            acceptBtn.style.pointerEvents = 'auto';
            acceptBtn.style.cursor = 'pointer';
            acceptBtn.title = '';
        }
    }
}