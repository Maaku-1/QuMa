<?php
include 'dbcon.php';

// Check if the student is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['user_id']; // Use the user ID from the session

// Fetch subjects the student is enrolled in along with the admin details
$query = "
    SELECT s.id, s.subject_name, s.department, s.year, s.section,
           a.fname AS admin_fname, a.mi AS admin_mi, a.lname AS admin_lname
    FROM subjects s
    JOIN student_subjects ss ON s.id = ss.subject_id
    JOIN admin a ON s.admin_id = a.admin_id
    WHERE ss.student_id = ?
";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$student_id]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Enrolled Subjects</title>
    <link rel="stylesheet" href="source/css/bsf-admin-subject-view.css">
    <link rel="stylesheet" href="source/css/admin-subject-view.css">
    <link rel="stylesheet" href="source/css/fonts.css">
    <link rel="icon" type="image/x-icon" href="assets/pictures/QM Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="container mt-5">
        <h1 class="mb-4">Enrolled Subjects</h1>

        <?php if (count($subjects) > 0): ?>
            <div class="row">
                <?php foreach ($subjects as $subject): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card subject-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">
                                            <a href="subject_details.php?subject_id=<?php echo htmlspecialchars($subject['id']); ?>" class="subject-name">
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text"><?php echo htmlspecialchars($subject['department'] . " - " . $subject['year'] . " " . $subject['section']); ?></p>
                                        <p class="card-text"><?php echo htmlspecialchars($subject['admin_fname'] . ' ' . $subject['admin_mi'] . '. ' . $subject['admin_lname']); ?></p>
                                    </div>
                                    <div class="more-options-wrapper">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">You are not enrolled in any subjects.</p>
        <?php endif; ?>

    </div>

    <!-- Modal for Removing Subject -->
    <div class="modal fade" id="remove-subject-modal" tabindex="-1" aria-labelledby="remove-subject-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="remove-subject-modalLabel">Remove Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to remove this subject from your enrolled list?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Remove</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Functions -->
    <script src="source/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="source/js/inputPreventE.js"></script>
    <script src="source/js/clearModalFIelds.js"></script>
    <script src="source/js/toggleSidebar.js"></script>
    <script src="source/js/profilePicture.js"></script>
    <script src="source/js/checkSubjectQuiz.js"></script>
    <script src="source/js/editProfile.js"></script>
    <script src="source/js/sidebarProfileCenter.js"></script>
    <script src="source/js/syncSidebar.js"></script>
    <script src="source/js/stickyBackground.js"></script>
    <script src="source/js/tablePagination.js"></script>
</body>
</html>
