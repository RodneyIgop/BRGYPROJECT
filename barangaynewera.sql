-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2026 at 09:32 AM
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
(5, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-26 12:41:42'),
(6, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-27 04:53:39'),
(7, 1, 'MASTER ACCOUNT', 'Superadmin', 'Block User', 'Block resident account - Resident: Rodney Odiamar (UID: UID-9885)', 'residentsaccounts.php', '::1', 'Successful', '2025-12-27 05:01:30'),
(8, 1, 'MASTER ACCOUNT', 'Superadmin', 'Unblock User', 'Unblock resident account - Resident: Rodney Odiamar (UID: UID-9885)', 'residentsaccounts.php', '::1', 'Successful', '2025-12-27 05:05:13'),
(9, 1, 'MASTER ACCOUNT', 'Superadmin', 'Block User', 'Block resident account - Resident: Gerald Louis Sumaylo (UID: UID-5106)', 'residentsaccounts.php', '::1', 'Successful', '2025-12-27 05:25:59'),
(10, 1, 'MASTER ACCOUNT', 'Superadmin', 'Unblock User', 'Unblock resident account - Resident: Gerald Louis Sumaylo (UID: UID-5106)', 'residentsaccounts.php', '::1', 'Successful', '2025-12-27 05:26:02'),
(11, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-27 08:58:33'),
(12, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-28 08:15:32'),
(13, 1, 'ADMIN ACCOUNT', 'Admin', 'Reject Request', 'Rejected document request for Gerald Louis Sumaylo - Document: Business Permit - Reason: Duplicate request', 'adminpending.php', '::1', 'Successful', '2025-12-28 08:15:58'),
(14, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-29 02:46:01'),
(15, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-29 06:23:53'),
(16, 4, 'TEST69 Balbuena', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-29 16:00:34'),
(17, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-30 13:29:22'),
(18, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2025-12-31 04:32:22'),
(19, 1, 'ADMIN ACCOUNT', 'Admin', 'Reject Request', 'Rejected document request for Rodney Odiamar - Document: Certificate of Residency - Reason: Duplicate request', 'adminpending.php', '::1', 'Successful', '2025-12-31 05:06:59'),
(20, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2026-01-03 05:22:45'),
(21, 1, 'ADMIN ACCOUNT', 'Admin', 'Login', 'Admin logged in successfully', 'adminLogin.php', '::1', 'Successful', '2026-01-03 07:22:34');

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
  `requestDate` datetime DEFAULT NULL,
  `status` enum('pending','read','approved','rejected') DEFAULT 'pending'
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

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `image`, `date`) VALUES
(1, 'ANNOUNCEMENT', '....', 'images (1).jpg', '2025-12-30 00:00:00'),
(2, 'IMPORTANTS', '.....', 'images (2).jpg', '2025-12-29 00:00:00'),
(3, 'URGENT', '.....', 'images (3).jpg', '2025-12-28 00:00:00');

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
(2, 'Rodney Odiamar', '2025-12-26', 'Certificate of Residency', 'declined', 'Invalid information'),
(3, 'Gerald Louis Sumaylo', '2025-12-28', 'Business Permit', 'declined', 'Duplicate request'),
(4, 'Rodney Odiamar', '2025-12-26', 'Certificate of Residency', 'declined', 'Duplicate request');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `official_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('Present','Late','Absent','On Leave') NOT NULL DEFAULT 'Present',
  `scheduled_time_in` time DEFAULT '08:00:00',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `official_id`, `date`, `time_in`, `time_out`, `status`, `scheduled_time_in`, `notes`, `created_at`, `updated_at`) VALUES
(16, 13, '2025-12-29', '13:55:41', NULL, 'Late', '08:00:00', NULL, '2025-12-29 05:55:41', '2025-12-29 05:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_settings`
--

CREATE TABLE `attendance_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_settings`
--

INSERT INTO `attendance_settings` (`id`, `setting_name`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'scheduled_time_in', '08:00:00', 'Default scheduled time in for officials', '2025-12-27 07:44:28', '2025-12-27 07:44:28'),
(2, 'grace_period_minutes', '15', 'Grace period in minutes before marking as late', '2025-12-27 07:44:28', '2025-12-27 07:44:28'),
(3, 'work_days', 'Monday,Tuesday,Wednesday,Thursday,Friday', 'Regular working days', '2025-12-27 07:44:28', '2025-12-27 07:44:28'),
(4, 'auto_time_out', '17:00:00', 'Automatic time out if not manually recorded', '2025-12-27 07:44:28', '2025-12-27 07:44:28');

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
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `notification_type` enum('resident','admin') NOT NULL,
  `original_request_id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `status` enum('unread','read','deleted') DEFAULT 'unread',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officials`
--

CREATE TABLE `officials` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `position` enum('Captain','Kagawad','Secretary','Treasurer','Tanod','Barangay Health Worker','Barangay Nutrition Scholar','Barangay Day Care Worker','SK Chairman','SK Kagawad','Other') NOT NULL,
  `gmail` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `officials`
--

INSERT INTO `officials` (`id`, `full_name`, `position`, `gmail`, `contact_number`, `created_at`, `updated_at`) VALUES
(13, 'juan dela cruz', 'Captain', 'geraldlouissumaylo@gmail.com', '09223086018', '2025-12-29 05:55:36', '2025-12-29 05:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_requests`
--

CREATE TABLE `password_reset_requests` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `verification_code` varchar(6) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_requests`
--

INSERT INTO `password_reset_requests` (`id`, `email`, `verification_code`, `reset_token`, `is_used`, `expires_at`, `created_at`) VALUES
(5, 'rhodneyodiamar@gmail.com', '964762', 'ceef497f3dc5bcd7e7daaab2f2897900492aaa0fa385d3fad89b79bc168f8770', 1, '2025-12-29 16:53:13', '2025-12-29 15:43:13'),
(6, 'rrodneyodiamar@gmail.com', '491543', 'c80dc1ae6dfc32eca484b89e67e0566a57cae6e61534234cc6738ffd28783831', 1, '2025-12-29 17:03:42', '2025-12-29 15:53:42'),
(7, 'rhodneyodiamar@gmail.com', '143897', '5603e11c557db36cee35d0d39fa196dfd511c05b3e41d89c9cbc28a33e121bda', 1, '2025-12-30 06:02:48', '2025-12-30 04:52:48'),
(9, 'rhodneyodiamar@gmail.com', '410955', '56f1bfbce24db2ab63257441203368e8d51607b69d936662f2e7bdbd267e92a2', 1, '2025-12-30 06:05:06', '2025-12-30 04:55:06'),
(10, 'rhodneyodiamar@gmail.com', '390370', '940c662359d4d5cac1c26038ab457550b8a79897125ba005d168742b27c8856a', 1, '2025-12-30 06:08:32', '2025-12-30 04:58:32'),
(11, 'rhodneyodiamar@gmail.com', '176288', '3af6a9af21a5290853a534e5e121751dc43ab0932a31e145e47647b05a409a88', 1, '2025-12-30 06:09:09', '2025-12-30 04:59:09'),
(12, 'rhodneyodiamar@gmail.com', '626343', '651cf0f8fbf69816fdb204d38fb1ef9cf02d38a946dc759538d593d0195459b1', 1, '2025-12-30 06:12:38', '2025-12-30 05:02:38'),
(13, 'rhodneyodiamar@gmail.com', '948539', 'c354b7822951ab1ffdfd888b0e2da3dd0fb5e15bc387bc587551bf89187962dd', 1, '2025-12-30 06:14:47', '2025-12-30 05:04:47');

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
(8, 9885, 'Rodney Odiamar', 'Barangay Clearance', 'Scholarship', 'TEST 69', '2025-12-30 09:34:12', 'pending', NULL, NULL, NULL, '2025-12-30 08:34:12', '2025-12-30 08:34:12'),
(9, 9885, 'Rodney Odiamar', 'Barangay Clearance', 'Business', 'testtae', '2025-12-30 12:31:21', 'pending', NULL, NULL, NULL, '2025-12-30 11:31:21', '2025-12-30 11:31:21');

-- --------------------------------------------------------

--
-- Table structure for table `rejected_admin_requests`
--

CREATE TABLE `rejected_admin_requests` (
  `id` int(11) NOT NULL,
  `RequestID` varchar(50) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contactnumber` varchar(20) DEFAULT NULL,
  `requestDate` datetime NOT NULL,
  `rejectionDate` datetime DEFAULT current_timestamp(),
  `rejectionReason` text DEFAULT NULL,
  `rejected_by` varchar(50) DEFAULT NULL,
  `status` enum('rejected','archived') DEFAULT 'rejected'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rejected_admin_requests`
--

INSERT INTO `rejected_admin_requests` (`id`, `RequestID`, `lastname`, `firstname`, `middlename`, `suffix`, `birthdate`, `age`, `email`, `contactnumber`, `requestDate`, `rejectionDate`, `rejectionReason`, `rejected_by`, `status`) VALUES
(1, '21', 'TAE', 'BURAT', 'Test', 'test', '0000-00-00', 18, 'rrodneyodiamar@gmail.com', '2147483647', '2025-12-30 14:40:51', '2026-01-03 15:16:19', NULL, 'Rodney Canillo', 'rejected'),
(2, '22', 'Canillo', 'Rodney', 'sadsas', 'test', '0000-00-00', 20, 'rhodneyodiamar@gmail.com', '2147483647', '2026-01-03 15:54:42', '2026-01-03 16:00:02', NULL, 'Rodney Canillo', 'rejected');

-- --------------------------------------------------------

--
-- Table structure for table `rejected_resident_requests`
--

CREATE TABLE `rejected_resident_requests` (
  `id` int(11) NOT NULL,
  `RequestID` varchar(50) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contactnumber` varchar(20) DEFAULT NULL,
  `requestDate` datetime NOT NULL,
  `rejectionDate` datetime DEFAULT current_timestamp(),
  `rejectionReason` text DEFAULT NULL,
  `rejected_by` varchar(50) DEFAULT NULL,
  `status` enum('rejected','archived') DEFAULT 'rejected'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rejected_resident_requests`
--

INSERT INTO `rejected_resident_requests` (`id`, `RequestID`, `lastname`, `firstname`, `middlename`, `suffix`, `birthdate`, `age`, `email`, `contactnumber`, `requestDate`, `rejectionDate`, `rejectionReason`, `rejected_by`, `status`) VALUES
(1, '28', 'Canillo', 'burat', 'Test', 'test', '0000-00-00', 18, 'odiamarrodney@gmail.com', '2147483647', '2025-12-30 14:27:17', '2026-01-03 15:20:16', NULL, 'Rodney Canillo', 'rejected'),
(2, '27', 'Canillo', 'Rodney', 'Test', 'test', '0000-00-00', 10, 'rhodneyodiamar@gmail.com', '2147483647', '2025-12-30 14:14:57', '2026-01-03 15:25:32', NULL, 'Rodney Canillo', 'rejected');

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
  `contact_number` varchar(20) DEFAULT NULL,
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
(5, 'RODNEY P. ODIAMAR', '1995-07-18', 'Male', 'Married', 'Purok 4, Barangay New Era', '09415678901', '2025-12-26 08:46:38');

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
(3, 'SPA30301', 'Canillo', 'Rodney', 'user', 'user', 'rrodneyodiamar@gmail.com', '', 'Odi!2Mar', '420653', '2018-12-13', 7),
(4, 'SPA82118', 'Canillo', 'Rodney', 'sadsas', 'test', 'rodney.odiamar@cvsu.edu.ph', '', 'Odi!2Mar', '113519', NULL, 0);

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
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('pending','read','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userrequest`
--

INSERT INTO `userrequest` (`RequestID`, `lastname`, `firstname`, `middlename`, `suffix`, `birthdate`, `address`, `age`, `censusnumber`, `email`, `contactnumber`, `password`, `verificationCode`, `dateRequested`, `profile_picture`, `status`) VALUES
(29, 'Freecs', 'Gon', 'sadsas', 'test', '0000-00-00', 'Purok 4, Barangay New Era', 18, 123456, 'odiamarrodney@gmail.com', 2147483647, 'Odi!2Mar', 125363, '2026-01-03 16:25:22', NULL, 'pending');

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
(1, 'UID-5106', 'Sumaylo', 'Gerald Louis', 'F.', '', '0000-00-00', 20, 2147483647, 'Blk 56-7 New Era Dasmarinas City,Cavite', '1234', 'gewaosumaylo@gmail.com', '7m!PZ@K2a', 'active', 'uploads/profiles/user_UID-5106_1766909648.jpg', '', '', '', NULL),
(2, 'UID-9885', 'Odiamar', 'Rodney', 'P.', '', '2004-06-14', 21, 2147483647, 'Purok 4, Barangay New Era', '1235', 'feaheaven@gmail.com', 'Q#5A!r9Sx', 'active', 'uploads/profiles/user_UID-9885_1767103200.png', '', '', '', NULL);

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
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`official_id`,`date`),
  ADD KEY `idx_attendance_date` (`date`),
  ADD KEY `idx_attendance_status` (`status`),
  ADD KEY `idx_attendance_official_date` (`official_id`,`date`);

--
-- Indexes for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`messageID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`notification_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_request_id` (`original_request_id`);

--
-- Indexes for table `officials`
--
ALTER TABLE `officials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gmail` (`gmail`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_gmail` (`gmail`);

--
-- Indexes for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `pending_requests`
--
ALTER TABLE `pending_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_requested` (`date_requested`);

--
-- Indexes for table `rejected_admin_requests`
--
ALTER TABLE `rejected_admin_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_id` (`RequestID`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rejection_date` (`rejectionDate`);

--
-- Indexes for table `rejected_resident_requests`
--
ALTER TABLE `rejected_resident_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_id` (`RequestID`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rejection_date` (`rejectionDate`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `adminrequests`
--
ALTER TABLE `adminrequests`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `admintbl`
--
ALTER TABLE `admintbl`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `officials`
--
ALTER TABLE `officials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pending_requests`
--
ALTER TABLE `pending_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rejected_admin_requests`
--
ALTER TABLE `rejected_admin_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rejected_resident_requests`
--
ALTER TABLE `rejected_resident_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `superadmin`
--
ALTER TABLE `superadmin`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `userrequest`
--
ALTER TABLE `userrequest`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `usertbl`
--
ALTER TABLE `usertbl`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`official_id`) REFERENCES `officials` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
