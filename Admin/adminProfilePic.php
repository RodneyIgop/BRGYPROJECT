<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../Connection/conn.php';

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit();
}

$admin_id = $_SESSION['admin_id'];
$response = ['success' => false, 'message' => ''];

// ---------------- Remove profile picture ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    try {
        $stmt = $conn->prepare("SELECT profile_picture FROM admintbl WHERE AdminID = ?");
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $existingPath = $row['profile_picture'] ?? '';
        if ($existingPath && strpos($existingPath, 'uploads/adminprofiles/') === 0) {
            $fileOnDisk = __DIR__ . '/' . str_replace(['../', './'], '', $existingPath);
            if (file_exists($fileOnDisk)) {
                @unlink($fileOnDisk);
            }
        }

        $stmt = $conn->prepare("UPDATE admintbl SET profile_picture = NULL WHERE AdminID = ?");
        $stmt->bind_param('i', $admin_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to clear profile picture');
        }
        $stmt->close();

        $response = [
            'success' => true,
            'message' => 'Profile picture removed successfully!',
            'imagePath' => '../images/tao.png'
        ];
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}
// ---------------- Upload new profile picture ----------------
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = __DIR__ . '/uploads/adminprofiles/';

    try {
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        if (!is_writable($target_dir)) {
            throw new Exception('Upload directory is not writable');
        }

        $file = $_FILES['profile_picture'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
        $target_file = $target_dir . $new_filename;
        $web_path = 'uploads/adminprofiles/' . $new_filename;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload failed with error code: ' . $file['error']);
        }
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            throw new Exception('File is not an image.');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File too large. Max 5MB.');
        }
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception('Invalid file type.');
        }
        if (!move_uploaded_file($file['tmp_name'], $target_file)) {
            throw new Exception('Failed to move uploaded file.');
        }

        $stmt = $conn->prepare('UPDATE admintbl SET profile_picture = ? WHERE AdminID = ?');
        if (!$stmt) throw new Exception('DB prepare failed: ' . $conn->error);
        $stmt->bind_param('si', $web_path, $admin_id);
        if (!$stmt->execute()) throw new Exception('DB update failed: ' . $stmt->error);
        $stmt->close();

        $_SESSION['profile_picture'] = $web_path;
        $response = [
            'success' => true,
            'message' => 'Profile picture updated successfully!',
            'imagePath' => $web_path
        ];
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
