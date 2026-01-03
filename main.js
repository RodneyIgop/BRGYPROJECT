document.addEventListener('DOMContentLoaded', () => {
  const burger = document.querySelector('.burger');
  const navbar = document.querySelector('.navbar');
  const navLinks = document.querySelector('.nav-links');

  if (!burger || !navbar) return;

  const closeMenu = () => {
    navbar.classList.remove('open');
    burger.setAttribute('aria-expanded', 'false');
  };

  const toggleMenu = () => {
    const isOpen = navbar.classList.toggle('open');
    burger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  };

  burger.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleMenu();
  });

  if (navLinks) {
    navLinks.addEventListener('click', (e) => {
      const t = e.target;
      if (t && t.tagName === 'A') closeMenu();
    });
  }

  document.addEventListener('click', (e) => {
    if (!navbar.contains(e.target)) closeMenu();
  });

  // Reset on resize to desktop
  const mq = window.matchMedia('(min-width: 769px)');
  const handleResize = () => { if (mq.matches) closeMenu(); };
  window.addEventListener('resize', handleResize);
});

// Announcements/News filter tabs
document.addEventListener('DOMContentLoaded', function () {
  const tabs = Array.from(document.querySelectorAll('.filter-tab'));
  const items = Array.from(document.querySelectorAll('.announcement-item'));

  if (!tabs.length || !items.length) return;

  function applyFilter(type) {
    items.forEach((el) => {
      const matches = el.getAttribute('data-type') === type;
      el.style.display = matches ? '' : 'none';
    });
  }

  // Initialize based on the tab with .active (fallback to 'news')
  const activeTab = tabs.find(t => t.classList.contains('active')) || tabs[0];
  applyFilter(activeTab?.getAttribute('data-filter') || 'news');

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      applyFilter(tab.getAttribute('data-filter'));
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const section = document.getElementById('getintouch');
  if (!section) return;

  const inputs = section.querySelectorAll('.top-boxes input.textbox.small');
  const messageEl = section.querySelector('textarea.textbox.large');
  const sendBtn = section.querySelector('button.options');
  if (!inputs || inputs.length < 3 || !messageEl || !sendBtn) return;

  sendBtn.addEventListener('click', (e) => {
    e.preventDefault();

    const fullname = (inputs[0].value || '').trim();
    const email = (inputs[1].value || '').trim();
    const contactnumber = (inputs[2].value || '').trim();
    const message = (messageEl.value || '').trim();

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'landingpage.php#getintouch';
    form.style.display = 'none';

    const addField = (name, value) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      form.appendChild(input);
    };

    addField('send_message', '1');
    addField('fullname', fullname);
    addField('email', email);
    addField('contactnumber', contactnumber);
    addField('message', message);

    document.body.appendChild(form);
    form.submit();
  });
});
