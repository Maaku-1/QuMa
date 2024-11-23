<?php
include 'dbcon.php';
session_start();

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['student', 'admin'])) {
    header("Location: index.php");
    exit;
}

// Determine the table and ID column based on user role
if ($_SESSION['user_role'] === 'student') {
    $table = 'student';
    $id_column = 'student_id';
    $user_id = $_SESSION['user_id'];
} elseif ($_SESSION['user_role'] === 'admin') {
    $table = 'admin';
    $id_column = 'admin_id';
    $user_id = $_SESSION['user_id'];
} else {
    echo json_encode(["status" => "error", "message" => "Invalid user role."]);
    exit;
}

// Get data from AJAX request
$last_name = $_POST['last_name'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$middle_name = $_POST['middle_name'] ?? '';
$department = $_POST['department'] ?? '';

// Prepare and execute update query
$query = "UPDATE $table SET lname = ?, fname = ?, mi = ?, department = ? WHERE $id_column = ?";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$last_name, $first_name, $middle_name, $department, $user_id]);

echo json_encode(["status" => "success", "message" => "Profile updated successfully!"]);
?>
