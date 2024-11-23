<?php
$host = 'localhost';
$dbname = 'qumadb2';
$username = 'root';
$password = ''; 

try {
    $pdoConnect = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdoConnect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>