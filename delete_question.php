<?php
include 'dbcon.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Check if question_id, quiz_id, and subject_id are provided
if (!isset($_GET['question_id']) || !is_numeric($_GET['question_id']) ||
    !isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id']) ||
    !isset($_GET['subject_id'])) { // Ensure subject_id is captured
    die("Invalid question, quiz, or subject ID.");
}

$question_id = $_GET['question_id'];
$quiz_id = $_GET['quiz_id'];
$subject_id = $_GET['subject_id']; // Capture subject_id

// Prepare SQL statement to delete answers associated with the question
$delete_answers_query = "DELETE FROM answers WHERE question_id = ?";
$delete_answers_stmt = $pdoConnect->prepare($delete_answers_query);

// Delete the associated answers
$delete_answers_stmt->execute([$question_id]);

// Now delete the question
$delete_query = "DELETE FROM questions WHERE id = ?";
$delete_stmt = $pdoConnect->prepare($delete_query);

if ($delete_stmt->execute([$question_id])) {
    // Redirect to the quiz details page after successful deletion, including subject_id
    header("Location: quiz_details.php?quiz_id=$quiz_id&subject_id=" . htmlspecialchars($subject_id));
    exit;
} else {
    echo "Error deleting question.";
}
?>
