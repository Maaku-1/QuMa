<?php
include 'dbcon.php';
session_start();

// Set default timezone to UTC +8:00
date_default_timezone_set('Asia/Shanghai'); // Adjust this as needed for your specific timezone

// Check if the student is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: index.php");
    exit;
}

// Check if subject_id is provided
if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    die("Invalid subject ID.");
}

$subject_id = $_GET['subject_id'];
$student_id = $_SESSION['user_id']; // Get the student ID from the session

// Fetch subject details
$query = "
    SELECT subject_name, department, year, section
    FROM subjects
    WHERE id = ?
";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$subject_id]);
$subject = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subject) {
    die("Subject not found.");
}

// Fetch quizzes associated with the subject
$quizzesQuery = "
    SELECT id, quiz_name, start_time, end_time, is_draft, allow_see_upcoming
    FROM quizzes
    WHERE subject_id = ? AND (is_draft = 0 OR allow_see_upcoming = 1)
    ORDER BY start_time
";
$quizzesStmt = $pdoConnect->prepare($quizzesQuery);
$quizzesStmt->execute([$subject_id]);
$quizzes = $quizzesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="source/css/bsf-student-subject-view.css">
    <link rel="stylesheet" href="source/css/student-subject-view.css">
    <link rel="stylesheet" href="source/css/fonts.css">
    <link rel="icon" type="image/x-icon" href="assets/pictures/QM Logo.png">
    <title>QuMa</title>
    <style>
        /* Color coding for quiz status */
        .quiz-body-status.upcoming {
            color: #ffa500;
            /* Orange for upcoming */
            font-weight: bold;
        }

        .quiz-body-status.ongoing {
            color: #28a745;
            /* Green for ongoing */
            font-weight: bold;
        }

        .quiz-body-status.ended {
            color: #dc3545;
            /* Red for ended */
            font-weight: bold;
        }

        .quiz-body-name a {
            pointer-events: none;
            /* Disable links for non-ongoing quizzes */
        }

        .quiz-body-name a.ongoing {
            pointer-events: auto;
            /* Enable link for ongoing quizzes */
        }

        .header-hero-wrapper a {
            text-decoration: none;
            /* Remove underline */
        }
    </style>
</head>

<body>
    <div class="student-dashboard-wrapper" id="student-dashboard">
        <div class="student-dashboard-content">

            <!-- HEADER -->
            <div class="header-wrapper" id="header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="header-hero-wrapper">
                            <div class="header-hero-wrapper">
                                <a href="studentdashboard.php" class="header-hero font-poppins-bold">QM</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main -->
            <div class="main-wrapper" id="main">
                <div class="main-content">

                    <div class="table-responsive">
                        <div class="quiz-table">
                            <div class="quiz-table-header">
                                <span class="quiz-header-name font-poppins-bold">Quiz Name</span>
                                <span class="quiz-header-start-date font-poppins-bold">Start Date</span>
                                <span class="quiz-header-end-date font-poppins-bold">End Date</span>
                                <span class="quiz-header-status font-poppins-bold">Status</span>
                                <span class="quiz-header-blank"></span>
                            </div>
                            <div class="quiz-table-body">
                                <?php if ($quizzes): ?>
                                    <?php
                                    $current_time = new DateTime(); // Get current time
                                    foreach ($quizzes as $quiz):
                                        $start_time = new DateTime($quiz['start_time']);
                                        $end_time = new DateTime($quiz['end_time']);
                                        $is_disabled = ($current_time < $start_time); // Quiz not started
                                        $is_ended = ($current_time > $end_time); // Quiz ended
                                        $is_ongoing = !$is_disabled && !$is_ended; // Quiz is ongoing
                                        ?>
                                        <div class="quiz-table-body-row-template">
                                            <span class="quiz-body-name font-rubik">
                                                <?php if ($is_ongoing): ?>
                                                    <a class="ongoing"
                                                        href="take_quiz.php?quiz_id=<?php echo $quiz['id']; ?>"><?php echo htmlspecialchars($quiz['quiz_name']); ?></a>
                                                <?php else: ?>
                                                    <span><?php echo htmlspecialchars($quiz['quiz_name']); ?></span>
                                                <?php endif; ?>
                                            </span>
                                            <span
                                                class="quiz-body-start-date font-rubik"><?php echo $start_time->format('d/m/Y H:i'); ?></span>
                                            <span
                                                class="quiz-body-end-date font-rubik"><?php echo $end_time->format('d/m/Y H:i'); ?></span>
                                            <span
                                                class="quiz-body-status font-rubik <?php echo $is_disabled ? 'upcoming' : ($is_ended ? 'ended' : 'ongoing'); ?>">
                                                <?php echo $is_disabled ? 'Upcoming' : ($is_ended ? 'Ended' : 'Ongoing'); ?>
                                            </span>
                                            <span class="quiz-body-more-options">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="quiz-body-more-options-icon"
                                                    data-bs-toggle="dropdown" aria-expanded="false" viewBox="0 -960 960 960">
                                                    <path
                                                        d="M480-160q-33 0-56.5-23.5T400-240q0-33 23.5-56.5T480-320q33 0 56.5 23.5T560-240q0 33-23.5 56.5T480-160Zm0-240q-33 0-56.5-23.5T400-480q0-33 23.5-56.5T480-560q33 0 56.5 23.5T560-480q0 33-23.5 56.5T480-400Zm0-240q-33 0-56.5-23.5T400-720q0-33 23.5-56.5T480-800q33 0 56.5 23.5T560-720q0 33-23.5 56.5T480-640Z" />
                                                </svg>
                                                <div class="dropdown-menu">
            <div class="quiz-more-options-menu">
                <button type="button" class="view-grade font-poppins-regular"
                        data-bs-toggle="modal" data-bs-target="#view-grade-modal"
                        data-quiz-id="<?php echo $quiz['id']; ?>">View Grade</button>
            </div>
        </div>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="quiz-table-body-row-template">
                                        <span class="quiz-body-name font-rubik">No quizzes available</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <nav>
                        <ul class="pagination" id="pagination-controls">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW GRADE MODAL -->
    <div class="modal fade" id="view-grade-modal" data-bs-keyboard="false" aria-labelledby="View Grade"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content view-grade-modal-content">
                <div class="modal-header view-grade-modal-header">
                    <span class="view-grade-header font-rubik-medium">View Grade</span>
                </div>
                <div class="modal-body view-grade-modal-body">
                    <iframe id="view-grade-iframe" src="" frameborder="0" style="width: 100%; height: 300px;"></iframe>
                </div>
                <div class="modal-footer view-grade-modal-footer">
                    <div class="view-grade-buttons">
                        <button type="button" class="btn view-grade-cancel font-rubik"
                            data-bs-dismiss="modal">Cancel</button>
                        
                    </div>
                </div>
            </div>
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
    <Script>
        document.addEventListener('DOMContentLoaded', function () {
    // Add event listener to View Grade buttons
    const viewGradeButtons = document.querySelectorAll('.view-grade');

    viewGradeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const quizId = button.getAttribute('data-quiz-id'); // Get the quiz ID from the button's data attribute

            // Set the iframe src to fetch results based on quiz ID
            const iframe = document.getElementById('view-grade-iframe');
            iframe.src = 'fetch_results.php?quiz_id=' + quizId;
        });
    });
});

    </Script>
</body>

</html>