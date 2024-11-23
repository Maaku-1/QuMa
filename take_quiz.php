<?php
session_start();
include 'dbcon.php';
include 'quiz_functions.php';

// Set default timezone to UTC +8:00
date_default_timezone_set('Asia/Shanghai');

// Ensure the user is logged in and is a student
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: index.php");
    exit;
}

// Check if student ID exists in the student table
$student_id = $_SESSION['user_id'];

if (!checkStudentExists($student_id)) {
    die("Student ID does not exist in the database.");
}

// Check if quiz_id is provided
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die("Invalid quiz ID.");
}

$quiz_id = $_GET['quiz_id'];

// Fetch quiz details
$quiz = fetchQuizDetails($quiz_id);

if (!$quiz) {
    die("Quiz not found.");
}

// Check if the student has already taken this quiz
$resultCheckQuery = "SELECT COUNT(*) FROM quiz_results WHERE student_id = ? AND quiz_id = ?";
$resultCheckStmt = $pdoConnect->prepare($resultCheckQuery);
$resultCheckStmt->execute([$student_id, $quiz_id]);
$resultCount = $resultCheckStmt->fetchColumn();

if ($resultCount > 0) {
    // Store a message in the session
    $_SESSION['message'] = "You have already taken this quiz.";

    // Redirect to student dashboard if quiz has already been taken
    header("Location: studentdashboard.php");
    exit;
}

// Check if the quiz is available
$current_time = new DateTime();
$start_time = new DateTime($quiz['start_time']);
$end_time = new DateTime($quiz['end_time']);

if ($current_time < $start_time) {
    die("Quiz has not started yet.");
}
if ($current_time > $end_time) {
    die("Quiz has already ended.");
}

// Fetch questions
$questions = fetchQuestions($quiz_id);

// Randomize questions if needed
if ($quiz['randomize_questions'] === 1) {
    shuffle($questions);
}

