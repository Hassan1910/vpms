-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2025 at 07:10 PM
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
-- Database: `vpmsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parking_number` varchar(100) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `parking_number`, `vehicle_id`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(1, 5, '00012345_3', 21, '2025-06-06 15:39:03', '2025-06-25 11:24:42', 'completed', '2025-06-06 13:39:03'),
(2, 5, '00123456_2', 22, '2025-06-06 15:39:43', '2025-06-22 19:06:01', 'completed', '2025-06-06 13:39:43'),
(4, 5, '1354', 24, '2025-06-12 09:36:48', '2025-06-21 16:40:05', 'completed', '2025-06-12 07:36:49'),
(5, 5, '30', 25, '2025-06-18 09:23:57', '2025-06-25 11:23:53', 'completed', '2025-06-18 07:23:58'),
(6, 5, '00123456_2', 26, '2025-06-21 21:23:44', '2025-06-21 21:24:07', 'completed', '2025-06-21 19:23:44'),
(7, 4, '30', 27, '2025-06-23 10:43:09', '2025-06-23 12:26:37', 'completed', '2025-06-23 08:43:09'),
(8, 5, '00001354_4', 28, '2025-06-25 11:52:24', '2025-06-25 11:53:15', 'completed', '2025-06-25 09:52:24');

-- --------------------------------------------------------

--
-- Table structure for table `parking_space`
--

