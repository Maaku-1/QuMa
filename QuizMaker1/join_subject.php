<?php
session_start();
include 'dbcon.php';

// Check if student is logged in and has the correct role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: index.php");
    exit;
}

// Get student ID from session
$student_id = $_SESSION['user_id'];

// Fetch student details
try {
    $query = "SELECT department, year, section FROM student WHERE student_id = :student_id";
    $stmt = $pdoConnect->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found.");
    }

    // Fetch subjects not yet joined by the student
    $query = "
        SELECT s.id, s.subject_name, s.department, s.year, s.section
        FROM subjects s
        LEFT JOIN student_subjects ss ON s.id = ss.subject_id AND ss.student_id = :student_id
        WHERE s.department = :department
          AND s.year = :year
          AND s.section = :section
          AND ss.subject_id IS NULL
    ";
    $stmt = $pdoConnect->prepare($query);
    $stmt->bindParam(':department', $student['department']);
    $stmt->bindParam(':year', $student['year'], PDO::PARAM_INT);
    $stmt->bindParam(':section', $student['section']);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $subjects_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submission (joining via subject code)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_code']) && isset($_POST['subject_id'])) {
        $subject_code = $_POST['subject_code'];
        $subject_id = $_POST['subject_id'];

        // Check if the subject exists and the student is not already enrolled
        $query = "
            SELECT s.id 
            FROM subjects s
            LEFT JOIN student_subjects ss ON s.id = ss.subject_id AND ss.student_id = :student_id
            WHERE s.id = :subject_id AND s.code = :subject_code AND ss.subject_id IS NULL
        ";
        $stmt = $pdoConnect->prepare($query);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        $stmt->bindParam(':subject_code', $subject_code);
        $stmt->execute();
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($subject) {
            // Enroll the student in the subject
            $query = "INSERT INTO student_subjects (student_id, subject_id) VALUES (:student_id, :subject_id)";
            $stmt = $pdoConnect->prepare($query);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':subject_id', $subject['id'], PDO::PARAM_INT);
            if ($stmt->execute()) {
                $message = "You have successfully joined the subject.";
            } else {
                $message = "Failed to join the subject.";
            }
        } else {
            $message = "Invalid subject code or you're already enrolled in this subject.";
        }
        // Refresh page to show message
        header("Location: join_subject.php?message=" . urlencode($message));
        exit;
    }

    if (isset($_GET['message'])) {
        $message = $_GET['message'];
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Subject</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="source/css/bsf-admin-subject-view.css">
    <link rel="stylesheet" href="source/css/admin-subject-view.css">
    <link rel="stylesheet" href="source/css/fonts.css">
    <link rel="icon" type="image/x-icon" href="assets/pictures/QM Logo.png">
    <title>QuMa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        h2 {
            margin-bottom: 20px;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
        }
        .subject {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #fafafa;
        }
        .subject h3 {
            margin: 0 0 5px;
        }
        .subject p {
            margin: 0 0 10px;
        }
        input[type="text"] {
            width: calc(100% - 90px);
            padding: 8px;
            margin-right: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin-top: 20px;
            color: #ff0000;
        }
        .back-link {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            margin-bottom: 15px;
            display: inline-block;
        }
        .back-link:hover {
            text-decoration: underline;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 15px;
            }
            .subject {
                padding: 15px;
            }
            input[type="text"] {
                width: calc(100% - 70px);
            }
            button {
                width: 100%;
                margin-top: 10px;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.5rem;
            }
            .subject h3 {
                font-size: 1.2rem;
            }
            input[type="text"], button {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

    <!-- <a href="studentdashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a> -->

    <div class="container">
        <h2>Available Subjects</h2>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if (!empty($subjects_result)): ?>
            <?php foreach ($subjects_result as $subject): ?>
                <div class="subject">
                    <h3><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                    <p><?php echo htmlspecialchars($subject['department']); ?> - Year <?php echo htmlspecialchars($subject['year']); ?> - Section <?php echo htmlspecialchars($subject['section']); ?></p>
                    <form action="join_subject.php" method="post">
                        <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                        <input type="text" name="subject_code" placeholder="Enter Subject Code" required>
                        <button type="submit">Join</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No subjects available for you to join.</p>
        <?php endif; ?>
    </div>

</body>
</html>
