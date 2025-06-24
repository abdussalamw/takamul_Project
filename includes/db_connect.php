<?php
$host = 'localhost';
$db = 'u648829741_db_takamul';
$user = 'u648829741_takamuluser';
$pass = 'y3>[[VUvC$8Z';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>