<?php
require_once 'Connection/conn.php';

echo "Checking attendance table structure...\n";

// Get the table structure
$result = $conn->query("DESCRIBE attendance");

if ($result) {
    echo "Attendance table columns:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\nChecking attendance_settings table...\n";
$result = $conn->query("SELECT * FROM attendance_settings");

if ($result) {
    echo "Attendance settings:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['setting_name'] . " = " . $row['setting_value'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>
