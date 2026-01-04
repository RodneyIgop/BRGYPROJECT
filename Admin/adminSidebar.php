<style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background-color: #f5f5f5;
}

/* SIDEBAR */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 240px;
    min-width: 240px;
    max-width: 240px;
    height: 100vh;
    background-color: #014A7F;
    padding: 20px;
    overflow-y: auto;
    overflow-x: hidden;
    /* Reserve space for scrollbar to avoid width shift */
    scrollbar-gutter: stable;
    scrollbar-width: none;
    z-index: 100;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
}

.sidebar-logo img {
    width: 50px;
    height: 50px;
    margin-right: 10px;
}

.sidebar-logo h2 {
    color: #fff;
    font-size: 14px;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.sidebar-nav a, .sidebar-nav button {
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
    transition: background-color 0.2s;
}

.sidebar-nav a:hover, .sidebar-nav button:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-nav a.active {
    background-color: rgba(255, 255, 255, 0.2);
}

.sidebar-nav button {
    margin-top: auto;
}

/* MAIN CONTENT */
.main-content {
    margin-left: 240px;
    padding: 20px;
}

/* HEADER */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header h1 {
    font-size: 32px;
    color: #333;
}

.header-date {
    text-align: center;
    font-size: 14px;
    color: #333;
    background: #fff;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
    padding: 8px 16px;
    min-width: 200px;
}

.header-date .date {
    font-weight: bold;
}

.header-date .time {
    font-weight: bold;
}

/* DASHBOARD GRID */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.card {
    background: #fff;
    border-radius: 4px;
    padding: 24px;
    border: 1px solid #d9d9d9; /* replaced box-shadow */
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-height: 180px;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.card-icon {
    width: 24px;
    height: 24px;
}

.card-description {
    font-size: 14px;
    color: #666;
    line-height: 1.5;
}

.card-total {
    font-size: 14px;
    color: #666;
    font-weight: 600;
}

.card-link {
    color: #014A7F;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    cursor: pointer;
}

.card-link:hover {
    text-decoration: underline;
}

/* FOOTER */
.footer-contact i {
    font-size: 1rem;
}

.footer-urgent {
    font-size: 14px;
    font-style: italic;
    max-width: 250px;
    text-align: right;
}
.footer {
    background-color: #014A7F;
    color: #fff;
    padding: 40px 20px;
    margin-top: 40px;
    width: 100%;
    padding-left: 260px; /* account for wider sidebar */
}

.footer-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    flex-wrap: wrap;
    max-width: 1200px;
}

.footer-contact h3 {
    font-size: 16px;
    margin-bottom: 16px;
}

.footer-contact p, .footer-contact a {
    font-size: 14px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
    text-decoration: none;
}

.footer-contact a:hover {
    text-decoration: underline;
}

.footer-logo {
    text-align: center;
}

.footer-logo img {
    width: 80px;
    height: 80px;
    margin-bottom: 12px;
}

.footer-copyright {
    text-align: center;
    font-size: 12px;
    color: #bbb;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
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

/* Sidebar image icon styling */
.sidebar-nav a img,
.sidebar-nav button img,
.sidebar-dropdown summary img,
.submenu-link img {
    width: 1em;
    height: 1em;
    object-fit: contain;
    filter: brightness(0) invert(1);
}

/* Heavier look for specific status icons */
.submenu-link img[src*="pending"],
.submenu-link img[src*="approved"],
.submenu-link img[src*="complete"] {
    width: 1.4em;
    height: 1.4em;
    filter: brightness(0) invert(1);
}

/* Arrow icon inside summary (down.png) */
.sidebar-dropdown summary img:last-child {
    margin-left: auto;
    width: 1.2em;
    height: 1.2em;
    object-fit: contain;
    filter: brightness(0) invert(1);
    transition: transform 0.2s;
}
.sidebar-dropdown[open] summary img:last-child {
    transform: rotate(180deg);
}

/* Sidebar dropdown styles */
.sidebar-dropdown {
    display: flex;
    flex-direction: column;
}

.sidebar-dropdown summary {
    list-style: none;
    display: flex;
    width: 100%;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    color: #fff;
    padding: 12px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.2s;
}

.sidebar-dropdown summary:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-dropdown[open] summary {
    background-color: rgba(255, 255, 255, 0.2);
}

.sidebar-dropdown summary::-webkit-details-marker {
    display: none;
}

.submenu-link {
    padding: 8px 16px 8px 48px;
    color: #fff;
    text-decoration: none;
    font-size: 15px;
    border-radius: 6px;
}

.submenu-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 10px;
    }

    .main-content {
        margin-left: 0;
        padding: 16px;
    }

    .header {
        flex-direction: column;
        text-align: center;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .footer {
        margin-left: 0;
    }

    .footer-content {
        grid-template-columns: 1fr;
    }
}

</style>

<div class="sidebar">
        <div class="sidebar-logo">
            <img src="../images/brgylogo.png" alt="Logo">
            <h2>BARANGAY NEW ERA</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="adminIndex.php" class="active"> <img src="../images/home.png" alt="">Home</a>
            <a href="adminProfile.php"> <img src="../images/user.png" alt="">Profile</a>
            <details class="sidebar-dropdown">
                <summary><img src="../images/list.png" alt="">Request Lists <img src="../images/down.png" alt=""></summary>
                <a href="adminpending.php" class="submenu-link"> <img src="../images/pending.png" alt="">Pending and for review</a>
                <a href="adminapproved.php" class="submenu-link"> <img src="../images/approved.png" alt="">Approved and For Pick Up</a>
                <a href="adminRequestLists.php?status=approved" class="submenu-link"> <img src="../images/complete.png" alt="">Signed and released</a>
            </details>
            <a href="adminarchive.php"> <img src="../images/archive.png" alt="">Archive</a>
            <a href="adminAnnouncements.php"> <img src="../images/marketing.png" alt=""> Announcements</a>
            <a href="adminMessages.php"> <img src="../images/email.png" alt="">Messages</a>
            <a href="adminResidents.php"> <img src="../images/residents.png" alt="">Residents</a>
            <button onclick="showLogoutPopup()" style="margin-top:auto;"> <img src="../images/logout.png" alt="">Logout</button>
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
            // Redirect to login page (this will end the session)
            window.location.href = 'adminLogin.php';
        }

        // Close popup on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLogoutPopup();
            }
        });
    </script>

    <script src="adminIndex.js"></script>
