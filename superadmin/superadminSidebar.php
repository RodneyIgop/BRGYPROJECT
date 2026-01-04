
<style>
    /* ================= SIDEBAR ================= */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 240px;
    height: 100vh;
    background-color: #014A7F;
    padding: 20px;
    overflow-y: auto;
    scrollbar-gutter: stable;
    scrollbar-width: none;
    z-index: 100;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 30px;
}

.sidebar-logo img {
    width: 50px;
    height: 50px;
}

.sidebar-logo h2 {
    color: #fff;
    font-size: 14px;
    line-height: 1.2;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.sidebar-nav a,
.sidebar-nav button,
.sidebar-dropdown summary {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fff;
    text-decoration: none;
    padding: 12px 16px;
    border-radius: 8px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.2s ease;
}

.sidebar-nav a:hover,
.sidebar-nav button:hover,
.sidebar-dropdown summary:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-nav a.active,
.sidebar-dropdown[open] summary {
    background-color: rgba(255, 255, 255, 0.2);
}

.sidebar-nav button {
    margin-top: auto;
}

/* ================= DROPDOWN ================= */
.sidebar-dropdown {
    display: flex;
    flex-direction: column;
}

.sidebar-dropdown summary {
    list-style: none;
}

.sidebar-dropdown summary::-webkit-details-marker {
    display: none;
}

.submenu-link {
    padding: 8px 16px 8px 48px;
    font-size: 15px;
    border-radius: 6px;
}

.submenu-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

/* Sidebar Icons */
.sidebar-nav img,
.sidebar-dropdown summary img,
.submenu-link img {
    width: 1em;
    height: 1em;
    object-fit: contain;
    filter: brightness(0) invert(1);
}

/* Arrow rotation */
.sidebar-dropdown summary img:last-child {
    margin-left: auto;
    transition: transform 0.2s;
}

.sidebar-dropdown[open] summary img:last-child {
    transform: rotate(180deg);
}


/* Heavier look for specific status icons */
.submenu-link img[src*="addAdmin"],
.submenu-link img[src*="addUser"],
.submenu-link img[src*="pending"] {
    width: 2em;
    height: 1.4em;
    filter: brightness(0) invert(1);
}

/* ================== Logout Confirmation Popup ================== */
.logout-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9998;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.logout-overlay.show {
    opacity: 1;
    visibility: visible;
}

.logout-popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.8);
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    z-index: 9999;
    text-align: center;
    min-width: 320px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.logout-popup.show {
    opacity: 1;
    visibility: visible;
    transform: translate(-50%, -50%) scale(1);
}

.logout-popup h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 20px;
    font-weight: 600;
}

.logout-popup p {
    margin: 0 0 25px 0;
    color: #666;
    font-size: 16px;
    line-height: 1.5;
}

.logout-popup-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.logout-btn {
    padding: 10px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 80px;
}

.logout-btn-yes {
    background: #dc3545;
    color: white;
}

.logout-btn-yes:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.logout-btn-no {
    background: #6c757d;
    color: white;
}

.logout-btn-no:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.logout-btn:active {
    transform: translateY(0);
}
</style><!-- SIDEBAR (UNCHANGED STRUCTURE) -->
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="../images/brgylogo.png">
        <h2>BARANGAY NEW ERA</h2>
    </div>

    <nav class="sidebar-nav">
        <a href="superadmindashboard.php" >
            <img src="../images/home.png"> Home
        </a>

        <a href="superadminProfile.php" >
            <img src="../images/user.png"> Profile
        </a>

        <!-- ACCOUNT MANAGEMENT -->
        <details class="sidebar-dropdown">
            <summary>
                <img src="../images/list.png"> Account Management
                <img src="../images/down.png">
            </summary>
            <a href="superadminAdminAccs.php" class="submenu-link">
                <img src="../images/addAdmin.png"> Manage Admin Accounts
            </a>
            <a href="superadminUserAccs.php" class="submenu-link">
                <img src="../images/addUser.png"> Manage Residents Accounts
            </a>
            <a href="superadminAccounts.php" class="submenu-link">
                <img src="../images/addAdmin.png"> Manage Superadmin Accounts
            </a>
           
        </details>

        <!-- ACTIVITY LOGS -->
        <a href="superadminLogs.php">
            <img src="../images/monitor.png"> Activity Logs
        </a>

        <!-- ATTENDANCE -->
        <a href="superadminAttendance.php">
            <img src="../images/attendance.png"> Attendance
        </a>

        <!-- RESIDENT INFO -->
        <a href="superadminResidents.php">
            <img src="../images/residents.png"> Resident Information
        </a>

        <!-- ARCHIVES -->
        <a href="superadminarchive.php">
            <img src="../images/archive.png"> Archives
        </a>

        <button onclick="showLogoutPopup()" style="margin-top:auto;">
            <img src="../images/logout.png"> Logout
        </button>
    </nav>
</div>

<!-- Logout Confirmation Popup -->
<div class="logout-overlay" id="logoutOverlay" onclick="closeLogoutPopup()"></div>
<div class="logout-popup" id="logoutPopup">
    <h3>Confirm Logout</h3>
    <p>Are you sure you want to log out?</p>
    <div class="logout-popup-buttons">
        <button class="logout-btn logout-btn-yes" onclick="confirmLogout()">Yes</button>
        <button class="logout-btn logout-btn-no" onclick="closeLogoutPopup()">No</button>
    </div>
</div>

<script>
    function showLogoutPopup() {
        const overlay = document.getElementById('logoutOverlay');
        const popup = document.getElementById('logoutPopup');
        
        overlay.classList.add('show');
        popup.classList.add('show');
        
        // Prevent body scroll when popup is open
        document.body.style.overflow = 'hidden';
    }

    function closeLogoutPopup() {
        const overlay = document.getElementById('logoutOverlay');
        const popup = document.getElementById('logoutPopup');
        
        overlay.classList.remove('show');
        popup.classList.remove('show');
        
        // Restore body scroll
        document.body.style.overflow = 'auto';
    }

    function confirmLogout() {
        // Redirect to superadmin login page (this will end the session)
        window.location.href = 'superadminlogin.php?logout=true';
    }

    // Close popup on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeLogoutPopup();
        }
    });
</script>