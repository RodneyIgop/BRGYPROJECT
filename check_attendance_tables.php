<?php
require_once 'Connection/conn.php';

echo "Checking database tables...\n";

$tables_to_check = ['officials', 'attendance', 'attendance_settings'];
$missing_tables = [];

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        $missing_tables[] = $table;
        echo "Missing: $table\n";
    } else {
        echo "Found: $table\n";
    }
}

if (!empty($missing_tables)) {
    echo "\nMissing tables: " . implode(', ', $missing_tables);
    echo "\nPlease run the SQL schema file: Connection/attendance_system_schema_safe.sql\n";
} else {
    echo "\nAll attendance system tables are present!\n";
    
    // Check if there are any officials
    $result = $conn->query("SELECT COUNT(*) as count FROM officials");
    $count = $result->fetch_assoc()['count'];
    echo "Number of officials: $count\n";
    
    // Check attendance records
    $result = $conn->query("SELECT COUNT(*) as count FROM attendance");
    $count = $result->fetch_assoc()['count'];
    echo "Number of attendance records: $count\n";
}

$conn->close();
?>
