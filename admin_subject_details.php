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

$subject_id = (int) $_GET['subject_id'];
$admin_id = (int) $_SESSION['user_id']; // Get the admin's ID from the session

// Fetch subject details, ensure the admin owns it
$query = "
    SELECT subject_name, department, year, section, code
    FROM subjects
    WHERE id = :subject_id AND admin_id = :admin_id
";
$stmt = $pdoConnect->prepare($query);
$stmt->execute(['subject_id' => $subject_id, 'admin_id' => $admin_id]);
$subject = $stmt->fetch(PDO::FETCH_ASSOC);

// Debugging: Check if subject is fetched
if (!$subject) {
    die("Debug: Subject not found or you don't have permission to view it. (Subject ID: $subject_id, Admin ID: $admin_id)");
}

// Fetch quizzes related to this subject
$quizQuery = "
    SELECT id AS quiz_id, quiz_name, start_time, end_time
    FROM quizzes 
    WHERE subject_id = :subject_id
";
$quizStmt = $pdoConnect->prepare($quizQuery);
$quizStmt->execute(['subject_id' => $subject_id]);
$quizzes = $quizStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle quiz deletion
if (isset($_POST['delete_quiz_id'])) {
    $quiz_id = (int) $_POST['delete_quiz_id'];

    // Step 1: Delete all answers related to the quiz questions
    $deleteAnswersQuery = "DELETE a 
                           FROM answers a
                           JOIN questions q ON a.question_id = q.id
                           WHERE q.quiz_id = :quiz_id";
    $deleteAnswersStmt = $pdoConnect->prepare($deleteAnswersQuery);
    $deleteAnswersStmt->execute(['quiz_id' => $quiz_id]);

    // Step 2: Delete all questions related to the quiz
    $deleteQuestionsQuery = "DELETE FROM questions WHERE quiz_id = :quiz_id";
    $deleteQuestionsStmt = $pdoConnect->prepare($deleteQuestionsQuery);
    $deleteQuestionsStmt->execute(['quiz_id' => $quiz_id]);

    // Step 3: Finally, delete the quiz
    $deleteQuizQuery = "DELETE FROM quizzes WHERE id = :quiz_id AND subject_id = :subject_id";
    $deleteQuizStmt = $pdoConnect->prepare($deleteQuizQuery);
    $deleteQuizStmt->execute(['quiz_id' => $quiz_id, 'subject_id' => $subject_id]);

    // Redirect to avoid form resubmission
    header("Location: admin_subject_details.php?subject_id=" . $subject_id);
    exit;
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

                header("Location: admin_subject_details.php?subject_id=$subject_id");
                exit;
            } catch (PDOException $e) {
                error_log($e->getMessage());
                echo "An error occurred while creating the quiz. Please try again.";
            }
        }
    }
}
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
    <title>QuMa</title>
</head>

