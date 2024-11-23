<?php
// Include Composer's autoloader
require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

// Database connection details
require 'dbcon.php'; // Make sure to include your database connection here

$uploadSuccessful = false;
$error = '';

// Course and department abbreviation mapping
$courseAbbreviations = [
    'Bachelor of Science in Civil Engineering' => 'BSCE',
    'Bachelor of Science in Business Administration' => 'BSBA',
    'Bachelor of Science in Business Administration (Major in Marketing)' => 'BSBA-MKT',
    'Bachelor of Science in Entrepreneurship' => 'BSE',
    'Bachelor in Elementary Education' => 'BEED',
    'Bachelor in Elementary Education (Major in General Education)' => 'BEED-GE',
    'Bachelor of Science in Information Technology' => 'BSIT',
    'Bachelor of Science in Psychology' => 'BSP',
    'Bachelor of Science in Tourism Management' => 'BSTM'
];

// Department mapping
$departmentMapping = [
    'BSCE' => 'Civil Engineering',
    'BSBA' => 'Business Administration',
    'BSBA-MKT' => 'Business Administration',
    'BSE' => 'Entrepreneurship',
    'BEED' => 'Elementary Education',
    'BEED-GE' => 'Elementary Education',
    'BSIT' => 'Information Technology',
    'BSP' => 'Psychology',
    'BSTM' => 'Tourism Management'
];

// Initialize data array at the beginning
$data = [
    'student_no' => '',
    'name' => '',
    'course' => '', // This will hold the abbreviation
    'full_course' => '', // This will hold the full course name
    'year_level' => '',
    'lname' => '',
    'fname' => '',
    'mi' => '',
    'section' => '',
    'gender' => '',
    'department' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf_file'])) {
    // Get the uploaded file's info
    $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
    $fileSize = $_FILES['pdf_file']['size']; // Get the size of the uploaded file in bytes

    // Check if the file size is less than 500KB
    if ($fileSize > 500 * 1024) { // 500 KB in bytes
        $error = "The uploaded file exceeds the maximum size of 500 KB.";
    } else {
        // Initialize the parser
        $parser = new Parser();

        // Parse the PDF file directly from the temporary file
        $pdf = $parser->parseFile($fileTmpPath);

        // Extract the text from the PDF
        $text = $pdf->getText();

        // Split the text into lines
        $lines = preg_split('/\r\n|\n|\r/', $text);

        // Capture data directly from specific lines
        // Student No: Line 11
        if (isset($lines[10])) {
            $data['student_no'] = trim($lines[10]);
        }

        // Name: Line 12
        if (isset($lines[11])) {
            $data['name'] = trim($lines[11]);
            // Split name into lname, fname, and mi
            $nameParts = explode(",", $data['name']);
            if (count($nameParts) > 1) {
                $data['lname'] = ucwords(strtolower(trim($nameParts[0])));
                $firstAndMi = trim($nameParts[1]);
                $firstAndMiParts = explode(" ", $firstAndMi);

                $data['fname'] = ucwords(strtolower(implode(" ", array_slice($firstAndMiParts, 0, -1))));
                $data['mi'] = isset($firstAndMiParts[count($firstAndMiParts) - 1]) ? strtoupper(trim(trim($firstAndMiParts[count($firstAndMiParts) - 1], '.'))) : '';
            }
        }

        // Course: Line 13
        if (isset($lines[12])) {
            $data['full_course'] = trim($lines[12]);
            $data['course'] = $courseAbbreviations[$data['full_course']] ?? '';

            // Set department based on abbreviation
            if ($data['course']) {
                $data['department'] = $departmentMapping[$data['course']] ?? 'Unknown Department';
            } else {
                $error = "Course not recognized: " . htmlspecialchars($data['full_course']);
            }
        }

        // Year Level: Line 14
        if (isset($lines[13])) {
            $yearLevelText = trim($lines[13]);
            if (preg_match('/\d+/', $yearLevelText, $matches)) {
                $data['year_level'] = $matches[0];
            } else {
                $error = "Year level not recognized.";
            }
        }

        // Extract section from the schedule
        foreach ($lines as $line) {
            if (preg_match('/\s*LC\s+BSIT\s+\d+"?([A-Z])/', $line, $matches)) {
                $data['section'] = trim($matches[1]);
                break;
            }
        }

        // Validate if all fields were extracted successfully
        if (empty($data['student_no']) || empty($data['name']) || empty($data['course']) || empty($data['year_level'])) {
            $error = "Invalid file. Missing required information.";
        } else {
            // Check for duplicate student ID in the database
            $stmt = $pdoConnect->prepare("SELECT COUNT(*) FROM student WHERE student_id = :student_id");
            $stmt->execute(['student_id' => $data['student_no']]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $error = "{$data['student_no']} is already registered. Please check the uploaded file.";
            } else {
                $uploadSuccessful = true; // Flag to hide the form after successful upload
            }
        }
    }
}

