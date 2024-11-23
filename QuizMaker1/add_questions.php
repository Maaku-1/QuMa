<?php
include 'dbcon.php'; // Include database connection file
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$error_message = '';
$quiz_id = $_GET['quiz_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;

// Validate quiz ID
if (!$quiz_id || !is_numeric($quiz_id)) {
    die("Invalid quiz ID.");
}

// Set default question type
$default_question_type = 'multiple_choice';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = trim($_POST['question_text']);
    $question_type = $_POST['question_type'];
    $points = (int) $_POST['points'];

    // Validate inputs
    if (empty($question_text) || empty($question_type) || $points <= 0) {
        $error_message = "All fields are required, and points must be greater than 0.";
    } else {
        // Check for duplicate question
        $check_query = "SELECT COUNT(*) FROM questions WHERE quiz_id = ? AND question_text = ?";
        $check_stmt = $pdoConnect->prepare($check_query);
        $check_stmt->execute([$quiz_id, $question_text]);
        $duplicate_count = $check_stmt->fetchColumn();

        if ($duplicate_count > 0) {
            echo '<script>
                if (confirm("A question with this text already exists. Do you want to save it anyway?")) {
                    document.getElementById("question_form").submit();
                }
            </script>';
        } else {
            // Insert the question into the database
            $query = "INSERT INTO questions (quiz_id, question_text, question_type, points) VALUES (?, ?, ?, ?)";
            $stmt = $pdoConnect->prepare($query);

            if ($stmt->execute([$quiz_id, $question_text, $question_type, $points])) {
                $question_id = $pdoConnect->lastInsertId();
                // Handle choices for multiple-choice questions
                if ($question_type === 'multiple_choice') {
                    $choices = $_POST['choices'] ?? [];
                    $correct_choices = $_POST['correct_choices'] ?? [];

                    foreach ($choices as $index => $choice) {
                        if (!empty($choice)) {
                            $is_correct = in_array($index, $correct_choices) ? 1 : 0;
                            $choice_query = "INSERT INTO choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)";
                            $choice_stmt = $pdoConnect->prepare($choice_query);
                            $choice_stmt->execute([$question_id, $choice, $is_correct]);
                        }
                    }
                }
                // Handle identification questions
                elseif ($question_type === 'identification') {
                    $correct_answer = trim($_POST['correct_answer_identification']); // Corrected reference
                    if (!empty($correct_answer)) {
                        // Save the correct answer in the new identification_answers table
                        $identification_query = "INSERT INTO identification_answers (question_id, answer_text) VALUES (?, ?)";
                        $identification_stmt = $pdoConnect->prepare($identification_query);
                        if ($identification_stmt->execute([$question_id, $correct_answer])) {
                        } else {
                            $error_message = "Error saving identification answer: " . implode(", ", $identification_stmt->errorInfo());
                        }
                    } else {
                        $error_message = "Correct answer is required.";
                    }
                }

                // Handle enumeration questions
                elseif ($question_type === 'enumeration') {
                    $answers = $_POST['answers'] ?? [];
                    foreach ($answers as $answer) {
                        if (!empty($answer)) {
                            $enumeration_query = "INSERT INTO answers (question_id, answer_text) VALUES (?, ?)";
                            $enumeration_stmt = $pdoConnect->prepare($enumeration_query);
                            $enumeration_stmt->execute([$question_id, $answer]);
                        }
                    }
                }
                // Handle true/false questions
                elseif ($question_type === 'true_false') {
                    $correct_answer = $_POST['correct_answer'] ?? null;
                    if (!empty($correct_answer)) {
                        // Save the true/false answer correctly
                        $true_false_query = "INSERT INTO answers (question_id, answer_text) VALUES (?, ?)";
                        $true_false_stmt = $pdoConnect->prepare($true_false_query);
                        if ($true_false_stmt->execute([$question_id, $correct_answer])) {
                        } else {
                            $error_message = "Error saving true/false answer: " . implode(", ", $true_false_stmt->errorInfo());
                        }
                    } else {
                        $error_message = "Correct answer is required.";
                    }
                }

                echo '<script>
                window.parent.location.reload(); // Refresh the parent page
            </script>';
                exit();

            } else {
                $error_message = "Error adding question: " . implode(", ", $stmt->errorInfo());
            }
        }
    }
}