<body>
    <div class="admin-dashboard-wrapper" id="admin-dashboard">
        <div class="admin-dashboard-content">

            <!-- HEADER -->
            <div class="header-wrapper" id="header">
                <div class="header-content">
                    <div class="header-left">
                        <a href="admindashboard.php" style="text-decoration: none; color: inherit;">
                            <div class="header-hero-wrapper">
                                <span class="header-hero font-poppins-bold">QM</span>
                            </div>
                        </a>
                    </div>

                    <div class="header-right">
                        <div class="dropdown">
                            <button type="button" class="header-create-join-button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" class="header-create-join-button-icon"
                                    viewBox="0 -960 960 960">>
                                    <path
                                        d="M440-440v120q0 17 11.5 28.5T480-280q17 0 28.5-11.5T520-320v-120h120q17 0 28.5-11.5T680-480q0-17-11.5-28.5T640-520H520v-120q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640v120H320q-17 0-28.5 11.5T280-480q0 17 11.5 28.5T320-440h120ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z" />
                                </svg>
                            </button>

                            <div class="dropdown-menu">
                                <div class="header-create-join-menu">
                                    <button type="button" class="header-create-join-menu-btn font-poppins-medium"
                                        data-bs-toggle="modal" data-bs-target="#create-quiz-modal"
                                        data-subject-id="<?php echo $subject_id; ?>">
                                        Create Quiz
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main -->
            <div class="main-wrapper" id="main">
                <div class="main-content">
                    <div class="main-quiz-no-item-wrapper">
                        <span class="main-quiz-no-item-text font-inria-sans-bold">IT SEEMS YOU DON'T HAVE ANY EXISTING
                            QUIZ.</span>
                        <span class="main-quiz-no-item-text font-inria-sans-bold">CLICK
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-create-join-button-icon"
                                viewBox="0 -960 960 960">>
                                <path
                                    d="M440-440v120q0 17 11.5 28.5T480-280q17 0 28.5-11.5T520-320v-120h120q17 0 28.5-11.5T680-480q0-17-11.5-28.5T640-520H520v-120q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640v120H320q-17 0-28.5 11.5T280-480q0 17 11.5 28.5T320-440h120ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z" />
                            </svg> TO CREATE ONE.
                        </span>
                    </div>
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
                                <?php foreach ($quizzes as $quiz): ?>
                                    <div class="quiz-table-body-row-template">
                                        <span class="quiz-body-name font-rubik">
                                            <a
                                                href="quiz_details.php?quiz_id=<?php echo htmlspecialchars($quiz['quiz_id']); ?>&subject_id=<?php echo htmlspecialchars($subject_id); ?>">
                                                <?php echo htmlspecialchars($quiz['quiz_name']); ?>
                                            </a>
                                        </span>

                                        <span
                                            class="quiz-body-start-date font-rubik"><?php echo date('d/m/Y H:i', strtotime($quiz['start_time'])); ?></span>
                                        <span
                                            class="quiz-body-end-date font-rubik"><?php echo date('d/m/Y H:i', strtotime($quiz['end_time'])); ?></span>
                                        <span class="quiz-body-status font-rubik">Draft</span>
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
                                                        onclick="loadQuizResults(<?php echo htmlspecialchars($quiz['quiz_id']); ?>)">
                                                        View Grades
                                                    </button>

                                                    <button type="button" class="edit-quiz font-poppins-regular"
                                                        data-bs-toggle="modal" data-bs-target="#edit-quiz-modal"
                                                        data-quiz-id="<?php echo htmlspecialchars($quiz['quiz_id']); ?>">
                                                        Edit Quiz
                                                    </button>

                                                    <button type="button" class="remove-quiz font-poppins-regular"
                                                        data-bs-toggle="modal" data-bs-target="#remove-quiz-modal"
                                                        data-quiz-id="<?php echo htmlspecialchars($quiz['quiz_id']); ?>">
                                                        <!-- Add data-quiz-id -->
                                                        Remove Quiz
                                                    </button>
                                                </div>
                                            </div>

                                        </span>
                                    </div>
                                <?php endforeach; ?>
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

    <!-- CREATE QUIZ MODAL -->
    <div class="modal fade" id="create-quiz-modal" data-bs-keyboard="false" aria-labelledby="Create Quiz"
        aria-hidden="true">
        <div class="modal-dialog create-quiz-modal-dialog">
            <div class="modal-content create-quiz-modal-content">
                <div class="modal-header create-quiz-modal-header">
                    <span class="create-quiz-header font-rubik-medium">Create Quiz</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="create-quiz-form" class="modal-body create-quiz-modal-body" method="POST">
                    <div class="form-floating create-quiz-name-wrapper">
                        <input type="text" name="quiz_name" class="form-control font-rubik" id="create-quiz-name"
                            placeholder="Quiz Name" required>
                        <label for="create-quiz-name" class="font-rubik">Quiz Name</label>
                        <div class="invalid-feedback">
                            Quiz Name is Required
                        </div>
                    </div>
                    <div class="form-floating create-quiz-start-date-wrapper">
                        <input type="datetime-local" name="start_time" class="form-control font-rubik"
                            id="create-quiz-start-date" placeholder="Start Date and Time" required>
                        <label for="create-quiz-start-date" class="font-rubik">Start Date and Time</label>
                    </div>
                    <div class="form-floating create-quiz-end-date-wrapper">
                        <input type="datetime-local" name="end_time" class="form-control font-rubik"
                            id="create-quiz-end-date" placeholder="End Date and Time" required>
                        <label for="create-quiz-end-date" class="font-rubik">End Date and Time</label>
                    </div>
                    <div class="form-floating create-quiz-time-limit-wrapper">
                        <input type="number" name="time_limit" class="form-control font-rubik"
                            id="create-quiz-time-limit" placeholder="Time Limit (minutes)" required>
                        <label for="create-quiz-time-limit" class="font-rubik">Time Limit (minutes)</label>
                    </div>
                    <div class="create-quiz-switches">
                        <div class="form-check form-switch">
                            <input class="form-check-input" name="allow_answer_view" type="checkbox" role="switch"
                                id="create-quiz-show-answers">
                            <label class="form-check-label font-rubik" for="create-quiz-show-answers">Display Answers
                                after Quiz Time</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" name="randomize_questions" type="checkbox" role="switch"
                                id="create-quiz-random">
                            <label class="form-check-label font-rubik" for="create-quiz-random">Randomize
                                Questions</label>
                        </div>
                    </div>
                    <input type="hidden" name="subject_id" id="subject-id-input" value="">
                </form>
                <div class="modal-footer create-quiz-modal-footer">
                    <div class="create-quiz-buttons">
                        <button type="button" class="btn create-quiz-cancel font-rubik"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn create-quiz-confirm font-rubik"
                            id="confirm-button">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set the subject ID when the modal is opened
        var createQuizModal = document.getElementById('create-quiz-modal');
        createQuizModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var subjectId = button.getAttribute('data-subject-id'); // Extract info from data-* attributes
            var subjectIdInput = document.getElementById('subject-id-input');
            subjectIdInput.value = subjectId; // Update the hidden input field
        });

        // JavaScript to handle the Confirm button click
        document.getElementById('confirm-button').addEventListener('click', function () {
            var form = document.getElementById('create-quiz-form');
            var quizName = form.quiz_name.value.trim();
            var startTime = form.start_time.value;
            var endTime = form.end_time.value;
            var timeLimit = form.time_limit.value.trim();

            // Validate the form fields
            if (!quizName) {
                alert('Please enter a quiz name.');
                return;
            }
            if (!startTime) {
                alert('Please select a start date and time.');
                return;
            }
            if (!endTime) {
                alert('Please select an end date and time.');
                return;
            }

            // Check if start time and end time are the same
            if (startTime === endTime) {
                alert('Start time and end time cannot be the same.');
                return;
            }

            if (!timeLimit || isNaN(timeLimit) || timeLimit <= 0) {
                alert('Please enter a valid time limit (greater than 0).');
                return;
            }

            // Trigger form submission if validation passes
            form.submit();
        });
    </script>


    <!-- VIEW GRADE MODAL -->
    <div class="modal fade" id="view-grade-modal" data-bs-keyboard="false" aria-labelledby="View Grade"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content view-grade-modal-content">
                <div class="modal-header view-grade-modal-header">
                    <span class="view-grade-header font-rubik-medium">View Grades</span>
                </div>
                <div class="modal-body view-grade-modal-body" id="modal-body-content">
                    <!-- Content from quiz_results.php will be loaded here -->
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

    <script>
        function loadQuizResults(quizId) {
            // Use AJAX to fetch the quiz_results.php content, passing the quizId as a query parameter
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'quiz_results.php?quiz_id=' + quizId, true);
            xhr.onload = function () {
                if (this.status === 200) {
                    // Load the response text into the modal body
                    document.getElementById('modal-body-content').innerHTML = this.responseText;
                } else {
                    // Handle error, e.g., show an error message
                    document.getElementById('modal-body-content').innerHTML = '<p>Error loading grades. Please try again.</p>';
                }
            };
            xhr.send();
        }
    </script>



    <!-- REMOVE QUIZ MODAL -->
    <div class="modal fade" id="remove-quiz-modal" data-bs-keyboard="false" aria-labelledby="Remove Quiz"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content remove-quiz-modal-content">
                <div class="modal-header remove-quiz-modal-header">
                    <span class="remove-quiz-header font-rubik-medium">Remove Quiz</span>
                </div>
                <div class="modal-body remove-quiz-modal-body">
                    <span class="remove-quiz-text font-rubik">Are you sure? This quiz will be removed.</span>
                </div>
                <div class="modal-footer remove-quiz-modal-footer">
                    <div class="remove-quiz-buttons">
                        <button type="button" class="btn remove-quiz-cancel font-rubik"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn remove-quiz-confirm font-rubik"
                            id="remove-quiz-confirm-btn">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Declare a variable to hold the quiz ID
        let quizIdToRemove = null;

        // Set the quiz ID when the remove button is clicked
        document.querySelectorAll('.remove-quiz').forEach(button => {
            button.addEventListener('click', function () {
                // Get the quiz ID from the data attribute
                quizIdToRemove = this.getAttribute('data-quiz-id');
            });
        });

        // Handle confirmation button click
        document.getElementById('remove-quiz-confirm-btn').addEventListener('click', function () {
            if (quizIdToRemove) { // Ensure quizIdToRemove is set
                // AJAX request to remove the quiz
                fetch('remove_quiz.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ delete_quiz_id: quizIdToRemove }) // Use delete_quiz_id for deletion
                })
                    .then(response => {
                        // Check if the response is ok (status 200-299)
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Quiz removed successfully
                            alert('Quiz removed successfully.');
                            // Redirect to the subject details page
                            const subjectId = new URLSearchParams(window.location.search).get('subject_id'); // Get subject_id from the URL
                            window.location.href = `http://localhost/QuizMaker1/admin_subject_details.php?subject_id=${subjectId}`; // Redirect
                        } else {
                            // Handle error
                            alert('Error removing quiz: ' + data.message);
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('There was an error removing the quiz.');
                    });
            }
        });
    </script>

    <!-- EDIT QUIZ MODAL -->
    <div class="modal fade" id="edit-quiz-modal" data-bs-keyboard="false" aria-labelledby="Edit Quiz"
        aria-hidden="true">
        <div class="modal-dialog edit-quiz-modal-dialog">
            <div class="modal-content edit-quiz-modal-content">
                <div class="modal-header edit-quiz-modal-header">
                    <span class="create-quiz-header font-rubik-medium">Edit Quiz</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body edit-quiz-modal-body">
                    <iframe id="edit-quiz-iframe" src="" style="width: 100%; height: 450px; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to handle the modal opening -->
    <script>
        const editQuizModal = document.getElementById('edit-quiz-modal');
        editQuizModal.addEventListener('show.bs.modal', function (event) {
            // Get quiz ID from the button that triggered the modal
            const button = event.relatedTarget;
            const quizId = button.getAttribute('data-quiz-id');

            // Construct the URL for the iframe
            const subjectId = '<?php echo $subject_id; ?>'; // Get the subject ID from your PHP variable
            const iframeUrl = `edit_quiz.php?quiz_id=${quizId}&subject_id=${subjectId}`;

            // Set the iframe's src attribute to the URL
            const iframe = document.getElementById('edit-quiz-iframe');
            iframe.src = iframeUrl;
        });
    </script>

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
</body>

</html>