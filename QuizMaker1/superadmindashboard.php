<?php
session_start();

// Redirect to index.php if the user is not a superadmin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header("Location: index.php");
    exit;
}

// Include database connection
require_once 'dbcon.php'; // Adjust the path if needed
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            color: #333;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 10px 0;
        }

        a {
            text-decoration: none;
            color: #007BFF;
        }

        a:hover {
            text-decoration: underline;
        }

        button {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #c82333;
        }

        .message {
            color: green;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <h1>Super Admin Dashboard</h1>
    <ul>
        <li><a href="create_acc_student.php">Create Student Account</a></li>
        <li><a href="create_acc_admin.php">Create Admin Account</a></li>
        <li><a href="reset_password.php">Reset Password</a></li>
        <li><a href="describe_db.php">Database Example</a></li>
    </ul>

    <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
</body>

</html>
