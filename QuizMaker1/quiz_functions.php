<?php
include 'dbcon.php';

// Function to check if the student exists in the database
function checkStudentExists($student_id)
{
    global $pdoConnect;
    $checkStudentQuery = "SELECT COUNT(*) FROM student WHERE student_id = ?";
    $checkStudentStmt = $pdoConnect->prepare($checkStudentQuery);
    $checkStudentStmt->execute([$student_id]);
    return $checkStudentStmt->fetchColumn() > 0;
}

// Function to fetch quiz details
function fetchQuizDetails($quiz_id)
{
    global $pdoConnect;
    $query = "
        SELECT quiz_name, start_time, end_time, time_limit, randomize_questions
        FROM quizzes
        WHERE id = ?
    ";
    $stmt = $pdoConnect->prepare($query);
    $stmt->execute([$quiz_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to fetch questions for a specific quiz
function fetchQuestions($quiz_id)
{
    global $pdoConnect;
    $questionsQuery = "
        SELECT q.id, q.question_text, q.question_type, q.points,
        (SELECT COUNT(*) FROM answers WHERE question_id = q.id) as expected_answers
        FROM questions q
        WHERE quiz_id = ?
    ";
    $questionsStmt = $pdoConnect->prepare($questionsQuery);
    $questionsStmt->execute([$quiz_id]);
    return $questionsStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to save student's answer
function saveStudentAnswer($quiz_id, $student_id, $question_id, $answerText)
{
    global $pdoConnect;

    // If the question is multiple choice and answerText is an array, convert it to a string
    if (is_array($answerText)) {
        $answerText = implode(', ', $answerText);
    }

    // Check if an answer already exists for this student, quiz, and question
    $checkQuery = "
        SELECT COUNT(*) FROM student_answers 
        WHERE quiz_id = ? AND student_id = ? AND question_id = ?
    ";
    $checkStmt = $pdoConnect->prepare($checkQuery);
    $checkStmt->execute([$quiz_id, $student_id, $question_id]);
    
    if ($checkStmt->fetchColumn() == 0) { // Only insert if no existing answer found
        // Insert into student_answers table
        $insertQuery = "
            INSERT INTO student_answers (quiz_id, student_id, question_id, answer_text)
            VALUES (?, ?, ?, ?)
        ";
        $insertStmt = $pdoConnect->prepare($insertQuery);
        $insertStmt->execute([$quiz_id, $student_id, $question_id, $answerText]);
    }
}

// Function to calculate the score based on the student's answer
function calculateScore($question, $answer_id, $answerText)
{
    global $pdoConnect;
    $score = 0;

    if ($question['question_type'] === 'multiple_choice') {
        // Fetch correct answers
        $correctAnswerQuery = "
            SELECT choice_text FROM choices WHERE question_id = ? AND is_correct = 1
        ";
        $correctAnswerStmt = $pdoConnect->prepare($correctAnswerQuery);
        $correctAnswerStmt->execute([$question['id']]);
        $correctAnswers = $correctAnswerStmt->fetchAll(PDO::FETCH_COLUMN);

        // Process student's multiple-choice answers
        $providedAnswers = array_map('trim', explode(',', trim($answerText))); // Ensure no extra spaces

        $correctCount = 0;
        foreach ($providedAnswers as $providedAnswer) {
            if (in_array($providedAnswer, $correctAnswers)) {
                $correctCount++;
            }
        }

        // If no correct answers are selected, give 0 points
        if ($correctCount === 0) {
            return 0;
        }

        // Calculate score based on correct answers selected
        $score += ($question['points'] / count($correctAnswers)) * $correctCount; // Partial credit for each correct answer

    } elseif ($question['question_type'] === 'true_false') {
        // Fetch the correct answer for the True/False question
        $correctAnswerQuery = "
            SELECT answer_text FROM answers WHERE question_id = ?
        ";
        $correctAnswerStmt = $pdoConnect->prepare($correctAnswerQuery);
        $correctAnswerStmt->execute([$question['id']]);
        $correctAnswer = $correctAnswerStmt->fetchColumn();

        // Compare the student's answer with the correct answer
        if (trim(strtolower($answerText)) === trim(strtolower($correctAnswer))) {
            $score += $question['points'];
        }
    } elseif ($question['question_type'] === 'identification') {
        // Fetch correct answer for identification
        $correctAnswerQuery = "
            SELECT answer_text FROM identification_answers WHERE question_id = ?
        ";
        $correctAnswerStmt = $pdoConnect->prepare($correctAnswerQuery);
        $correctAnswerStmt->execute([$question['id']]);
        $correctAnswer = $correctAnswerStmt->fetchColumn();

        if (trim(strtolower($answerText)) === trim(strtolower($correctAnswer))) {
            $score += $question['points'];
        }
    } elseif ($question['question_type'] === 'enumeration') {
        // Fetch correct answers
        $correctAnswerQuery = "
            SELECT answer_text FROM answers WHERE question_id = ?
        ";
        $correctAnswerStmt = $pdoConnect->prepare($correctAnswerQuery);
        $correctAnswerStmt->execute([$question['id']]);
        $correctAnswers = $correctAnswerStmt->fetchAll(PDO::FETCH_COLUMN);

        // Normalize correct answers
        $correctAnswers = array_map('trim', $correctAnswers);

        // Normalize provided answers
        $providedAnswers = array_filter(array_map('trim', explode(',', $answerText))); // Ensure we filter out empty values
        $uniqueProvidedAnswers = array_unique($providedAnswers); // Remove duplicate answers

        // Count correct answers
        $correctCount = 0;
        foreach ($uniqueProvidedAnswers as $providedAnswer) {
            if (in_array($providedAnswer, $correctAnswers)) {
                $correctCount++;
            }
        }

        // Score calculation
        if ($correctCount > 0) {
            $score += ($question['points'] / $question['expected_answers']) * $correctCount; // Partial credit
        }
    }
    return $score;
}

// Function to save the quiz result
function saveQuizResult($quiz_id, $student_id, $score)
{
    global $pdoConnect;
    $resultInsertQuery = "
        INSERT INTO quiz_results (quiz_id, student_id, score)
        VALUES (?, ?, ?)
    ";
    $resultInsertStmt = $pdoConnect->prepare($resultInsertQuery);
    $resultInsertStmt->execute([$quiz_id, $student_id, $score]);
}

// Function to get the correct answer for comparison
function getCorrectAnswer($question_id, $question_type)
{
    global $pdoConnect;

    if ($question_type === 'multiple_choice') {
        $query = "
            SELECT choice_text FROM choices 
            WHERE question_id = ? AND is_correct = 1
        ";
    } elseif ($question_type === 'identification') {
        $query = "
            SELECT answer_text FROM identification_answers 
            WHERE question_id = ?
        ";
    } elseif ($question_type === 'enumeration') {
        $query = "
            SELECT answer_text FROM answers 
            WHERE question_id = ?
        ";
    } elseif ($question_type === 'true_false') {
        $query = "
            SELECT answer_text FROM answers 
            WHERE question_id = ?
        ";
    } else {
        return []; // Return an empty array for unknown question types
    }

    $stmt = $pdoConnect->prepare($query);
    $stmt->execute([$question_id]);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch all correct answers

    return $results ?: []; // Return an empty array if no results
}
?>