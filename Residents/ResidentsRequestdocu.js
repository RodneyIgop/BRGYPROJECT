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
            } else {
                icon.classList.remove('bi-x');
                icon.classList.add('bi-list');
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!sidebar.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                icon.classList.remove('bi-x');
                icon.classList.add('bi-list');
            }
        });
        
        // Close menu when window is resized above 768px
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                icon.classList.remove('bi-x');
                icon.classList.add('bi-list');
            }
        });
    }
});

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('requestType');
    const purposeSelect = document.getElementById('purpose');
    
    const PURPOSE_MAP = {
        'Barangay Clearance': ['Employment','Scholarship','Business','Bank Requirement','Government Requirement'],
        'Barangay Indigency': ['Employment','Scholarship','Business','Bank Requirement','Government Requirement'],
        'Certificate of Residency': ['Employment','Scholarship','Business','Bank Requirement','Government Requirement'],
        'Business Permit': ['Employment','Scholarship','Business','Bank Requirement','Government Requirement'],
        'Others': ['Employment','Scholarship','Business','Bank Requirement','Government Requirement']
    };
    
    function populatePurpose() {
        const val = typeSelect.value;
        purposeSelect.innerHTML = '<option value="" disabled selected>Select Purpose</option>';
        
        if (PURPOSE_MAP[val]) {
            PURPOSE_MAP[val].forEach(p => {
                const opt = document.createElement('option');
                opt.value = p;
                opt.textContent = p;
                purposeSelect.appendChild(opt);
            });
            purposeSelect.disabled = false;
        } else {
            purposeSelect.disabled = true;
        }
    }
    
    // Add event listener
    if (typeSelect) {
        typeSelect.addEventListener('change', populatePurpose);
    }
});