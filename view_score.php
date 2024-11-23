<?php
include 'dbcon.php';
session_start();

// Check if the student is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: index.php");
    exit;
}

// Check if quiz_id is provided
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die("Invalid quiz ID.");
}

$quiz_id = $_GET['quiz_id'];
$student_id = $_SESSION['user_id']; // Get the student ID from the session

// Fetch the quiz details
$quizQuery = "
    SELECT quiz_name, allow_answer_view, end_time
    FROM quizzes
    WHERE id = ?
";
$quizStmt = $pdoConnect->prepare($quizQuery);
$quizStmt->execute([$quiz_id]);
$quiz = $quizStmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die("Quiz not found.");
}

// Fetch the student's score for the quiz
$scoreQuery = "
    SELECT score
    FROM quiz_results
    WHERE quiz_id = ? AND student_id = ?
";
$scoreStmt = $pdoConnect->prepare($scoreQuery);
$scoreStmt->execute([$quiz_id, $student_id]);
$result = $scoreStmt->fetch(PDO::FETCH_ASSOC);
$score = $result ? $result['score'] : 0;

// Calculate percentage
$percentage = $score > 0 ? ($score / getTotalQuestions($quiz_id)) * 100 : 0;

// Fetch quiz questions and answers if allowed and after the quiz time
$questions = [];
$current_time = date('Y-m-d H:i:s'); // Get current time
if ($quiz['allow_answer_view'] && $current_time >= $quiz['end_time']) {
    $questions = getQuizQuestions($quiz_id);
}

// Function to get total number of questions
function getTotalQuestions($quiz_id) {
    global $pdoConnect;
    $totalQuery = "
        SELECT COUNT(*) as total
        FROM questions
        WHERE quiz_id = ?
    ";
    $stmt = $pdoConnect->prepare($totalQuery);
    $stmt->execute([$quiz_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Function to get quiz questions and answers
function getQuizQuestions($quiz_id) {
    global $pdoConnect;
    $questionQuery = "
        SELECT q.id as question_id, q.question_text, a.answer_text, a.is_correct
        FROM questions q
        LEFT JOIN answers a ON q.id = a.question_id
        WHERE q.quiz_id = ?
    ";
    $stmt = $pdoConnect->prepare($questionQuery);
    $stmt->execute([$quiz_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['quiz_name']); ?> - Score</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        p {
            color: #555;
            line-height: 1.6;
        }
        .score {
            font-weight: bold;
            font-size: 24px;
            color: #007BFF;
        }
        .percentage {
            font-weight: bold;
            font-size: 20px;
            color: #28A745;
        }
        .question {
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .correct {
            color: #28A745;
        }
        .incorrect {
            color: #DC3545;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007BFF;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($quiz['quiz_name']); ?></h1>
        <p class="score">Your Score: <?php echo htmlspecialchars($score); ?></p>
        <p class="percentage">Percentage: <?php echo number_format($percentage, 2); ?>%</p>
        
        <?php if ($quiz['allow_answer_view'] && $current_time >= $quiz['end_time']): ?>
            <h2>Questions and Answers:</h2>
            <?php foreach ($questions as $question): ?>
                <div class="question">
                    <p><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>
                    <p class="<?php echo $question['is_correct'] ? 'correct' : 'incorrect'; ?>">
                        Correct Answer: <?php echo htmlspecialchars($question['answer_text']); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Answers will be available after the quiz ends.</p>
        <?php endif; ?>
        
        <a href="studentdashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
