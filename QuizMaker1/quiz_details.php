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
    SELECT quiz_name, start_time, end_time, time_limit, allow_answer_view, allow_see_upcoming, randomize_questions, is_draft
    FROM quizzes
    WHERE id = ?
";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die("Quiz not found.");
}

// Fetch questions associated with the quiz
$questions_query = "
    SELECT id, question_text, question_type, points
    FROM questions
    WHERE quiz_id = ?
";
$questions_stmt = $pdoConnect->prepare($questions_query);
$questions_stmt->execute([$quiz_id]);
$questions = $questions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the quiz should be randomized for students
$randomize_questions = $quiz['randomize_questions'];

// Randomize questions if the setting is enabled and if the user is a student (not an admin)
if ($randomize_questions && $_SESSION['user_role'] !== 'admin') {
    shuffle($questions);
}

// Calculate total points
$total_points = 0;
foreach ($questions as $question) {
    $total_points += $question['points'];
}

// Function to convert question type to Title Case
function formatQuestionType($type)
{
    return ucwords(str_replace('_', ' ', $type));
}

// Function to truncate long question text
function truncateQuestionText($text, $maxLength = 10)
{
    return strlen($text) > $maxLength ? substr($text, 0, $maxLength) . '...' : $text;
}

// Determine if we need to show the add question form
$showAddQuestion = empty($questions); // Show add question if there are no questions
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="source/css/bsf-admin-subject-view.css">
    <link rel="stylesheet" href="source/css/admin-subject-view.css">
    <link rel="stylesheet" href="source/css/fonts.css">
    <link rel="icon" type="image/x-icon" href="assets/pictures/QM Logo.png">
    <title>Edit Quiz - <?php echo htmlspecialchars($quiz['quiz_name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1,
        h2 {
            text-align: center;
            color: #333;
        }

        .container {
            display: flex;
            justify-content: space-between;
            /* Adjust to space the containers evenly */
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-container,
        .questions-container,
        .edit-question-container {
            flex: 1;
            /* Each will take up equal space */
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-right: 20px;
        }

        .questions-container {
            margin-right: 20px;
            /* Margin to separate containers */
        }

        /* Adjust the last container to not have right margin */
        .edit-question-container {
            margin-right: 0;
            /* No right margin */
            height: 550px;
            /* Set fixed height for iframe container */
            display: flex;
            flex-direction: column;
        }

        ol {
            max-width: 100%;
            margin: 20px auto;
            padding: 0;
            list-style-type: decimal;
        }

        li {
            background: white;
            margin: 10px 0;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        a {
            margin-left: 15px;
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007BFF;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            text-align: center;
        }

        .edit-delete-links {
            display: flex;
            gap: 10px;
            white-space: nowrap;
        }

        /* Center the content of the header and main container */
        .admin-dashboard-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        iframe {
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            border: none;
            /* No border */
            border-radius: 8px;
            /* Optional: to match the container style */
        }

         /* Media Query for small screens */
    @media (max-width: 768px) {
        .container {
            flex-direction: column; /* Stack elements vertically */
        }

        .questions-container {
            margin-right: 0; /* Remove right margin */
            margin-bottom: 20px; /* Add bottom margin for separation */
        }

        .edit-question-container {
            margin-top: 20px; /* Move this container to the bottom */
        }
    }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header-wrapper" id="header">
        <a href="http://localhost/QuizMaker1/admin_subject_details.php?subject_id=<?php echo htmlspecialchars($subject_id); ?>" class="header-content">
            <div class="header-left">
                <div class="header-hero-wrapper">
                    <span class="header-hero font-poppins-bold">QM</span>
                </div>
            </div>
        </a>
    </div>

    <div class="admin-dashboard-wrapper" id="admin-dashboard">
        <div class="admin-dashboard-content">

            <h1>Edit Quiz: <?php echo htmlspecialchars($quiz['quiz_name']); ?></h1>

            <div class="container">
                <!-- Display existing questions with edit/delete links -->
                <div class="questions-container">
                    <h5>Questions (Total Points: <?php echo $total_points; ?>)</h5>
                    <?php if ($questions): ?>
                        <ol>
                            <?php foreach ($questions as $question): ?>
                                <li>
                                    <?php echo truncateQuestionText(htmlspecialchars($question['question_text']), 40); ?>
                                    (<?php echo formatQuestionType(htmlspecialchars($question['question_type'])); ?>,
                                    <?php echo htmlspecialchars($question['points']); ?> points)
                                    <div class="edit-delete-links">
                                        <a href="#"
                                            onclick="editQuestion(<?php echo $question['id']; ?>, '<?php echo $quiz_id; ?>', '<?php echo htmlspecialchars($subject_id); ?>'); return false;">Edit</a>
                                        <a href="delete_question.php?question_id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>&subject_id=<?php echo htmlspecialchars($subject_id); ?>"
                                            onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <p>No questions available for this quiz.</p>
                    <?php endif; ?>

                    <a href="#" class="back-link"
                        onclick="addQuestion('<?php echo $quiz_id; ?>', '<?php echo htmlspecialchars($subject_id); ?>'); return false;">Add
                        Questions</a>
                    <a href="admin_subject_details.php?subject_id=<?php echo htmlspecialchars($subject_id); ?>"
                        class="back-link">Back to Subject Details</a>
                </div>

                <!-- New Container for Editing Questions -->
                <div class="edit-question-container" id="edit-question-container">
                    <iframe id="edit-question-iframe"
                        src="add_questions.php?quiz_id=<?php echo $quiz_id; ?>&subject_id=<?php echo htmlspecialchars($subject_id); ?>"></iframe>
                </div>
            </div>

            <!-- JS Functions -->
            <script src="source/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
            <script src="source/js/inputPreventE.js"></script>
            <script src="source/js/clearModalFIelds.js"></script>
            <script src="source/js/toggleSidebar.js"></script>
            <script src="source/js/profilePicture.js"></script>
            <script src="source/js/checkSubjectQuiz.js"></script>
            <script src="source/js/editProfile.js"></script>
            <script src="source/js/sidebarProfileCenter.js"></script>
            <script src="source/js/syncSidebar.js"></script>
            <script src="source/js/stickyBackground.js"></script>
            <script src="source/js/tablePagination.js"></script>
            <script>
                function editQuestion(questionId, quizId, subjectId) {
                    // Set the iframe src to the edit question URL with parameters
                    const iframeSrc = `edit_question.php?question_id=${questionId}&quiz_id=${quizId}&subject_id=${subjectId}`;
                    document.getElementById('edit-question-iframe').src = iframeSrc;

                    // Show the edit question container
                    document.getElementById('edit-question-container').style.display = 'flex';
                }

                function addQuestion(quizId, subjectId) {
                    // Set the iframe src to the add question URL with parameters
                    const iframeSrc = `add_questions.php?quiz_id=${quizId}&subject_id=${subjectId}`;
                    document.getElementById('edit-question-iframe').src = iframeSrc;

                    // Show the edit question container
                    document.getElementById('edit-question-container').style.display = 'flex';
                }

                // Function to handle messages from the iframe
                window.addEventListener('message', function (event) {
                    // Make sure the message comes from a trusted source (optional)
                    if (event.origin !== window.location.origin) return;

                    if (event.data === "questionAdded" || event.data === "questionEdited") {
                        alert("Question added/edited successfully!");
                        // Reload the page to refresh the questions list
                        window.location.reload();
                    } else if (event.data === "questionError") {
                        alert("There was an error adding/editing the question. Please try again.");
                    }
                });

                function editQuestion(questionId, quizId, subjectId) {
                    const iframeSrc = `edit_question.php?question_id=${questionId}&quiz_id=${quizId}&subject_id=${subjectId}`;
                    document.getElementById('edit-question-iframe').src = iframeSrc;
                    document.getElementById('edit-question-container').style.display = 'flex';
                }

                function addQuestion(quizId, subjectId) {
                    const iframeSrc = `add_questions.php?quiz_id=${quizId}&subject_id=${subjectId}`;
                    document.getElementById('edit-question-iframe').src = iframeSrc;
                    document.getElementById('edit-question-container').style.display = 'flex';
                }
            </script>
        </div>
    </div>
</body>

</html>