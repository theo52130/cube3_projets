<?php
$host = 'localhost';
$db = 'test_cube_trois';
$user = 'root';
$pass = 'theooreo';

$dsn = "mysql:host=$host;dbname=$db";
$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
