<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../Connection/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['superadmin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Superadmin not logged in']);
    exit();
}

$superadmin_id = (int)$_SESSION['superadmin_id'];
$response = ['success' => false, 'message' => ''];

$colStmt = $conn->prepare("SHOW COLUMNS FROM superadmin LIKE 'profile_picture'");
if (!$colStmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}
$colStmt->execute();
$colStmt->store_result();
$hasProfilePicture = $colStmt->num_rows > 0;
$colStmt->close();

if (!$hasProfilePicture) {
    echo json_encode(['success' => false, 'message' => 'Profile picture column not found. Please update the database schema.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    try {
        $stmt = $conn->prepare('SELECT profile_picture FROM superadmin WHERE id = ?');
        $stmt->bind_param('i', $superadmin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $existingPath = $row['profile_picture'] ?? '';
        if ($existingPath && strpos($existingPath, 'uploads/superadminprofiles/') === 0) {
            $fileOnDisk = __DIR__ . '/' . str_replace(['../', './'], '', $existingPath);
            if (file_exists($fileOnDisk)) {
                @unlink($fileOnDisk);
            }
        }

        $stmt = $conn->prepare('UPDATE superadmin SET profile_picture = NULL WHERE id = ?');
        $stmt->bind_param('i', $superadmin_id);
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

    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = __DIR__ . '/uploads/superadminprofiles/';

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
        $new_filename = 'superadmin_' . $superadmin_id . '_' . time() . '.' . $ext;
        $target_file = $target_dir . $new_filename;
        $web_path = 'uploads/superadminprofiles/' . $new_filename;

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

        $stmt = $conn->prepare('UPDATE superadmin SET profile_picture = ? WHERE id = ?');
        if (!$stmt) {
            throw new Exception('DB prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('si', $web_path, $superadmin_id);
        if (!$stmt->execute()) {
            throw new Exception('DB update failed: ' . $stmt->error);
        }
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

    echo json_encode($response);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
