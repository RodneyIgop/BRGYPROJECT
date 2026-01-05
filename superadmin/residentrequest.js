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
        censusNumber: button.dataset.censusNumber || cells[2].textContent,
        fullName: cells[1].textContent,
        email: button.dataset.email || cells[3].textContent,
        contactNumber: button.dataset.contactNumber || cells[4].textContent,
        address: button.dataset.address || 'N/A',
        requestDate: button.dataset.requestDate || cells[5].textContent,
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
    // Adjust path for resident profile pictures (assuming they're in Residents/uploads/)
    const profilePath = requestData.profilePicture ? 
        (requestData.profilePicture.startsWith('uploads/') ? '../Residents/' + requestData.profilePicture : requestData.profilePicture) : 
        '../images/tao.png';
    profileImg.src = profilePath;
    
    // Set basic info
    document.getElementById('modalFullName').textContent = requestData.fullName;
    document.getElementById('modalRequestID').textContent = 'Request ID: ' + requestData.requestId;
    document.getElementById('modalCensusNumber').textContent = 'Census Number: ' + (requestData.censusNumber || 'N/A');
    document.getElementById('modalRequestDate').textContent = 'Request Date: ' + requestData.requestDate;
    
    // Set detailed info
    document.getElementById('modalEmail').textContent = requestData.email;
    document.getElementById('modalContactNumber').textContent = requestData.contactNumber;
    document.getElementById('modalAddress').textContent = requestData.address;
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
    
    // Redirect to generate resident ID page with request data
    const params = new URLSearchParams({
        requestId: currentRequestData.requestId,
        email: currentRequestData.email,
        firstName: currentRequestData.firstName,
        middleName: currentRequestData.middleName,
        lastName: currentRequestData.lastName,
        suffix: currentRequestData.suffix,
        censusNumber: currentRequestData.censusNumber
    });
    
    console.log('Generated params:', params.toString());
    window.location.href = `generateResidentID.php?${params.toString()}`;
}

function rejectRequest(requestId, button) {
    if(confirm('Are you sure you want to reject this resident request: ' + requestId + '?')) {
        // Send AJAX request to reject the request
        fetch('reject_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'requestId=' + encodeURIComponent(requestId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Request ' + requestId + ' has been rejected successfully');
                // Remove the row from the table
                const row = button.closest('tr');
                if (row) {
                    row.remove();
                }
                // Check if there are no more requests
                const tbody = document.querySelector('.admin-table tbody');
                if (tbody && tbody.children.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">No resident requests found.</td></tr>';
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