CREATE TABLE `parking_space` (
  `id` int(11) NOT NULL,
  `parking_number` varchar(128) DEFAULT NULL,
  `status` text DEFAULT NULL,
  `price_per_hour` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_space`
--

INSERT INTO `parking_space` (`id`, `parking_number`, `status`, `price_per_hour`) VALUES
(1, '00012345_1', 'available', '60'),
(2, '00123456_2', 'available', '50'),
(3, '00012345_3', 'available', '60'),
(4, '00001354_4', 'available', '70'),
(5, '00001234_5', 'booked', '100'),
(6, '0757172576', 'booked', '900'),
(8, '1354', 'available', '50'),
(9, '10', 'booked', '900'),
(10, '30', 'available', '12');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `mpesa_checkout_id` varchar(100) DEFAULT NULL,
  `parking_number` int(11) DEFAULT NULL,
  `amount` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `receipt_url` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `booking_id`, `mpesa_checkout_id`, `parking_number`, `amount`, `status`, `receipt_url`, `remarks`, `created_at`) VALUES
(1, 1, NULL, 10, '30.00', 'paid', '', 'Payment received', '2025-06-14 11:42:39'),
(2, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:8080/users/receipt.php?pk=2', NULL, NULL),
(3, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:8080/users/receipt.php?pk=3', NULL, NULL),
(4, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:8080/users/receipt.php?pk=4', NULL, NULL),
(5, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:8080/users/receipt.php?pk=5', NULL, NULL),
(6, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:8080/users/receipt.php?pk=6', NULL, NULL),
(7, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:8080/users/receipt.php?pk=7', NULL, NULL),
(8, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:8080/users/receipt.php?pk=8', NULL, NULL),
(9, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:8080/users/receipt.php?pk=9', NULL, NULL),
(10, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=10', NULL, NULL),
(11, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=11', NULL, NULL),
(12, 2, 'ws_CO_21062025171715508757172576', 2, '18050', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=12', NULL, NULL),
(13, 2, NULL, 2, '18050', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=13', NULL, NULL),
(14, 2, 'ws_CO_21062025172028037757172576', 2, '18050', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=14', NULL, NULL),
(15, 2, 'ws_CO_21062025172022266757172576', 2, '18050', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=15', NULL, NULL),
(16, 2, 'ws_CO_21062025172346459757172576', 2, '18050', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=16', NULL, NULL),
(17, 2, 'ws_CO_21062025173643622757172576', 2, '18050', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=17', NULL, NULL),
(18, 4, 'ws_CO_21062025174228790757172576', 8, '10', 'paid', 'NLJ7RT61SV', 'The service request is processed successfully.', NULL),
(19, 1, 'ws_CO_21062025192835772757172576', 3, '21780', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=19', NULL, NULL),
(20, 6, 'ws_CO_21062025222426398757172576', 2, '50', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=20', NULL, NULL),
(21, 2, 'ws_CO_22062025200433479757172576', 2, '19400', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=21', NULL, NULL),
(22, 2, 'ws_CO_22062025200603584757172576', 2, '19400', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=22', NULL, NULL),
(23, 2, 'ws_CO_22062025200839869757172576', 2, '19400', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=23', NULL, NULL),
(24, 2, NULL, 2, '19400', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=24', NULL, NULL),
(25, 2, 'ws_CO_22062025200933776757172576', 2, '19400', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=25', NULL, NULL),
(26, 7, 'ws_CO_23062025114549692757172576', 10, '12', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=26', NULL, NULL),
(27, 7, 'ws_CO_23062025132640672757172576', 10, '24', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=27', NULL, NULL),
(28, 5, NULL, 10, '2040', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=28', NULL, NULL),
(29, 1, NULL, 3, '27120', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=29', NULL, NULL),
(30, 1, NULL, 3, '27120', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=30', NULL, NULL),
(31, 1, NULL, 3, '27120', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=31', NULL, NULL),
(32, 1, NULL, 3, '27120', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=32', NULL, NULL),
(33, 1, NULL, 3, '27120', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=33', NULL, NULL),
(34, 1, NULL, 3, '27120', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=34', NULL, NULL),
(35, 8, NULL, 4, '70', 'pending', 'http://127.0.0.1:80/vpms/users/receipt.php?pk=35', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `ID` int(10) NOT NULL,
  `AdminName` varchar(120) DEFAULT NULL,
  `UserName` varchar(120) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Password` varchar(120) DEFAULT NULL,
  `AdminRegdate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`ID`, `AdminName`, `UserName`, `MobileNumber`, `Email`, `Password`, `AdminRegdate`) VALUES
(1, 'Admin', 'admin', 7898799798, 'tester1@gmail.com', 'f925916e2754e5e03f75dd58a5733251', '2024-05-01 05:38:23');

-- --------------------------------------------------------

--
-- Table structure for table `tblcategory`
--

CREATE TABLE `tblcategory` (
  `ID` int(10) NOT NULL,
  `VehicleCat` varchar(120) DEFAULT NULL,
  `CreationDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblcategory`
--

INSERT INTO `tblcategory` (`ID`, `VehicleCat`, `CreationDate`) VALUES
(1, 'Four Wheeler Vehicle', '2024-05-03 11:06:50'),
(2, 'Two Wheeler Vehicle', '2024-05-03 11:06:50'),
(4, 'Bicycles', '2024-05-03 11:06:50'),
(6, 'Electric Vehicle', '2024-08-16 06:41:40'),
(10, 'motor vehicle', '2025-03-28 10:34:06'),
(11, 'four wheeled vehicle', '2025-05-01 13:17:40'),
(12, 'sixteen wheeled vehicle', '2025-05-09 10:10:35');

-- --------------------------------------------------------

--
-- Table structure for table `tblparkingspaces`
--

CREATE TABLE `tblparkingspaces` (
  `SpaceID` int(10) NOT NULL,
  `SpaceNumber` varchar(50) NOT NULL,
  `Status` enum('Available','Occupied') DEFAULT 'Available',
  `VehicleID` int(10) DEFAULT NULL,
  `CreationDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblparkingspaces`
--

INSERT INTO `tblparkingspaces` (`SpaceID`, `SpaceNumber`, `Status`, `VehicleID`, `CreationDate`) VALUES
(1, 'A1', 'Available', NULL, '2025-03-08 10:42:58'),
(2, 'A2', 'Available', NULL, '2025-03-08 10:42:58'),
(3, 'B1', 'Available', NULL, '2025-03-08 10:42:58'),
(4, 'B2', 'Occupied', NULL, '2025-03-08 10:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `tblpayments`
--

CREATE TABLE `tblpayments` (
  `PaymentID` int(10) NOT NULL,
  `VehicleID` int(10) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentDate` timestamp NULL DEFAULT current_timestamp(),
  `PaymentMethod` varchar(50) DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblregusers`
--

CREATE TABLE `tblregusers` (
  `ID` int(5) NOT NULL,
  `FirstName` varchar(250) DEFAULT NULL,
  `LastName` varchar(250) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(250) DEFAULT NULL,
  `Password` varchar(250) DEFAULT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblregusers`
--

INSERT INTO `tblregusers` (`ID`, `FirstName`, `LastName`, `MobileNumber`, `Email`, `Password`, `RegDate`) VALUES
(2, 'Anuj', 'Kumar', 1234567890, 'ak@gmail.com', 'f925916e2754e5e03f75dd58a5733251', '2024-06-01 18:05:56'),
(3, 'prince', 'wilson', 757172576, 'princewilson@gmail.com', '25d55ad283aa400af464c76d713c07ad', '2025-03-08 11:51:20'),
(4, 'Chris', 'Brown', 766512555, 'chrisbrown@gmail.com', '25d55ad283aa400af464c76d713c07ad', '2025-03-26 20:35:51'),
(5, 'Anastacia', 'Jenner', 766512523, 'anastaciajenner@gmail.com', '25d55ad283aa400af464c76d713c07ad', '2025-03-26 20:41:56'),
(6, 'Victor', 'Austin', 709305656, 'keithaustin6@gmail.com', '25d55ad283aa400af464c76d713c07ad', '2025-03-26 20:44:20');

-- --------------------------------------------------------

--
-- Table structure for table `tblvehicle`
--

CREATE TABLE `tblvehicle` (
  `ID` int(10) NOT NULL,
  `ParkingNumber` varchar(120) DEFAULT NULL,
  `VehicleCategory` varchar(120) NOT NULL,
  `VehicleCompanyname` varchar(120) DEFAULT NULL,
  `RegistrationNumber` varchar(120) DEFAULT NULL,
  `OwnerName` varchar(120) DEFAULT NULL,
  `OwnerContactNumber` bigint(10) DEFAULT NULL,
  `InTime` timestamp NULL DEFAULT current_timestamp(),
  `OutTime` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `ParkingCharge` varchar(120) NOT NULL,
  `Remark` mediumtext NOT NULL,
  `Status` varchar(5) NOT NULL,
  `SpaceID` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblvehicle`
--

INSERT INTO `tblvehicle` (`ID`, `ParkingNumber`, `VehicleCategory`, `VehicleCompanyname`, `RegistrationNumber`, `OwnerName`, `OwnerContactNumber`, `InTime`, `OutTime`, `ParkingCharge`, `Remark`, `Status`, `SpaceID`) VALUES
(1, '125061388', 'Electric Vehicle', 'Tata Nexon', 'DL8CAS1234', 'Amit', 1233211230, '2024-08-16 06:42:36', '2024-08-16 06:43:43', '50', 'NA', 'Out', NULL),
(2, '787303637', 'Two Wheeler Vehicle', 'Honda Actvia', 'UP81AN4851', 'Anuj kumar', 1234567890, '2024-08-16 06:47:23', '2024-08-16 06:48:26', '20', 'NA', 'Out', NULL),
(4, '737183894', 'Two Wheeler Vehicle', 'Austria', '23456789', 'Jesse von', 757172576, '2025-03-08 10:27:34', '2025-03-08 10:28:55', '300', 'no comment', 'Out', NULL),
(5, '768530829', 'Two Wheeler Vehicle', 'mazda', '757172576', 'prince wilson', 757172576, '2025-03-08 11:55:47', '2025-03-26 20:34:08', '300', 'served', 'Out', NULL),
(6, '680322176', 'Two Wheeler Vehicle', 'Austria', '42556154', 'Chris Brown', 766512555, '2025-03-26 20:39:28', NULL, '', '', '', NULL),
(7, '197337503', 'Four Wheeler Vehicle', 'Lexus', '45555555', 'Anastacia Jenner', 766512523, '2025-03-26 20:49:35', '2025-06-06 14:36:13', '1', 'n/a', 'Out', NULL),
(8, '353187074', 'Two Wheeler Vehicle', 'Jaguar', '23456789', 'Owen Francis', 709305656, '2025-03-26 20:51:06', NULL, '', '', '', NULL),
(9, '366641723', 'Electric Vehicle', 'ftghfdd', 'kbc', 'zxcv', 2345, '2025-03-28 10:35:56', '2025-03-28 10:37:32', '1000', 'served', 'Out', NULL),
(10, '835081418', 'Four Wheeler Vehicle', 'mazda', '23514355', 'Anastacia Jenner', 757172576, '2025-05-01 19:48:45', NULL, '', '', '', NULL),
(11, '449228498', 'Four Wheeler Vehicle', 'mazda', '23514355', 'Anastacia Jenner', 766512523, '2025-05-09 10:19:09', NULL, '', '', '', NULL),
(12, '329907273', 'Two Wheeler Vehicle', 'Austria', '123456', 'Chris Brown', 701554121, '2025-05-10 16:11:10', NULL, '', '', '', NULL),
(13, '616328066', 'Two Wheeler Vehicle', 'lamborgini', 'kbc122R', 'Anastacia Jenner', 701554121, '2025-05-16 09:47:09', NULL, '', '', '', NULL),
(14, '00012345_3', 'Two Wheeler Vehicle', 'Austria', '23456789', 'Anastacia Jenner', 701554121, '2025-05-25 11:32:33', NULL, '', '', '', NULL),
(15, '00012345_3', 'Two Wheeler Vehicle', 'Austria', '23456789', 'Anastacia Jenner', 701554121, '2025-05-25 11:34:15', NULL, '', '', '', NULL),
(16, '00012345_3', 'Two Wheeler Vehicle', 'Austria', '23456789', 'Anastacia Jenner', 701554121, '2025-05-25 11:34:22', NULL, '', '', '', NULL),
(17, '00123456_2', 'Four Wheeler Vehicle', 'mazda', '23456789', 'Anastacia Jenner', 757172576, '2025-05-25 11:46:32', NULL, '', '', '', NULL),
(18, '00123456_2', 'Four Wheeler Vehicle', 'mazda', '23456789', 'Anastacia Jenner', 757172576, '2025-05-25 12:38:43', NULL, '', '', '', NULL),
(19, '00012345_1', 'Four Wheeler Vehicle', 'subaru', '23456789', 'Anastacia Jenner', 701554121, '2025-06-03 20:09:37', '2025-06-23 07:45:16', '300', 'ok well paid', 'Out', NULL),
(20, '00012345_3', 'Two Wheeler Vehicle', 'subaru', 'kbc122R', 'Anastacia ', 701554121, '2025-06-06 13:26:53', NULL, '', '', '', NULL),
(21, '00012345_3', 'Two Wheeler Vehicle', 'subaru', 'kbc122R', 'Anastacia ', 701554121, '2025-06-06 13:39:03', NULL, '', '', '', NULL),
(22, '00123456_2', 'Four Wheeler Vehicle', 'Austria', 'Kcd233p', 'Jesse von', 701554121, '2025-06-06 13:39:43', '2025-06-23 08:14:46', '5', 'ok', 'Out', NULL),
(24, '1354', 'Electric Vehicle', 'belta', '45555555555555555555', 'Anastacia Jenner', 1234567891, '2025-06-12 07:36:48', NULL, '', '', '', NULL),
(25, '30', 'Bicycles', 'Energy', '1234', 'Jesse von', 708111111, '2025-06-18 07:23:57', NULL, '', '', '', NULL),
(26, '00123456_2', 'Four Wheeler Vehicle', 'mazda', '23514355', 'Tom Mark', 1234567891, '2025-06-21 19:23:44', NULL, '', '', '', NULL),
(27, '30', 'Four Wheeler Vehicle', 'belta', '1224356', NULL, NULL, '2025-06-23 08:43:09', NULL, '', '', '', NULL),
(28, '00001354_4', 'Electric Vehicle', 'porsche', '555555', NULL, NULL, '2025-06-25 09:52:24', NULL, '', '', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone_number` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_parking` (`parking_number`),
  ADD KEY `fk_vehicle` (`vehicle_id`);

--
-- Indexes for table `parking_space`
--
ALTER TABLE `parking_space`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parking_number` (`parking_number`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `parking_number` (`parking_number`);

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `VehicleCat` (`VehicleCat`);

--
-- Indexes for table `tblparkingspaces`
--
ALTER TABLE `tblparkingspaces`
  ADD PRIMARY KEY (`SpaceID`),
  ADD KEY `VehicleID` (`VehicleID`);

--
-- Indexes for table `tblpayments`
--
ALTER TABLE `tblpayments`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `VehicleID` (`VehicleID`);

--
-- Indexes for table `tblregusers`
--
ALTER TABLE `tblregusers`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `MobileNumber` (`MobileNumber`);

--
-- Indexes for table `tblvehicle`
--
ALTER TABLE `tblvehicle`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `SpaceID` (`SpaceID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `parking_space`
--
ALTER TABLE `parking_space`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblcategory`
--
ALTER TABLE `tblcategory`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tblparkingspaces`
--
ALTER TABLE `tblparkingspaces`
  MODIFY `SpaceID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tblpayments`
--
ALTER TABLE `tblpayments`
  MODIFY `PaymentID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblregusers`
--
ALTER TABLE `tblregusers`
  MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tblvehicle`
--
ALTER TABLE `tblvehicle`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_parking` FOREIGN KEY (`parking_number`) REFERENCES `parking_space` (`parking_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `tblregusers` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `tblvehicle` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`parking_number`) REFERENCES `parking_space` (`id`);

--
-- Constraints for table `tblparkingspaces`
--
ALTER TABLE `tblparkingspaces`
  ADD CONSTRAINT `tblparkingspaces_ibfk_1` FOREIGN KEY (`VehicleID`) REFERENCES `tblvehicle` (`ID`) ON DELETE SET NULL;

--
-- Constraints for table `tblpayments`
--
ALTER TABLE `tblpayments`
  ADD CONSTRAINT `tblpayments_ibfk_1` FOREIGN KEY (`VehicleID`) REFERENCES `tblvehicle` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tblvehicle`
--
ALTER TABLE `tblvehicle`
  ADD CONSTRAINT `tblvehicle_ibfk_1` FOREIGN KEY (`SpaceID`) REFERENCES `tblparkingspaces` (`SpaceID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
