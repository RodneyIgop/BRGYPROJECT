<?php
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "barangaynewera";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper character encoding
$conn->set_charset("utf8mb4");

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}
?>