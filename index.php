<?php
require_once __DIR__ . '/Connection/conn.php';

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contactnumberRaw = trim($_POST['contactnumber'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $digitsOnly = preg_replace('/\D+/', '', $contactnumberRaw);

    if ($fullname === '' || $message === '' || $digitsOnly === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: index.php?msg=error#getintouch');
        exit;
    }

    $contactnumber = (int)$digitsOnly;

    $dateColumnName = null;
    $dateColumnType = null;
    $columnsResult = $conn->query('SHOW COLUMNS FROM messages');
    if ($columnsResult instanceof mysqli_result) {
        while ($col = $columnsResult->fetch_assoc()) {
            $field = (string)($col['Field'] ?? '');
            $type = strtolower((string)($col['Type'] ?? ''));
            if ($field === '') continue;

            $looksLikeDateType = (strpos($type, 'date') !== false) || (strpos($type, 'timestamp') !== false);
            $lname = strtolower($field);
            $looksLikeDateName = in_array($lname, ['date', 'datesent', 'date_sent', 'sentdate', 'sent_date', 'createdat', 'created_at', 'createdon', 'created_on', 'timestamp'], true)
                || (strpos($lname, 'date') !== false)
                || (strpos($lname, 'time') !== false);

            if ($looksLikeDateType && $looksLikeDateName) {
                $dateColumnName = $field;
                $dateColumnType = $type;
                break;
            }
        }
        $columnsResult->free();
    }

    $stmt = null;
    if ($dateColumnName && preg_match('/^[A-Za-z0-9_]+$/', $dateColumnName)) {
        $sql = 'INSERT INTO messages (Fullname, email, contactnumber, message, `' . $dateColumnName . '`) VALUES (?, ?, ?, ?, ?)';
        $nowValue = (strpos($dateColumnType, 'date') !== false && strpos($dateColumnType, 'time') === false)
            ? date('Y-m-d')
            : date('Y-m-d H:i:s');
        $stmt = $conn->prepare($sql);
        if ($stmt) $stmt->bind_param('ssiss', $fullname, $email, $contactnumber, $message, $nowValue);
    } else {
        $stmt = $conn->prepare('INSERT INTO messages (Fullname, email, contactnumber, message) VALUES (?, ?, ?, ?)');
        if ($stmt) $stmt->bind_param('ssis', $fullname, $email, $contactnumber, $message);
    }

    if (!$stmt) {
        header('Location: index.php?msg=error#getintouch');
        exit;
    }

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: index.php?msg=sent#getintouch');
        exit;
    }

    $stmt->close();
    header('Location: index.php?msg=error#getintouch');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>\
<link rel="stylesheet" href="landingpage.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Barangay New Era | Document Request System</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<style>
body {
  scroll-behavior: smooth;
}

.navbar-brand img {
  height: 40px;
}

section {
  padding: 80px 0;
}

.hero {
  position: relative;
  background-image: url('./images/barangay hall_background.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  min-height: calc(80vh - 70px);
  display: flex;
  align-items: center;
  padding-top: 70px;
}

.hero::before {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
}

.hero > .container {
  position: relative;
  z-index: 1;
}
/* Logo */
.hero-logo {
  max-width: 400px;
  width: 100%;
}
.title-text {
  font-weight: bold;
  font-size: clamp(1.8rem, 6vw, 3.5rem); /* scales fluidly */
  line-height: 1.2;
}

.subtitle-text {
  font-size: clamp(1rem, 2.5vw, 1.5rem); /* fluid */
  margin-top: 0.5rem;
}

/* Buttons */
.custom-button-color {
  background-color: #014A7F;
  color: white;
  border: none;
  padding: 12px 24px;
  text-decoration: none;
  font-weight: bold;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  transition: 0.3s ease;
  white-space: nowrap;
}

.custom-button-color:hover {
  background-color: #014A7F;
  color: #ffffff !important;
}

.btn-icon {
  width: 24px;
  height: 24px;
  margin-right: 8px;
  filter: brightness(0) invert(1);
}

/* Mobile buttons wrap */
@media (max-width: 576px) {
  .d-flex.flex-wrap.justify-content-center.justify-content-md-start {
    flex-direction: column;
    align-items: center;
  }
  .custom-button-color {
    width: 80%;
    justify-content: center;
    margin: 5px 0;
  }
.hero img {
  max-width: 300px;
}
}

.hero .display-4 {
  font-size: 2.5rem;
}

@media (max-width: 768px) {
  .hero .display-4 {
    font-size: 1.8rem;
    text-align: center;
  }
  .hero .lead {
    font-size: 1rem;
    text-align: center;
  }
  .hero img {
    max-width: 250px;
  }
  .hero .mt-4 a {
    display: block;
    margin: 10px auto;
  }
}

@media (max-width: 768px) {
  #About .row {
    flex-direction: column;
  }
  #About img {
    margin-bottom: 20px;
  }
}

