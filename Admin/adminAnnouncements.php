<?php
include '../Connection/conn.php';
?>

<?php
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: adminAnnouncements.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel - Announcements</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
        .btn-orange {
            background-color: #014A7F !important;
            color: #fff !important;
            border: none;
        }
        .btn-orange:hover, .btn-orange:focus {
            background-color: #014A7F !important;
            color: #fff !important;
        }
        .custom-btn {
            background-color:#014A7F ;
        }
        .custom-text {
            color: #ffffff;
        }
        .custom-text1{
            color:#014A7F ;
        }
        
        /* Enhanced Announcement Card Styles */
        .announcement-card {
            border-radius: 16px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
        }
        
        .announcement-image-container {
            height: 200px;
            overflow: hidden;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
        }
        
        .announcement-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .announcement-card:hover .announcement-image {
            transform: scale(1.05);
        }
        
        .announcement-date {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .announcement-title {
            color: #2c3e50;
            font-weight: 600;
            line-height: 1.4;
            min-height: 48px;
            display: flex;
            align-items: center;
        }
        
        .announcement-description {
            color: #5a6c7d;
            font-size: 0.95rem;
            line-height: 1.6;
            min-height: 60px;
        }
        
        .announcement-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .btn-edit-announcement {
            background-color: #ffc107;
            border: none;
            color: #212529;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .btn-edit-announcement:hover {
            background-color: #e0a800;
            transform: translateY(-1px);
        }
        
        .btn-delete-announcement {
            background-color: #dc3545;
            border: none;
            color: white;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .btn-delete-announcement:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }
        
        /* Header improvements */
        .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 {
            background: linear-gradient(135deg, #014A7F 0%, #0288d1 100%);
            padding: 2rem;
            border-radius: 16px;
            color: white;
            margin-bottom: 2rem !important;
        }
        
        .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 h3 {
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 .btn {
            background-color: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
            padding: 10px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 .btn:hover {
            background-color: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        /* Main content positioning to avoid sidebar */
        body {
            padding-left: 240px;
        }
        
        .section {
            margin-left: 20px;
            margin-right: 20px;
            padding-top: 20px;
        }
        
        @media (max-width: 768px) {
            body {
                padding-left: 0;
            }
            .section {
                margin-left: 10px;
                margin-right: 10px;
            }
        }
  </style>
</head>

<?php include 'adminSidebar.php'; ?>

<body>

<section class="section mt-5 mb-5" id="announcements">

  <div class="d-flex flex-column align-items-center text-center mt-3 mb-4">
    <h3 class="fw-bold mb-3">Manage News & Updates</h3>

    <a href="adminAnnouncementsAdd.php"
       class="btn custom-btn custom-text mb-3">
       + Add New Announcement
    </a>
  </div>

  <div class="text-center mb-4"></div>

  <div class="row justify-content-center g-4">
  <?php
  $stmt = $conn->prepare(
      "SELECT id, title, description, image, date 
      FROM announcements 
      ORDER BY date DESC"
  );

  $stmt->execute();
  $result = $stmt->get_result();
  $announcements = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  if (!empty($announcements)):
      foreach ($announcements as $row):
  ?>

    <div class="col-md-4 mb-4">
      <div class="card h-100 announcement-card border-0 shadow-lg">
        <div class="announcement-image-container">
          <img 
            src="../uploaded_img/<?= htmlspecialchars($row['image']) ?>" 
            alt="Announcement Image"
            class="announcement-image">
        </div>

        <div class="card-body p-4">
          <div class="announcement-date mb-2">
            <i class="bi bi-calendar3 me-2"></i>
            <?= date("F d, Y", strtotime($row['date'])) ?>
          </div>

          <h5 class="announcement-title mb-3">
            <?= htmlspecialchars($row['title']) ?>
          </h5>

          <div class="announcement-description mb-4">
            <?= htmlspecialchars($row['description']) ?>
          </div>

          <div class="announcement-actions">
            <!-- EDIT BUTTON -->
            <button class="btn btn-sm btn-edit-announcement me-2" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
              <i class="bi bi-pencil-square me-1"></i>Edit
            </button>

            <!-- DELETE BUTTON -->
            <button class="btn btn-sm btn-delete-announcement" onclick="confirmDelete(<?= $row['id'] ?>)">
              <i class="bi bi-trash me-1"></i>Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Edit Announcement</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <form method="POST" action="adminAnnouncementsEdit.php" enctype="multipart/form-data">
            <div class="modal-body">

              <input type="hidden" name="id" value="<?= $row['id'] ?>">

              <div class="mb-3">
                <label class="form-label fw-bold">Title</label>
                <input type="text" name="title" class="form-control"
                value="<?= htmlspecialchars($row['title']) ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Description</label>
                <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($row['description']) ?></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Current Image</label><br>
                <img src="../uploaded_img/<?= htmlspecialchars($row['image']) ?>"
                class="img-fluid rounded mb-2"
                style="max-height: 150px;">
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Change Image (optional)</label>
                <input type="file" name="image" class="form-control">
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Date</label>
                <input type="date" name="date" class="form-control"
                  value="<?= $row['date'] ?>" required>
              </div>

            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary">
                Save Changes
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

  <?php
      endforeach;
  else:
      echo "<p class='text-center'>No announcements available.</p>";
  endif;
  ?>

  </div>
</section>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: "Delete Announcement?",
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "rgba(13, 194, 58, 1)",
        cancelButtonColor: "#d11323ff",
        confirmButtonText: "Yes, delete it"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "adminAnnouncements.php?delete=" + id;
        }
    });
}
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
