<?php
require_once 'Connection/conn.php';

echo "<h3>Email Usage Check</h3>";

// Check superadmin emails
echo "<h4>Superadmin Emails:</h4>";
$stmt = $conn->prepare('SELECT Email FROM superadmin');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo "- " . htmlspecialchars($row['Email']) . "<br>";
}
$stmt->close();

// Check admin emails
echo "<h4>Admin Emails:</h4>";
$stmt = $conn->prepare('SELECT email FROM admintbl');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo "- " . htmlspecialchars($row['email']) . "<br>";
}
$stmt->close();

// Check resident emails (first 10)
echo "<h4>Resident Emails (first 10):</h4>";
$stmt = $conn->prepare('SELECT email FROM usertbl LIMIT 10');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo "- " . htmlspecialchars($row['email']) . "<br>";
}
$stmt->close();

// Check pending request emails (first 10)
echo "<h4>Pending Request Emails (first 10):</h4>";
$stmt = $conn->prepare('SELECT email FROM userrequest LIMIT 10');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo "- " . htmlspecialchars($row['email']) . "<br>";
}
$stmt->close();

$conn->close();
?>
