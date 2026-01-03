<?php
include '../Connection/conn.php';
session_start();

// Assuming the resident's name is stored in session
$residentName = isset($_SESSION['resident_name']) ? $_SESSION['resident_name'] : 'Resident';
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Resident Dashboard - BARANGAY NEW ERA</title>

  <!-- Bootstrap -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
  />

  <!-- Bootstrap Icons (ONLY once — latest version) -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
  />

  <!-- Font Awesome (optional, still okay) -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
  />

  <!-- Your custom CSS -->
  <link rel="stylesheet" href="ResidentsIndex.css" />

  <!-- Fix: ensure Bootstrap Icons don’t turn into emoji -->
</head>
 
   <style>
    i.bi {
      font-family: "bootstrap-icons" !important;
      font-style: normal;
    }
    .sidebar {
        width: 250px;
        background: #00386b;
        color: #fff;
        display: flex;
        flex-direction: column;
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        z-index: 1000;
    }

    /* Logo section */
    .sidebar .logo {
        width: 100%;
        padding: 25px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 10px;
        /* height: 178px; */
    }

    .sidebar .logo-img {
        width: 80px;
        height: auto;
        margin-bottom: 10px;
        border-radius: 50%;
        object-fit: cover;
    }

    .sidebar .logo-text {
        color: #fff;
    }

    .sidebar .logo h2 {
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin: 10px 0;
        text-transform: uppercase;
        /* height: 23px; */
    }

    .sidebar .logo p {
        font-size: 0.7rem;
        margin: 0;
        opacity: 0.9;
        letter-spacing: 0.5px;
    }

    .sidebar nav ul {
        list-style: none;
        margin: 0;
        padding: 0;

    }

    .sidebar nav ul li {
        width: 100%;
        position: relative;
    }

    /* Link styles */
    .sidebar nav a {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 25px;
        color: #fff;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .sidebar nav a:hover,
    .sidebar nav a.active {
        background: #0d4070;
        /* darker blue on hover/active */
    }

    /* Left indicator bar */
    .sidebar nav a.active::before,
    .sidebar nav a:hover::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 5px;
        background: #3b82f6;
        /* bright blue indicator */
    }

    .sidebar nav i {
        width: 20px;
        text-align: center;
        font-size: 1.1em;
    }

    /* Logout link at bottom */
    /* .logout {
        margin-top: auto;
    }

    .logout a {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 25px;
        color: #fff;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .logout a:hover {
        background: #0d4070;
    }

    /* Icons specific styles */
    /* .bi-house::before {
        content: "\f3e5";
    }

    .bi-person::before {
        content: "\f4e1";
    }

    .bi-file-earmark-text::before {
        content: "\f31c";
    }

    .bi-card-checklist::before {
        content: "\f2d9";
    } */ */
</style> 

