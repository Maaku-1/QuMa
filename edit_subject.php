<?php
include 'dbcon.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Check if subject_id is provided
if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    die("Invalid subject ID.");
}

$subject_id = $_GET['subject_id'];
$admin_id = $_SESSION['user_id']; // Get the admin's ID from the session

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $subject_name = $_POST['subject_name'];
    $department = $_POST['department'];
    $year = $_POST['year'];
    $section = $_POST['section'];

    // Validate inputs
    if (empty($subject_name) || empty($year) || empty($section)) {
        echo "Subject name, year, and section are required.";
    } else {
        // Prepare SQL statement to update data
        $query = "UPDATE subjects 
                  SET subject_name = ?, department = ?, year = ?, section = ? 
                  WHERE id = ? AND admin_id = ?";
        $stmt = $pdoConnect->prepare($query);
        
        if ($stmt->execute([$subject_name, $department, $year, $section, $subject_id, $admin_id])) {
            echo "Subject updated successfully.";
        } else {
            echo "Error updating subject.";
        }
    }
}

// Fetch subject details for form pre-fill
$query = "
    SELECT subject_name, department, year, section
    FROM subjects
    WHERE id = ? AND admin_id = ?
";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$subject_id, $admin_id]);
$subject = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subject) {
    die("Subject not found or you don't have permission to edit it.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject</title>
</head>
<body>
    <h1>Edit Subject</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?subject_id=' . $subject_id; ?>">
        <label for="subject_name">Subject Name:</label>
        <input type="text" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required><br><br>
        
        <label for="department">Department:</label>
        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($subject['department']); ?>"><br><br>

        <label for="year">Year:</label>
        <input type="number" id="year" name="year" value="<?php echo htmlspecialchars($subject['year']); ?>" required><br><br>

        <label for="section">Section:</label>
        <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($subject['section']); ?>" required><br><br>

        <button type="submit">Update Subject</button>
    </form>
    <a href="admin_subject_details.php?subject_id=<?php echo $subject_id; ?>">Back to Subject Details</a><br>
    <a href="admindashboard.php">Back to Admin Dashboard</a>
</body>
</html>
