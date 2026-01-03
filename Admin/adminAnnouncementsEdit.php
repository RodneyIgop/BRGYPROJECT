<?php
include '../Connection/conn.php'; // uses your MySQLi connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $id = sanitize($_POST['id']);
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $date = sanitize($_POST['date']);

    // Validate date format and prevent future dates
    if (!strtotime($date)) {
        die("Invalid date format provided.");
    }
    
    if (strtotime($date) > strtotime(date('Y-m-d'))) {
        die("Announcement date cannot be in the future.");
    }

    // Fetch existing announcement
    $stmt = $conn->prepare("SELECT image FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();

    if (!$existing) {
        die("Announcement not found.");
    }

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        // New image uploaded
        $image = time() . '_' . $_FILES['image']['name']; // unique filename
        $image_tmp = $_FILES['image']['tmp_name'];
        move_uploaded_file($image_tmp, "../uploaded_img/$image");
    } else {
        // Keep existing image
        $image = $existing['image'];
    }

    // Update announcement with validated date
    $update = $conn->prepare("UPDATE announcements SET title = ?, description = ?, image = ?, date = ? WHERE id = ?");
    $update->bind_param("ssssi", $title, $description, $image, $date, $id);
    $update->execute();
    $update->close();

    // Redirect back to announcements page
    header("Location: adminAnnouncements.php");
    exit;
}
?>