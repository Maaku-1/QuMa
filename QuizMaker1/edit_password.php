<?php
session_start();

if (!isset($_SESSION['user_role']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$dashboard_url = $_SESSION['user_role'] . 'dashboard.php';

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'dbcon.php';

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Input validation for minimum password length
    if (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif ($new_password === $confirm_password) {
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

        $role = $_SESSION['user_role'];
        $id_column = $role . "_id";
        $user_id = $_SESSION['user_id'];
        $table = $role;

        $query = "UPDATE $table SET password = ? WHERE $id_column = ?";
        $stmt = $pdoConnect->prepare($query);

        if ($stmt->execute([$hashed_new_password, $user_id])) {
            // Successful password update
            $success_message = "Password updated successfully. Please log in again.";
            // Log out the user by destroying the session
            session_destroy();
        } else {
            $error_message = "Error updating password.";
        }
    } else {
        $error_message = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="source/css/bsf-admin-subject-view.css">
    <link rel="stylesheet" href="source/css/admin-subject-view.css">
    <link rel="stylesheet" href="source/css/fonts.css">
    <link rel="icon" type="image/x-icon" href="assets/pictures/QM Logo.png">
    <title>Change Password</title>
    <style>
        .centered-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: top;
            height: 100vh;
            text-align: center;
        }

        .centered-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        input.form-control {
            border: 1px solid #ccc;
            /* Default border */
            border-radius: 4px;
            /* Rounded corners */
            padding: 8px;
            /* Padding inside the input */
            width: 250px;
            /* Fixed width for inputs */
            transition: border-color 0.3s;
            /* Smooth transition for any border color change */
        }

        input.form-control:focus {
            outline: none;
            /* Remove default outline on focus */
            border-color: #007BFF;
            /* Border color on focus */
        }
    </style>
</head>

<body>
    <div class="admin-dashboard-wrapper" id="admin-dashboard">
        <div class="admin-dashboard-content">

            <!-- HEADER -->
            <div class="header-wrapper" id="header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="header-hero-wrapper">
                            <span class="header-hero font-poppins-bold">QM</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Centered Container for Password Change Form -->
            <div class="centered-container">
                <h1>Change Your Password</h1><br>

                <?php if ($success_message): ?>
                    <p style="color: green;"><?php echo $success_message; ?></p>
                    <a href="index.php" class="btn edit-quiz-confirm font-rubik">OK</a>
                <?php elseif ($error_message): ?>
                    <p style="color: red;"><?php echo $error_message; ?></p>
                <?php else: ?>
                    <?php if (isset($_GET['warning']) && $_GET['warning'] == 1): ?>
                        <p style="color: red;">Warning: You are still using the default password. It is recommended to change
                            your password for security reasons.</p>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control font-rubik"
                            required><br><br>

                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control font-rubik"
                            required><br><br>

                        <button type="submit" class="btn edit-quiz-confirm font-rubik">Change Password</button>
                    </form>

                    <br>
                    <a href="<?php echo $dashboard_url; ?>"
                        onclick="return confirm('Warning: You are still using the default password. Are you sure you don\'t want to change your password?');">
                        Skip to Dashboard
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordForm = document.querySelector('form[action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"]');
    const errorMessage = document.createElement('p');
    errorMessage.style.color = 'red';
    errorMessage.style.textAlign = 'center';
    passwordForm.insertBefore(errorMessage, passwordForm.firstChild); // Insert the error message element at the top

    passwordForm.addEventListener('submit', function (event) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Clear previous error messages
        errorMessage.textContent = '';

        // Check password length
        if (newPassword.length < 8) {
            errorMessage.textContent = "Password must be at least 8 characters long.";
            event.preventDefault(); // Prevent form submission
            return;
        }

        // Check if passwords match
        if (newPassword !== confirmPassword) {
            errorMessage.textContent = "Passwords do not match.";
            event.preventDefault(); // Prevent form submission
            return;
        }
    });
});
</script>

</body>

</html>