$score = 0;
$totalPoints = 0;
$submittedAnswers = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate that all questions are answered
    $allAnswered = true;
    foreach ($questions as $question) {
        $question_id = $question['id'];
        if (!isset($_POST['answer'][$question_id]) && empty($_POST['custom_answer'][$question_id])) {
            $allAnswered = false;
            break;
        }
    }

    if (!$allAnswered) {
        echo "<h2>Please answer all questions before submitting!</h2>";
    } else {
        // Process quiz submission
        foreach ($questions as $question) {
            $question_id = $question['id'];
            $answer_id = isset($_POST['answer'][$question_id]) ? $_POST['answer'][$question_id] : [];


            // Handle multiple choice answers differently
            if ($question['question_type'] === 'multiple_choice') {
                // Fetch the text for each selected choice (if any)
                if (!empty($answer_id)) {
                    $answerTextArray = [];
                    foreach ($answer_id as $choice_id) {
                        // Fetch the choice text based on the selected ID
                        $choiceQuery = "SELECT choice_text FROM choices WHERE id = ?";
                        $choiceStmt = $pdoConnect->prepare($choiceQuery);
                        $choiceStmt->execute([$choice_id]);
                        $choiceText = $choiceStmt->fetchColumn(); // Get the choice text

                        if ($choiceText) {
                            $answerTextArray[] = $choiceText;
                        }
                    }
                    // Convert array of selected choice texts to a comma-separated string
                    $answerText = implode(', ', $answerTextArray);
                }

                // Now call saveStudentAnswer with the generated answerText
                saveStudentAnswer($quiz_id, $student_id, $question_id, $answerText);
            } else {
                // Handle custom answers (text inputs)
                $answerText = isset($_POST['custom_answer'][$question_id])
                    ? (is_array($_POST['custom_answer'][$question_id])
                        ? implode(', ', array_map('trim', $_POST['custom_answer'][$question_id]))
                        : trim($_POST['custom_answer'][$question_id]))
                    : '';
            }

            // Handle true/false answers
            if ($question['question_type'] === 'true_false') {
                if (isset($_POST['answer'][$question_id])) {
                    $answerText = $_POST['answer'][$question_id]; // Set True/False value
                }
            }

            // Insert into student_answers
            saveStudentAnswer($quiz_id, $student_id, $question_id, $answerText);

            // Calculate score
            $earnedPoints = calculateScore($question, $answer_id, $answerText);
            $score += $earnedPoints; // Add earned points to score
            $totalPoints += $question['points']; // Count all questions for total points

            // Store the answer for comparison
            $submittedAnswers[$question_id] = [
                'student_answer' => $answerText,
                'correct_answer' => getCorrectAnswer($question_id, $question['question_type']),
            ];
        }

        // Store final score
        saveQuizResult($quiz_id, $student_id, $score);

        // Display final score and answers comparison
        $percentage = ($totalPoints > 0) ? ($score / $totalPoints) * 100 : 0; // Avoid division by zero
        echo "<h2>Your Score: $score/$totalPoints (" . round($percentage, 2) . "%)</h2>";
        echo "<h3>Answers Comparison:</h3>";

        foreach ($questions as $question) {
            $question_id = $question['id'];
            $question_text = $question['question_text'];
            $question_type = ucfirst($question['question_type']); // Capitalize the first letter of the question type

            // Fetch the correct and student's answer for each question
            $student_answer = isset($submittedAnswers[$question_id]['student_answer'])
                ? htmlspecialchars($submittedAnswers[$question_id]['student_answer'])
                : "Not answered";

            // Fetch the correct answer
            $correct_answer = isset($submittedAnswers[$question_id]['correct_answer'])
                ? $submittedAnswers[$question_id]['correct_answer']
                : "Not available";

            // Check if the correct answer is an array and then implode
            if (is_array($correct_answer)) {
                $correct_answer = htmlspecialchars(implode(', ', $correct_answer)); // Ensure proper spacing
            } else {
                $correct_answer = htmlspecialchars($correct_answer); // Just HTML escape if not an array
            }

            // Determine if the answer was correct
            $is_correct = ($student_answer === $correct_answer) ? "Correct" : "Incorrect";

            // Calculate points for this specific question
            $earnedPoints = calculateScore($question, $_POST['answer'][$question_id] ?? [], $student_answer);

            // Display the question, question type, and answers
            echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
            echo "<p><strong>Question:</strong> " . htmlspecialchars($question_text) . " (" . $question['points'] . " point(s))</p>";
            echo "<p><strong>Type:</strong> " . htmlspecialchars($question_type) . "</p>";
            // New logic for enumeration answers
            if ($question['question_type'] === 'enumeration') {
                // Extract submitted answers
                $providedAnswers = array_filter(array_map('trim', explode(',', $student_answer)));
                $uniqueProvidedAnswers = array_unique($providedAnswers);

                $correctAnswers = getCorrectAnswer($question_id, $question['question_type']);
                $earnedPoints = 0;

                // Initialize display strings
                $providedAnswersDisplay = [];

                foreach ($uniqueProvidedAnswers as $answer) {
                    $is_correct = in_array($answer, $correctAnswers) ? "Correct" : "Incorrect";
                    $pointsForAnswer = in_array($answer, $correctAnswers) ? ($question['points'] / $question['expected_answers']) : 0;

                    // Track points for each unique answer
                    $earnedPoints += $pointsForAnswer;

                    $providedAnswersDisplay[] = "$answer ($is_correct" . ($pointsForAnswer > 0 ? ", " . round($pointsForAnswer, 2) . " points" : "") . ")";
                }
                echo "<p><strong>Your Answer:</strong> " . implode(', ', $providedAnswersDisplay) . " <em>(Total: " . round($earnedPoints, 2) . " points)</em></p>";
            } else {
                echo "<p><strong>Your Answer:</strong> " . $student_answer . " <em>($is_correct)</em> ($earnedPoints points)</p>";
            }

            echo "<p><strong>Correct Answer:</strong> " . $correct_answer . "</p>";
            echo "</div>";
        }

        echo '<form action="studentdashboard.php" method="get">';
        echo '<button type="submit">Back to Dashboard</button>';
        echo '</form>';
        exit;
    }
}
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
    <title><?php echo htmlspecialchars($quiz['quiz_name']); ?></title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;

            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;

        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        .quiz-box {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .quiz-box p {
            margin: 0 0 10px;
        }

        .question-text {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
        }

        label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 1em;
        }

        input[type="text"],
        input[type="checkbox"],
        input[type="radio"] {
            margin-right: 10px;
            padding: 5px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .timer {
            font-size: 1.5em;
            font-weight: bold;
            color: #dc3545;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            border: 2px solid #dc3545;
            border-radius: 5px;
            padding: 5px 10px;
            z-index: 9999;
        }

        button[type="submit"] {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .header-wrapper {
            background: transparent;
            /* Header untouched */
        }

        /* Container for answer comparison */
.answer-comparison {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
}

/* Question text styling */
.answer-comparison .question {
    font-size: 1.2em;
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
}

/* Answer labels */
.answer-comparison .label {
    font-size: 1em;
    font-weight: bold;
    color: #555;
    margin-right: 5px;
}

/* Styling for correct answers */
.answer-comparison .correct-answer {
    color: #28a745;
    font-weight: bold;
}

/* Styling for incorrect answers */
.answer-comparison .incorrect-answer {
    color: #dc3545;
    font-weight: bold;
}

/* Styling for points and feedback */
.answer-comparison .feedback {
    margin-top: 10px;
    font-size: 0.9em;
    color: #6c757d;
    font-style: italic;
}

/* Styling for points */
.answer-comparison .points {
    font-size: 1em;
    font-weight: bold;
    color: #007bff;
}

/* Responsive handling */
@media (max-width: 600px) {
    .answer-comparison {
        font-size: 0.9em;
        padding: 10px;
    }
}

    </style>
    <script src="quiz_timer.js"></script>
</head>

<body>
    <!-- HEADER -->
    <div class="header-wrapper" id="header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-hero-wrapper">
                    <span class="header-hero font-poppins-bold">QM</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="timer" id="timer" data-time-limit="<?php echo $quiz['time_limit'] * 60; ?>"></div>
        <h1><?php echo htmlspecialchars($quiz['quiz_name']); ?></h1>

        <form id="quizForm" method="post">
            <?php foreach ($questions as $question): ?>
                <div class="quiz-box">
                    <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                    <p>Type: <?php echo htmlspecialchars(ucfirst($question['question_type'])); ?> | Points:
                        <?php echo htmlspecialchars($question['points']); ?></p>

                    <?php if ($question['question_type'] === 'multiple_choice'): ?>
                        <?php
                        $choicesQuery = "SELECT id, choice_text FROM choices WHERE question_id = ?";
                        $choicesStmt = $pdoConnect->prepare($choicesQuery);
                        $choicesStmt->execute([$question['id']]);
                        $choices = $choicesStmt->fetchAll(PDO::FETCH_ASSOC);
                        $correctChoiceCountQuery = "SELECT COUNT(*) FROM choices WHERE question_id = ? AND is_correct = 1";
                        $correctChoiceCountStmt = $pdoConnect->prepare($correctChoiceCountQuery);
                        $correctChoiceCountStmt->execute([$question['id']]);
                        $correctChoiceCount = $correctChoiceCountStmt->fetchColumn();
                        ?>
                        <p><strong>Select <?php echo $correctChoiceCount; ?> answer(s)</strong></p>
                        <?php foreach ($choices as $choice): ?>
                            <label>
                                <input type="checkbox" name="answer[<?php echo $question['id']; ?>][]"
                                    value="<?php echo $choice['id']; ?>">
                                <?php echo htmlspecialchars($choice['choice_text']); ?>
                            </label>
                        <?php endforeach; ?>

                    <?php elseif ($question['question_type'] === 'identification'): ?>
                        <input type="text" name="custom_answer[<?php echo $question['id']; ?>]" placeholder="Your answer"
                            required>

                    <?php elseif ($question['question_type'] === 'enumeration'): ?>
                        <?php for ($i = 1; $i <= $question['expected_answers']; $i++): ?>
                            <input type="text" name="custom_answer[<?php echo $question['id']; ?>][]"
                                placeholder="Answer <?php echo $i; ?>" required>
                        <?php endfor; ?>

                    <?php elseif ($question['question_type'] === 'true_false'): ?>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="True" required> True
                        </label>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="False" required> False
                        </label>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit">Submit Quiz</button>
        </form>
    </div>
</body>

</html>