// Store request data for modal
let currentRequestData = {};
let currentRejectionRequestId = null;

// Refresh variables
let isRefreshing = false;

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
    
    // Redirect directly to generate employee ID page with request data
    const params = new URLSearchParams({
        requestId: currentRequestData.requestId,
        email: currentRequestData.email,
        firstName: currentRequestData.firstName,
        middleName: currentRequestData.middleName,
        lastName: currentRequestData.lastName,
        suffix: currentRequestData.suffix
    });
    
    window.location.href = `generateEmployeeID.php?${params.toString()}`;
}

// Function to refresh table after acceptance (to be called from the employee ID generation page)
function refreshAfterAcceptance() {
    refreshTable();
}

function rejectRequest(requestId, event) {
    currentRejectionRequestId = requestId;
    openRejectionModal();
}

function openRejectionModal() {
    const modal = document.getElementById('rejectionReasonModal');
    modal.classList.add('show');
    
    // Reset form
    const radios = document.querySelectorAll('input[name="rejectionReason"]');
    radios.forEach(radio => radio.checked = false);
    document.getElementById('otherReasonContainer').style.display = 'none';
    document.getElementById('otherReasonText').value = '';
    document.getElementById('confirmRejectBtn').disabled = true;
}

function closeRejectionModal() {
    const modal = document.getElementById('rejectionReasonModal');
    modal.classList.remove('show');
    currentRejectionRequestId = null;
}

function confirmReject() {
    if (!currentRejectionRequestId) return;
    
    const selectedReason = document.querySelector('input[name="rejectionReason"]:checked');
    if (!selectedReason) {
        showErrorNotification('Please select a reason for rejection');
        return;
    }
    
    let rejectionReason = selectedReason.value;
    if (rejectionReason === 'Other') {
        const otherText = document.getElementById('otherReasonText').value.trim();
        if (!otherText) {
            showErrorNotification('Please specify the reason for rejection');
            return;
        }
        rejectionReason = otherText;
    }
    
    // Show processing overlay
    showProcessingOverlay();
    
    // Create and send AJAX request with rejection reason
    const formData = new FormData();
    formData.append('requestId', currentRejectionRequestId);
    formData.append('rejectionReason', rejectionReason);
    
    fetch('reject_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideProcessingOverlay();
        if (data.success) {
            closeRejectionModal();
            showSuccessNotification(data.message);
            // Refresh table after successful rejection
            refreshTable();
        } else {
            showErrorNotification(data.message);
        }
    })
    .catch(error => {
        hideProcessingOverlay();
        console.error('Error:', error);
        showErrorNotification('An error occurred while rejecting the request');
    });
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

// Add event listeners for rejection reason radio buttons
document.addEventListener('DOMContentLoaded', function() {
    // Radio button change event
    const radioButtons = document.querySelectorAll('input[name="rejectionReason"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            const confirmBtn = document.getElementById('confirmRejectBtn');
            const otherContainer = document.getElementById('otherReasonContainer');
            
            if (this.value === 'Other') {
                otherContainer.style.display = 'block';
                confirmBtn.disabled = true; // Disable until user types something
            } else {
                otherContainer.style.display = 'none';
                confirmBtn.disabled = false;
            }
        });
    });
    
    // Other reason text field input event
    const otherReasonText = document.getElementById('otherReasonText');
    if (otherReasonText) {
        otherReasonText.addEventListener('input', function() {
            const confirmBtn = document.getElementById('confirmRejectBtn');
            const isOtherSelected = document.querySelector('input[name="rejectionReason"][value="Other"]:checked');
            
            if (isOtherSelected) {
                confirmBtn.disabled = this.value.trim() === '';
            }
        });
    }
    
    // Close rejection modal when clicking outside
    window.addEventListener('click', function(event) {
        const rejectionModal = document.getElementById('rejectionReasonModal');
        if (event.target === rejectionModal) {
            closeRejectionModal();
        }
    });
});

// Processing overlay functions
function showProcessingOverlay() {
    const overlay = document.getElementById('processingOverlay');
    overlay.classList.add('show');
}

function hideProcessingOverlay() {
    const overlay = document.getElementById('processingOverlay');
    overlay.classList.remove('show');
}

// Success notification functions
function showSuccessNotification(message) {
    const notification = document.getElementById('successNotification');
    const messageElement = document.getElementById('successMessage');
    messageElement.textContent = message;
    notification.classList.add('show');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        hideSuccessNotification();
    }, 5000);
}

