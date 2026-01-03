<?php
require_once '../Connection/conn.php';

echo "<h2>Checking barangaynewera database tables...</h2>";

// Show all tables in the database
$tables_result = $conn->query("SHOW TABLES");
echo "<h3>All Tables in barangaynewera:</h3>";
echo "<ul>";
while ($table = $tables_result->fetch_array()) {
    echo "<li>" . $table[0] . "</li>";
}
echo "</ul>";

// Check specifically for attendance table
$attendance_check = $conn->query("SHOW TABLES LIKE 'attendance'");
if ($attendance_check && $attendance_check->num_rows > 0) {
    echo "<h3>Attendance table exists!</h3>";
    
    // Show table structure
    $structure = $conn->query("DESCRIBE attendance");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Show sample data
    $data = $conn->query("SELECT * FROM attendance LIMIT 5");
    if ($data && $data->num_rows > 0) {
        echo "<h3>Sample Data:</h3>";
        echo "<table border='1'>";
        // Header
        $fields = $data->fetch_fields();
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        // Data
        $data->data_seek(0);
        while ($row = $data->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<h3>Attendance table does NOT exist!</h3>";
}

$conn->close();
?>
