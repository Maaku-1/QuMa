<?php
include 'dbcon.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Check if subject_id is provided and valid
if (isset($_GET['subject_id']) && is_numeric($_GET['subject_id'])) {
    $subject_id = $_GET['subject_id'];

    // Prepare the delete statement
    $deleteQuery = "DELETE FROM subjects WHERE id = ? AND admin_id = ?";
    $stmt = $pdoConnect->prepare($deleteQuery);
    $stmt->execute([$subject_id, $_SESSION['user_id']]);

    // Check if any rows were affected
    if ($stmt->rowCount() > 0) {
        echo "Subject deleted successfully.";
    } else {
        echo "Error deleting subject or subject not found.";
    }
} else {
    echo "Invalid subject ID.";
}

header("Location: admindashboard.php");
exit;
