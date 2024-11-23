<?php
include 'dbcon.php';

// Start the session
session_start();

// Check if the file has been uploaded
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error']);
    exit;
}

// Get the uploaded file's data
$file = $_FILES['profile_image'];
$fileType = mime_content_type($file['tmp_name']);
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

// Validate the file type
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.']);
    exit;
}

// Read file contents and encode for database storage
$fileData = file_get_contents($file['tmp_name']);

// Get user ID and table information
$user_id = $_SESSION['user_id'];
$table = $_SESSION['user_role'] === 'student' ? 'student' : 'admin';
$id_column = $_SESSION['user_role'] === 'student' ? 'student_id' : 'admin_id';

// Update the profile image in the database
$query = "UPDATE $table SET profile_image = ? WHERE $id_column = ?";
$stmt = $pdoConnect->prepare($query);

if ($stmt->execute([$fileData, $user_id])) {
    echo json_encode(['status' => 'success', 'message' => 'Profile image updated successfully', 'image_data' => base64_encode($fileData)]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile image']);
}
?>