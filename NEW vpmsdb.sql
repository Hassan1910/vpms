-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 07, 2025 at 10:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vpmsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblfeedback`
--

CREATE TABLE `tblfeedback` (
  `ID` int(10) NOT NULL,
  `UserID` int(5) NOT NULL,
  `Subject` varchar(255) NOT NULL,
  `Message` text NOT NULL,
  `Status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `Priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `Category` varchar(100) DEFAULT 'General',
  `CreatedDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedDate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `AdminID` int(10) DEFAULT NULL,
  `AdminResponse` text DEFAULT NULL,
  `AdminResponseDate` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblfeedback`
--

INSERT INTO `tblfeedback` (`ID`, `UserID`, `Subject`, `Message`, `Status`, `Priority`, `Category`, `CreatedDate`, `UpdatedDate`, `AdminID`, `AdminResponse`, `AdminResponseDate`) VALUES
(7, 9, 'test', 'test qwerty', 'Closed', 'High', 'General', '2025-08-07 04:47:16', '2025-08-07 08:24:17', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblfeedback`
--
ALTER TABLE `tblfeedback`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `AdminID` (`AdminID`),
  ADD KEY `Status` (`Status`),
  ADD KEY `Priority` (`Priority`),
  ADD KEY `idx_feedback_user_status` (`UserID`,`Status`),
  ADD KEY `idx_feedback_created_date` (`CreatedDate`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblfeedback`
--
ALTER TABLE `tblfeedback`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblfeedback`
--
ALTER TABLE `tblfeedback`
  ADD CONSTRAINT `tblfeedback_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `tblregusers` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblfeedback_ibfk_2` FOREIGN KEY (`AdminID`) REFERENCES `tbladmin` (`ID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
