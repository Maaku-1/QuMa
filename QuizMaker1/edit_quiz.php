<?php
include 'dbcon.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Check if quiz_id is provided
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die("Invalid quiz ID.");
}

$quiz_id = $_GET['quiz_id'];
$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : null;  // Capture subject_id if provided

// Fetch quiz details
$query = "
    SELECT quiz_name, start_time, end_time, time_limit, allow_answer_view, randomize_questions
    FROM quizzes
    WHERE id = ?
";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die("Quiz not found.");
}

// Handle form submission for updating quiz details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quiz_name = $_POST['quiz_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $time_limit = $_POST['time_limit'];
    $allow_answer_view = isset($_POST['allow_answer_view']) ? 1 : 0;
    $randomize_questions = isset($_POST['randomize_questions']) ? 1 : 0;

    // Update quiz details in the database
    $update_query = "
        UPDATE quizzes
        SET quiz_name = ?, start_time = ?, end_time = ?, time_limit = ?, allow_answer_view = ?, randomize_questions = ?
        WHERE id = ?
    ";
    $update_stmt = $pdoConnect->prepare($update_query);
    $update_stmt->execute([
        $quiz_name,
        $start_time,
        $end_time,
        $time_limit,
        $allow_answer_view,
        $randomize_questions,
        $quiz_id
    ]);

    echo "<script>
    alert('Quiz updated successfully!');
    // Redirect parent window to the subject details page
    window.parent.location.href = 'admin_subject_details.php?subject_id=$subject_id';
    // Close the modal
    window.parent.document.getElementById('edit-quiz-modal').style.display = 'none';
</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Quiz - <?php echo htmlspecialchars($quiz['quiz_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

    </style>
</head>

<body>

    <!-- EDIT QUIZ STATIC FORM -->
    <div class="edit-quiz-modal-content">
        <div class="modal-header">
            <span class="create-quiz-header font-rubik-medium">Edit Quiz</span>
        </div>
        <form method="post">
            <div class="form-floating mb-3">
                <input type="text" class="form-control font-rubik" id="edit-quiz-name" name="quiz_name"
                    value="<?php echo htmlspecialchars($quiz['quiz_name']); ?>" placeholder="Quiz Name" required>
                <label for="edit-quiz-name" class="font-rubik">Quiz Name</label>
                <div class="invalid-feedback">Quiz Name is Required</div>
            </div>
            <div class="form-floating mb-3">
                <input type="datetime-local" class="form-control font-rubik" id="edit-quiz-start-date" name="start_time"
                    value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($quiz['start_time']))); ?>"
                    placeholder="Start Date and Time" required>
                <label for="edit-quiz-start-date" class="font-rubik">Start Date and Time</label>
            </div>
            <div class="form-floating mb-3">
                <input type="datetime-local" class="form-control font-rubik" id="edit-quiz-end-date" name="end_time"
                    value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($quiz['end_time']))); ?>"
                    placeholder="End Date and Time" required>
                <label for="edit-quiz-end-date" class="font-rubik">End Date and Time</label>
            </div>
            <div class="form-floating mb-3">
                <input type="number" class="form-control font-rubik" id="edit-quiz-time-limit" name="time_limit"
                    value="<?php echo htmlspecialchars($quiz['time_limit']); ?>" placeholder="Time Limit" required>
                <label for="edit-quiz-time-limit" class="font-rubik">Time Limit</label>
            </div>
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="edit-quiz-show-answers"
                        name="allow_answer_view" <?php echo $quiz['allow_answer_view'] ? 'checked' : ''; ?>>
                    <label class="form-check-label font-rubik" for="edit-quiz-show-answers">Display Answers after Quiz
                        Time</label>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="edit-quiz-random"
                        name="randomize_questions" <?php echo $quiz['randomize_questions'] ? 'checked' : ''; ?>>
                    <label class="form-check-label font-rubik" for="edit-quiz-random">Randomize Questions</label>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Confirm</button>
                
            </div>
        </form>
    </div>

</body>

</html>