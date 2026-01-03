// ---------- Helper: toast notification ---------
function showNotification(message, type = 'info') {
    const note = document.createElement('div');
    note.textContent = message;
    Object.assign(note.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        padding: '10px 15px',
        borderRadius: '4px',
        color: '#fff',
        zIndex: 9999,
        backgroundColor:
            type === 'success'
                ? '#4caf50'
                : type === 'error'
                ? '#f44336'
                : '#2196f3',
    });
    document.body.appendChild(note);
    setTimeout(() => note.remove(), 4000);
}

// ---------- Edit & Logout Buttons (legacy) ---------
function editProfile() {
    document.getElementById('editProfileModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editProfileModal').classList.remove('show');
}

function closeVerificationModal() {
    document.getElementById('verificationModal').classList.remove('show');
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'adminLogin.php?logout=true';
    }
}

// ---------- Profile picture feature ---------------
document.addEventListener('DOMContentLoaded', () => {
    const img = document.querySelector('.profile-image img');
    if (!img) return;

    // hidden file input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.style.display = 'none';
    document.body.appendChild(fileInput);

    // dropdown menu
    const dropdown = document.createElement('div');
    dropdown.className = 'profile-dropdown';
    document.body.appendChild(dropdown);

    const addOption = (label, cb, border) => {
        const o = document.createElement('div');
        o.className = 'dropdown-option';
        o.textContent = label;
        if (border) o.style.borderTop = '1px solid #eee';
        o.addEventListener('click', cb);
        dropdown.appendChild(o);
    };

    addOption('View Profile Picture', () => {
        window.open(img.src, '_blank');
        dropdown.style.display = 'none';
    });

    addOption('Choose New Profile Picture', () => {
        fileInput.click();
        dropdown.style.display = 'none';
    }, true);

    addOption('Remove Profile Picture', () => {
        if (!confirm('Remove current profile picture?')) return;
        fetch('adminProfilePic.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=remove',
        })
            .then((r) => r.json())
            .then((d) => {
                if (d.success) {
                    img.src = d.imagePath + '?t=' + Date.now();
                    showNotification(d.message, 'success');
                } else throw new Error(d.message || 'Failed');
            })
            .catch((err) => {
                console.error(err);
                showNotification(err.message || 'Error', 'error');
            });
        dropdown.style.display = 'none';
    }, true);

    // toggle dropdown
    img.addEventListener('click', (e) => {
        e.stopPropagation();
        const rect = img.getBoundingClientRect();
        dropdown.style.top = rect.bottom + window.scrollY + 'px';
        dropdown.style.left = rect.left + window.scrollX + 'px';
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });

    document.addEventListener('click', () => (dropdown.style.display = 'none'));
    dropdown.addEventListener('click', (e) => e.stopPropagation());

    // handle file upload
    fileInput.addEventListener('change', (e) => {
        if (!e.target.files.length) return;
        const file = e.target.files[0];
        const formData = new FormData();
        formData.append('profile_picture', file);

        // loading overlay
        img.style.opacity = '0.5';
        const overlay = document.createElement('div');
        overlay.textContent = 'Uploading...';
        Object.assign(overlay.style, {
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            color: '#00386b',
            fontWeight: 'bold',
            backgroundColor: 'rgba(255,255,255,0.8)',
            padding: '5px 10px',
            borderRadius: '5px',
        });
        img.parentNode.style.position = 'relative';
        img.parentNode.appendChild(overlay);

        fetch('adminProfilePic.php', {
            method: 'POST',
            body: formData,
        })
            .then((r) => r.json())
            .then((d) => {
                if (d.success) {
                    img.src = d.imagePath + '?t=' + Date.now();
                    showNotification('Profile picture updated!', 'success');
                } else throw new Error(d.message || 'Upload failed');
            })
            .catch((err) => {
                console.error(err);
                showNotification(err.message || 'Error uploading', 'error');
            })
            .finally(() => {
                overlay.remove();
                img.style.opacity = '1';
            });
    });
});

// ---------- Edit Profile Functionality ----------
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit profile form submission
    document.getElementById('editProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        closeEditModal();
        document.getElementById('verificationModal').classList.add('show');
    });
    
    // Handle verification form submission
    document.getElementById('verificationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const verifyId = document.getElementById('verify_id').value.trim(); // Trim whitespace
        const actualId = actualAdminID;
        
        // Debug logging
        console.log('Entered ID (trimmed):', verifyId);
        console.log('Actual ID:', actualId);
        console.log('actualAdminID variable exists:', typeof actualAdminID !== 'undefined');
        console.log('Type of verifyId:', typeof verifyId);
        console.log('Type of actualId:', typeof actualId);
        console.log('Length of verifyId:', verifyId.length);
        console.log('Length of actualId:', actualId ? actualId.length : 'undefined');
        console.log('Comparison result:', verifyId === actualId);
        
        // Check if actualAdminID is defined
        if (typeof actualAdminID === 'undefined') {
            alert('Error: Admin ID not properly loaded. Please refresh the page.');
            return;
        }
        
        if (verifyId === actualId) {
            // Submit the form data
            const formData = new FormData(document.getElementById('editProfileForm'));
            formData.append('action', 'update_profile');
            
            fetch('adminUpdateProfile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);
                if (data.success) {
                    alert('Profile updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating profile: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating profile: ' + error.message);
            });
        } else {
            alert('Incorrect Employee ID');
        }
    });
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const editModal = document.getElementById('editProfileModal');
        const verifyModal = document.getElementById('verificationModal');
        
        if (event.target == editModal) {
            closeEditModal();
        }
        if (event.target == verifyModal) {
            closeVerificationModal();
        }
    };
});