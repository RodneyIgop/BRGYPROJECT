<?php
require_once '../Connection/conn.php';

echo "<h2>Attendance Table Structure in barangaynewera:</h2>";

// Check if attendance table exists
$attendance_check = $conn->query("SHOW TABLES LIKE 'attendance'");
if ($attendance_check && $attendance_check->num_rows > 0) {
    // Show table structure
    $structure = $conn->query("DESCRIBE attendance");
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th style='padding: 10px;'>Field</th><th style='padding: 10px;'>Type</th><th style='padding: 10px;'>Null</th><th style='padding: 10px;'>Key</th><th style='padding: 10px;'>Default</th><th style='padding: 10px;'>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>" . $row['Field'] . "</strong></td>";
        echo "<td style='padding: 8px;'>" . $row['Type'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Null'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Key'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample data
    $data = $conn->query("SELECT * FROM attendance LIMIT 5");
    if ($data && $data->num_rows > 0) {
        echo "<h3>Sample Data (First 5 records):</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        // Header
        $fields = $data->fetch_fields();
        echo "<tr style='background: #f0f0f0;'>";
        foreach ($fields as $field) {
            echo "<th style='padding: 10px;'>" . $field->name . "</th>";
        }
        echo "</tr>";
        // Data
        $data->data_seek(0);
        while ($row = $data->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td style='padding: 8px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data found in attendance table.</p>";
    }
    
    // Show total count
    $count_result = $conn->query("SELECT COUNT(*) as total FROM attendance");
    if ($count_result) {
        $count = $count_result->fetch_assoc();
        echo "<p><strong>Total records:</strong> " . $count['total'] . "</p>";
    }
    
} else {
    echo "<p>Attendance table does not exist in barangaynewera database.</p>";
}

$conn->close();
?>
