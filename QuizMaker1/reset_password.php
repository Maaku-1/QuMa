<?php
session_start();

// Redirect to index.php if the user is not a superadmin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header("Location: index.php");
    exit;
}

// Include database connection
require_once 'dbcon.php'; // Adjust the path if needed

$message = '';
$userData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_user'])) {
    $searchInput = trim($_POST['search_input']);

    try {
        // Search in both admin and student tables
        $adminQuery = "SELECT 
                    'admin' AS user_type, 
                    admin_id AS user_id, 
                    fname, 
                    lname, 
                    department AS additional_info 
               FROM admin 
               WHERE admin_id = :search OR fname LIKE :searchLike OR lname LIKE :searchLike";

        $studentQuery = "SELECT 
                     'student' AS user_type, 
                     student_id AS user_id, 
                     fname, 
                     lname, 
                     section AS additional_info 
                FROM student 
                WHERE student_id = :search OR fname LIKE :searchLike OR lname LIKE :searchLike";

        $stmt = $pdoConnect->prepare("$adminQuery UNION $studentQuery");

        $stmt->execute([
            'search' => $searchInput,
            'searchLike' => '%' . $searchInput . '%'
        ]);

        $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($userData)) {
            $message = 'No user found matching the input.';
        }
    } catch (PDOException $e) {
        $message = 'Error searching for user: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $id = $_POST['user_id'];
    $userType = $_POST['user_type'];

    // Default password
    $defaultPassword = 'QM' . $id;

    try {
        // Reset password based on user type
        $sql = $userType === 'admin'
            ? "UPDATE admin SET password = :password WHERE admin_id = :id"
            : "UPDATE student SET password = :password WHERE student_id = :id";

        $stmt = $pdoConnect->prepare($sql);
        $stmt->execute([
            'password' => password_hash($defaultPassword, PASSWORD_DEFAULT),
            'id' => $id
        ]);

        $message = 'Password reset successfully for ' . htmlspecialchars($userType) . ' with ID: ' . htmlspecialchars($id);
    } catch (PDOException $e) {
        $message = 'Error resetting password: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        form {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        .message {
            color: green;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <h1>Reset Password</h1>

    <a href="superadmindashboard.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px;">Back to Dashboard</a>

    <form action="" method="post">
        <label for="search_input">Search User (Name or ID):</label>
        <input type="text" name="search_input" id="search_input" required>
        <button type="submit" name="search_user">Search</button>
    </form>

    <?php if ($userData): ?>
        <table>
            <thead>
                <tr>
                    <th>User Type</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Department/Section</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userData as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($user['additional_info']); ?>
                        </td>
                        <td>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="user_type" value="<?php echo $user['user_type']; ?>">
                                <button type="submit" name="reset_password"
                                    onclick="return confirm('Are you sure you want to reset the password for this user?')">Reset
                                    Password</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
</body>

</html>