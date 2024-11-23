<?php
include 'dbcon.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Get question_id and quiz_id from the URL
$question_id = $_GET['question_id'];
$quiz_id = $_GET['quiz_id'];
$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : ''; // Get subject_id from the URL or default to an empty string

// Fetch the question from the database
$stmt = $pdoConnect->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    die("Question not found.");
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated question data from the form
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type'];
    $points = $_POST['points']; // Get points from the form
    $is_success = true;

    try {
        // Begin transaction
        $pdoConnect->beginTransaction();

        // Update the question
        $updateQuestionStmt = $pdoConnect->prepare("UPDATE questions SET question_text = ?, question_type = ?, points = ? WHERE id = ?");
        $updateQuestionStmt->execute([$question_text, $question_type, $points, $question_id]);

        // Clear previous answers based on the question type
        if ($question_type === 'multiple_choice') {
            // Check if there is at least one correct answer
            if (empty($_POST['is_correct'])) {
                throw new Exception("You must select at least one correct answer for multiple choice questions.");
            }

            // Delete existing choices for this question
            $pdoConnect->prepare("DELETE FROM choices WHERE question_id = ?")->execute([$question_id]);

            // Insert new choices
            foreach ($_POST['choice_texts'] as $index => $choice_text) {
                // If the checkbox is checked, set is_correct to 1; otherwise, 0
                $choice_id = $_POST['choice_ids'][$index];
                $is_correct = isset($_POST['is_correct']) && in_array($choice_id, $_POST['is_correct']) ? 1 : 0;

                // Insert each choice with the correct or incorrect flag
                $insertChoiceStmt = $pdoConnect->prepare("INSERT INTO choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)");
                $insertChoiceStmt->execute([$question_id, $choice_text, $is_correct]);
            }
        } elseif ($question_type === 'identification') {
            $pdoConnect->prepare("DELETE FROM identification_answers WHERE question_id = ?")->execute([$question_id]);

            // Insert the identification answer
            $correct_answer = $_POST['correct_answer'];
            $insertIdentificationStmt = $pdoConnect->prepare("INSERT INTO identification_answers (question_id, answer_text) VALUES (?, ?)");
            $insertIdentificationStmt->execute([$question_id, $correct_answer]);
        } elseif ($question_type === 'enumeration') {
            $pdoConnect->prepare("DELETE FROM answers WHERE question_id = ?")->execute([$question_id]);

            // Insert enumeration answers
            foreach ($_POST['enumeration_answers'] as $answer_text) {
                $insertEnumerationStmt = $pdoConnect->prepare("INSERT INTO answers (question_id, answer_text) VALUES (?, ?)");
                $insertEnumerationStmt->execute([$question_id, $answer_text]);
            }
        } elseif ($question_type === 'true_false') {
            $pdoConnect->prepare("DELETE FROM answers WHERE question_id = ?")->execute([$question_id]);

            // Get the selected true/false answer
            $correct_answer = $_POST['true_false_answer'] ?? null;

            // Ensure that the correct_answer is either 'true' or 'false'
            if ($correct_answer === null) {
                throw new Exception("You must select either true or false.");
            }

            // Insert the true/false answer based on the answer text
            $insertTrueFalseStmt = $pdoConnect->prepare("INSERT INTO answers (question_id, answer_text) VALUES (?, ?)");
            $insertTrueFalseStmt->execute([$question_id, $correct_answer]);
        }

        // Commit the transaction
        $pdoConnect->commit();

        $success_message = "Question updated successfully!";
        if ($stmt->execute($params)) {
            // Success message, replace with your success handling code
            echo '<script>
                    window.parent.postMessage("questionAdded", "*");
                  </script>';
            exit();
        } else {
            // Handle the error case
            echo '<script>
                    window.parent.postMessage("questionError", "*");
                  </script>';
            exit();
        }

    } catch (Exception $e) {
        // Rollback the transaction on error
        $pdoConnect->rollBack();
        $is_success = false;
        $error_message = "Error updating question: " . $e->getMessage();
    }
}