function hideSuccessNotification() {
    const notification = document.getElementById('successNotification');
    notification.classList.remove('show');
}

// Error notification functions
function showErrorNotification(message) {
    const notification = document.getElementById('errorNotification');
    const messageElement = document.getElementById('errorMessage');
    messageElement.textContent = message;
    notification.classList.add('show');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        hideErrorNotification();
    }, 5000);
}

function hideErrorNotification() {
    const notification = document.getElementById('errorNotification');
    notification.classList.remove('show');
}

// Manual refresh function
function manualRefresh() {
    if (!isRefreshing) {
        refreshTable();
    }
}

function refreshTable() {
    if (isRefreshing) return;
    
    isRefreshing = true;
    
    fetch('refresh_requests.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        isRefreshing = false;
        
        if (data.success) {
            updateTable(data.requests);
            showSuccessNotification(`Table refreshed - ${data.count} requests found`);
        } else {
            showErrorNotification('Failed to refresh table: ' + data.message);
        }
    })
    .catch(error => {
        isRefreshing = false;
        console.error('Error refreshing table:', error);
        showErrorNotification('An error occurred while refreshing the table');
    });
}

function updateTable(requests) {
    const tbody = document.querySelector('.admin-table tbody');
    
    if (!requests || requests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">No admin requests found.</td></tr>';
        return;
    }
    
    let tableHTML = '';
    requests.forEach(request => {
        const validationBadge = request.validationClass === 'success' 
            ? '<span class="badge bg-success">✓ match in the residents list</span>'
            : '<span class="badge bg-danger">❌ doesnt match any in the residents list</span>';
        
        tableHTML += `
            <tr>
                <td>${htmlspecialchars(request.adminID)}</td>
                <td>${htmlspecialchars(request.fullName)}</td>
                <td>${validationBadge}</td>
                <td>${htmlspecialchars(request.Email)}</td>
                <td>${htmlspecialchars(request.ContactNumber)}</td>
                <td>${htmlspecialchars(request.requestDate)}</td>
                <td>
                    <button class="action-btn view-btn" 
                        data-request-id="${htmlspecialchars(request.adminID)}"
                        data-first-name="${htmlspecialchars(request.FirstName)}"
                        data-middle-name="${htmlspecialchars(request.MiddleName)}"
                        data-last-name="${htmlspecialchars(request.LastName)}"
                        data-suffix="${htmlspecialchars(request.Suffix)}"
                        data-email="${htmlspecialchars(request.Email)}"
                        data-contact-number="${htmlspecialchars(request.ContactNumber)}"
                        data-birthdate="${htmlspecialchars(request.birthdate)}"
                        data-age="${htmlspecialchars(request.age)}"
                        data-profile-picture="${htmlspecialchars(request.profile_picture)}"
                        data-request-date="${htmlspecialchars(request.requestDate)}"
                        data-can-approve="${request.canApprove ? 'true' : 'false'}"
                        onclick="viewRequest(this)">View</button>
                   
                    <button class="action-btn reject-btn" onclick="rejectRequest('${htmlspecialchars(request.adminID)}', event)">Reject</button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = tableHTML;
}

// Helper function for HTML escaping
function htmlspecialchars(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function showRefreshSpinner() {
    const spinner = document.getElementById('refreshSpinner');
    if (spinner) {
        spinner.classList.add('spinning');
    }
}

function hideRefreshSpinner() {
    const spinner = document.getElementById('refreshSpinner');
    if (spinner) {
        spinner.classList.remove('spinning');
    }
}

function updateRefreshStatus(status) {
    const statusElement = document.getElementById('refreshStatus');
    if (statusElement) {
        statusElement.textContent = status;
        statusElement.className = status === 'Active' ? 'status-active' : 'status-paused';
    }
}

function startAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    autoRefreshInterval = setInterval(() => {
        if (isAutoRefreshEnabled && !isRefreshing) {
            refreshTable();
        }
    }, refreshIntervalTime);
    
    updateRefreshStatus('Active');
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
    
    updateRefreshStatus('Paused');
}

function toggleAutoRefresh() {
    const toggle = document.getElementById('autoRefreshToggle');
    isAutoRefreshEnabled = toggle.checked;
    
    if (isAutoRefreshEnabled) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
}