<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../Connection/conn.php';

// Determine user ID from session (support both keys)
if (isset($_SESSION['resident_id'])) {
    $user_id = $_SESSION['resident_id'];
} elseif (isset($_SESSION['UserID'])) {
    $user_id = $_SESSION['UserID'];
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$response = ['success' => false, 'message' => ''];

// ---------------------------------------------------------
// REMOVE PROFILE PICTURE REQUEST
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    try {
        // Fetch existing path
        $stmt = $conn->prepare("SELECT profile_picture FROM usertbl WHERE UID = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $existingPath = $row['profile_picture'] ?? '';
        // Delete the file if it exists and is inside uploads/profiles
        if ($existingPath && strpos($existingPath, 'uploads/profiles/') === 0) {
            $fileOnDisk = __DIR__ . '/' . str_replace(['../', './'], '', $existingPath);
            if (file_exists($fileOnDisk)) {
                @unlink($fileOnDisk);
            }
        }

        // Update db to null
        $stmt = $conn->prepare("UPDATE usertbl SET profile_picture = NULL WHERE UID = ?");
        $stmt->bind_param("s", $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to clear profile picture: ' . $stmt->error);
        }
        $stmt->close();

        unset($_SESSION['profile_picture']);
        $response = [
            'success' => true,
            'message' => 'Profile picture removed successfully!',
            'imagePath' => '../images/tao.png'
        ];
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
        error_log('Remove profile picture error: ' . $e->getMessage());
    }
}

// ---------------------------------------------------------
// UPLOAD PROFILE PICTURE
// ---------------------------------------------------------
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = __DIR__ . "/uploads/profiles/";
    
    try {
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        
        // Verify directory is writable
        if (!is_writable($target_dir)) {
            throw new Exception('Upload directory is not writable');
        }
        
        $file = $_FILES['profile_picture'];
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        $web_path = "uploads/profiles/" . $new_filename;
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload failed with error code: ' . $file['error']);
        }
        
        // Check if file is an actual image
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            throw new Exception('File is not an image.');
        } 
        
        // Check file size (max 5MB)
        if ($file["size"] > 5000000) {
            throw new Exception('File is too large. Maximum size is 5MB.');
        }
        
        // Allow certain file formats
        if (!in_array($file_extension, ["jpg", "jpeg", "png", "gif"])) {
            throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed.');
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Update database with new profile picture path
        $stmt = $conn->prepare("UPDATE usertbl SET profile_picture = ? WHERE UID = ?");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
       $stmt->bind_param("ss", $web_path, $user_id);
        
        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Profile picture updated successfully!',
                'imagePath' => $web_path
            ];
            // Update session
            $_SESSION['profile_picture'] = $web_path;
        } else {
            throw new Exception('Database update failed: ' . $stmt->error);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
        // Log the error for debugging
        error_log('Profile picture upload error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'No file was uploaded or invalid request method';
}

// Always return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>