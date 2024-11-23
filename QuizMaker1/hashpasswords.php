<?php
include 'dbcon.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hash_passwords'])) {
    try {
        // Hash passwords for student accounts
        $query_student = "SELECT id, password FROM student WHERE password NOT LIKE 'QM%'";
        $stmt_student = $pdoConnect->prepare($query_student);
        $stmt_student->execute();
        $students = $stmt_student->fetchAll(PDO::FETCH_ASSOC);
        foreach ($students as $student) {
            $hashed_password = password_hash($student['password'], PASSWORD_DEFAULT);
            $update_query = "UPDATE student SET password = :hashed_password WHERE id = :id";
            $stmt_update_student = $pdoConnect->prepare($update_query);
            $stmt_update_student->bindParam(':hashed_password', $hashed_password);
            $stmt_update_student->bindParam(':id', $student['id']);
            $stmt_update_student->execute();
        }

        // Hash passwords for admin accounts
        $query_admin = "SELECT id, password FROM admin WHERE password NOT LIKE 'QM%'";
        $stmt_admin = $pdoConnect->prepare($query_admin);
        $stmt_admin->execute();
        $admins = $stmt_admin->fetchAll(PDO::FETCH_ASSOC);
        foreach ($admins as $admin) {
            $hashed_password = password_hash($admin['password'], PASSWORD_DEFAULT);
            $update_query = "UPDATE admin SET password = :hashed_password WHERE id = :id";
            $stmt_update_admin = $pdoConnect->prepare($update_query);
            $stmt_update_admin->bindParam(':hashed_password', $hashed_password);
            $stmt_update_admin->bindParam(':id', $admin['id']);
            $stmt_update_admin->execute();
        }

        // Hash passwords for superadmin accounts
        $query_superadmin = "SELECT id, password FROM superadmin WHERE password NOT LIKE 'QM%'";
        $stmt_superadmin = $pdoConnect->prepare($query_superadmin);
        $stmt_superadmin->execute();
        $superadmins = $stmt_superadmin->fetchAll(PDO::FETCH_ASSOC);
        foreach ($superadmins as $superadmin) {
            $hashed_password = password_hash($superadmin['password'], PASSWORD_DEFAULT);
            $update_query = "UPDATE superadmin SET password = :hashed_password WHERE id = :id";
            $stmt_update_superadmin = $pdoConnect->prepare($update_query);
            $stmt_update_superadmin->bindParam(':hashed_password', $hashed_password);
            $stmt_update_superadmin->bindParam(':id', $superadmin['id']);
            $stmt_update_superadmin->execute();
        }

        echo "Passwords hashed successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hash Passwords</title>
</head>
<body>
    <h1>Hash Passwords</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <button type="submit" name="hash_passwords">Hash Passwords</button>
    </form>
    <a href="superadmindashboard.php">Back to Super Admin dasboard</a>
</body>
</html>
