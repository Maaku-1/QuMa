<?php
include 'dbcon.php';
// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$admin_id = $_SESSION['user_id']; // Get the admin ID from the session

// Fetch subjects created by this admin and join with the admin table to get teacher's name and department
$query = "
    SELECT s.id, s.subject_name, s.department, s.year, s.section, s.code,
           CONCAT(a.lname, ', ', a.fname, IF(a.mi IS NOT NULL, CONCAT(' ', a.mi), '')) AS teacher_name
    FROM subjects s
    JOIN admin a ON s.admin_id = a.admin_id
    WHERE s.admin_id = ?
";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$admin_id]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/pictures/QM Logo.png">
    <style>
        /* Add your styles here if needed */
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="row">
        <?php if (count($subjects) > 0): ?>
            <?php foreach ($subjects as $subject): ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-template" style="width: 350px; height: 300px;">
                        <div class="card-body subject-card-body">
                            <div class="more-options-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" class="more-options-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" viewBox="0 -960 960 960">
                                    <path d="M480-160q-33 0-56.5-23.5T400-240q0-33 23.5-56.5T480-320q33 0 56.5 23.5T560-240q0 33-23.5 56.5T480-160Zm0-240q-33 0-56.5-23.5T400-480q0-33 23.5-56.5T480-560q33 0 56.5 23.5T560-480q0 33-23.5 56.5T480-400Zm0-240q-33 0-56.5-23.5T400-720q0-33 23.5-56.5T480-800q33 0 56.5 23.5T560-720q0 33-23.5 56.5T480-640Z"/>
                                </svg>
                                <div class="dropdown-menu">
                                    <div class="subject-card-menu">
                                        <button type="button" class="view-subject-code font-poppins-regular" data-bs-toggle="modal" data-bs-target="#view-subject-code-modal" data-subject-id="<?php echo htmlspecialchars($subject['id']); ?>" data-subject-code="<?php echo htmlspecialchars($subject['code']); ?>">
                                            View Code
                                        </button>
                                        <button type="button" class="remove-subject font-poppins-regular" data-bs-toggle="modal" data-bs-target="#remove-subject-modal" data-subject-id="<?php echo htmlspecialchars($subject['id']); ?>">
                                            Remove Subject
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-details">
                                <span class="card-subject-name font-rubik-medium" id="subject-name">
                                    <a href="admin_subject_details.php?subject_id=<?php echo htmlspecialchars($subject['id']); ?>" class="card-subject-name-link"><?php echo htmlspecialchars($subject['subject_name']); ?></a>
                                </span>
                                <div class="card-subject-teacher-program-year-section-wrapper">
                                    <span class="card-subject-teacher font-rubik" id="name"><?php echo htmlspecialchars($subject['teacher_name']); ?></span>
                                    <span class="card-subject-program font-rubik" id="program"><?php echo htmlspecialchars($subject['department']); ?></span>
                                    <span class="card-subject-year-section font-rubik" id="year section"><?php echo htmlspecialchars($subject['year'] . ' - ' . $subject['section']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p>You have not created any subjects.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- VIEW SUBJECT CODE MODAL -->
<div class="modal fade" id="view-subject-code-modal" data-bs-keyboard="false" aria-labelledby="viewSubjectCodeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content view-subject-code-modal-content">
            <div class="modal-header view-subject-code-modal-header">
                <span class="view-subject-code-header font-rubik-medium">Subject Code</span>
            </div>
            <div class="modal-body view-subject-code-modal-body">
                <div class="form-floating view-subject-code-wrapper">
                    <input type="text" class="form-control font-rubik" id="view-subject-code" placeholder="Subject Code" readonly>
                    <label for="view-subject-code" class="font-rubik">Subject Code</label>
                </div>
            </div>
            <div class="modal-footer view-subject-code-modal-footer">
                <div class="view-subject-code-buttons">
                    <button type="button" class="btn view-subject-code-cancel font-rubik" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn view-subject-code-confirm font-rubik" id="copy-button">Copy Code</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('copy-button').addEventListener('click', function() {
        const subjectCodeInput = document.getElementById('view-subject-code');
        
        // Select the text in the input
        subjectCodeInput.select();
        subjectCodeInput.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text to the clipboard
        navigator.clipboard.writeText(subjectCodeInput.value).then(() => {
            alert('Subject code copied to clipboard!'); // Notify the user
        }).catch(err => {
            console.error('Failed to copy: ', err); // Handle any errors
        });
    });
</script>

<!-- REMOVE SUBJECT MODAL -->
<div class="modal fade" id="remove-subject-modal" data-bs-keyboard="false" aria-labelledby="removeSubjectLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content remove-subject-modal-content">
            <div class="modal-header remove-subject-modal-header">
                <span class="remove-subject-header font-rubik-medium">Remove Subject</span>
            </div>
            <div class="modal-body remove-subject-modal-body">
                <span class="remove-subject-text font-rubik">Are you sure? This subject will be removed.</span>
            </div>
            <div class="modal-footer remove-subject-modal-footer">
                <div class="remove-subject-buttons">
                    <button type="button" class="btn remove-subject-cancel font-rubik" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn remove-subject-confirm font-rubik">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script>
$(document).ready(function() {
    // Set up the remove subject button to confirm deletion
    $('.remove-subject-confirm').on('click', function() {
        var subjectId = $('.remove-subject').data('subject-id');
        if (subjectId) {
            // Redirect to the delete action
            window.location.href = "delete_subject.php?subject_id=" + subjectId;
        }
    });

    // Update subject id and code in the modal for view code
    $('.view-subject-code').on('click', function() {
        var subjectId = $(this).data('subject-id');
        var subjectCode = $(this).data('subject-code');
        $('#view-subject-code').val(subjectCode); // Set the subject code in the modal input
    });
});
</script>

</body>
</html>
