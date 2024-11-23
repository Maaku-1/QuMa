<?php
include 'dbcon.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input fields
    $subject_name = trim($_POST['subject_name']);
    $program = trim($_POST['program']);
    $year = intval($_POST['year']);
    $section = trim($_POST['section']);

    // Check if the subject name is provided
    if (empty($subject_name) || empty($program) || empty($section)) {
        echo "<script>alert('All fields are required.');</script>";
    } else {
        // Check for duplicate subject name
        $checkDuplicateStmt = $pdoConnect->prepare("SELECT COUNT(*) FROM subjects WHERE subject_name = :subject_name");
        $checkDuplicateStmt->bindParam(':subject_name', $subject_name);
        $checkDuplicateStmt->execute();
        $duplicateCount = $checkDuplicateStmt->fetchColumn();

        if ($duplicateCount > 0) {
            echo "<script>alert('Subject Name already exists. Please choose a different name.');</script>";
        } else {
            // Generate a 6-digit code
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6)); // Generating random code
            
            try {
                // Insert into the subjects table
                $stmt = $pdoConnect->prepare("INSERT INTO subjects (subject_name, code, department, year, section, admin_id) VALUES (:subject_name, :code, :department, :year, :section, :admin_id)");
                $stmt->bindParam(':subject_name', $subject_name);
                $stmt->bindParam(':code', $code);
                $stmt->bindParam(':department', $program);
                $stmt->bindParam(':year', $year);
                $stmt->bindParam(':section', $section);
                $stmt->bindParam(':admin_id', $_SESSION['user_id']); // Assuming admin_id is stored in session
                $stmt->execute();

                // Redirect to admindashboard.php on success
                echo "<script>window.location.href='admindashboard.php';</script>";
            } catch (PDOException $e) {
                echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject</title>
</head>

<body>
    <!-- CREATE SUBJECT MODAL -->
    <div class="modal fade" id="create-subject-modal" data-bs-keyboard="false" aria-labelledby="Create Subject"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content create-subject-modal-content">
                <div class="modal-header create-subject-modal-header">
                    <span class="create-subject-header font-rubik-medium">Create Subject</span>
                </div>
                <form class="modal-body create-subject-modal-body" id="subject-form" method="POST" action="">
                    <div class="form-floating create-subject-name-wrapper">
                        <input type="text" class="form-control font-rubik" name="subject_name" id="create-subject-name"
                            placeholder="Subject Name" required>
                        <label for="create-subject-name" class="font-rubik">Subject Name</label>
                        <div class="invalid-feedback">
                            Subject Name is Required
                        </div>
                    </div>
                    <div class="form-floating create-subject-program-wrapper">
                        <select class="form-select font-rubik" name="program" id="create-subject-program"
                            aria-label="Program" required>
                            <option value="" disabled selected>Select Program</option>
                            <option value="BSCE">BSCE</option>
                            <option value="BSBA">BSBA</option>
                            <option value="BSE">BSE</option>
                            <option value="BEED">BEED</option>
                            <option value="BEED-GE">BEED-GE</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSP">BSP</option>
                            <option value="BSTM">BSTM</option>
                        </select>
                        <label for="create-subject-program" class="font-rubik">Program</label>
                        <div class="invalid-feedback">
                            Program is Required
                        </div>
                    </div>
                    <select class="form-select create-subject-year font-rubik" name="year" id="create-subject-year"
                        aria-label="year" required>
                        <option value="1" selected>1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                        <option value="5">5th Year</option>
                    </select>
                    <div class="form-floating create-subject-section-wrapper">
                        <input type="text" class="form-control font-rubik" name="section" id="create-subject-section"
                            placeholder="Section" required>
                        <label for="create-subject-section" class="font-rubik">Section</label>
                    </div>
                </form>
                <div class="modal-footer create-subject-modal-footer">
                    <div class="create-subject-buttons">
                        <button type="button" class="btn create-subject-cancel font-rubik"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn create-subject-confirm font-rubik" id="confirm-button">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to handle the Confirm button click
        document.getElementById('confirm-button').addEventListener('click', function() {
            // Check if the form is valid
            var form = document.getElementById('subject-form');
            if (form.checkValidity()) {
                form.submit(); // Submit the form if valid
            } else {
                alert('Please fill out all required fields.'); // Alert if the form is not valid
            }
        });
    </script>
</body>

</html>