// Fetch answers based on question type
$answers = [];
if ($question['question_type'] === 'multiple_choice') {
    $stmt = $pdoConnect->prepare("SELECT * FROM choices WHERE question_id = ?");
} elseif ($question['question_type'] === 'identification') {
    $stmt = $pdoConnect->prepare("SELECT * FROM identification_answers WHERE question_id = ?");
} elseif ($question['question_type'] === 'enumeration') {
    $stmt = $pdoConnect->prepare("SELECT * FROM answers WHERE question_id = ?");
} elseif ($question['question_type'] === 'true_false') {
    $stmt = $pdoConnect->prepare("SELECT * FROM answers WHERE question_id = ? LIMIT 1");
}

$stmt->execute([$question_id]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Question</title>
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

        .hidden {
            display: none;
            /* or use visibility: hidden; */
        }
    </style>
    <script>
        const maxChoices = 25; // Maximum number of choices for multiple choice
        const maxEnumeration = 25; // Maximum number of enumeration answers

        // Function to toggle visibility and required fields based on question type
        function toggleOptions() {
            const questionType = document.getElementById('question_type').value;

            // Hide all options initially
            document.getElementById('multiple_choice_options').classList.add('hidden');
            document.getElementById('identification_options').classList.add('hidden');
            document.getElementById('enumeration_options').classList.add('hidden');
            document.getElementById('true_false_options').classList.add('hidden');

            // Remove "required" attribute from all inputs
            document.querySelectorAll('.multiple-choice-input, .identification-input, .enumeration-input, .true-false-input').forEach(input => {
                input.removeAttribute('required');
            });

            // Show the appropriate section and add the "required" attribute
            if (questionType === 'multiple_choice') {
                document.getElementById('multiple_choice_options').classList.remove('hidden');
                document.querySelectorAll('.multiple-choice-input').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            } else if (questionType === 'identification') {
                document.getElementById('identification_options').classList.remove('hidden');
                document.querySelector('.identification-input').setAttribute('required', 'required');
            } else if (questionType === 'enumeration') {
                document.getElementById('enumeration_options').classList.remove('hidden');
                document.querySelectorAll('.enumeration-input').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            } else if (questionType === 'true_false') {
                document.getElementById('true_false_options').classList.remove('hidden');
            }
        }

        // Function to add a new choice input for multiple choice questions
        function addChoice() {
            const choicesContainer = document.getElementById('choices_container');
            const choiceCount = choicesContainer.querySelectorAll('.choice').length;

            if (choiceCount < maxChoices) {
                const choiceDiv = document.createElement('div');
                choiceDiv.className = 'choice';
                choiceDiv.innerHTML = `
                    <input type="text" name="choice_texts[]" class="multiple-choice-input" placeholder="Choice text" required>
                    <input type="checkbox" name="is_correct[]" value="${choiceCount + 1}"> Correct
                    <button type="button" class="delete-button" onclick="removeChoice(this)">Delete</button>
                `;
                choicesContainer.appendChild(choiceDiv);
            } else {
                alert('Maximum number of choices reached.');
            }
        }

        // Function to remove a choice input for multiple choice questions
        function removeChoice(button) {
            const choiceDiv = button.parentElement;
            choiceDiv.remove();
        }

        // Function to add a new enumeration answer
        function addEnumerationAnswer() {
            const enumerationContainer = document.getElementById('enumeration_answers_container');
            const answerCount = enumerationContainer.querySelectorAll('.answer').length;

            if (answerCount < maxEnumeration) {
                const answerDiv = document.createElement('div');
                answerDiv.className = 'answer';
                answerDiv.innerHTML = `
                    <input type="text" name="enumeration_answers[]" class="enumeration-input" placeholder="Answer text" required>
                    <button type="button" class="delete-button" onclick="removeEnumerationAnswer(this)">Delete</button>
                `;
                enumerationContainer.appendChild(answerDiv);
            } else {
                alert('Maximum number of enumeration answers reached.');
            }
        }

        // Function to remove an enumeration answer
        function removeEnumerationAnswer(button) {
            const answerDiv = button.parentElement;
            answerDiv.remove();
        }
    </script>
</head>

<body onload="toggleOptions()">

    <h1>Edit Question</h1>

    <?php if (isset($is_success) && !$is_success): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="question_text">Question Text:</label>
        <input type="text" id="question_text" name="question_text"
            value="<?php echo htmlspecialchars($question['question_text']); ?>" required>

        <div class="visible-label">
            <?php echo 'Question Type: ' . htmlspecialchars($question['question_type']); ?>
        </div>

        <select id="question_type" class="hidden" name="question_type" onchange="toggleOptions()">
            <option value="multiple_choice" <?php if ($question['question_type'] === 'multiple_choice')
                echo 'selected'; ?>>Multiple Choice</option>
            <option value="identification" <?php if ($question['question_type'] === 'identification')
                echo 'selected'; ?>>
                Identification</option>
            <option value="enumeration" <?php if ($question['question_type'] === 'enumeration')
                echo 'selected'; ?>>
                Enumeration</option>
            <option value="true_false" <?php if ($question['question_type'] === 'true_false')
                echo 'selected'; ?>>
                True/False</option>
        </select>

        <!-- Points -->
        <label for="points">Points:</label>
        <input type="number" id="points" name="points" value="<?php echo htmlspecialchars($question['points']); ?>"
            required>

        <!-- Multiple Choice Options -->
        <div id="multiple_choice_options" class="hidden">
            <h3>Choices:</h3>
            <div id="choices_container">
                <?php foreach ($answers as $index => $choice): ?>
                    <div class="choice">
                        <input type="hidden" name="choice_ids[]" value="<?php echo $choice['id']; ?>">
                        <input type="text" name="choice_texts[]" class="multiple-choice-input"
                            value="<?php echo htmlspecialchars($choice['choice_text']); ?>" placeholder="Choice text"
                            required>
                        <input type="checkbox" name="is_correct[]" value="<?php echo $choice['id']; ?>" <?php echo $choice['is_correct'] ? 'checked' : ''; ?>> Correct
                        <button type="button" class="delete-button" onclick="removeChoice(this)">Delete</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addChoice()">Add Choice</button>
        </div>

        <!-- Identification Options -->
        <div id="identification_options" class="hidden">
            <h3>Correct Answer:</h3>
            <input type="text" name="correct_answer" class="identification-input"
                value="<?php echo htmlspecialchars($answers[0]['answer_text'] ?? ''); ?>" required>
        </div>

        <!-- Enumeration Options -->
        <div id="enumeration_options" class="hidden">
            <h3>Answers:</h3>
            <div id="enumeration_answers_container">
                <?php foreach ($answers as $answer): ?>
                    <div class="answer">
                        <input type="text" name="enumeration_answers[]" class="enumeration-input"
                            value="<?php echo htmlspecialchars($answer['answer_text']); ?>" placeholder="Answer text"
                            required>
                        <button type="button" class="delete-button" onclick="removeEnumerationAnswer(this)">Delete</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addEnumerationAnswer()">Add Answer</button>
        </div>

        <!-- True/False Options -->
        <div id="true_false_options" class="hidden">
            <h3>Correct Answer:</h3>
            <label>
                <input type="radio" name="true_false_answer" value="True" <?php if (isset($answers[0]) && $answers[0]['answer_text'] === 'True')
                    echo 'checked'; ?>> True
            </label>
            <label>
                <input type="radio" name="true_false_answer" value="False" <?php if (isset($answers[0]) && $answers[0]['answer_text'] === 'False')
                    echo 'checked'; ?>> False
            </label>
        </div>

        <button type="submit">Update Question</button>
    </form>

    <script>
        // Function to prevent interaction with the dropdown
        document.getElementById('question_type').addEventListener('mousedown', function (e) {
            e.preventDefault(); // Prevent the default action
        });
    </script>
</body>

</html>