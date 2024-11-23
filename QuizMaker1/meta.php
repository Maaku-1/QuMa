<?php
function getMetadata($filePath) {
    // Path to ExifTool
    $exifToolPath = "C:\\Users\\mark\\Downloads\\exiftool-12.97_64\\exiftool-12.97_64\\exiftool.exe";
    
    // Execute the command
    $command = escapeshellcmd($exifToolPath) . " -json " . escapeshellarg($filePath);
    $output = shell_exec($command);
    
    // Check for errors
    if ($output === null) {
        return "Error running ExifTool.";
    }
    
    // Decode the JSON output
    $metadata = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return "Error decoding JSON for file: " . json_last_error_msg();
    }

    return $metadata;
}

// Handle file uploads and comparison
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file1']) && isset($_FILES['file2'])) {
    $file1Path = $_FILES['file1']['tmp_name'];
    $file2Path = $_FILES['file2']['tmp_name'];

    $file1Name = $_FILES['file1']['name'];
    $file2Name = $_FILES['file2']['name'];

    $metadata1 = getMetadata($file1Path);
    $metadata2 = getMetadata($file2Path);

    // Compare metadata
    if (is_array($metadata1) && is_array($metadata2)) {
        // Flatten the metadata for easier comparison
        $flatMetadata1 = array_shift($metadata1); // Get the first element since it's an array
        $flatMetadata2 = array_shift($metadata2); // Get the first element since it's an array
    } else {
        $error = "Metadata could not be retrieved.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compare PDF Metadata</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        h2 {
            color: #555;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        input[type="file"] {
            margin-bottom: 15px;
            padding: 8px;
        }

        button {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #218838;
        }

        .comparison-container {
            margin-top: 20px;
            padding: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .result-item {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .error {
            color: red;
            text-align: center;
        }

        .file-section {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .file-section h3 {
            margin: 0;
            color: #333;
        }

        .difference {
            background-color: #ffeeba;
            padding: 10px;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            color: #856404;
        }
    </style>
</head>
<body>
    <h1>Upload Two PDF Files to Compare Metadata</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="file1">Select first PDF file:</label>
        <input type="file" name="file1" accept=".pdf" required><br>
        
        <label for="file2">Select second PDF file:</label>
        <input type="file" name="file2" accept=".pdf" required><br>
        
        <button type="submit">Upload and Compare</button>
    </form>

    <?php if (isset($flatMetadata1) && isset($flatMetadata2)): ?>
        <div class="comparison-container">
            <h2>Comparison Results</h2>
            <div class="file-section">
                <h3>File 1: <?php echo htmlspecialchars($file1Name); ?> Metadata:</h3>
                <?php foreach ($flatMetadata1 as $key => $value): ?>
                    <div class='result-item'><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></div>
                <?php endforeach; ?>
            </div>

            <div class="file-section">
                <h3>File 2: <?php echo htmlspecialchars($file2Name); ?> Metadata:</h3>
                <?php foreach ($flatMetadata2 as $key => $value): ?>
                    <div class='result-item'><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></div>
                <?php endforeach; ?>
            </div>

            <h3>Differences:</h3>
            <div class="difference">
                <?php
                $foundDifferences = false; // Flag to check if differences were found
                // Loop through the keys of the first file's metadata for comparison
                foreach ($flatMetadata1 as $key => $value) {
                    if (isset($flatMetadata2[$key])) {
                        if ($flatMetadata1[$key] !== $flatMetadata2[$key]) {
                            echo "<div class='result-item'><strong>$key:</strong> File 1 = " . htmlspecialchars(is_array($value) ? json_encode($value) : $value) . ", ";
                            echo "File 2 = " . htmlspecialchars(is_array($flatMetadata2[$key]) ? json_encode($flatMetadata2[$key]) : $flatMetadata2[$key]) . "</div>";
                            $foundDifferences = true;
                        }
                    } else {
                        echo "<div class='result-item'><strong>$key:</strong> Only in File 1 = " . htmlspecialchars(is_array($value) ? json_encode($value) : $value) . "</div>";
                        $foundDifferences = true;
                    }
                }

                // Check for keys that are only in File 2
                foreach ($flatMetadata2 as $key => $value) {
                    if (!isset($flatMetadata1[$key])) {
                        echo "<div class='result-item'><strong>$key:</strong> Only in File 2 = " . htmlspecialchars(is_array($value) ? json_encode($value) : $value) . "</div>";
                        $foundDifferences = true;
                    }
                }

                // If no differences found
                if (!$foundDifferences) {
                    echo "<div class='result-item'>No differences found between the two files.</div>";
                }
                ?>
            </div>
        </div>
    <?php elseif (isset($error)): ?>
        <p class='error'><?php echo $error; ?></p>
    <?php endif; ?>
</body>
</html>