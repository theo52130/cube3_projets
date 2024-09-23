<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test_cube_trois";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
