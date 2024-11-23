<?php
session_start();

include 'dbcon.php'; // Ensure your database connection is correct

// Define roles and associated dashboard pages
$roles = [
    'admin' => ['table' => 'admin', 'id_column' => 'admin_id', 'dashboard' => 'admindashboard.php'],
    'student' => ['table' => 'student', 'id_column' => 'student_id', 'dashboard' => 'studentdashboard.php'],
    'superadmin' => ['table' => 'superadmin', 'id_column' => 'superadmin_id', 'dashboard' => 'superadmindashboard.php'],
];

// Handle user logout
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = [];

    // Delete the remember me cookie if it exists
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: login.php");
    exit;
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    foreach ($roles as $role => $data) {
        $table = $data['table'];
        $id_column = $data['id_column'];
        $dashboard = $data['dashboard'];

        try {
            $stmt = $pdoConnect->prepare("SELECT * FROM $table WHERE $id_column = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if the user exists and the password is correct
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_role'] = $role;
                $_SESSION['user_id'] = $username;

                // Check if the password is still the default (QM + user_id)
                $default_password = "QM" . $username;
                if (password_verify($default_password, $user['password'])) {
                    header("Location: edit_password.php?warning=1");
                    exit;
                }

                // Generate a unique token for remember me functionality
                if (isset($_POST['rememberMe']) && $_POST['rememberMe'] == 'on') {
                    $token = bin2hex(random_bytes(32)); // Generate a secure token

                    setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), '/', '', isset($_SERVER["HTTPS"]), true);

                    $updateStmt = $pdoConnect->prepare("UPDATE $table SET remember_me_token = :token WHERE $id_column = :username");
                    $updateStmt->bindParam(':token', $token);
                    $updateStmt->bindParam(':username', $username, PDO::PARAM_INT);

                    // Execute the update and check success
                    if ($updateStmt->execute()) {
                        echo "Token successfully saved.";
                    } else {
                        echo "Failed to save token.";
                    }
                } else {
                    // Delete the remember me cookie and token if not checked
                    if (isset($_COOKIE['remember_me'])) {
                        setcookie('remember_me', '', time() - 3600, '/');
                    }

                    // Clear the remember_me_token in the database
                    $updateStmt = $pdoConnect->prepare("UPDATE $table SET remember_me_token = NULL WHERE $id_column = :username");
                    $updateStmt->bindParam(':username', $username, PDO::PARAM_INT);
                    $updateStmt->execute();
                }

                // Redirect to the appropriate dashboard
                header("Location: $dashboard");
                exit;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
    }

    // If no user is found
    echo "Invalid ID or password.";
}

// Check if remember me cookie exists and automatically log in the user
if (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    foreach ($roles as $role => $data) {
        $table = $data['table'];
        $id_column = $data['id_column'];
        $dashboard = $data['dashboard'];

        try {
            // Find the user based on the remember me token in the database
            $stmt = $pdoConnect->prepare("SELECT * FROM $table WHERE remember_me_token = :token");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if the user exists
            if ($user) {
                $_SESSION['user_role'] = $role;
                $_SESSION['user_id'] = $user[$id_column];
                header("Location: $dashboard");
                exit;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="source/css/bsf-index.css">
    <link rel="stylesheet" href="source/css/index.css">
    <link rel="stylesheet" href="source/css/fonts.css">
    <link rel="stylesheet" href="source/css/loading-spinner.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>QuMa</title>
    <style>
        /* Styles for the QR modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            position: relative;
            margin: 15% auto;
            padding: 20px;
            background-color: white;
            width: 80%;
            max-width: 600px;
            height: 80%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .modal iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }

        .mt-3 .btn {
            margin-right: 10px;
            margin-bottom: 2px;
        }
    </style>
</head>

<body>

    <!-- Loading Spinner -->
    <div id="spinner">
        <div class="spinner"></div>
    </div>

    <!-- Modal for displaying QR Code -->
    <div id="qrModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeQRModal()">×</button>
            <iframe src="qr.php"></iframe>
        </div>
    </div>

    <!-- Wrapper -->
    <div class="wrapper hidden" id="content">
        <div class="left">
            <div class="header">
                <span class="logo font-pacifico">quiz maker</span>
            </div>

            <!-- Body -->
            <div class="body">
                <div class="hero">
                    <span class="material-symbols-rounded hero-icon">key</span>
                    <span class="hero-text font-poppins-bold">Login to your <span>DHVSU</span> Account</span>
                </div>

                <!-- Updated Form (preserving old functionality) -->
                <form id="loginForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="form-floating">
                        <input type="text" pattern="[0-9]*" inputmode="numeric" id="username" name="username"
                            class="form-control font-rubik" placeholder="School ID" required>
                        <label class="font-rubik" for="username">ID</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" id="password" name="password" class="form-control font-rubik"
                            placeholder="Password" required>
                        <label class="font-rubik" for="password">Password</label>
                        <span id="toggle-password" class="material-symbols-rounded show-pw">visibility</span>
                    </div>
                    <div class="form-check rm">
                        <input type="checkbox" id="rememberMe" name="rememberMe" class="form-check-input">
                        <label class="form-check-label font-rubik" for="rememberMe">Remember Me</label>
                    </div>
                    <button type="submit" class="btn btn-primary font-rubik login">LOGIN</button>
                </form>
                <div class="mt-3">
                    <button class="btn btn-primary font-rubik login" onclick="showQRModal()">Open QR</button>
                    <button class="btn btn-primary font-rubik login"
                        onclick="window.location.href='cor.php'">Register</button>
                </div>
            </div>
            <!-- Buttons -->

            <!-- Footer -->
            <div class="footer">
                <span class="footer-text font-pt-sans-regular-italic">CAPSTONE PROJECT BY GROUP 2</span>
            </div>
        </div>

        <!-- DHVSU Background -->
        <div class="right">
            <div class="background"></div>
        </div>
    </div>

    <!-- JS Functions -->
    <script src="source/bootstrap/dist/js/bootstrap.js" crossorigin="anonymous"></script>
    <script src="source/js/showPass.js" crossorigin="anonymous"></script>
    <script src="source/js/loadingSpinner.js" crossorigin="anonymous"></script>
    <script>
        // Function to show the QR modal
        function showQRModal() {
            document.getElementById('qrModal').style.display = 'block';
        }

        // Function to close the QR modal
        function closeQRModal() {
            document.getElementById('qrModal').style.display = 'none';
        }
    </script>
</body>

</html>