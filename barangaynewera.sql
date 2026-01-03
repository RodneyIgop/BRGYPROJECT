-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2025 at 02:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barangaynewera`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_role` enum('Admin','Superadmin') NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `page` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('Successful','Failed','Pending') DEFAULT 'Successful',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `user_name`, `user_role`, `action`, `description`, `page`, `ip_address`, `status`, `created_at`) VALUES
(1, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-26 07:54:15'),
(2, 1, 'ADMIN ACCOUNT', 'Admin', 'Approve Request', 'Approved document request for Gerald Louis Sumaylo - Document: Barangay Clearance', 'adminpending.php', '::1', 'Successful', '2025-12-26 08:13:13'),
(3, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-26 08:15:15'),
(4, 1, 'ADMIN ACCOUNT', 'Admin', 'Reject Request', 'Rejected document request for Rodney Odiamar - Document: Certificate of Residency - Reason: Invalid information', 'adminpending.php', '::1', 'Successful', '2025-12-26 08:56:15'),
(5, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-26 12:41:42');

-- --------------------------------------------------------

--
-- Table structure for table `adminrequests`
--

CREATE TABLE `adminrequests` (
  `RequestID` int(11) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255) NOT NULL,
  `suffix` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `age` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contactnumber` int(11) NOT NULL,
  `password` int(11) NOT NULL,
  `requestDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admintbl`
--

CREATE TABLE `admintbl` (
  `AdminID` int(11) NOT NULL,
  `employeeID` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255) NOT NULL,
  `suffix` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `age` int(11) NOT NULL,
  `contactnumber` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verificationcode` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admintbl`
--

INSERT INTO `admintbl` (`AdminID`, `employeeID`, `lastname`, `firstname`, `middlename`, `suffix`, `birthdate`, `age`, `contactnumber`, `email`, `profile_picture`, `password`, `verificationcode`) VALUES
(1, 'ADM-4416', 'ACCOUNT', 'ADMIN', '.', '', '2008-01-12', 17, 2147483647, 'geraldlouissumaylo@gmail.com', 'uploads/adminprofiles/admin_1_1766735785.jpg', 'T@9Fq#L4x', 0);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `image`, `date`) VALUES
(1, 'ANNOUNCEMENT', '....', 'images (1).jpg', 2025),
(2, 'IMPORTANT ', '.....', 'images (2).jpg', 2025),
(3, 'URGENT', '.....', 'images (3).jpg', 2026);

-- --------------------------------------------------------

--
-- Table structure for table `approved`
--

CREATE TABLE `approved` (
  `RequestID` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `documenttype` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `dateRequested` date NOT NULL,
  `status` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approved`
--

INSERT INTO `approved` (`RequestID`, `fullname`, `documenttype`, `purpose`, `dateRequested`, `status`, `action`) VALUES
(1, 'Gerald Louis Sumaylo', 'Barangay Clearance', 'Employment', '2025-12-26', 'completed', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `archivetbl`
--

CREATE TABLE `archivetbl` (
  `ArchiveID` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `daterequested` date NOT NULL,
  `documenttype` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `reason` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archivetbl`
--

INSERT INTO `archivetbl` (`ArchiveID`, `fullname`, `daterequested`, `documenttype`, `status`, `reason`) VALUES
(1, 'Gerald Louis Sumaylo', '2025-12-26', 'Certificate of Residency', 'cancelled', 'Cancelled by user'),
(2, 'Rodney Odiamar', '2025-12-26', 'Certificate of Residency', 'declined', 'Invalid information');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `messageID` int(11) NOT NULL,
  `Fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contactnumber` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `dateSent` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pending_requests`
--

CREATE TABLE `pending_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `date_requested` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `date_processed` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_requests`
--

INSERT INTO `pending_requests` (`id`, `user_id`, `fullname`, `document_type`, `purpose`, `notes`, `date_requested`, `status`, `date_processed`, `processed_by`, `remarks`, `created_at`, `updated_at`) VALUES
(6, 9885, 'Rodney Odiamar', 'Certificate of Residency', 'Government ID', 'hehe', '2025-12-26 10:00:45', 'pending', NULL, NULL, NULL, '2025-12-26 09:00:45', '2025-12-26 09:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `released`
--

CREATE TABLE `released` (
  `RequestID` int(11) NOT NULL,
  `fullname` int(11) NOT NULL,
  `document` int(11) NOT NULL,
  `purpose` int(11) NOT NULL,
  `daterequested` int(11) NOT NULL,
  `datereleased` int(11) NOT NULL,
  `Id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `action` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `resident_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `date_of_birth` date NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated') NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`resident_id`, `full_name`, `date_of_birth`, `sex`, `civil_status`, `address`, `contact_number`, `created_at`) VALUES
(1, 'LARA KATHERINE S. CANALES', '2002-05-14', 'Female', 'Single', 'Purok 1, Barangay New Era', '09171234567', '2025-12-26 08:46:38'),
(2, 'JOHN DOMINIC D. CANILLO', '2001-09-22', 'Male', 'Single', 'Purok 2, Barangay New Era', '09182345678', '2025-12-26 08:46:38'),
(3, 'SIRPOLO L. ECIJA', '1998-03-10', 'Male', 'Married', 'Purok 3, Barangay New Era', '09293456789', '2025-12-26 08:46:38'),
(4, 'KIRSTEN KHATE G. MIRAL', '2003-11-05', 'Female', 'Single', 'Purok 1, Barangay New Era', '09304567890', '2025-12-26 08:46:38'),
(5, 'RODNEY P. ODIAMAR', '1995-07-18', 'Male', 'Married', 'Purok 4, Barangay New Era', '09415678901', '2025-12-26 08:46:38'),
(6, 'GERALD LOUIS F. SUMAYLO', '2000-01-25', 'Male', 'Single', 'Purok 2, Barangay New Era', '09526789012', '2025-12-26 08:46:38');

-- --------------------------------------------------------

--
-- Table structure for table `superadmin`
--

CREATE TABLE `superadmin` (
  `id` int(255) NOT NULL,
  `employeeID` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `FirstName` varchar(255) NOT NULL,
  `MiddleName` varchar(255) NOT NULL,
  `Suffix` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `verificationcode` varchar(255) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `superadmin`
--

INSERT INTO `superadmin` (`id`, `employeeID`, `LastName`, `FirstName`, `MiddleName`, `Suffix`, `Email`, `profile_picture`, `Password`, `verificationcode`, `birthdate`, `age`) VALUES
(1, 'SPA37552', 'ACCOUNT', 'MASTER', 'MWEHEHE', '', 'geraldlouis.sumaylo@cvsu.edu.ph', 'uploads/superadminprofiles/superadmin_1_1766742747.jpeg', 'R8$kM!2pQ', '995999', NULL, 0),
(2, 'SPA87666', 'ADMIN', 'SUPER', 'R.', '', 'soulimaxxi@gmail.com', '', '@L3pM8!Zq', '175654', '2007-02-10', 18);

-- --------------------------------------------------------

--
-- Table structure for table `userrequest`
--

CREATE TABLE `userrequest` (
  `RequestID` int(11) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255) NOT NULL,
  `suffix` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `address` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `censusnumber` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contactnumber` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verificationCode` int(11) NOT NULL,
  `dateRequested` datetime DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usertbl`
--

CREATE TABLE `usertbl` (
  `UserID` int(11) NOT NULL,
  `UID` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `FirstName` varchar(255) NOT NULL,
  `MiddleName` varchar(255) NOT NULL,
  `Suffix` varchar(255) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `Age` int(11) NOT NULL,
  `ContactNumber` int(11) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `CensusNumber` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `status` enum('active','blocked') DEFAULT 'active',
  `profile_picture` varchar(255) NOT NULL,
  `document` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `dateRequested` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertbl`
--

INSERT INTO `usertbl` (`UserID`, `UID`, `LastName`, `FirstName`, `MiddleName`, `Suffix`, `birthdate`, `Age`, `ContactNumber`, `Address`, `CensusNumber`, `email`, `Password`, `status`, `profile_picture`, `document`, `purpose`, `notes`, `dateRequested`) VALUES
(1, 'UID-5106', 'Sumaylo', 'Gerald Louis', 'F.', '', '0000-00-00', 20, 2147483647, 'Blk 56-7 New Era Dasmarinas City,Cavite', '1234', 'gewaosumaylo@gmail.com', '7m!PZ@K2a', 'active', 'uploads/profiles/user_UID-5106_1766737599.jpg', '', '', '', NULL),
(2, 'UID-9885', 'Odiamar', 'Rodney', 'P.', '', '0000-00-00', 21, 2147483647, 'Purok 4, Barangay New Era', '1235', 'feaheaven@gmail.com', 'Q#5A!r9Sx', 'active', 'uploads/profiles/user_UID-9885_1766739322.jpg', '', '', '', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_user_role` (`user_role`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `adminrequests`
--
ALTER TABLE `adminrequests`
  ADD PRIMARY KEY (`RequestID`);

--
-- Indexes for table `admintbl`
--
ALTER TABLE `admintbl`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `approved`
--
ALTER TABLE `approved`
  ADD PRIMARY KEY (`RequestID`);

--
-- Indexes for table `archivetbl`
--
ALTER TABLE `archivetbl`
  ADD PRIMARY KEY (`ArchiveID`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`messageID`);

--
-- Indexes for table `pending_requests`
--
ALTER TABLE `pending_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_requested` (`date_requested`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`resident_id`);

--
-- Indexes for table `superadmin`
--
ALTER TABLE `superadmin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `userrequest`
--
ALTER TABLE `userrequest`
  ADD PRIMARY KEY (`RequestID`);

--
-- Indexes for table `usertbl`
--
ALTER TABLE `usertbl`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `adminrequests`
--
ALTER TABLE `adminrequests`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admintbl`
--
ALTER TABLE `admintbl`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `approved`
--
ALTER TABLE `approved`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `archivetbl`
--
ALTER TABLE `archivetbl`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pending_requests`
--
ALTER TABLE `pending_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `superadmin`
--
ALTER TABLE `superadmin`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `userrequest`
--
ALTER TABLE `userrequest`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `usertbl`
--
ALTER TABLE `usertbl`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
