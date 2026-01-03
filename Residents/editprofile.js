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

// Add this script to your ResidentsProfile.php
document.addEventListener('DOMContentLoaded', function() {
    const profileImage = document.querySelector('.profile-image img');
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.style.display = 'none';
    document.body.appendChild(fileInput);

    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            const formData = new FormData();
            formData.append('profile_picture', file);

            // Show loading state
            profileImage.style.opacity = '0.5';

            // Show loading state
            const loadingText = document.createElement('div');
            loadingText.textContent = 'Uploading...';
            loadingText.style.position = 'absolute';
            loadingText.style.top = '50%';
            loadingText.style.left = '50%';
            loadingText.style.transform = 'translate(-50%, -50%)';
            loadingText.style.color = '#00386b';
            loadingText.style.fontWeight = 'bold';
            loadingText.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
            loadingText.style.padding = '5px 10px';
            loadingText.style.borderRadius = '5px';
            profileImage.parentNode.style.position = 'relative';
            profileImage.parentNode.appendChild(loadingText);

            // Send file to server
            fetch('editprofile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the profile image source with cache buster
                    profileImage.src = data.imagePath + '?t=' + new Date().getTime();
                    // Show success message
                    showNotification('Profile picture updated successfully!', 'success');
                } else {
                    throw new Error(data.message || 'Failed to update profile picture');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message || 'Error uploading profile picture', 'error');
            })
            .finally(() => {
                // Remove loading state
                if (loadingText.parentNode) {
                    loadingText.parentNode.removeChild(loadingText);
                }
                profileImage.style.opacity = '1';
            });
        }
    });

    // Create dropdown menu
    const dropdown = document.createElement('div');
    dropdown.className = 'profile-dropdown';
    dropdown.style.display = 'none';
    dropdown.style.position = 'absolute';
    dropdown.style.backgroundColor = '#fff';
    dropdown.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    dropdown.style.borderRadius = '4px';
    dropdown.style.overflow = 'hidden';
    dropdown.style.zIndex = '1000';
    
    const viewOption = document.createElement('div');
    viewOption.className = 'dropdown-option';
    viewOption.textContent = 'View Profile Picture';
    viewOption.style.padding = '10px 15px';
    viewOption.style.cursor = 'pointer';
    viewOption.addEventListener('click', function() {
        // Open image in new tab or show in a modal
        window.open(profileImage.src, '_blank');
        dropdown.style.display = 'none';
    });

    const changeOption = document.createElement('div');
    changeOption.className = 'dropdown-option';
    changeOption.textContent = 'Choose New Profile Picture';
    changeOption.style.padding = '10px 15px';
    changeOption.style.cursor = 'pointer';
    changeOption.style.borderTop = '1px solid #eee';
    changeOption.addEventListener('click', function() {
        fileInput.click();
        dropdown.style.display = 'none';
    });

    const removeOption = document.createElement('div');
    removeOption.className = 'dropdown-option';
    removeOption.textContent = 'Remove Profile Picture';
    removeOption.style.padding = '10px 15px';
    removeOption.style.cursor = 'pointer';
    removeOption.style.borderTop = '1px solid #eee';
    removeOption.addEventListener('click', function() {
        if (!confirm('Are you sure you want to remove your profile picture?')) return;
        fetch('editprofile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=remove'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                profileImage.src = data.imagePath + '?t=' + new Date().getTime();
                showNotification(data.message, 'success');
            } else {
                throw new Error(data.message || 'Failed to remove');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification(err.message || 'Error removing profile picture', 'error');
        });
        dropdown.style.display = 'none';
    });

    dropdown.appendChild(viewOption);
    dropdown.appendChild(changeOption);
    dropdown.appendChild(removeOption);
    document.body.appendChild(dropdown);

    // Toggle dropdown on profile image click
    profileImage.addEventListener('click', function(e) {
        e.stopPropagation();
        const rect = profileImage.getBoundingClientRect();
        dropdown.style.top = (rect.bottom + window.scrollY) + 'px';
        dropdown.style.left = (rect.left + window.scrollX) + 'px';
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        dropdown.style.display = 'none';
    });

    // Prevent dropdown from closing when clicking inside it
    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

// ---------------- Edit Profile Modal Logic ----------------
document.addEventListener('DOMContentLoaded', () => {
    const editBtn = document.getElementById('editProfile');
    const modal = document.getElementById('editProfileModal');
    const closeModal = document.getElementById('closeEditModal');
    const cancelBtn = document.getElementById('cancelEdit');
    const form = document.getElementById('editProfileForm');

    if (!editBtn || !modal || !closeModal || !form) return;

    // jQuery UI datepicker for birthdate if jQuery present
    if (window.$ && $('#editBirthdate').length) {
        $('#editBirthdate').datepicker({
            changeMonth:true,
            changeYear:true,
            yearRange:'1900:+0',
            maxDate: new Date(),
            dateFormat:'mm/dd/yy',
            onSelect:function(dateText){
                const bdate=new Date(dateText);
                const diff=Date.now()-bdate.getTime();
                const ageDt=new Date(diff);
                const yr=ageDt.getUTCFullYear();
                $('#editAge').val(Math.abs(yr-1970));
            }
        });
    }

    const openModal = () => { 
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        // Focus on first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input');
            if (firstInput) firstInput.focus();
        }, 100);
    };
    
    const hideModal = () => { 
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    editBtn.addEventListener('click', openModal);
    closeModal.addEventListener('click', hideModal);
    
    // Handle cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', hideModal);
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', (e) => { 
        if (e.target === modal) hideModal(); 
    });
    
    // Close modal when escape key is pressed
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            hideModal();
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new URLSearchParams(new FormData(form));

        // Auto-calculate age when birthdate changes
        const birthInput = document.getElementById('editBirthdate');
        const ageInput = document.getElementById('editAge');
        if (birthInput && ageInput) {
            birthInput.addEventListener('change', () => {
                const bdate = new Date(birthInput.value);
                if (!isNaN(bdate)) {
                    const diff = Date.now() - bdate.getTime();
                    const ageDt = new Date(diff);
                    const year = ageDt.getUTCFullYear();
                    ageInput.value = Math.abs(year - 1970);
                }
            });
        }

        // Show loading state
        const submitBtn = form.querySelector('.btn-save');
        const originalContent = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Saving...</span>';
        submitBtn.disabled = true;

        fetch('submit_edit_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification('Profile updated successfully!', 'success');
                hideModal();
                // Refresh the page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Error updating profile');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification(err.message || 'Error submitting request', 'error');
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalContent;
            submitBtn.disabled = false;
        });
    });
});