.card-img-top {
  width: 100%;
  height: auto;
}

.service-card img {
  width: 50px;
  max-width: 100%;
}

@media (max-width: 768px) {
  .service-card img {
    width: 40px;
  }
}

@media (max-width: 768px) {
  #getintouch .row > div {
    width: 100%;
  }
}

@media (max-width: 768px) {
  footer .row {
    flex-direction: column;
    text-align: center;
  }
  footer img {
    margin-bottom: 15px;
  }
}

@media (max-width: 768px) {
  .navbar .btn {
    margin: 5px 0;
    width: 100%;
  }
}


.service-card img {
  width: 70px;
}

footer {
  background: #014A7F;
  color: #fff;
  padding: 40px 0;
}

footer a {
  color: #fff;
  text-decoration: none;
}

footer a:hover {
  text-decoration: underline;
}

/* About Section Paragraph Styling */
#About .col-md-6 p {
    font-size: 1.60rem;            
    line-height: 1.8;              
    color: #1a1a1a;                
    text-align: justify;            
    font-weight: 500;               
    margin-top: 20px;               
}


#About .col-md-6 p strong {
    font-weight: 700;
    color: #014A7F; /* Barangay theme color */
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #About .col-md-6 p {
        text-align: left;           
        font-size: 1rem;
    }
}
.btn-icon {
  filter: brightness(0) invert(1);
}

</style>
</head>

<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow custom-bg">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="images/brgylogo.png" class="mr-2">
      <strong>Barangay New Era</strong>
      <div>hehe</div>
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"><a class="nav-link" href="#Home">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#About">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#announcements">Announcements</a></li>
        <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
        <li class="nav-item ml-lg-3">
          <a class="btn btn-light btn-sm" href="Residents/residentlogin.php">Login</a>
        </li>
        <li class="nav-item ml-lg-2">
          <a class="btn btn-outline-light btn-sm" href="Residents/residentregister.php">Register</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ================= HERO ================= -->
<!-- <section id="Home" class="hero pt-5">
  <div class="container">
    <div class="row align-items-center justify-content-between">

      <div class="col-md-6 text-center text-md-left mr-md-5">

        <p class="font-weight-bold custom-text-color2 display-3">
          Welcome to <br>
          <span class="custom-text-color1">Barangay New Era</span>
        </p>
        <p class="lead custom-text-color1">Document Request System</p>

       <div class="mt-4">
          <a href="Residents/residentlogin.php" class="custom-button-color btn-lg mr-4">
            <img src="images/docu.png" alt="Document Icon" class="btn-icon" style="width:24px; margin-right:8px;">
            Request Documents
          </a>

          <a href="Residents/residentregister.php" class="custom-button-color btn-lg">
            <img src="images/tao.png" alt="Register Icon" class="btn-icon" style="width:24px; margin-right:8px;">
            Register Now
          </a>
        </div>

      </div>

      <div class="col-md-5 text-center">
        <img src="images/brgylogo.png" class="img-fluid" style="max-width: 400px;">
      </div>

    </div>
  </div>
</section> -->

<section id="Home" class="hero pt-5">
  <div class="container">
    <div class="row align-items-center justify-content-between">

      <!-- Text Section -->
      <div class="col-lg-6 col-md-7 text-center text-md-left mb-5 mb-md-0">

        <p class="font-weight-bold custom-text-color1 special-font title-text">
          Welcome to <br>
          <span class="custom-text-color1 special-font">Barangay New Era</span>
        </p>
        <p class="lead custom-text-color1 subtitle-text">Document Request System</p>

        <!-- Buttons -->
        <div class="mt-4 d-flex flex-wrap justify-content-center justify-content-md-start">
          <a href="Residents/residentlogin.php" class="custom-button-color d-flex align-items-center m-1">
            <img src="images/docu.png" alt="Document Icon" class="btn-icon">
            Request Documents
          </a>

          <a href="Residents/residentregister.php" class="custom-button-color d-flex align-items-center m-1">
            <img src="images/tao.png" alt="Register Icon" class="btn-icon">
            Register Now
          </a>
        </div>

      </div>

      <!-- Logo Section -->
      <div class="col-lg-5 col-md-5 text-center">
        <img src="images/brgylogo.png" class="img-fluid hero-logo">
      </div>

    </div>
  </div>
