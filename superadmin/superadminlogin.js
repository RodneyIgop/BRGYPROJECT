document.addEventListener('DOMContentLoaded', () => {
  const navbar = document.querySelector('.navbar');
  const burger = document.querySelector('.burger');
  const navLinks = document.querySelector('.nav-links');
  const dropdowns = Array.from(document.querySelectorAll('.dropdown'));

  if (!navbar || !burger || !navLinks) return;

  const closeMenu = () => {
    navbar.classList.remove('open');
    burger.setAttribute('aria-expanded', 'false');
  };

  const toggleMenu = () => {
    const isOpen = navbar.classList.toggle('open');
    burger.setAttribute('aria-expanded', String(isOpen));
  };

  const closeAllDropdowns = () => {
    dropdowns.forEach(d => d.classList.remove('open'));
  };

  burger.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleMenu();
  });

  navLinks.addEventListener('click', (e) => {
    const target = e.target;
    if (target && target.tagName === 'A') closeMenu();
  });

  dropdowns.forEach(drop => {
    const toggle = drop.querySelector('.dropdown-toggle');
    if (!toggle) return;
    toggle.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdowns.forEach(d => { if (d !== drop) d.classList.remove('open'); });
      drop.classList.toggle('open');
    });
    drop.addEventListener('click', (e) => {
      const t = e.target;
      if (t && t.tagName === 'A') {
        drop.classList.remove('open');
        closeMenu();
      }
    });
  });

  document.addEventListener('click', (e) => {
    if (!navbar.contains(e.target)) {
      closeMenu();
      closeAllDropdowns();
    } else {
      const insideAnyDropdown = e.target.closest('.dropdown');
      if (!insideAnyDropdown) closeAllDropdowns();
    }
  });

  const mq = window.matchMedia('(min-width: 769px)');
  const handleResize = () => { if (mq.matches) { closeMenu(); closeAllDropdowns(); } };
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