// Handle account creation after successful upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_account'])) {
    $student_no = $_POST['student_no'];
    $lname = $_POST['lname'];
    $fname = $_POST['fname'];
    $mi = $_POST['mi'];
    $year_level = $_POST['year_level'];
    $section = $_POST['section'];
    $gender = $_POST['gender']; // Get gender from form
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department_data = $_POST['course'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match. Please try again.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert data into the database
        $stmt = $pdoConnect->prepare("INSERT INTO student (student_id, fname, mi, lname, section, year, gender, department, password) VALUES (:student_id, :fname, :mi, :lname, :section, :year, :gender, :department, :password)");

        // Execute the prepared statement
        if (
            $stmt->execute([
                'student_id' => $student_no,
                'fname' => $fname,
                'mi' => $mi,
                'lname' => $lname,
                'section' => $section,
                'year' => $year_level, // Assuming year_level maps to year
                'gender' => $gender, // Store gender
                'department' => $department_data, // Ensure department is correctly set
                'password' => $hashedPassword
            ])
        ) {
            // Account creation success message
            echo "<h2>\n\nAccount created successfully for $lname, $fname ($student_no)!</h2>";
        } else {
            $error = "Error creating account. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload PDF</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha384-DyZV8KJvsc2x8hE7GJ1R0PLT2Kg6UEdB+2lIj7lOZzwnZs6tpSfn6nI9OS6tE1xf" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        h1,
        h2 {
            text-align: center;
            color: #5a5a5a;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="file"],
        select {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        input[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            text-align: center;
        }

        .readonly {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }

        .password-error {
            color: red;
            font-size: 14px;
            text-align: center;
        }

        .tamper-warning {
            color: red;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }

        /* Back button style */
        .back-button {
            display: block;
            margin: 20px auto;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            width: fit-content;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
    </style>

    <script>
        function confirmAccountCreation() {
            const password = document.forms['studentForm']['password'].value;
            const confirmPassword = document.forms['studentForm']['confirm_password'].value;

            if (password !== confirmPassword) {
                document.getElementById('password-error').innerText = "Passwords do not match.";
                return false;
            }

            const tamperWarning =
                "Warning: Altering official documents, such as a Certificate of Registration from a university like Don Honorio Ventura State University (DHVSU) in Lubao, is illegal under Philippine law.\n" +
                "This action typically falls under provisions related to forgery, falsification of public documents, or the use of falsified documents, as stated in the Revised Penal Code of the Philippines.\n\n" +
                "Are you sure the following information is correct?\n\n" +
                "Student ID: " + document.forms['studentForm']['student_no'].value + "\n" +
                "Last Name: " + document.forms['studentForm']['lname'].value + "\n" +
                "First Name: " + document.forms['studentForm']['fname'].value + "\n" +
                "Middle Initial: " + document.forms['studentForm']['mi'].value + "\n" +
                "Course: " + document.forms['studentForm']['course'].value + "\n" +
                "Year Level: " + document.forms['studentForm']['year_level'].value + "\n" +
                "Section: " + document.forms['studentForm']['section'].value + "\n" +
                "Gender: " + document.forms['studentForm']['gender'].value;

            return confirm(tamperWarning);
        }

        function validateTerms() {
            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                alert("You must agree to the Terms and Services before uploading.");
                return false; // Prevent form submission
            }
            return true; // Allow form submission
        }
    </script>
</head>

<body>
    <h1>Upload PDF and Create Account</h1>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($uploadSuccessful): ?>
        <h2>Extracted Information:</h2>
        <form name="studentForm" method="POST" onsubmit="return confirmAccountCreation()">
            <label for="student_no">Student ID:</label>
            <input type="text" name="student_no" value="<?php echo htmlspecialchars($data['student_no']); ?>" readonly
                class="readonly"><br>

            <label for="lname">Last Name:</label>
            <input type="text" name="lname" value="<?php echo htmlspecialchars($data['lname']); ?>" readonly
                class="readonly"><br>

            <label for="fname">First Name:</label>
            <input type="text" name="fname" value="<?php echo htmlspecialchars($data['fname']); ?>" readonly
                class="readonly"><br>

            <label for="mi">Middle Initial:</label>
            <input type="text" name="mi" value="<?php echo htmlspecialchars($data['mi']); ?>" readonly class="readonly"><br>

            <label for="course">Course:</label>
            <input type="text" name="course" value="<?php echo htmlspecialchars($data['course']); ?>" readonly
                class="readonly"><br>

            <label for="year_level">Year Level:</label>
            <input type="text" name="year_level" value="<?php echo htmlspecialchars($data['year_level']); ?>" readonly
                class="readonly"><br>

            <label for="section">Section:</label>
            <input type="text" name="section" value="<?php echo htmlspecialchars($data['section']); ?>" readonly
                class="readonly"><br>

            <label for="gender">Gender:</label>
            <select name="gender" required>
                <option value="">Select...</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select><br>

            <label for="password">Password:</label>
            <input type="password" name="password" required><br>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required><br>

            <p id="password-error" class="password-error"></p>

            <input type="submit" name="create_account" value="Create Account">
        </form>

        <!-- Add a JavaScript function to redirect after successful account creation -->
        <script>
            redirectToHome();
        </script>

    <?php else: ?>
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateTerms()">
            <label for="pdf_file">Upload PDF:</label>
            <input type="file" name="pdf_file" accept=".pdf" required>
            <input type="submit" value="Upload">

            <label>
                <input type="checkbox" id="terms" name="terms" required>
                I have read and agree to the <a href="terms_and_services.html" target="_blank">Terms and Services</a>.
            </label>
        </form>


        <!-- Back button -->
        <a href="index.php" class="back-button">Back to Home</a>
    <?php endif; ?>
</body>

</html>