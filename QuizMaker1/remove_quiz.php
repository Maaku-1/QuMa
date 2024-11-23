<?php
include 'dbcon.php';
header('Content-Type: application/json');
session_start();

// Ensure user is logged in and is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get the quiz ID from the request
$data = json_decode(file_get_contents("php://input"), true);
$quiz_id = $data['delete_quiz_id'] ?? null; // Updated to match the expected parameter name

if ($quiz_id && is_numeric($quiz_id)) {
    // Prepare the delete statement
    $stmt = $pdoConnect->prepare("DELETE FROM quizzes WHERE id = ?");
    if ($stmt->execute([$quiz_id])) {
        echo json_encode(['success' => true, 'message' => 'Quiz deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not delete quiz.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID.']);
}
?>