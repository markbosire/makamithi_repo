<?php

$servername = 'mysql-makamithi.alwaysdata.net';
$username = 'makamithi';
$password = 'Makamithi@456';
$dbname = 'makamithi_vetcare';
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
