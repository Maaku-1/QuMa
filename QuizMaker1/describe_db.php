<?php
session_start();

// Redirect to index.php if the user is not a superadmin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header("Location: index.php");
    exit;
}

include 'dbcon.php';

$query = "
    SELECT 
        c.table_name AS 'Table Name',
        c.column_name AS 'Column Name',
        c.column_type AS 'Column Type',
        c.is_nullable AS 'Is Nullable',
        c.column_default AS 'Default Value',
        c.extra AS 'Extra Info',
        kcu.constraint_name AS 'Constraint Name',
        kcu.referenced_table_name AS 'Referenced Table',
        kcu.referenced_column_name AS 'Referenced Column'
    FROM 
        information_schema.columns AS c
    LEFT JOIN 
        information_schema.key_column_usage AS kcu
    ON 
        c.table_name = kcu.table_name 
        AND c.column_name = kcu.column_name
        AND c.table_schema = kcu.table_schema
    WHERE 
        c.table_schema = :dbname
    ORDER BY 
        c.table_name, c.ordinal_position
";

try {
    $stmt = $pdoConnect->prepare($query);
    $stmt->bindParam(':dbname', $dbname, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<div class='container'>";
    echo "<a href='superadmindashboard.php' class='btn-back'>Back to Dashboard</a>";
    echo "<h1>Database Structure for '$dbname'</h1>";
    echo "<textarea id='dbText' style='display:none;'>";

    $current_table = "";
    foreach ($results as $row) {
        if ($current_table != $row['Table Name']) {
            if ($current_table != "") {
                echo "\n";
            }
            $current_table = $row['Table Name'];
            echo "Table: $current_table\n";
        }
        echo "{$row['Column Name']}, {$row['Column Type']}, {$row['Is Nullable']}, {$row['Default Value']}, {$row['Extra Info']}, {$row['Constraint Name']}, {$row['Referenced Table']}, {$row['Referenced Column']}\n";
    }

    echo "</textarea>";
    echo "<button onclick='copyText()' class='btn-copy'>Copy to Clipboard</button>";

    if (count($results) > 0) {
        foreach ($results as $row) {
            if ($current_table != $row['Table Name']) {
                if ($current_table != "") {
                    echo "</table><br>";
                }
                $current_table = $row['Table Name'];
                echo "<h2>Table: $current_table</h2>";
                echo "<table>";
                echo "<tr><th>Column Name</th><th>Column Type</th><th>Is Nullable</th><th>Default Value</th><th>Extra Info</th><th>Constraint Name</th><th>Referenced Table</th><th>Referenced Column</th></tr>";
            }
            echo "<tr>";
            echo "<td>" . $row['Column Name'] . "</td>";
            echo "<td>" . $row['Column Type'] . "</td>";
            echo "<td>" . $row['Is Nullable'] . "</td>";
            echo "<td>" . $row['Default Value'] . "</td>";
            echo "<td>" . $row['Extra Info'] . "</td>";
            echo "<td>" . ($row['Constraint Name'] ?: '-') . "</td>";
            echo "<td>" . ($row['Referenced Table'] ?: '-') . "</td>";
            echo "<td>" . ($row['Referenced Column'] ?: '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No tables found in the database.</p>";
    }
    echo "</div>";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<script>
function copyText() {
    var textArea = document.getElementById("dbText");
    textArea.style.display = "block";
    textArea.select();
    textArea.setSelectionRange(0, 99999);
    document.execCommand("copy");
    textArea.style.display = "none";
    alert("Database structure copied to clipboard!");
}
</script>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        color: #333;
        margin: 0;
        padding: 0;
        display: flex; /* Flexbox for centering */
        justify-content: center; /* Horizontal centering */
        align-items: center; /* Vertical centering */
        min-height: 100vh; /* Full viewport height */
    }
    .container {
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        max-width: 90%;
        overflow-x: auto;
        text-align: center;
    }
    h1 {
        font-size: 1.5rem;
        margin-bottom: 20px;
    }
    h2 {
        font-size: 1.2rem;
        margin: 20px 0 10px;
    }
    table {
        margin: 0 auto;
        border-collapse: collapse;
        width: auto;
        font-size: 0.9rem;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        white-space: nowrap;
    }
    th {
        background-color: #007BFF;
        color: white;
        font-size: 1rem;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
    .btn-copy {
        display: inline-block;
        margin: 10px 0;
        background-color: #28a745;
        color: white;
        padding: 8px 15px;
        font-size: 0.9rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .btn-copy:hover {
        background-color: #218838;
    }
    .btn-back {
        display: inline-block;
        margin-bottom: 20px;
        background-color: #007BFF;
        color: white;
        padding: 8px 15px;
        font-size: 0.9rem;
        text-decoration: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .btn-back:hover {
        background-color: #0056b3;
    }
</style>