<body>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="../images/brgylogo.png" alt="Barangay Logo" class="logo-img">
            <div class="logo-text">
                <h2>BARANGAY NEW ERA</h2>
            </div>
        </div>

        <nav>
            <ul>
                <li><a href="ResidentsIndex.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="ResidentsProfile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li class="active"><a href="ResidentsRequestDocu.php"><i class="fas fa-file-alt"></i> Document
                        Request</a></li>
                <li><a href="residentstrackrequest.php"><i class="fas fa-list"></i> My Request</a></li>
                <li><a href="ResidentsArchive.php"><i class="fas fa-archive"></i> Archive</a></li>
                <li class="logout"><a href="residentlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main -->
    <main class="main">
        <div class="content-wrapper">
            <div class="header">
                <div class="greeting">
                    <h2>Hello, <?php echo htmlspecialchars($residentName); ?></h2>
                </div>
                <div class="clock-card">
                    <div id="currentDate"></div>
                    <div class="time" id="currentTime"></div>
                </div>
            </div>

            <h5 class="text-center mb-3">Steps in Requesting a Document</h5>
            <div class="step-box">
                <div class="carousel-container">
                    <div class="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="step-number">1</div>
                                <h4>Click the Document Request</h4>
                                <p>Click on the Document Request option in the sidebar menu.</p>
                            </div>
                            <div class="carousel-item">
                                <div class="step-number">2</div>
                                <h4>Select Document Type</h4>
                                <p>Choose the specific document you need from the available options.</p>
                            </div>
                            <div class="carousel-item">
                                <div class="step-number">3</div>
                                <h4>Provide the necessary details</h4>
                                <p>Fill in all the required information for your document request.</p>
                            </div>
                            <div class="carousel-item">
                                <div class="step-number">4</div>
                                <h4>Click submit</h4>
                                <p>Review your information and click the submit button to finalize your request.</p>
                            </div>
                        </div>
                        <button class="carousel-control prev" onclick="moveSlide(-1)">❮</button>
                        <button class="carousel-control next" onclick="moveSlide(1)">❯</button>
                    </div>
                    <div class="carousel-indicators">
                        <span class="indicator active" onclick="goToSlide(0)"></span>
                        <span class="indicator" onclick="goToSlide(1)"></span>
                        <span class="indicator" onclick="goToSlide(2)"></span>
                        <span class="indicator" onclick="goToSlide(3)"></span>
                    </div>
                </div>
                <div class="note-box">
                    <p><strong>Note:</strong> To track the progress of your requested document, you may view it in My
                        Request.</p>
                </div>
            </div>

            <div class="quick-links">
                <!-- My Profile -->
                <div class="quick-card text-center">
                    <i class="bi bi-person-fill" style="font-size:1.8rem;"></i>
                    <h6>My Profile</h6>
                    <p>Manage your personal information</p>
                    <a href="ResidentsProfile.php">View Profile <i class="bi bi-arrow-right"></i></a>
                </div>
                <!-- Request Documents -->
                <div class="quick-card text-center">
                    <i class="bi bi-file-earmark-text" style="font-size:1.8rem;"></i>
                    <h6>Request Documents</h6>
                    <p>Request barangay certificates and official documents</p>
                    <a href="ResidentsRequestDocu.php">Make Request <i class="bi bi-arrow-right"></i></a>
                </div>
                <!-- View Request -->
                <div class="quick-card text-center">
                    <i class="bi bi-eye" style="font-size:1.8rem;"></i>
                    <h6>View Request</h6>
                    <p>Track the status of your submitted request</p>
                    <a href="residentstrackrequest.php">View Request <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="quick-card text-center">
                    <i class="bi bi-archive-fill" style="font-size:1.8rem;"></i>
                    <h6>Archived</h6>
                    <p>View canceled or declined requests</p>
                    <a href="ResidentsArchive.php">View Archived <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>

            <h6 class="announcement-title">Latest Announcement for You</h6>

        </div>
        <section id="announcements" class="custom-bg">
            <div class="container">
                <div class="text-center custom mb-5">

                </div>

                <div class="row justify-content-center">
                    <?php
                    $stmt = $conn->prepare("SELECT title, description, image, date FROM announcements ORDER BY date DESC LIMIT 3");
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <img src="../images/<?= htmlspecialchars($row['image']) ?>" class="card-img-top"
                                        style="height:200px;object-fit:cover;">

                                    <div class="card-body text-center">
                                        <small class="custom-text-color2 font-weight-bold">
                                            <?= date("F d, Y", strtotime($row['date'])) ?>
                                        </small>
                                        <h5 class="mt-2"><?= htmlspecialchars($row['title']) ?></h5>
                                        <p><?= htmlspecialchars($row['description']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endwhile;
                    else:
                        echo '<p class="text-center">No announcements available.</p>';
                    endif;
                    $stmt->close();
                    ?>
                </div>
        </section>


    </main>

    <script src="ResidentsIndex.js"></script>
</body>


</html>