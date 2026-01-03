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

function updateClock() {
    try {
        const now = new Date();
        const options = {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};
        
        const dateElement = document.getElementById('currentDate');
        const timeElement = document.getElementById('currentTime');
        
        if (dateElement) {
            dateElement.textContent = now.toLocaleDateString('en-US', options);
        } else {
            console.error('Could not find element with ID: currentDate');
        }
        
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
        } else {
            console.error('Could not find element with ID: currentTime');
        }
    } catch (error) {
        console.error('Error updating clock:', error);
    }
}

// Run the clock when the page loads
document.addEventListener('DOMContentLoaded', function() {
    updateClock();
    // Update every second for smooth time updates
    setInterval(updateClock, 1000);
});

// Carousel functionality
let currentSlide = 0;
let slideInterval;

function updateCarousel() {
    const carouselInner = document.querySelector('.carousel-inner');
    const indicators = document.querySelectorAll('.indicator');
    const items = document.querySelectorAll('.carousel-item');
    
    if (!carouselInner) return;
    
    // Update active class
    items.forEach((item, index) => {
        if (index === currentSlide) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
    
    // Update indicators
    indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentSlide);
    });
}

function moveSlide(direction) {
    const slides = document.querySelectorAll('.carousel-item');
    if (!slides.length) return;
    
    currentSlide = (currentSlide + direction + slides.length) % slides.length;
    updateCarousel();
    resetInterval();
}

function goToSlide(index) {
    const slides = document.querySelectorAll('.carousel-item');
    if (index >= 0 && index < slides.length) {
        currentSlide = index;
        updateCarousel();
        resetInterval();
    }
}

function resetInterval() {
    clearInterval(slideInterval);
    startAutoSlide();
}

function startAutoSlide() {
    slideInterval = setInterval(() => {
        moveSlide(1);
    }, 5000);
}

// Initialize the carousel when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the carousel
    updateCarousel();
    
    // Start auto-slide after a short delay
    setTimeout(startAutoSlide, 500000);
    
    // Pause auto-slide on hover
    const carousel = document.querySelector('.carousel-container');
    if (carousel) {
        carousel.addEventListener('mouseenter', () => clearInterval(slideInterval));
        carousel.addEventListener('mouseleave', startAutoSlide);
    }
});