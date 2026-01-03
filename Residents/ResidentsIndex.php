<?php
include '../Connection/conn.php';
?>
<?php
session_start();
// Assuming the resident's name is stored in session
$residentName = isset($_SESSION['resident_name']) ? $_SESSION['resident_name'] : 'Resident';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ResidentsIndex.css">
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="../images/brgylogo.png" alt="Logo">
            <strong>BARANGAY NEW ERA</strong>
        </div>
        <nav class="flex-grow-1">
            <a href="#" class="active"><i class="bi bi-house"></i>Home</a>
            <a href="ResidentsProfile.php"><i class="bi bi-person"></i>Profile</a>
            <a href="ResidentsRequestDocu.php"><i class="bi bi-file-earmark-text"></i>Document Request</a>
            <a href="residentstrackrequest.php"><i class="bi bi-card-checklist"></i>My Request</a>
            <a href="ResidentsArchive.php"><i class="bi bi-archive"></i>Archive</a>
            <div class="logout">
                <a href="../index.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
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
        <p><strong>Note:</strong> To track the progress of your requested document, you may view it in My Request.</p>
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
                    <i class="bi bi-eye" style="font-size:1.8rem;"></i>
                    <h6>Archived</h6>
                    <p>View your cancelled and declined document requests</p>
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
                    <img src="../uploaded_img/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height:200px;object-fit:contain;image-rendering:-webkit-optimize-contrast;image-rendering:crisp-edges;image-rendering:pixelated;">

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