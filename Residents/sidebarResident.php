<style>
    .container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
    width: 250px;
    background-color: #00386b;
    color: white;
    padding: 20px 0;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
}

.logo {
    padding: 0 20px 20px;
    margin-bottom: 20px;
    margin-left: 4em;
}

.logo h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-left: -3em;
}

.sidebar nav ul {
    list-style: none;
}

.sidebar nav ul li {
    margin: 5px 0;
}

.sidebar nav ul li a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 12px 20px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
}

.sidebar nav ul li a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.sidebar nav ul li a:hover,
.sidebar nav ul li.active a {
    background-color: rgba(255, 255, 255, 0.1);
    border-left: 4px solid #3b82f6;
    padding-left: 16px;
}
</style>
 
 
 <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="logo">
                <img src="../images/brgylogo.png" alt="Barangay Logo" style="width:80px;margin-bottom:10px;">
                <h2>BARANGAY&nbsp;NEW&nbsp;ERA</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="ResidentsIndex.php"><i class="fas fa-home"></i> Home</a></li>
                    <li class="active"><a href="#"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="ResidentsRequestDocu.php"><i class="fas fa-file-alt"></i> Document Request</a></li>
                    <li><a href="residentstrackrequest.php"><i class="fas fa-list"></i> My Request</a></li>
                    <li><a href="ResidentsArchive.php"><i class="fas fa-archive"></i> Archive</a></li>
                    <li><a href="residentlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>