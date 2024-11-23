<?php
include 'dbcon.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $student_id = $_POST['student_id'];
    $fname = $_POST['fname'];
    $mi = $_POST['mi'];
    $lname = $_POST['lname'];
    $section = $_POST['section'];
    $year = $_POST['year'];
    $gender = $_POST['gender'];
    $department = $_POST['department'];

    // Check if student_id already exists
    $query_check_duplicate = "SELECT id FROM student WHERE student_id = ?";
    $stmt_check_duplicate = $pdoConnect->prepare($query_check_duplicate); // Use $pdoConnect here
    $stmt_check_duplicate->execute([$student_id]);
    $result_check_duplicate = $stmt_check_duplicate->fetch(PDO::FETCH_ASSOC);
    if ($result_check_duplicate) {
        echo "Student ID already exists.";
        exit();
    }

    // Validate student ID length
    if (strlen($student_id) !== 10 || !ctype_digit($student_id)) {
        echo "Student ID must be 10 digits.";
        exit();
    }

    // Set password as "QM" + student_id
    $password = "QM" . $student_id;

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into student table
    $query = "INSERT INTO student (student_id, password, fname, mi, lname, section, year, gender, department) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdoConnect->prepare($query); // Use $pdoConnect here
    $stmt->execute([$student_id, $hashed_password, $fname, $mi, $lname, $section, $year, $gender, $department]);
    if ($stmt->rowCount() > 0) {
        echo "Student account created successfully";
    } else {
        echo "Error creating student account.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Student Account</title>
</head>

<body>
    <h1>Create Student Account</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="student_id">Student ID (10 digits):</label>
        <input type="number" id="student_id" name="student_id" required min="1000000000" max="9999999999"
            oninput="javascript: if (this.value.length > 10) this.value = this.value.slice(0, 10);">
        <br><br>

        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" required><br><br>

        <label for="mi">Middle Initial (Max 2 characters):</label>
        <input type="text" id="mi" name="mi" maxlength="2" required><br><br>

        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" required><br><br>

        <label for="section">Section (1 character):</label>
        <input type="text" id="section" name="section" maxlength="1" required><br><br>

        <label for="year">Year:</label>
        <input type="number" id="year" name="year" required><br><br>

        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select><br><br>

        <label for="department">Department:</label>
        <input type="text" id="department" name="department" required><br><br>

        <button type="submit">Create Account</button>
    </form>
    <a href="superadmindashboard.php">Back to Super Admin dasboard</a>
</body>

</html>
