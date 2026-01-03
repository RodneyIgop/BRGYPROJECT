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
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            border: 1px solid #e1e5e9;
            background: #fff;
            position: relative;
        }
        
        .announcement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #014A7F, #0288d1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .announcement-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
            border-color: #014A7F;
        }
        
        .announcement-card:hover::before {
            opacity: 1;
        }
        
        .announcement-image-container {
            height: 220px;
            overflow: hidden;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
        }
        
        .announcement-image-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .announcement-card:hover .announcement-image-container::after {
            opacity: 1;
        }
        
        .announcement-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .announcement-card:hover .announcement-image {
            transform: scale(1.08);
        }
        
        .announcement-date {
            color: #6c757d;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .announcement-title {
            color: #1a1a1a;
            font-weight: 700;
            line-height: 1.3;
            min-height: 56px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }
        
        .announcement-description {
            color: #5a6c7d;
            font-size: 0.95rem;
            line-height: 1.7;
            min-height: 72px;
            font-weight: 400;
        }
        
        .announcement-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            padding-top: 16px;
            border-top: 1px solid #e9ecef;
            margin-top: auto;
        }
        
        .btn-edit-announcement {
            background: linear-gradient(135deg, #ffc107, #ffb300);
            border: none;
            color: #212529;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
            flex: 1;
        }
        
        .btn-edit-announcement:hover {
            background: linear-gradient(135deg, #ffb300, #ff9800);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
        }
        
        .btn-delete-announcement {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            color: white;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
            flex: 1;
        }
        
        .btn-delete-announcement:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }
        
        /* Header improvements */
        .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 {
            background: linear-gradient(135deg, #014A7F 0%, #0288d1 100%);
            padding: 2.5rem;
            border-radius: 20px;
            color: white;
            margin-bottom: 2.5rem !important;
            box-shadow: 0 8px 32px rgba(1, 74, 127, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
            text-decoration: none;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 h3 {
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
            position: relative;
            z-index: 1;
        }
        
        .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 .btn {
            background-color: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
            padding: 12px 28px;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 12px;
            font-size: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 .btn:hover {
            background-color: rgba(255,255,255,0.3);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        /* Empty state styling */
        .text-center:not(.d-flex) p {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 3rem;
            border-radius: 16px;
            color: #6c757d;
            font-size: 1.1rem;
            font-weight: 500;
            margin: 2rem 0;
            border: 2px dashed #dee2e6;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .announcement-card {
                margin-bottom: 1.5rem;
            }
            
            .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .d-flex.flex-column.align-items-center.text-center.mt-3.mb-4 h3 {
                font-size: 1.5rem;
            }
            
            .announcement-actions {
                flex-direction: column;
            }
            
            .btn-edit-announcement,
            .btn-delete-announcement {
                width: 100%;
            }
        }
        
        /* Modal enhancements */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #014A7F, #0288d1);
            border: none;
            padding: 1.5rem;
        }
        
        .modal-header .modal-title {
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .modal-footer {
            border: none;
            padding: 1.5rem;
            background: #f8f9fa;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #014A7F;
            box-shadow: 0 0 0 0.2rem rgba(1, 74, 127, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #014A7F, #0288d1);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0288d1, #014A7F);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(1, 74, 127, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
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
    <h3 class="fw-bold mb-3 custom-text">Manage News & Updates</h3>

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
                <label class="form-label fw-bold">Announcement Date</label>
                <input type="date" name="date" class="form-control"
                  value="<?= date('Y-m-d', strtotime($row['date'])) ?>" required
                  max="<?= date('Y-m-d') ?>">
                <div class="form-text">
                  <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Current: <?= date("F d, Y", strtotime($row['date'])) ?>
                  </small>
                </div>
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
