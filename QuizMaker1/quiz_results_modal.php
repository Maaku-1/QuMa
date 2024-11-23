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
$quizQuery = "SELECT quiz_name FROM quizzes WHERE id = ?";
$quizStmt = $pdoConnect->prepare($quizQuery);
$quizStmt->execute([$quiz_id]);
$quiz = $quizStmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die("Quiz not found.");
}

// Fetch quiz results for students
$resultsQuery = "
    SELECT s.student_id, s.lname, s.fname, s.mi, s.year, s.section, qr.score
    FROM quiz_results qr
    JOIN student s ON qr.student_id = s.student_id
    WHERE qr.quiz_id = ?
";
$resultsStmt = $pdoConnect->prepare($resultsQuery);
$resultsStmt->execute([$quiz_id]);
$quizResults = $resultsStmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare CSV download
if (isset($_POST['download'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="QUMA-' . htmlspecialchars($quiz['quiz_name']) . '-' . date('m-d-Y') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student ID', 'Last Name', 'First Name', 'MI', 'Year-Section', 'Score']);

    foreach ($quizResults as $result) {
        fputcsv($output, [
            $result['student_id'],
            $result['lname'],
            $result['fname'],
            $result['mi'] ?? '',
            $result['year'] . '-' . $result['section'],
            $result['score']
        ]);
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['quiz_name']); ?> - Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .chart-container {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        canvas {
            width: 35% !important; /* Adjust width to fit */
            height: auto !important; /* Auto height */
        }
    </style>
</head>
<body>

<h2><?php echo htmlspecialchars($quiz['quiz_name']); ?> Results</h2>

<table>
    <tr>
        <th>Student ID</th>
        <th>Name</th>
        <th>Year-Section</th>
        <th>Score/Points</th>
    </tr>
    <?php foreach ($quizResults as $result): ?>
        <tr>
            <td><?php echo htmlspecialchars($result['student_id']); ?></td>
            <td><?php echo htmlspecialchars($result['lname'] . ', ' . $result['fname'] . ' ' . ($result['mi'] ?? '') . '.'); ?></td>
            <td><?php echo htmlspecialchars($result['year'] . '-' . $result['section']); ?></td>
            <td><?php echo htmlspecialchars($result['score']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Button to download results as CSV -->
<form method="post">
    <button type="submit" name="download">Download Quiz Results</button>
</form>

<div class="chart-container">
    <canvas id="scoreBarChart"></canvas>
    <canvas id="scorePieChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Generate Charts
    function generateCharts() {
        const scores = <?php echo json_encode(array_column($quizResults, 'score')); ?>;
        const labels = <?php echo json_encode(array_column($quizResults, 'student_id')); ?>;

        // Bar Chart
        const ctxBar = document.getElementById('scoreBarChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Scores',
                    data: scores,
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

        // Pie Chart
        const ctxPie = document.getElementById('scorePieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Scores',
                    data: scores,
                    backgroundColor: ['rgba(255, 99, 132, 0.6)', 'rgba(54, 162, 235, 0.6)', 'rgba(255, 206, 86, 0.6)', 'rgba(75, 192, 192, 0.6)'],
                    borderColor: 'rgba(255, 255, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    // Generate charts on page load
    window.onload = generateCharts;
</script>

</body>
</html>
