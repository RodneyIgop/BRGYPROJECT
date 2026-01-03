-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2025 at 05:33 AM
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

--
-- Dumping data for table `adminrequests`
--

INSERT INTO `adminrequests` (`RequestID`, `lastname`, `firstname`, `middlename`, `suffix`, `birthdate`, `age`, `email`, `contactnumber`, `password`, `requestDate`) VALUES
(13, 'Odiamar', 'Rodney', 'Pantila', '', '2004-06-14', 21, 'odiamarrodney@gmail.com', 2147483647, 0, '2025-12-22');

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
(1001, 'ADM-1884', 'Miral', 'Kirsten', '', '', '2007-05-25', 18, 2147483647, 'summerlovesu49@gmail.com', 'uploads/adminprofiles/admin_1001_1766720921.jpeg', 'T123456n!', 0),
(1002, 'ADM-6742', 'Canillo', 'Rodney', 'Limbo', '', '2007-01-05', 18, 2147483647, 'rodney.odiamar@cvsu.edu.ph', '', 'C123456n!', 0);

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
(3, 'sasdasd', 'fafafsfas', 'barangay hall_background.jpg', 2025),
(4, 'hehe', 'heheheehehhe', 'barangay hall_background.jpg', 2025);

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
(1, 'Gerald Louis Fagutao Sumaylo', 'Barangay Clearance', 'Business Permit', '2025-12-20', 'approved', 'For Pick Up');

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

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`messageID`, `Fullname`, `email`, `contactnumber`, `message`, `dateSent`) VALUES
(1, 'KIRSTEN MIRAL', 'kirstenkhatemiral@gmail.com', 2147483647, 'ambaho po ng kanal dito sa amin', '2025-12-20'),
(2, 'Deo Balbuena', 'odiamarrodney@gmail.com', 958847412, 'SADASDASDASDASDAS', '2025-12-22'),
(3, 'Rodney Odiamar', 'odiamarrodney@gmail.com', 2147483647, 'dadasdsadasd', '2025-12-22'),
(4, 'luwis', 'admin@mail.com', 2147483647, 'dadsdadasda', '2025-12-22');

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
(1, 'SPA99354', 'Miral', 'Kirsten', '', '', 'summerlovesu49@gmail.com', '', 'T12345n!', '229259', '2000-05-25', 25),
(2, 'SPA36699', 'Canillo', 'Rodney', 'Limbo', '', 'rodney.odiamar@cvsu.edu.ph', 'uploads/superadminprofiles/superadmin_2_1766720015.jpg', 'DiwataPares12345!', '298149', NULL, 0);

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
  `profile_picture` varchar(255) NOT NULL,
  `document` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `dateRequested` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertbl`
--

INSERT INTO `usertbl` (`UserID`, `UID`, `LastName`, `FirstName`, `MiddleName`, `Suffix`, `birthdate`, `Age`, `ContactNumber`, `Address`, `CensusNumber`, `email`, `Password`, `profile_picture`, `document`, `purpose`, `notes`, `dateRequested`) VALUES
(8, 'UID-6208', 'Balbuena', 'Deo', 'Pares', '', '2008-12-03', 17, 2147483647, 'QUEZON CITY BESIDE PASIG RIVER', '123456', 'odiamarrodney@gmail.com', 'T123456n!', 'uploads/profiles/user_UID-6208_1766720666.png', '', '', '', NULL),
(9, 'UID-3426', 'Malupiton', 'Joel', 'Burger', '', '2012-03-01', 13, 2147483647, 'Dasma', '123456', 'rhodneyodiamar@gmail.com', 'R123456@a', '', '', '', '', NULL);

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `adminrequests`
--
ALTER TABLE `adminrequests`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `admintbl`
--
ALTER TABLE `admintbl`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1003;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `approved`
--
ALTER TABLE `approved`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `archivetbl`
--
ALTER TABLE `archivetbl`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `superadmin`
--
ALTER TABLE `superadmin`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `userrequest`
--
ALTER TABLE `userrequest`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `usertbl`
--
ALTER TABLE `usertbl`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
