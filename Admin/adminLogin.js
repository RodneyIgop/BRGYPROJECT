document.addEventListener('DOMContentLoaded', () => {
  const navbar = document.querySelector('.navbar');
  const burger = document.querySelector('.burger');
  const navLinks = document.querySelector('.nav-links');

  if (!navbar || !burger || !navLinks) return;

  const closeMenu = () => {
    navbar.classList.remove('open');
    burger.setAttribute('aria-expanded', 'false');
  };

  const toggleMenu = () => {
    const isOpen = navbar.classList.toggle('open');
    burger.setAttribute('aria-expanded', String(isOpen));
  };

  burger.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleMenu();
  });

  // Close when clicking a nav link
  navLinks.addEventListener('click', (e) => {
    const target = e.target;
    if (target && target.tagName === 'A') {
      closeMenu();
    }
  });

  // Close when clicking outside
  document.addEventListener('click', (e) => {
    if (!navbar.contains(e.target)) {
      closeMenu();
    }
  });

  // Reset on resize to desktop
  const mq = window.matchMedia('(min-width: 769px)');
  const handleResize = () => {
    if (mq.matches) {
      closeMenu();
    }
  };
  window.addEventListener('resize', handleResize);
});


document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', () => {
            const inputId = toggle.getAttribute('data-target');
            const input = document.getElementById(inputId);
            const icon = toggle.querySelector('i');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
