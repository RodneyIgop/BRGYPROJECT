<?php
// Database connection details
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

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Check if birthdate column exists
$checkBirthdate = $conn->query("SHOW COLUMNS FROM superadmin LIKE 'birthdate'");
if ($checkBirthdate->num_rows == 0) {
    // Add birthdate column
    $sql = "ALTER TABLE superadmin ADD COLUMN birthdate DATE NULL AFTER Email";
    if ($conn->query($sql)) {
        echo "Birthdate column added successfully.<br>";
    } else {
        echo "Error adding birthdate column: " . $conn->error . "<br>";
    }
} else {
    echo "Birthdate column already exists.<br>";
}

// Check if age column exists
$checkAge = $conn->query("SHOW COLUMNS FROM superadmin LIKE 'age'");
if ($checkAge->num_rows == 0) {
    // Add age column
    $sql = "ALTER TABLE superadmin ADD COLUMN age INT NULL AFTER birthdate";
    if ($conn->query($sql)) {
        echo "Age column added successfully.<br>";
    } else {
        echo "Error adding age column: " . $conn->error . "<br>";
    }
} else {
    echo "Age column already exists.<br>";
}

$conn->close();
?>
