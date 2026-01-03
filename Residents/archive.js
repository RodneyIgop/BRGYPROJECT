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

// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Reason Modal functionality
    document.addEventListener('click', function(ev) {
        const btn = ev.target.closest('.reason-btn');
        if (!btn) return;
        ev.preventDefault();
        
        const row = btn.closest('tr');
        document.getElementById('r-requestId').textContent = btn.dataset.id;
        document.getElementById('r-document').textContent = row ? row.children[3].textContent : '';
        document.getElementById('r-purpose').textContent = row ? row.children[4].textContent : '';
        document.getElementById('r-date').textContent = row ? row.children[2].textContent : '';
        document.getElementById('r-status').textContent = row ? row.children[1].textContent : '';
        document.getElementById('r-reason').textContent = btn.dataset.reason;
        
        showModal('reasonModal');
    });

    // Close modal
    document.getElementById('reasonClose').addEventListener('click', () => hideModal('reasonModal'));

    // Close modal when clicking outside
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
