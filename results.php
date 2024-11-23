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

// Fetch quiz details
$quizQuery = "SELECT quiz_name, subject_id FROM quizzes WHERE id = ?";
$quizStmt = $pdoConnect->prepare($quizQuery);
$quizStmt->execute([$quiz_id]);
$quiz = $quizStmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die("Quiz not found.");
}

// Fetch student answers and question details
$studentAnswersQuery = "
    SELECT sa.student_id, sa.question_id, sa.answer_text, q.question_text, q.question_type, q.points
    FROM student_answers sa
    JOIN questions q ON sa.question_id = q.id
    WHERE sa.quiz_id = ?
";
$studentAnswersStmt = $pdoConnect->prepare($studentAnswersQuery);
$studentAnswersStmt->execute([$quiz_id]);
$studentAnswers = $studentAnswersStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if there are no answers submitted
if (empty($studentAnswers)) {
    $noAnswersMessage = "No answers have been submitted for this quiz.";
}

// Fetch all questions and their possible answers
$questionsQuery = "
    SELECT q.id as question_id, q.question_text, q.question_type, q.points,
           c.choice_text as choice_text,
           (SELECT answer_text FROM answers a WHERE a.question_id = q.id AND a.is_correct = 1 LIMIT 1) as correct_answer
    FROM questions q
    LEFT JOIN choices c ON q.id = c.question_id
    WHERE q.quiz_id = ?
";
$questionsStmt = $pdoConnect->prepare($questionsQuery);
$questionsStmt->execute([$quiz_id]);
$questionsData = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize results storage
$results = [];
foreach ($questionsData as $question) {
    $questionId = $question['question_id'];
    $questionType = $question['question_type'];

    if (!isset($results[$questionType])) {
        $results[$questionType] = [];
    }

    if (!isset($results[$questionType][$questionId])) {
        $results[$questionType][$questionId] = [
            'question_text' => $question['question_text'],
            'points' => $question['points'],
            'answers' => [],
        ];
    }

    // Add choice/answer to the results
    if ($question['choice_text']) {
        $results[$questionType][$questionId]['answers'][$question['choice_text']] = 0; // Initialize
    }
}

// Process student answers to tally them
foreach ($studentAnswers as $answer) {
    $questionId = $answer['question_id'];
    $answerText = $answer['answer_text'] ?? ''; // Use null coalescing to avoid undefined index
    $questionType = $answer['question_type'];

    if ($questionType === 'enumeration') {
        // Split the concatenated answers into an array
        $splitAnswers = array_filter(array_map('trim', explode(',', $answerText)));
        foreach ($splitAnswers as $splitAnswer) {
            if (isset($results[$questionType][$questionId]['answers'][$splitAnswer])) {
                $results[$questionType][$questionId]['answers'][$splitAnswer]++;
            } else {
                // In case the student's split answer is not a defined choice, consider it a custom input
                if (!empty($splitAnswer)) { // Only count non-empty answers
                    $results[$questionType][$questionId]['answers'][$splitAnswer] = 1;
                }
            }
        }
    } else {
        // For other question types
        if (isset($results[$questionType][$questionId]['answers'][$answerText])) {
            $results[$questionType][$questionId]['answers'][$answerText]++;
        } else {
            // In case a student's answer is not a defined choice, consider it a custom input
            if (!empty($answerText)) { // Only count non-empty answers
                $results[$questionType][$questionId]['answers'][$answerText] = 1;
            }
        }
    }
}

// Get total number of students who answered the quiz
$totalStudents = count(array_unique(array_column($studentAnswers, 'student_id')));

// Function to generate chart data
function generateChartData($results, $questionId, $questionType)
{
    $chartData = [];
    if (isset($results[$questionType][$questionId]['answers'])) {
        foreach ($results[$questionType][$questionId]['answers'] as $key => $value) {
            $chartData[] = ['label' => $key, 'value' => $value];
        }
    }
    return $chartData;
}

// HTML and JavaScript for charts
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['quiz_name']); ?> - Results</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
            /* For positioning dropdown */
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .dropdown {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            min-width: 160px;
            z-index: 10;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .chart-container {
            margin: 20px 0;
        }

        .chart-grid {
            display: flex;
            justify-content: space-between;
        }

        .chart-grid>div {
            width: 45%;
        }

        .message {
            color: red;
            text-align: center;
        }

        .choice-details {
            text-align: left;
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    


    <h1><?php echo htmlspecialchars($quiz['quiz_name']); ?> Results</h1>
    <h4>Total Students Answered: <?php echo $totalStudents; ?></h4>

    <?php if (isset($noAnswersMessage)): ?>
        <div class="message"><?php echo $noAnswersMessage; ?></div>
    <?php else: ?>
        <?php if (empty($results)): ?>
            <div class="message">No results available for this quiz.</div>
        <?php else: ?>
            <?php
            // Counter for question numbering
            $questionCounter = 1;
            ?>
            <?php foreach ($results as $questionType => $questions): ?>
                <?php foreach ($questions as $questionId => $questionData): ?>
                    <h2><?php echo htmlspecialchars($questionData['question_text']); ?> (Points:
                        <?php echo $questionData['points']; ?>)
                    </h2>
                    <p><strong>Question Number:</strong> <?php echo $questionCounter++; ?></p>
                    <p><strong>Question Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $questionType)); ?></p>

                    <!-- Bar Chart and Pie Chart in Grid -->
                    <div class="chart-grid">
                        <!-- Bar Chart -->
                        <div>
                            <canvas id="bar-chart-<?php echo $questionId; ?>"></canvas>
                            <div class="choice-details">
                                <?php
                                // Prepare answers for display
                                foreach ($results[$questionType][$questionId]['answers'] as $answerText => $count) {
                                    echo "<p>" . htmlspecialchars($answerText) . ": " . $count . " student(s)</p>";
                                }
                                ?>
                            </div>
                            <script>
                                var ctxBar = document.getElementById('bar-chart-<?php echo $questionId; ?>').getContext('2d');
                                var chartDataBar = <?php echo json_encode(generateChartData($results, $questionId, $questionType)); ?>;

                                var labelsBar = chartDataBar.map(item => item.label);
                                var valuesBar = chartDataBar.map(item => item.value);

                                new Chart(ctxBar, {
                                    type: 'bar',
                                    data: {
                                        labels: labelsBar,
                                        datasets: [{
                                            label: 'Number of Students',
                                            data: valuesBar,
                                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            </script>
                        </div>

                        <!-- Pie Chart -->
                        <div>
                            <canvas id="pie-chart-<?php echo $questionId; ?>"></canvas>
                            <script>
                                // Function to generate random colors
                                function getRandomColor() {
                                    const letters = '0123456789ABCDEF';
                                    let color = '#';
                                    for (let i = 0; i < 6; i++) {
                                        color += letters[Math.floor(Math.random() * 16)];
                                    }
                                    return color;
                                }

                                var ctxPie = document.getElementById('pie-chart-<?php echo $questionId; ?>').getContext('2d');

                                // Generate an array of random colors based on the number of labels
                                var pieColors = labelsBar.map(() => getRandomColor());

                                new Chart(ctxPie, {
                                    type: 'pie',
                                    data: {
                                        labels: labelsBar,
                                        datasets: [{
                                            label: 'Number of Students',
                                            data: valuesBar,
                                            backgroundColor: pieColors, // Use dynamically generated colors
                                            borderColor: 'rgba(255, 255, 255, 1)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true
                                    }
                                });
                            </script>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

</body>

</html>