</section>



<!-- ================= ABOUT ================= -->
<section id="About">
  <div class="container">
    <div class="text-center mb-4">
      <h2>About Our Barangay</h2>
    </div>

    <div class="row align-items-center">
      <div class="col-md-6 mb-3">
      <img src="images/barangay hall_background.jpg"
          class="img-fluid rounded shadow">
      </div>
      <div class="col-md-6">
        <p>
            <strong>Barangay New Era</strong> is a vibrant and thriving community dedicated to 
            fostering unity, safety, and progress among its residents. Our barangay is committed 
            to providing excellent public service and promoting a harmonious environment for all.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ================= ANNOUNCEMENTS ================= -->
<section id="announcements" class="bg-light">
  <div class="container">
    <div class="text-center custom mb-5">
      <h2>Announcements</h2>
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
          <img src="images/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
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

<!-- ================= SERVICES ================= -->
<section id="services">
  <div class="container">
    <div class="text-center mb-5">
      <h2>Barangay Services</h2>
    </div>

   <div class="row text-center services-row">
    <?php
      $services = [
      ["clearance.png","Barangay Clearance"],
      ["indigency.png","Certificate of Indigency"],
      ["residency.png","Certificate of Residency"],
      ["permit2.png","Barangay Permit"]
      ];
      foreach ($services as $s):
    ?>
      <div class="col-md-3 mb-4">
        <div class="card service-card shadow-sm h-100">
          <div class="card-body">
            <img src="images/<?= $s[0] ?>">
            <h6 class="mt-3"><?= $s[1] ?></h6>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <a href="Residents/residentlogin.php" class="btn custom-button-color ">
        Request Now
      </a>
    </div>
  </div>
</section>

<!-- ================= CONTACT ================= -->
<section id="getintouch" class="bg-light">
  <div class="container">
    <div class="text-center mb-4">
      <h2>Get In Touch</h2>
    </div>

<?php if(isset($_GET['msg'])): ?>
  <div id="messageAlert" class="alert <?= $_GET['msg']=='sent' ? 'alert-success' : 'alert-danger' ?>" role="alert">
    <?= $_GET['msg']=='sent' ? 'Message sent successfully!' : 'Failed to send message.' ?>
  </div>

  <script>
    // Hide the alert after 1 second (1000 milliseconds)
    setTimeout(function() {
      const alert = document.getElementById('messageAlert');
      if(alert) {
        alert.style.transition = "opacity 0.5s";
        alert.style.opacity = '0';
        setTimeout(()=>alert.remove(), 500); // remove from DOM after fade
      }
    }, 1000);
  </script>
<?php endif; ?>

    <form method="POST" action="#getintouch" class="row">
      <div class="col-md-4 mb-3">
        <input type="text" name="fullname" class="form-control" placeholder="Name" required>
      </div>
      <div class="col-md-4 mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="col-md-4 mb-3">
        <input type="text" name="contactnumber" class="form-control" placeholder="Contact No." required>
      </div>
      <div class="col-12 mb-3">
        <textarea name="message" class="form-control" rows="4" placeholder="Message..." required></textarea>
      </div>
      <div class="col-12 text-center">
        <button type="submit" name="send_message" class="btn custom-button-color btn-lg">Send Message</button>
      </div>
    </form>
  </div>
</section>

<!-- ================= FOOTER ================= -->
<footer>
  <div class="container">
    <div class="row">

      <div class="col-md-4 mb-3">
        <img src="images/brgylogo.png" width="80">
      </div>

      <div class="col-md-4 mb-3">
        <h5>Contact Us</h5>
        <p>📞 0997-645-314</p>
        <p>📧 barangay.newera@email.com</p>
        <p>📍 New Era, Dasmariñas City, Cavite</p>
      </div>

      <div class="col-md-4">
        <p>
          Office Hours:<br>
          Monday – Friday<br>
          8:00 AM – 5:00 PM
        </p>
      </div>

    </div>

    <hr style="background:#fff">
    <p class="text-center mb-0">© 2025 Barangay New Era</p>
  </div>
</footer>

<!-- ================= SCRIPTS ================= -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
