<?php
include 'dbcon.php';
session_start();

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['student', 'admin'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['user_role'] === 'student') {
    $table = 'student';
    $id_column = 'student_id';
    $user_id = $_SESSION['user_id'];
} elseif ($_SESSION['user_role'] === 'admin') {
    $table = 'admin';
    $id_column = 'admin_id';
    $user_id = $_SESSION['user_id'];
} else {
    echo "Invalid user role.";
    exit;
}

$query = "SELECT * FROM $table WHERE $id_column = ?";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    echo "User not found.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];

    // Verify the current password
    if (!password_verify($current_password, $user_data['password'])) {
        echo "Current password is incorrect.";
        exit;
    }

    // Prepare fields to be updated
    $fields_to_update = [];
    $params = [];

    if (!empty($_POST['fname'])) {
        $fields_to_update[] = "fname = ?";
        $params[] = $_POST['fname'];
    }
    if (!empty($_POST['mi'])) {
        $fields_to_update[] = "mi = ?";
        $params[] = $_POST['mi'];
    }
    if (!empty($_POST['lname'])) {
        $fields_to_update[] = "lname = ?";
        $params[] = $_POST['lname'];
    }
    if ($_SESSION['user_role'] === 'student') {
        if (!empty($_POST['section'])) {
            $fields_to_update[] = "section = ?";
            $params[] = $_POST['section'];
        }
        if (!empty($_POST['year'])) {
            $fields_to_update[] = "year = ?";
            $params[] = $_POST['year'];
        }
        if (!empty($_POST['gender'])) {
            $fields_to_update[] = "gender = ?";
            $params[] = $_POST['gender'];
        }
    }
    if (!empty($_POST['department'])) {
        $fields_to_update[] = "department = ?";
        $params[] = $_POST['department'];
    }

    // Check if the new password fields are filled in
    if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            // Hash the new password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $fields_to_update[] = "password = ?";
            $params[] = $hashed_new_password;
        } else {
            echo "New password and confirm password do not match.";
            exit;
        }
    }

    if (!empty($fields_to_update)) {
        $params[] = $user_id;
        $query = "UPDATE $table SET " . implode(", ", $fields_to_update) . " WHERE $id_column = ?";
        $stmt = $pdoConnect->prepare($query);

        if ($stmt->execute($params)) {
            echo "Profile updated successfully.";
        } else {
            echo "Error updating profile.";
        }
    } else {
        echo "No changes made.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
</head>

<body>
    <h1>Edit Profile</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required><br><br>

        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($user_data['fname']); ?>"><br><br>

        <label for="mi">Middle Initial:</label>
        <input type="text" id="mi" name="mi" value="<?php echo htmlspecialchars($user_data['mi']); ?>"><br><br>

        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($user_data['lname']); ?>"><br><br>

        <?php if ($_SESSION['user_role'] === 'student'): ?>
            <label for="section">Section:</label>
            <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($user_data['section']); ?>"><br><br>

            <label for="year">Year:</label>
            <input type="number" id="year" name="year" value="<?php echo htmlspecialchars($user_data['year']); ?>"><br><br>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender">
                <option value="Male" <?php echo ($user_data['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($user_data['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo ($user_data['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
            </select><br><br>
        <?php endif; ?>

        <label for="department">Department:</label>
        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($user_data['department']); ?>"><br><br>

        <h2>Change Password (Optional)</h2>
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password"><br><br>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password"><br><br>

        <button type="submit">Save Changes</button>
    </form>
    <a href="profile.php">Back to Profile</a>
</body>

</html>
