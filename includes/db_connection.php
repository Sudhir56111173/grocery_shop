<?php
// Database connection settings
$host = 'localhost';
$db   = 'grocery_shop';
$user = 'root'; // Change if needed
$pass = '';    // Change if needed

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
// Connection successful
?>
