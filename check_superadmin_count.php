<?php
require_once 'Connection/conn.php';

// Check current number of superadmin accounts
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM superadmin');
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];
$stmt->close();

echo "Current superadmin count: " . $count;

// Also check if connection works
if ($conn) {
    echo "\nDatabase connection: OK";
} else {
    echo "\nDatabase connection: FAILED";
}

$conn->close();
?>
