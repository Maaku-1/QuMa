<?php
include 'dbcon.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $admin_id = $_POST['admin_id'];
    $fname = $_POST['fname'];
    $mi = $_POST['mi'];
    $lname = $_POST['lname'];
    $department = $_POST['department'];

    // Check if admin_id already exists
    $query_check_duplicate = "SELECT id FROM admin WHERE admin_id = ?";
    $stmt_check_duplicate = $pdoConnect->prepare($query_check_duplicate); // Use $pdoConnect here
    $stmt_check_duplicate->execute([$admin_id]);
    $result_check_duplicate = $stmt_check_duplicate->fetch(PDO::FETCH_ASSOC);
    if ($result_check_duplicate) {
        echo "Admin ID already exists.";
        exit();
    }

    // Validate admin ID length
    if (strlen($admin_id) !== 10 || !ctype_digit($admin_id)) {
        echo "Admin ID must be 10 digits.";
        exit();
    }

    // Set password as "QM" + admin_id
    $password = "QM" . $admin_id;

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into admin table
    $query = "INSERT INTO admin (admin_id, password, fname, mi, lname, department) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdoConnect->prepare($query); // Use $pdoConnect here
    $stmt->execute([$admin_id, $hashed_password, $fname, $mi, $lname, $department]);
    if ($stmt->rowCount() > 0) {
        echo "Admin account created successfully";
    } else {
        echo "Error creating admin account.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
</head>

<body>
    <h1>Create Admin Account</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="admin_id">Admin ID:</label>
        <input type="number" id="admin_id" name="admin_id" required min="1000000000" max="9999999999"
            oninput="javascript: if (this.value.length > 10) this.value = this.value.slice(0, 10);">
        <br><br>

        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" required><br><br>

        <label for="mi">Middle Initial:</label>
        <input type="text" id="mi" name="mi" maxlength="2" required><br><br>

        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" required><br><br>

        <label for="department">Department:</label>
        <input type="text" id="department" name="department" required><br><br>

        <button type="submit">Create Account</button>
    </form>
    <a href="superadmindashboard.php">Back to Super Admin dasboard</a>
</body>

</html>
