<?php
include 'dbcon.php';
session_start();

// Redirect if not logged in as student
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: index.php");
    exit;
}

// Student role setup
$table = 'student';
$id_column = 'student_id';
$user_id = $_SESSION['user_id'];

// Fetch student profile data
$query = "SELECT * FROM $table WHERE $id_column = ?";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    echo "User not found.";
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Verify current password
    if (!password_verify($currentPassword, $user_data['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New passwords do not match.";
    } else {
        // Hash new password and update in the database
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateQuery = "UPDATE $table SET password = ? WHERE $id_column = ?";
        $updateStmt = $pdoConnect->prepare($updateQuery);
        $updateStmt->execute([$hashedPassword, $user_id]);

        $success = "Password changed successfully.";
    }
}

// Handle profile update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fname = $_POST['fname'];
    $mi = $_POST['mi'];
    $lname = $_POST['lname'];
    $department = $_POST['department'];

    $updateQuery = "UPDATE $table SET fname = ?, mi = ?, lname = ?, department = ? WHERE $id_column = ?";
    $updateStmt = $pdoConnect->prepare($updateQuery);
    $updateStmt->execute([$fname, $mi, $lname, $department, $user_id]);

    echo json_encode(['status' => 'success']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .error {
            color: red;
            text-align: center;
        }
        .success {
            color: green;
            text-align: center;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 15px;
            }
            button {
                width: 100%; /* Buttons should take full width on smaller screens */
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Student Profile</h2>

        <!-- Profile Details -->
        <form id="profile-form">
            <label>
                First Name:
                <input type="text" id="first-name" readonly value="<?php echo htmlspecialchars($user_data['fname']); ?>">
            </label>
            <label>
                Middle Initial:
                <input type="text" id="middle-name" readonly value="<?php echo htmlspecialchars($user_data['mi']); ?>">
            </label>
            <label>
                Last Name:
                <input type="text" id="last-name" readonly value="<?php echo htmlspecialchars($user_data['lname']); ?>">
            </label>
            <label>
                Student ID:
                <input type="text" id="student-id" readonly value="<?php echo htmlspecialchars($user_data[$id_column]); ?>">
            </label>
            <label>
                Department:
                <input type="text" id="department" readonly value="<?php echo htmlspecialchars($user_data['department']); ?>">
            </label>
        </form>

        <!-- Edit, Cancel, and Save Buttons -->
        <div class="button-container">
            <button id="edit-profile-btn">Edit</button>
            <button id="cancel-edit-btn" style="display:none;">Cancel</button>
            <button id="save-profile-btn" style="display:none;">Save</button>
        </div>

        <hr>

        <!-- Change Password Form -->
        <h3>Change Password</h3>
        <form id="password-form" method="POST">
            <label>
                Current Password:
                <input type="password" name="current_password" required>
            </label>
            <label>
                New Password:
                <input type="password" name="new_password" required>
            </label>
            <label>
                Confirm New Password:
                <input type="password" name="confirm_password" required>
            </label>
            <div class="button-container">
                <button type="submit" name="change_password">Change Password</button>
            </div>
        </form>

        <!-- Display Error or Success Messages -->
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="button-container">
            <button onclick="window.location.href='studentdashboard.php'">Back to Dashboard</button>
        </div>
    </div>

    <script>
        // Toggle form fields to be editable, except for the student ID
        document.getElementById('edit-profile-btn').addEventListener('click', () => {
            document.querySelectorAll('#profile-form input').forEach(input => {
                // Only make inputs editable if they're not the student ID
                if (input.id !== 'student-id') {
                    input.readOnly = false;
                }
            });
            document.getElementById('edit-profile-btn').style.display = 'none';
            document.getElementById('cancel-edit-btn').style.display = 'inline';
            document.getElementById('save-profile-btn').style.display = 'inline';
        });

        // Cancel editing
        document.getElementById('cancel-edit-btn').addEventListener('click', () => {
            document.querySelectorAll('#profile-form input').forEach(input => {
                input.readOnly = true; // Set all fields back to readonly
            });
            document.getElementById('edit-profile-btn').style.display = 'inline';
            document.getElementById('cancel-edit-btn').style.display = 'none';
            document.getElementById('save-profile-btn').style.display = 'none';
        });

        // AJAX to save profile changes
        document.getElementById('save-profile-btn').addEventListener('click', (e) => {
            e.preventDefault();
            const formData = new FormData();
            formData.append('fname', document.getElementById('first-name').value);
            formData.append('mi', document.getElementById('middle-name').value);
            formData.append('lname', document.getElementById('last-name').value);
            formData.append('department', document.getElementById('department').value);
            formData.append('update_profile', true); // Indicate this is a profile update

            fetch('', { // Submit to the same script
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Profile updated successfully');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });
    </script>
</body>
</html>