// Fetch question types for the form
$question_types = ['multiple_choice', 'identification', 'enumeration', 'true_false'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Questions</title>
    <style>
        h1 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .choice,
        .answer {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .choice input[type="text"],
        .answer input[type="text"] {
            flex: 1;
            margin-right: 10px;
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .hidden {
            display: none;
        }

        .delete-button {
            background-color: #dc3545;
            margin-left: 10px;
        }

        .delete-button:hover {
            background-color: #c82333;
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
    </style>
</head>

<body>
    <h1>Add Questions to Quiz</h1>
    <form method="post" action="" id="question_form">
        <input type="hidden" name="quiz_id" value="<?php echo htmlspecialchars($quiz_id); ?>">

        <label for="question_text">Question:</label>
        <input type="text" name="question_text" id="question_text" required>

        <label for="question_type">Question Type:</label>
        <select name="question_type" id="question_type" required onchange="toggleOptions()">
            <?php foreach ($question_types as $type): ?>
                <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($type === $default_question_type) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $type))); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="points">Points:</label>
        <input type="number" name="points" id="points" required min="1">

        <!-- Multiple Choice Options -->
        <div id="multiple_choice_options"
            class="<?php echo ($default_question_type === 'multiple_choice') ? '' : 'hidden'; ?>">
            <label>Choices:</label>
            <div id="choice_container">
                <div class="choice">
                    <input type="text" name="choices[]" required>
                    <label>
                        <input type="checkbox" name="correct_choices[]" value="0" style="margin-left: 10px;">
                        Correct
                    </label>
                    <button type="button" class="delete-button" onclick="removeChoice(this)">Delete</button>
                </div>
            </div>
            <button type="button" onclick="addChoice()">Add Another Choice</button><br>
        </div>

        <!-- Identification Options -->
        <div id="identification_options" class="hidden">
            <label for="correct_answer_identification">Correct Answer:</label>
            <input type="text" name="correct_answer_identification" id="correct_answer_identification"
                placeholder="Answer" required>
        </div>

        <!-- Enumeration Options -->
        <div id="enumeration_options" class="hidden">
            <label>Answers:</label>
            <div id="answers_container">
                <div class="answer">
                    <input type="text" name="answers[]" placeholder="Answer 1" required>
                    <button type="button" class="delete-button" onclick="removeAnswer(this)">Delete</button>
                </div>
            </div>
            <button type="button" onclick="addAnswer()">Add Another Answer</button>
        </div>

        <!-- True/False Options -->
        <div id="true_false_options" class="hidden">
            <label for="correct_answer_true_false">Correct Answer:</label>
            <select name="correct_answer" id="correct_answer_true_false" required>
                <option value="True">True</option>
                <option value="False">False</option>
            </select>
        </div>

        <br><button type="submit">Add Question</button>
    </form>

    <?php if ($error_message): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <script>
        // Add multiple choice dynamically
        function addChoice() {
            var container = document.getElementById('choice_container');
            var index = container.querySelectorAll('input[name="choices[]"]').length;

            if (index < 26) {
                var div = document.createElement('div');
                div.className = 'choice';
                div.innerHTML = `
                <input type="text" name="choices[]" required>
                <label>
                    <input type="checkbox" name="correct_choices[]" value="${index}" style="margin-left: 10px;">
                    Correct
                </label>
                <button type="button" class="delete-button" onclick="removeChoice(this)">Delete</button>
            `;
                container.appendChild(div);
            } else {
                alert('You can only add up to 26 choices.');
            }
        }

        // Add enumeration answer dynamically
        function addAnswer() {
            var container = document.getElementById('answers_container');
            var index = container.querySelectorAll('input[name="answers[]"]').length;

            if (index < 26) {
                var div = document.createElement('div');
                div.className = 'answer';
                div.innerHTML = `
                <input type="text" name="answers[]" placeholder="Answer ${index + 1}" required>
                <button type="button" class="delete-button" onclick="removeAnswer(this)">Delete</button>
            `;
                container.appendChild(div);
            } else {
                alert('You can only add up to 26 answers.');
            }
        }

        // Remove choice dynamically
        function removeChoice(button) {
            var choiceDiv = button.parentElement;
            choiceDiv.parentElement.removeChild(choiceDiv);
        }

        // Remove answer dynamically
        function removeAnswer(button) {
            var answerDiv = button.parentElement;
            answerDiv.parentElement.removeChild(answerDiv);
        }

        // Toggle visibility of options based on selected question type
        function toggleOptions() {
            var questionType = document.getElementById('question_type').value;

            // Reset all options to hidden
            document.getElementById('multiple_choice_options').classList.add('hidden');
            document.getElementById('identification_options').classList.add('hidden');
            document.getElementById('enumeration_options').classList.add('hidden');
            document.getElementById('true_false_options').classList.add('hidden');

            // Show the appropriate options
            if (questionType === 'multiple_choice') {
                document.getElementById('multiple_choice_options').classList.remove('hidden');
            } else if (questionType === 'identification') {
                document.getElementById('identification_options').classList.remove('hidden');
            } else if (questionType === 'enumeration') {
                document.getElementById('enumeration_options').classList.remove('hidden');
            } else if (questionType === 'true_false') {
                document.getElementById('true_false_options').classList.remove('hidden');
            }

            // Remove 'required' attribute from all options initially
            document.querySelectorAll('#multiple_choice_options input[required], #identification_options input[required], #enumeration_options input[required]').forEach(function (input) {
                input.removeAttribute('required');
            });

            // Add 'required' attribute based on selected question type
            if (questionType === 'multiple_choice') {
                document.querySelectorAll('#multiple_choice_options input[type="text"]').forEach(function (input) {
                    input.setAttribute('required', 'required');
                });
            } else if (questionType === 'identification') {
                document.getElementById('correct_answer_identification').setAttribute('required', 'required');
            } else if (questionType === 'enumeration') {
                document.querySelectorAll('#answers_container input[required]').forEach(function (input) {
                    input.setAttribute('required', 'required');
                });
            }
        }

        // Validate form submission
        document.getElementById('question_form').addEventListener('submit', function (event) {
            var questionType = document.getElementById('question_type').value;

            if (questionType === 'multiple_choice') {
                // Check if at least one correct answer is selected
                var correctChoices = document.querySelectorAll('input[name="correct_choices[]"]:checked');
                if (correctChoices.length === 0) {
                    alert('Please select at least one correct choice for multiple-choice questions.');
                    event.preventDefault(); // Prevent form submission
                    return false;
                }
            }

            // Allow form submission if validation passes
            return true;
        });

        // Initial call to set the default visibility
        toggleOptions();
    </script>

</body>

</html>