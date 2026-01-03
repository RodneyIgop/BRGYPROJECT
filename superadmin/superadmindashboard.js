document.addEventListener('DOMContentLoaded', () => {
  const timeEl = document.getElementById('currentTime');
  const dateEl = document.getElementById('currentDate');

  
  function updateDateTime() {
    const now = new Date();
    const optionsDate = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
    const formattedDate = now.toLocaleDateString('en-US', optionsDate);
    const formattedTime = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true });
    if (dateEl) dateEl.textContent = formattedDate;
    if (timeEl) timeEl.textContent = formattedTime;
  }

  updateDateTime();
  setInterval(updateDateTime, 1000);

  // Highlight active link based on current URL
  document.querySelectorAll('.sidebar-nav a').forEach(link => {
    if (link.href === window.location.href) {
      link.classList.add('active');
    }
  });

  window.logout = function logout() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = 'superadminLogin.php?logout=true';
    }
  }
});
