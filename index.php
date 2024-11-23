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