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

// ---------- Logout Button (legacy) ---------
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'superadminlogin.php?logout=true';
    }
}

document.addEventListener('DOMContentLoaded', () => {
  const img = document.querySelector('.profile-image img');
  if (!img) return;

  const fileInput = document.createElement('input');
  fileInput.type = 'file';
  fileInput.accept = 'image/*';
  fileInput.style.display = 'none';
  document.body.appendChild(fileInput);

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

    fetch('superadminProfilePic.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=remove',
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.success) {
          img.src = d.imagePath + '?t=' + Date.now();
          showNotification(d.message || 'Removed', 'success');
        } else {
          throw new Error(d.message || 'Failed');
        }
      })
      .catch((err) => {
        console.error(err);
        showNotification(err.message || 'Error', 'error');
      });

    dropdown.style.display = 'none';
  }, true);

  img.addEventListener('click', (e) => {
    e.stopPropagation();
    const rect = img.getBoundingClientRect();
    dropdown.style.top = rect.bottom + window.scrollY + 'px';
    dropdown.style.left = rect.left + window.scrollX + 'px';
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
  });

  document.addEventListener('click', () => (dropdown.style.display = 'none'));
  dropdown.addEventListener('click', (e) => e.stopPropagation());

  fileInput.addEventListener('change', (e) => {
    if (!e.target.files.length) return;

    const file = e.target.files[0];
    const formData = new FormData();
    formData.append('profile_picture', file);

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

    fetch('superadminProfilePic.php', {
      method: 'POST',
      body: formData,
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.success) {
          img.src = d.imagePath + '?t=' + Date.now();
          showNotification('Profile picture updated!', 'success');
        } else {
          throw new Error(d.message || 'Upload failed');
        }
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
