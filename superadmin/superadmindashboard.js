document.addEventListener('DOMContentLoaded', () => {
  const timeEl = document.getElementById('currentTime');
  const dateEl = document.getElementById('currentDate');

  // ================= DATE / TIME =================
  function updateDateTime() {
    const now = new Date();
    const optionsDate = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
    const formattedDate = now.toLocaleDateString('en-US', optionsDate);
    const formattedTime = now.toLocaleTimeString(
      'en-US',
      { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true }
    );

    if (dateEl) dateEl.textContent = formattedDate;
    if (timeEl) timeEl.textContent = formattedTime;
  }

  updateDateTime();
  setInterval(updateDateTime, 1000);

  // ================= ACTIVE LINK =================
  document.querySelectorAll('.sidebar-nav a').forEach(link => {
    if (link.href === window.location.href) {
      link.classList.add('active');
    }
  });

  // ================= LOGOUT =================
  window.logout = function logout() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = 'superadminLogin.php?logout=true';
    }
  }

  // ================= NOTIFICATIONS =================
  window.toggleNotifications = function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('show');

    // Remove badge when dropdown is opened and update last view time
    if (dropdown.classList.contains('show')) {
      const badge = document.querySelector('.notification-badge');
      if (badge) badge.remove();
      
      // Update the last view time to track new notifications
      fetch('update_notifications_viewed.php', { method: 'POST' });
      
      setTimeout(() => document.addEventListener('click', closeNotificationsOutside), 100);
    }
  }

  function closeNotificationsOutside(event) {
    const dropdown = document.getElementById('notificationDropdown');
    const bell = document.querySelector('.notification-bell');

    if (!dropdown.contains(event.target) && !bell.contains(event.target)) {
      dropdown.classList.remove('show');
      document.removeEventListener('click', closeNotificationsOutside);
    }
  }

  // ================= VIEW NOTIFICATION =================
  window.viewNotification = function viewNotification(link, id, type) {
    event.stopPropagation();
    
    // Mark notification as read in database
    fetch('mark_notification_read_new.php', {
      method: 'POST',
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}`
    });
    
    // Navigate to the link
    window.location.href = link;
  }

  // ================= DELETE NOTIFICATION =================
  window.deleteNotification = function deleteNotification(id, type) {
    event.stopPropagation();
    
    if (!confirm('Are you sure you want to delete this notification?')) {
      return;
    }

    // Remove from UI immediately
    const item = event.target.closest('.notification-item');
    item.remove();

    // Update badge count
    const badge = document.querySelector('.notification-badge');
    if (badge) {
      let count = parseInt(badge.textContent) || 0;
      count = Math.max(count - 1, 0);
      if (count === 0) badge.remove();
      else badge.textContent = count;
    }

    // Check if no notifications left
    const notificationList = document.querySelector('.notification-list');
    const remainingItems = notificationList.querySelectorAll('.notification-item');
    
    if (remainingItems.length === 0) {
      notificationList.innerHTML = `
        <div class="no-notifications">
          <i class="bi bi-bell-slash"></i>
          <p>No new notifications</p>
        </div>
      `;
      
      // Hide clear all and view all links
      const clearAll = document.querySelector('.clear-all');
      const viewAll = document.querySelector('.view-all');
      if (clearAll) clearAll.style.display = 'none';
      if (viewAll) viewAll.style.display = 'none';
    }

    // Delete from database
    fetch('delete_notification_db.php', {
      method: 'POST',
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}`
    });
  }

  // ================= VIEW ALL NOTIFICATIONS =================
  window.viewAllNotifications = function viewAllNotifications(event) {
    event.preventDefault();
    event.stopPropagation();
    
    // Navigate to all notifications page
    window.location.href = 'all_notifications.php';
  }

  // ================= CLEAR ALL =================
  window.clearAllNotifications = function clearAllNotifications(event) {
    event.preventDefault();
    event.stopPropagation();

    if (!confirm('Are you sure you want to clear all notifications?')) {
      return;
    }

    // Remove badge
    const badge = document.querySelector('.notification-badge');
    if (badge) badge.remove();

    // Reset list UI
    const notificationList = document.querySelector('.notification-list');
    if (notificationList) {
      notificationList.innerHTML = `
        <div class="no-notifications">
          <i class="bi bi-bell-slash"></i>
          <p>No new notifications</p>
        </div>
      `;
    }

    // Hide clear all and view all links
    const clearAll = document.querySelector('.clear-all');
    const viewAll = document.querySelector('.view-all');
    if (clearAll) clearAll.style.display = 'none';
    if (viewAll) viewAll.style.display = 'none';

    // Delete all from DB
    fetch('clear_all_notifications.php', { method: 'POST' });

    // Close dropdown
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.remove('show');
  }
});
