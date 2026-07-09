-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2025 at 07:38 PM
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
-- Database: `hobbyhive2`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_category`
--

CREATE TABLE `activity_category` (
  `CATEGORY_ID` int(11) NOT NULL,
  `TYPE_ID` int(2) NOT NULL,
  `CATEGORY_NAME` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_type`
--

CREATE TABLE `activity_type` (
  `TYPE_ID` int(2) NOT NULL,
  `TYPE_NAME` enum('SPORTS','PERFORMING ARTS') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BOOKING_ID` int(11) NOT NULL,
  `BOOKING_PLACEMENTDATE` date DEFAULT NULL,
  `BOOKING_PAX` int(3) NOT NULL,
  `BOOKING_STARTDATE` date NOT NULL,
  `BOOKING_ENDDATE` date NOT NULL,
  `BOOKING_CHOSENPRICING` enum('TRIAL CLASS','ONE MONTH PACKAGE','ONE YEAR PACKAGE') NOT NULL,
  `BOOKING_TOTALPRICE` decimal(6,2) NOT NULL,
  `IS_PAID` int(1) NOT NULL DEFAULT 0,
  `IS_DONE` int(1) NOT NULL DEFAULT 0,
  `IS_CANCELED` int(1) NOT NULL DEFAULT 0,
  `OT_ID` int(11) NOT NULL,
  `OFFER_ID` int(11) NOT NULL,
  `PARENT_USERNAME` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_payment`
--

CREATE TABLE `booking_payment` (
  `PAYMENT_ID` int(11) NOT NULL,
  `PAYMENT_AMOUNT` decimal(6,2) NOT NULL,
  `PAYMENT_TYPE` enum('PAYMENT','REFUND') NOT NULL,
  `BOOKING_ID` int(11) NOT NULL,
  `COACHWALLET_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child`
--

CREATE TABLE `child` (
  `CHILD_ID` int(11) NOT NULL,
  `PARENT_USERNAME` varchar(12) NOT NULL,
  `CHILD_NAME` varchar(50) NOT NULL,
  `CHILD_GENDER` enum('F','M') NOT NULL,
  `CHILD_AGE` int(2) NOT NULL,
  `HAS_ACTIVITY` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coach`
--

CREATE TABLE `coach` (
  `COACH_USERNAME` varchar(12) NOT NULL,
  `COACH_NAME` varchar(50) NOT NULL,
  `COACH_PASS` varchar(8) NOT NULL,
  `COACH_PHONE` varchar(20) NOT NULL,
  `COACH_EMAIL` varchar(30) DEFAULT NULL,
  `COACH_PROPIC` longblob NOT NULL,
  `COACH_GENDER` enum('F','M') DEFAULT NULL,
  `COACH_AGE` int(2) DEFAULT NULL,
  `COACH_ABOUT` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coach_accreditation`
--

CREATE TABLE `coach_accreditation` (
  `CREDIT_ID` int(11) NOT NULL,
  `CREDIT_DESC` text NOT NULL,
  `CREDIT_PIC` longblob NOT NULL,
  `COACH_USERNAME` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coach_achievement`
--

CREATE TABLE `coach_achievement` (
  `ACHIEVE_ID` int(11) NOT NULL,
  `ACHIEVE_DESC` text NOT NULL,
  `COACH_USERNAME` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coach_wallet`
--

CREATE TABLE `coach_wallet` (
  `COACHWALLET_ID` int(11) NOT NULL,
  `COACH_USERNAME` varchar(12) NOT NULL,
  `COACH_AMOUNT` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offered_activity`
--

CREATE TABLE `offered_activity` (
  `OFFER_ID` int(11) NOT NULL,
  `OFFER_NAME` varchar(30) NOT NULL,
  `OFFER_DESC` text NOT NULL,
  `OFFER_MINAGE` int(2) NOT NULL,
  `OFFER_MAXAGE` int(2) NOT NULL,
  `OFFER_STATE` enum('SELANGOR','KUALA LUMPUR','MELAKA','JOHOR','PERAK') NOT NULL,
  `OFFER_LOCATION` varchar(80) NOT NULL,
  `IS_AVAILABLE` int(1) NOT NULL DEFAULT 1,
  `COACH_USERNAME` varchar(12) NOT NULL,
  `CATEGORY_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offered_pic`
--

CREATE TABLE `offered_pic` (
  `OP_ID` int(11) NOT NULL,
  `OP_PIC` longblob NOT NULL,
  `OFFER_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offered_pricing`
--

CREATE TABLE `offered_pricing` (
  `PRICING_ID` int(11) NOT NULL,
  `PRICING_TYPE` enum('TRIAL CLASS','ONE MONTH PACKAGE','ONE YEAR PACKAGE') NOT NULL,
  `PRICE` decimal(6,2) NOT NULL,
  `OFFER_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offered_time`
--

CREATE TABLE `offered_time` (
  `OT_ID` int(11) NOT NULL,
  `OFFER_ID` int(11) NOT NULL,
  `OT_DAY` enum('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY') NOT NULL,
  `OT_STARTTIME` time NOT NULL,
  `OT_ENDTIME` time NOT NULL,
  `OT_PAX` int(3) NOT NULL,
  `OT_SLOTSLEFT` int(3) NOT NULL,
  `OT_TYPE` enum('TRIAL CLASS','ONE MONTH PACKAGE','ONE YEAR PACKAGE') NOT NULL,
  `IS_REMOVED` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parent`
--

CREATE TABLE `parent` (
  `PARENT_USERNAME` varchar(12) NOT NULL,
  `PARENT_NAME` varchar(50) NOT NULL,
  `PARENT_PASS` varchar(8) NOT NULL,
  `PARENT_PHONE` varchar(20) NOT NULL,
  `PARENT_EMAIL` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parent_trans`
--

CREATE TABLE `parent_trans` (
  `TRANS_ID` int(11) NOT NULL,
  `TRANS_TYPE` enum('RELOAD','PAYMENT','REFUND') NOT NULL,
  `TRANS_AMOUNT` decimal(6,2) NOT NULL,
  `PARENTWALLET_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parent_wallet`
--

CREATE TABLE `parent_wallet` (
  `PARENTWALLET_ID` int(11) NOT NULL,
  `PARENT_USERNAME` varchar(12) NOT NULL,
  `PARENT_AMOUNT` decimal(6,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_record`
--

CREATE TABLE `payment_record` (
  `RECORD_ID` int(11) NOT NULL,
  `TRANS_ID` int(11) NOT NULL,
  `BOOKING_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `REVIEW_ID` int(11) NOT NULL,
  `REVIEW_RATE` int(1) NOT NULL,
  `REVIEW_WRITE` text NOT NULL,
  `REVIEW_DATE` date NOT NULL,
  `BOOKING_ID` int(11) NOT NULL,
  `OFFER_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `selected_child`
--

CREATE TABLE `selected_child` (
  `SELECTED_ID` int(11) NOT NULL,
  `CHILD_ID` int(11) NOT NULL,
  `BOOKING_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_category`
--
ALTER TABLE `activity_category`
  ADD PRIMARY KEY (`CATEGORY_ID`),
  ADD KEY `TYPE_ID` (`TYPE_ID`);

--
-- Indexes for table `activity_type`
--
ALTER TABLE `activity_type`
  ADD PRIMARY KEY (`TYPE_ID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BOOKING_ID`),
  ADD KEY `OT_ID` (`OT_ID`),
  ADD KEY `OFFER_ID` (`OFFER_ID`),
  ADD KEY `PARENT_USERNAME` (`PARENT_USERNAME`);

--
-- Indexes for table `booking_payment`
--
ALTER TABLE `booking_payment`
  ADD PRIMARY KEY (`PAYMENT_ID`),
  ADD KEY `BOOKING_ID` (`BOOKING_ID`),
  ADD KEY `COACHWALLET_ID` (`COACHWALLET_ID`);

--
-- Indexes for table `child`
--
ALTER TABLE `child`
  ADD PRIMARY KEY (`CHILD_ID`),
  ADD KEY `PARENT_USERNAME` (`PARENT_USERNAME`);

--
-- Indexes for table `coach`
--
ALTER TABLE `coach`
  ADD PRIMARY KEY (`COACH_USERNAME`);

--
-- Indexes for table `coach_accreditation`
--
ALTER TABLE `coach_accreditation`
  ADD PRIMARY KEY (`CREDIT_ID`),
  ADD KEY `COACH_USERNAME` (`COACH_USERNAME`);

--
-- Indexes for table `coach_achievement`
--
ALTER TABLE `coach_achievement`
  ADD PRIMARY KEY (`ACHIEVE_ID`),
  ADD KEY `COACH_USERNAME` (`COACH_USERNAME`);

--
-- Indexes for table `coach_wallet`
--
ALTER TABLE `coach_wallet`
  ADD PRIMARY KEY (`COACHWALLET_ID`),
  ADD KEY `COACH_USERNAME` (`COACH_USERNAME`);

--
-- Indexes for table `offered_activity`
--
ALTER TABLE `offered_activity`
  ADD PRIMARY KEY (`OFFER_ID`),
  ADD KEY `COACH_USERNAME` (`COACH_USERNAME`),
  ADD KEY `CATEGORY_ID` (`CATEGORY_ID`);

--
-- Indexes for table `offered_pic`
--
ALTER TABLE `offered_pic`
  ADD PRIMARY KEY (`OP_ID`),
  ADD KEY `OFFER_ID` (`OFFER_ID`);

--
-- Indexes for table `offered_pricing`
--
ALTER TABLE `offered_pricing`
  ADD PRIMARY KEY (`PRICING_ID`),
  ADD KEY `OFFER_ID` (`OFFER_ID`);

--
-- Indexes for table `offered_time`
--
ALTER TABLE `offered_time`
  ADD PRIMARY KEY (`OT_ID`),
  ADD KEY `OFFER_ID` (`OFFER_ID`);

--
-- Indexes for table `parent`
--
ALTER TABLE `parent`
  ADD PRIMARY KEY (`PARENT_USERNAME`);

--
-- Indexes for table `parent_trans`
--
ALTER TABLE `parent_trans`
  ADD PRIMARY KEY (`TRANS_ID`),
  ADD KEY `PARENTWALLET_ID` (`PARENTWALLET_ID`);

--
-- Indexes for table `parent_wallet`
--
ALTER TABLE `parent_wallet`
  ADD PRIMARY KEY (`PARENTWALLET_ID`),
  ADD KEY `PARENT_USERNAME` (`PARENT_USERNAME`);

--
-- Indexes for table `payment_record`
--
ALTER TABLE `payment_record`
  ADD PRIMARY KEY (`RECORD_ID`),
  ADD KEY `TRANS_ID` (`TRANS_ID`),
  ADD KEY `BOOKING_ID` (`BOOKING_ID`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`REVIEW_ID`),
  ADD KEY `BOOKING_ID` (`BOOKING_ID`),
  ADD KEY `OFFER_ID` (`OFFER_ID`);

--
-- Indexes for table `selected_child`
--
ALTER TABLE `selected_child`
  ADD PRIMARY KEY (`SELECTED_ID`),
  ADD KEY `CHILD_ID` (`CHILD_ID`),
  ADD KEY `BOOKING_ID` (`BOOKING_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_category`
--
ALTER TABLE `activity_category`
  MODIFY `CATEGORY_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_type`
--
ALTER TABLE `activity_type`
  MODIFY `TYPE_ID` int(2) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `BOOKING_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_payment`
--
ALTER TABLE `booking_payment`
  MODIFY `PAYMENT_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `child`
--
ALTER TABLE `child`
  MODIFY `CHILD_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coach_accreditation`
--
ALTER TABLE `coach_accreditation`
  MODIFY `CREDIT_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coach_achievement`
--
ALTER TABLE `coach_achievement`
  MODIFY `ACHIEVE_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coach_wallet`
--
ALTER TABLE `coach_wallet`
  MODIFY `COACHWALLET_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offered_activity`
--
ALTER TABLE `offered_activity`
  MODIFY `OFFER_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offered_pic`
--
ALTER TABLE `offered_pic`
  MODIFY `OP_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offered_pricing`
--
ALTER TABLE `offered_pricing`
  MODIFY `PRICING_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offered_time`
--
ALTER TABLE `offered_time`
  MODIFY `OT_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parent_trans`
--
ALTER TABLE `parent_trans`
  MODIFY `TRANS_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parent_wallet`
--
ALTER TABLE `parent_wallet`
  MODIFY `PARENTWALLET_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_record`
--
ALTER TABLE `payment_record`
  MODIFY `RECORD_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `REVIEW_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `selected_child`
--
ALTER TABLE `selected_child`
  MODIFY `SELECTED_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_category`
--
ALTER TABLE `activity_category`
  ADD CONSTRAINT `activity_category_ibfk_1` FOREIGN KEY (`TYPE_ID`) REFERENCES `activity_type` (`TYPE_ID`);

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`OT_ID`) REFERENCES `offered_time` (`OT_ID`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`OFFER_ID`) REFERENCES `offered_activity` (`OFFER_ID`),
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`PARENT_USERNAME`) REFERENCES `parent` (`PARENT_USERNAME`);

--
-- Constraints for table `booking_payment`
--
ALTER TABLE `booking_payment`
  ADD CONSTRAINT `booking_payment_ibfk_1` FOREIGN KEY (`BOOKING_ID`) REFERENCES `booking` (`BOOKING_ID`),
  ADD CONSTRAINT `booking_payment_ibfk_2` FOREIGN KEY (`COACHWALLET_ID`) REFERENCES `coach_wallet` (`COACHWALLET_ID`);

--
-- Constraints for table `child`
--
ALTER TABLE `child`
  ADD CONSTRAINT `child_ibfk_1` FOREIGN KEY (`PARENT_USERNAME`) REFERENCES `parent` (`PARENT_USERNAME`);

--
-- Constraints for table `coach_accreditation`
--
ALTER TABLE `coach_accreditation`
  ADD CONSTRAINT `coach_accreditation_ibfk_1` FOREIGN KEY (`COACH_USERNAME`) REFERENCES `coach` (`COACH_USERNAME`);

--
-- Constraints for table `coach_achievement`
--
ALTER TABLE `coach_achievement`
  ADD CONSTRAINT `coach_achievement_ibfk_1` FOREIGN KEY (`COACH_USERNAME`) REFERENCES `coach` (`COACH_USERNAME`);

--
-- Constraints for table `coach_wallet`
--
ALTER TABLE `coach_wallet`
  ADD CONSTRAINT `coach_wallet_ibfk_1` FOREIGN KEY (`COACH_USERNAME`) REFERENCES `coach` (`COACH_USERNAME`);

--
-- Constraints for table `offered_activity`
--
ALTER TABLE `offered_activity`
  ADD CONSTRAINT `offered_activity_ibfk_1` FOREIGN KEY (`COACH_USERNAME`) REFERENCES `coach` (`COACH_USERNAME`),
  ADD CONSTRAINT `offered_activity_ibfk_2` FOREIGN KEY (`CATEGORY_ID`) REFERENCES `activity_category` (`CATEGORY_ID`);

--
-- Constraints for table `offered_pic`
--
ALTER TABLE `offered_pic`
  ADD CONSTRAINT `offered_pic_ibfk_1` FOREIGN KEY (`OFFER_ID`) REFERENCES `offered_activity` (`OFFER_ID`);

--
-- Constraints for table `offered_pricing`
--
ALTER TABLE `offered_pricing`
  ADD CONSTRAINT `offered_pricing_ibfk_1` FOREIGN KEY (`OFFER_ID`) REFERENCES `offered_activity` (`OFFER_ID`);

--
-- Constraints for table `offered_time`
--
ALTER TABLE `offered_time`
  ADD CONSTRAINT `offered_time_ibfk_1` FOREIGN KEY (`OFFER_ID`) REFERENCES `offered_activity` (`OFFER_ID`);

--
-- Constraints for table `parent_trans`
--
ALTER TABLE `parent_trans`
  ADD CONSTRAINT `parent_trans_ibfk_1` FOREIGN KEY (`PARENTWALLET_ID`) REFERENCES `parent_wallet` (`PARENTWALLET_ID`);

--
-- Constraints for table `parent_wallet`
--
ALTER TABLE `parent_wallet`
  ADD CONSTRAINT `parent_wallet_ibfk_1` FOREIGN KEY (`PARENT_USERNAME`) REFERENCES `parent` (`PARENT_USERNAME`);

--
-- Constraints for table `payment_record`
--
ALTER TABLE `payment_record`
  ADD CONSTRAINT `payment_record_ibfk_1` FOREIGN KEY (`TRANS_ID`) REFERENCES `parent_trans` (`TRANS_ID`),
  ADD CONSTRAINT `payment_record_ibfk_2` FOREIGN KEY (`BOOKING_ID`) REFERENCES `booking` (`BOOKING_ID`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`BOOKING_ID`) REFERENCES `booking` (`BOOKING_ID`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`OFFER_ID`) REFERENCES `offered_activity` (`OFFER_ID`);

--
-- Constraints for table `selected_child`
--
ALTER TABLE `selected_child`
  ADD CONSTRAINT `selected_child_ibfk_1` FOREIGN KEY (`CHILD_ID`) REFERENCES `child` (`CHILD_ID`),
  ADD CONSTRAINT `selected_child_ibfk_2` FOREIGN KEY (`BOOKING_ID`) REFERENCES `booking` (`BOOKING_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
