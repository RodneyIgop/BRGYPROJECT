<?php
// This script will create the userrequest table
require_once 'Connection/conn.php';

// SQL to create the userrequest table
$sql = "CREATE TABLE IF NOT EXISTS `userrequest` (
  `RequestID` int(11) NOT NULL AUTO_INCREMENT,
  `LastName` varchar(255) NOT NULL,
  `FirstName` varchar(255) NOT NULL,
  `MiddleName` varchar(255) DEFAULT NULL,
  `Suffix` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `Age` int(11) DEFAULT NULL,
  `ContactNumber` bigint(20) DEFAULT NULL,
  `Address` varchar(255) NOT NULL,
  `CensusNumber` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `requestDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `verificationCode` int(6) DEFAULT NULL,
  `isVerified` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`RequestID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table userrequest created successfully or already exists";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
