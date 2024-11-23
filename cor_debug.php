<?php
// Include Composer's autoloader
require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

// Initialize error variable
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf_file'])) {
    // Get the uploaded file's info
    $fileTmpPath = $_FILES['pdf_file']['tmp_name'];

    // Initialize the parser
    $parser = new Parser();

    // Parse the PDF file directly from the temporary file
    $pdf = $parser->parseFile($fileTmpPath);

    // Extract the text from the PDF
    $text = $pdf->getText();

    // Split the text into lines
    $lines = preg_split('/\r\n|\n|\r/', $text);

    // Initialize data array
    $data = [
        'student_no' => '',
        'name' => '',
        'course' => '',
        'year_level' => '',
        'lname' => '',
        'fname' => '',
        'mi' => '',
        'section' => '',
        'gender' => '',
        'department' => ''
    ];

    // Capture data directly from specific lines
    // Student No: Line 11
    if (isset($lines[10])) {
        $data['student_no'] = trim($lines[10]);
    }

    // Name: Line 12
    if (isset($lines[11])) {
        $data['name'] = trim($lines[11]);
        // Split name into lname, fname, and mi
        $nameParts = explode(",", $data['name']); // Assuming "Lastname, Firstname1 Firstname2 MiddleInitial"
        if (count($nameParts) > 1) {
            $data['lname'] = ucwords(strtolower(trim($nameParts[0]))); // Convert last name to lowercase then capitalize
            $firstAndMi = trim($nameParts[1]); // Get everything after the comma
            $firstAndMiParts = explode(" ", $firstAndMi); // Split by spaces

            // Assign the first name as everything except the last part
            $data['fname'] = ucwords(strtolower(implode(" ", array_slice($firstAndMiParts, 0, -1)))); // Convert to lowercase then capitalize
            // Assign the last part as the middle initial, ensure it's uppercase
            $data['mi'] = isset($firstAndMiParts[count($firstAndMiParts) - 1]) ? strtoupper(trim(trim($firstAndMiParts[count($firstAndMiParts) - 1], '.'))) : ''; // Capitalize middle initial
        }
    }


    // Course: Line 13
    if (isset($lines[12])) {
        $data['course'] = trim($lines[12]);
    }

    // Year Level: Line 14
    if (isset($lines[13])) {
        $yearLevelText = trim($lines[13]); // Get the full year level text
        // Use regex to extract the numeric part
        if (preg_match('/\d+/', $yearLevelText, $matches)) {
            $data['year_level'] = $matches[0]; // Assign only the numeric value found
        } else {
            $error = "Year level not recognized.";
        }
    }


    // Extract section from the schedule
    foreach ($lines as $line) {
        if (preg_match('/\s*LC\s+BSIT\s+\d+"?([A-Z])/', $line, $matches)) {
            $data['section'] = trim($matches[1]); // Capture only the letter (e.g., "B")
            break;
        }
    }

    // Validate if all fields were extracted successfully
    if (empty($data['student_no']) || empty($data['name']) || empty($data['course']) || empty($data['year_level'])) {
        $error = "Invalid file. Missing required information.";
    } else {
        // Convert full course name to abbreviation
        // Check if the department is set based on course abbreviation
        if (array_key_exists($data['course'], $courseAbbreviations)) {
            $data['course'] = $courseAbbreviations[$data['course']];
            // Set department based on course abbreviation
            $data['department'] = $departmentMapping[$data['course']] ?? 'Unknown Department';
        } else {
            $error = "Course not recognized.";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        input[type="file"] {
            margin-bottom: 10px;
        }

        button {
            background: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #4cae4c;
        }

        .error {
            color: red;
        }

        .back-button {
            background: #d9534f;
        }

        .back-button:hover {
            background: #c9302c;
        }
    </style>
</head>

<body>
    <h1>Upload PDF File for Debugging</h1>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form id="uploadForm" action="cor_debug.php" method="post" enctype="multipart/form-data">
        <input type="file" name="pdf_file" accept=".pdf" required>
        <button type="submit">Upload</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf_file']) && !$error): ?>
        <h2>Extracted Information</h2>
        <p><strong>Student No:</strong> <?= htmlspecialchars($data['student_no']) ?></p>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($data['lname']) ?></p>
        <p><strong>First Name:</strong> <?= htmlspecialchars($data['fname']) ?></p>
        <p><strong>Middle Initial:</strong> <?= htmlspecialchars($data['mi']) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($data['course']) ?></p>
        <p><strong>Year Level:</strong> <?= htmlspecialchars($data['year_level']) ?></p>
        <p><strong>Section:</strong> <?= htmlspecialchars($data['section']) ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($data['department']) ?></p>
    <?php endif; ?>

    <button onclick="window.location.href='index.php';" class="back-button">Back</button>
</body>

</html>