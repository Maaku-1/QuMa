<?php
include 'dbcon.php';

// Start session only if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Check if subject_id is provided
if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    die("Invalid subject ID.");
}

$subject_id = $_GET['subject_id'];
$admin_id = $_SESSION['user_id'];

// Fetch the subject to ensure it belongs to the admin
$subject_query = "SELECT id, subject_name FROM subjects WHERE id = ? AND admin_id = ?";
$subject_stmt = $pdoConnect->prepare($subject_query);
$subject_stmt->execute([$subject_id, $admin_id]);
$subject = $subject_stmt->fetch(PDO::FETCH_ASSOC);

if (!$subject) {
    die("Subject not found or you don't have permission to create a quiz for it.");
}

// Handle quiz creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_name = $_POST['quiz_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $time_limit = $_POST['time_limit'];
    $allow_answer_view = isset($_POST['allow_answer_view']) ? 1 : 0;
    $randomize_questions = isset($_POST['randomize_questions']) ? 1 : 0;
    $is_draft = 1; // Always saving as a draft

    // Validate inputs
    if (empty($quiz_name) || empty($start_time) || empty($end_time) || empty($time_limit)) {
        echo "All fields are required.";
    } elseif (strtotime($start_time) >= strtotime($end_time)) {
        echo "End time must be later than start time.";
    } elseif ($time_limit <= 0) {
        echo "Time limit must be a positive number.";
    } else {
        // Check for duplicate quiz
        $check_query = "SELECT COUNT(*) FROM quizzes WHERE quiz_name = ? AND subject_id = ?";
        $check_stmt = $pdoConnect->prepare($check_query);
        $check_stmt->execute([$quiz_name, $subject_id]);
        $duplicate_count = $check_stmt->fetchColumn();

        if ($duplicate_count > 0) {
            echo "A quiz with this name already exists for this subject.";
        } else {
            try {
                $query = "INSERT INTO quizzes (quiz_name, subject_id, start_time, end_time, time_limit, allow_answer_view, is_draft, randomize_questions) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdoConnect->prepare($query);
                $stmt->execute([$quiz_name, $subject_id, $start_time, $end_time, $time_limit, $allow_answer_view, $is_draft, $randomize_questions]);

                $quiz_id = $pdoConnect->lastInsertId();

                // Redirect to add_questions.php with quiz_id and subject_id
                header("Location: add_questions.php?quiz_id=$quiz_id&subject_id=$subject_id");
                exit;
            } catch (PDOException $e) {
                error_log($e->getMessage());
                echo "An error occurred while creating the quiz. Please try again.";
            }
        }
    }
}
?>
