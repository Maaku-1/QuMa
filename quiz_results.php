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

    <iframe src="quiz_results_modal.php?quiz_id=<?php echo $quiz_id; ?>" style="width: 100%; height: 100vh; border: none; overflow: hidden;"></iframe><br>
    <iframe src="results.php?quiz_id=<?php echo $quiz_id; ?>" style="width: 100%; height: 100vh; border: none; overflow: hidden;"></iframe>

</body>

</html>