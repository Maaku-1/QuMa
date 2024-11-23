<?php
session_start();
include 'dbcon.php';

// Validate quiz_id
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$quiz_id = $_GET['quiz_id'];
$student_id = $_SESSION['user_id'];

// Fetch quiz results
$resultQuery = "
    SELECT score 
    FROM quiz_results 
    WHERE quiz_id = ? AND student_id = ?
";
$stmt = $pdoConnect->prepare($resultQuery);
$stmt->execute([$quiz_id, $student_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate total questions and total points
$totalQuestionsQuery = "SELECT id, points FROM questions WHERE quiz_id = ?";
$totalStmt = $pdoConnect->prepare($totalQuestionsQuery);
$totalStmt->execute([$quiz_id]);
$questions = $totalStmt->fetchAll(PDO::FETCH_ASSOC);

$totalQuestions = count($questions);
$totalPoints = array_sum(array_column($questions, 'points')); // Sum of points

$score = $result['score'] ?? 0;
$percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;

// Fetch quiz settings for answer view permission
$quizSettingsQuery = "SELECT allow_answer_view FROM quizzes WHERE id = ?";
$quizSettingsStmt = $pdoConnect->prepare($quizSettingsQuery);
$quizSettingsStmt->execute([$quiz_id]);
$quizSettings = $quizSettingsStmt->fetch(PDO::FETCH_ASSOC);
$allowAnswerView = $quizSettings['allow_answer_view'] ?? 0;

// Prepare response
$response = [
    'success' => true,
    'score' => $score,
    'totalQuestions' => $totalQuestions,
    'percentage' => $percentage,
];

// Check if answers should be displayed
if ($allowAnswerView == 1) {
    // Fetch student answers along with points from questions table
    $answersQuery = "
        SELECT sa.question_id, sa.answer_text, q.points 
        FROM student_answers sa
        JOIN questions q ON sa.question_id = q.id
        WHERE sa.quiz_id = ? AND sa.student_id = ?
    ";
    $answersStmt = $pdoConnect->prepare($answersQuery);
    $answersStmt->execute([$quiz_id, $student_id]);
    $studentAnswers = $answersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare arrays for correct answers and question texts
    $correctAnswers = [];
    foreach ($studentAnswers as $studentAnswer) {
        $question_id = $studentAnswer['question_id'];

        // Get question text and type
        $questionQuery = "SELECT question_text, question_type FROM questions WHERE id = ?";
        $questionStmt = $pdoConnect->prepare($questionQuery);
        $questionStmt->execute([$question_id]);
        $question = $questionStmt->fetch(PDO::FETCH_ASSOC);

        // Determine correct answer based on question type
        switch ($question['question_type']) {
            case 'multiple_choice':
                $correctAnswerQuery = "
                    SELECT choice_text 
                    FROM choices 
                    WHERE question_id = ? AND is_correct = 1
                ";
                break;
            case 'identification':
                $correctAnswerQuery = "
                    SELECT answer_text 
                    FROM identification_answers 
                    WHERE question_id = ?
                ";
                break;
            case 'true_false':
            case 'enumeration':
                $correctAnswerQuery = "
                    SELECT answer_text 
                    FROM answers 
                    WHERE question_id = ?
                ";
                break;
        }

        $correctAnswerStmt = $pdoConnect->prepare($correctAnswerQuery);
        $correctAnswerStmt->execute([$question_id]);
        $correctAnswer = $correctAnswerStmt->fetchColumn();

        $correctAnswers[$question_id] = [
            'correctAnswer' => $correctAnswer,
            'points' => $studentAnswer['points'], // points for each question from the join
            'question_text' => $question['question_text'],
            'question_type' => $question['question_type'],
        ];
    }

    // Add answers to response
    $response['studentAnswers'] = $studentAnswers;
    $response['correctAnswers'] = $correctAnswers;
} else {
    // Provide a simple score summary
    $response['message'] = "Score: $score / Total: $totalQuestions";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .result {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .result p {
            margin: 5px 0;
        }
        .answer {
            margin: 10px 0;
            padding: 8px;
            background: #e0f7fa;
            border-left: 5px solid #00796b;
        }
        .correct {
            background: #c8e6c9;
            border-left: 5px solid #388e3c;
        }
        .wrong {
            background: #ffcdd2;
            border-left: 5px solid #d32f2f;
        }
    </style>
</head>
<body>


    <h1>Quiz Results</h1>
    <div class="result">
        <p>Score: <?php echo $response['score']; ?></p>
        <p>Total Questions: <?php echo $response['totalQuestions']; ?></p>
        <p>Percentage: <?php echo $response['percentage']; ?>%</p>
        <?php if ($allowAnswerView == 1): ?>
            <h3>Your Answers</h3>
            <?php foreach ($response['studentAnswers'] as $studentAnswer): ?>
                <div class="answer">
                    <?php 
                    $question_id = $studentAnswer['question_id'];
                    $pointsGot = $correctAnswers[$question_id]['points']; // Points for the question
                    ?>
                    <p>Question: <?php echo htmlspecialchars($correctAnswers[$question_id]['question_text']); ?></p>
                    <p>Question Type: <?php echo htmlspecialchars($correctAnswers[$question_id]['question_type']); ?></p>
                    <p>Your Answer: <?php echo htmlspecialchars($studentAnswer['answer_text']); ?> (Points: <?php echo $pointsGot; ?>)</p>
                    <p>Correct Answer: <?php echo htmlspecialchars($correctAnswers[$question_id]['correctAnswer']); ?> (Points: <?php echo $correctAnswers[$question_id]['points']; ?>)</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?php echo $response['message']; ?></p>
        <?php endif; ?>
    </div>


</body>
</html>
