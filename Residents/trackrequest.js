// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            
            // Change icon based on menu state
            const icon = mobileMenuToggle.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.classList.remove('bi-list');
                icon.classList.add('bi-x');
                // Prevent body scroll when menu is open
                document.body.style.overflow = 'hidden';
            } else {
                icon.classList.remove('bi-x');
                icon.classList.add('bi-list');
                document.body.style.overflow = '';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!sidebar.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                icon.classList.remove('bi-x');
                icon.classList.add('bi-list');
                document.body.style.overflow = '';
            }
        });
        
        // Close menu when window is resized above 768px
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                icon.classList.remove('bi-x');
                icon.classList.add('bi-list');
                document.body.style.overflow = '';
            }
        });
        
        // Close menu when escape key is pressed
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                icon.classList.remove('bi-x');
                icon.classList.add('bi-list');
                document.body.style.overflow = '';
            }
        });
    }
});

// Notification helper
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.padding = '10px 15px';
    notification.style.borderRadius = '4px';
    notification.style.backgroundColor = type === 'success' ? '#4caf50' : (type === 'error' ? '#f44336' : '#2196f3');
    notification.style.color = '#fff';
    notification.style.zIndex = '9999';
    document.body.appendChild(notification);
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 4000);
}

// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Reason Modal functionality
    document.addEventListener('click', function(ev) {
        const btn = ev.target.closest('.reason-btn');
        if (!btn) return;
        ev.preventDefault();
        document.getElementById('r-requestId').textContent = btn.dataset.id;
        const row = btn.closest('tr');
        document.getElementById('r-document').textContent = row ? row.children[4].textContent : '';
        document.getElementById('r-purpose').textContent = row ? row.children[5].textContent : '';
        document.getElementById('r-date').textContent = row ? row.children[3].textContent : '';
        document.getElementById('r-reason').textContent = btn.dataset.reason;
        showModal('reasonModal');
    });

    // Edit Request Modal functionality
    document.addEventListener('click', function(ev) {
        const btn = ev.target.closest('.edit-request-btn');
        if (!btn) return;
        ev.preventDefault();
        document.getElementById('editRequestId').value = btn.dataset.id;
        document.getElementById('editDocumentType').value = btn.dataset.document;
        document.getElementById('editPurpose').value = btn.dataset.purpose;
        document.getElementById('editNotes').value = btn.dataset.notes;
        showModal('editModal');
    });

    // Cancel Request Modal functionality
    document.addEventListener('click', function(ev) {
        const btn = ev.target.closest('.cancel-request-btn');
        if (!btn) return;
        ev.preventDefault();
        document.getElementById('cancelRequestId').textContent = btn.dataset.id;
        showModal('cancelModal');
    });

    // Close modals
    document.getElementById('reasonClose').addEventListener('click', () => hideModal('reasonModal'));
    document.getElementById('editClose').addEventListener('click', () => hideModal('editModal'));
    document.getElementById('editCancel').addEventListener('click', () => hideModal('editModal'));
    document.getElementById('cancelClose').addEventListener('click', () => hideModal('cancelModal'));
    document.getElementById('cancelNo').addEventListener('click', () => hideModal('cancelModal'));

    // Edit form submission
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const requestId = document.getElementById('editRequestId').value;
        const documentType = document.getElementById('editDocumentType').value;
        const purpose = document.getElementById('editPurpose').value;
        const notes = document.getElementById('editNotes').value;

        // Show loading state
        const submitBtn = this.querySelector('.btn-save');
        const originalContent = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating...</span>';
        submitBtn.disabled = true;

        fetch('update_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `requestId=${requestId}&documentType=${encodeURIComponent(documentType)}&purpose=${encodeURIComponent(purpose)}&notes=${encodeURIComponent(notes)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Request updated successfully!', 'success');
                hideModal('editModal');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Error updating request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message || 'Error updating request', 'error');
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalContent;
            submitBtn.disabled = false;
        });
    });

    // Cancel request confirmation
    document.getElementById('cancelYes').addEventListener('click', function() {
        const requestId = document.getElementById('cancelRequestId').textContent;
        
        // Show loading state
        const submitBtn = this;
        const originalContent = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Cancelling...</span>';
        submitBtn.disabled = true;

        fetch('cancel_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `requestId=${requestId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Request cancelled successfully!', 'success');
                hideModal('cancelModal');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Error cancelling request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message || 'Error cancelling request', 'error');
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalContent;
            submitBtn.disabled = false;
        });
    });

    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            hideModal(e.target.id);
        }
    });

    // Close modal when escape key is pressed
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="flex"]');
            if (openModal) {
                hideModal(openModal.id);
            }
        }
    });
});

// Modal helper functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Focus on first focusable element
        setTimeout(() => {
            const focusableElement = modal.querySelector('button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (focusableElement) {
                focusableElement.focus();
            }
        }, 100);
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}
