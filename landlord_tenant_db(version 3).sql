-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2025 at 12:58 PM
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
-- Database: `landlord_tenant_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddLandlord` (IN `p_fullName` VARCHAR(100), IN `p_passwordTxt` VARCHAR(255), IN `p_phone` VARCHAR(15))   BEGIN
  INSERT INTO user_tbl (full_name, password, phone_no)
  VALUES (p_fullName, p_passwordTxt, p_phone);
  INSERT INTO user_role_tbl (role_id, user_id, role_type)
  VALUES (1, LAST_INSERT_ID(), '1');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddLease` (IN `p_user_id` INT, IN `p_unit_id` INT, IN `p_balance` INT, IN `p_status` ENUM('Pending','Active','Terminated'))   BEGIN
  INSERT INTO lease_tbl (user_id, unit_id, lease_start_date, lease_end_date, balance, lease_status)
  VALUES (p_user_id, p_unit_id, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), p_balance, p_status);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddMaintenanceRequest` (IN `p_unit_id` INT, IN `p_user_id` INT, IN `p_description` VARCHAR(300))   BEGIN
  INSERT INTO maintenance_tbl (unit_id, user_id, description, maintenance_start_date, maintenance_status)
  VALUES (p_unit_id, p_user_id, p_description, NOW(), 'ongoing');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddPaymentRecord` (IN `p_lease_id` INT, IN `p_user_id` INT, IN `p_unit_id` INT, IN `p_receipt_upload` VARCHAR(255), IN `p_status` ENUM('confirmed','ongoing','late'))   BEGIN
  INSERT INTO payment_tbl (lease_id, user_id, unit_id, payment_date, receipt_upload, payment_status)
  VALUES (p_lease_id, p_user_id, p_unit_id, NOW(), p_receipt_upload, p_status);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddProperty` (IN `p_owner_id` INT, IN `p_prop_name` VARCHAR(100), IN `p_prop_location` VARCHAR(255))   BEGIN
  INSERT INTO property_tbl (user_id, property_name, location)
  VALUES (p_owner_id, p_prop_name, p_prop_location);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddTenant` (IN `p_fullName` VARCHAR(100), IN `p_passwordTxt` VARCHAR(255), IN `p_phone` VARCHAR(15))   BEGIN
  INSERT INTO user_tbl (full_name, password, phone_no)
  VALUES (p_fullName, p_passwordTxt, p_phone);
  INSERT INTO user_role_tbl (role_id, user_id, role_type)
  VALUES (2, LAST_INSERT_ID(), '2');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddUnit` (IN `p_property_id` INT, IN `p_user_id` INT, IN `p_rent` INT)   BEGIN
  INSERT INTO unit_tbl (property_id, user_id, rent)
  VALUES (p_property_id, p_user_id, p_rent);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ConfirmPaymentStatus` (IN `p_payment_id` INT)   BEGIN
  UPDATE payment_tbl
  SET payment_status = 'confirmed'
  WHERE payment_id = p_payment_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteLandlord` (IN `p_landlordId` INT)   BEGIN
  DELETE FROM user_role_tbl WHERE user_id = p_landlordId;
  DELETE FROM user_tbl WHERE user_id = p_landlordId;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteProperty` (IN `p_prop_id` INT)   BEGIN
  DELETE FROM property_tbl WHERE property_id = p_prop_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteTenant` (IN `p_tenantId` INT)   BEGIN
  DELETE FROM user_role_tbl WHERE user_id = p_tenantId;
  DELETE FROM user_tbl WHERE user_id = p_tenantId;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteUnit` (IN `p_unit_id` INT)   BEGIN
  DELETE FROM unit_tbl WHERE unit_id = p_unit_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ExpireOldOTPs` ()   BEGIN
  UPDATE otp_tbl
  SET status = 'Expired'
  WHERE expiration_time < NOW()
    AND status = 'Active';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GenerateOTP` (IN `p_user_id` INT)   BEGIN
  DECLARE p_otp_code VARCHAR(5);
  SET p_otp_code = LPAD(FLOOR(RAND() * 1000000), 5, '0');
  INSERT INTO otp_tbl (user_id, otp_code, expiration_time, status)
  VALUES (p_user_id, p_otp_code, DATE_ADD(NOW(), INTERVAL 2 MINUTE), 'Active');
  SELECT p_otp_code AS generated_otp;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetLeasesByTenantOrUnit` (IN `p_user_id` INT, IN `p_unit_id` INT)   BEGIN
  SELECT * FROM lease_tbl
  WHERE user_id = p_user_id OR unit_id = p_unit_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMaintenanceRequests` (IN `p_user_id` INT)   BEGIN
  SELECT * FROM maintenance_tbl WHERE user_id = p_user_id ORDER BY maintenance_start_date DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMessagesByUser` (IN `p_user_id` INT)   BEGIN
  SELECT * FROM message_tbl WHERE user_id = p_user_id ORDER BY date_sent DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPaymentsByUser` (IN `p_user_id` INT)   BEGIN
  SELECT * FROM payment_tbl WHERE user_id = p_user_id ORDER BY payment_date DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPropertiesByLandlord` (IN `p_user_id` INT)   BEGIN
  SELECT * FROM property_tbl WHERE user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUnitsByProperty` (IN `p_property_id` INT)   BEGIN
  SELECT * FROM unit_tbl WHERE property_id = p_property_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ReinstateLandlord` (IN `p_landlordId` INT)   BEGIN
  UPDATE user_tbl
  SET password = REPLACE(password, 'SUSPENDED_', '')
  WHERE user_id = p_landlordId
  AND password LIKE 'SUSPENDED_%';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SendMessageToUser` (IN `p_unit_id` INT, IN `p_user_id` INT, IN `p_message` VARCHAR(300))   BEGIN
  INSERT INTO message_tbl (unit_id, user_id, message, date_sent, message_status)
  VALUES (p_unit_id, p_user_id, p_message, NOW(), 'Pending');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SuspendLandlord` (IN `p_landlordId` INT)   BEGIN
  UPDATE user_tbl
  SET password = CONCAT('SUSPENDED_', password)
  WHERE user_id = p_landlordId;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TerminateLease` (IN `p_lease_id` INT)   BEGIN
  UPDATE lease_tbl
  SET lease_status = 'Terminated',
      lease_end_date = NOW()
  WHERE lease_id = p_lease_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateLandlord` (IN `p_landlordId` INT, IN `p_fullName` VARCHAR(100), IN `p_passwordTxt` VARCHAR(255), IN `p_phone` VARCHAR(15))   BEGIN
  UPDATE user_tbl
  SET full_name = p_fullName,
      password = p_passwordTxt,
      phone_no = p_phone
  WHERE user_id = p_landlordId;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateLease` (IN `p_lease_id` INT, IN `p_balance` INT, IN `p_status` ENUM('Pending','Active','Terminated'))   BEGIN
  UPDATE lease_tbl
  SET balance = p_balance,
      lease_status = p_status
  WHERE lease_id = p_lease_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateMaintenanceStatus` (IN `p_request_id` INT, IN `p_status` ENUM('ongoing','completed','rejected'))   BEGIN
  UPDATE maintenance_tbl
  SET maintenance_status = p_status,
      maintenance_end_date = NOW()
  WHERE request_id = p_request_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateProperty` (IN `p_prop_id` INT, IN `p_prop_name` VARCHAR(100), IN `p_prop_location` VARCHAR(255))   BEGIN
  UPDATE property_tbl
  SET property_name = p_prop_name,
      location = p_prop_location
  WHERE property_id = p_prop_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateTenant` (IN `p_tenantId` INT, IN `p_fullName` VARCHAR(100), IN `p_passwordTxt` VARCHAR(255), IN `p_phone` VARCHAR(15))   BEGIN
  UPDATE user_tbl
  SET full_name = p_fullName,
      password = p_passwordTxt,
      phone_no = p_phone
  WHERE user_id = p_tenantId;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUnit` (IN `p_unit_id` INT, IN `p_property_id` INT, IN `p_user_id` INT, IN `p_rent` INT)   BEGIN
  UPDATE unit_tbl
  SET property_id = p_property_id,
      user_id = p_user_id,
      rent = p_rent
  WHERE unit_id = p_unit_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserProfile` (IN `p_user_id` INT, IN `p_full_name` VARCHAR(100), IN `p_password` VARCHAR(255), IN `p_phone_no` VARCHAR(15))   BEGIN
  UPDATE user_tbl
  SET full_name = p_full_name,
      password = p_password,
      phone_no = p_phone_no
  WHERE user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UseOTP` (IN `p_otp_id` INT)   BEGIN
  UPDATE otp_tbl
  SET status = 'Used'
  WHERE otp_id = p_otp_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerifyOTP` (IN `p_user_id` INT, IN `p_otp_code` VARCHAR(6))   BEGIN
  SELECT otp_id, user_id, otp_code, expiration_time, status
  FROM otp_tbl
  WHERE user_id = p_user_id
    AND otp_code = p_otp_code
    AND status = 'Active'
    AND expiration_time > NOW();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerifyUserLogin` (IN `p_phone` VARCHAR(11), IN `p_password` VARCHAR(255))   BEGIN
  SELECT user_id, full_name
  FROM user_tbl
  WHERE phone_no = p_phone AND password = p_password;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lease_tbl`
--

CREATE TABLE `lease_tbl` (
  `lease_id` int(11) NOT NULL,
  `lease_start_date` date NOT NULL,
  `lease_end_date` date NOT NULL,
  `balance` decimal(12,2) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lease_status` enum('Pending','Active','Terminated') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lease_tbl`
--

INSERT INTO `lease_tbl` (`lease_id`, `lease_start_date`, `lease_end_date`, `balance`, `unit_id`, `user_id`, `lease_status`) VALUES
(5, '2026-10-27', '2027-10-27', 0.00, 7, 21, 'Terminated'),
(6, '2026-10-27', '2026-10-27', 0.00, 7, 21, 'Terminated'),
(7, '2026-10-27', '2027-10-26', 0.00, 7, 21, 'Terminated'),
(8, '2026-10-27', '2027-10-27', 1000000.00, 7, 21, 'Terminated'),
(9, '2025-10-26', '2026-10-26', 0.00, 7, 21, 'Active'),
(10, '2026-10-26', '2027-10-26', 254000.00, 7, 26, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_tbl`
--

CREATE TABLE `maintenance_tbl` (
  `request_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` varchar(300) DEFAULT NULL,
  `maintenance_start_date` date DEFAULT NULL,
  `maintenance_end_date` date DEFAULT NULL,
  `maintenance_status` enum('Ongoing','Completed','Rejected') DEFAULT 'Ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_tbl`
--

CREATE TABLE `message_tbl` (
  `message_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(300) DEFAULT NULL,
  `date_sent` date DEFAULT NULL,
  `message_status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_tbl`
--

CREATE TABLE `otp_tbl` (
  `otp_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_code` varchar(5) NOT NULL,
  `expiration_time` datetime NOT NULL,
  `status` enum('Active','Used','Expired') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_tbl`
--

CREATE TABLE `payment_tbl` (
  `payment_id` int(11) NOT NULL,
  `lease_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `receipt_upload` varchar(255) DEFAULT NULL,
  `payment_status` enum('Confirmed','Ongoing','Late') DEFAULT 'Ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_tbl`
--

INSERT INTO `payment_tbl` (`payment_id`, `lease_id`, `user_id`, `unit_id`, `amount`, `payment_date`, `receipt_upload`, `payment_status`) VALUES
(15, 5, 21, 7, 7.00, '2025-10-26', '2000', ''),
(16, 5, 21, 7, 7.00, '2025-10-26', '2000', ''),
(17, 5, 21, 7, 2000.00, '2025-10-26', '1761473213_money.jpeg', 'Ongoing'),
(18, 5, 21, 7, 2000.00, '2025-10-26', '1761473225_money.jpeg', 'Ongoing'),
(19, 6, 21, 7, 5000.00, '2025-10-26', '1761474608_money.jpeg', 'Ongoing'),
(20, 6, 21, 7, 5000.00, '2025-10-26', '1761475376_money.jpeg', 'Confirmed'),
(21, 6, 21, 7, 50000.00, '2025-10-26', '1761479131_money.jpeg', 'Ongoing'),
(22, 7, 21, 7, 200000.00, '2025-10-26', '1761479525_money.jpeg', 'Ongoing'),
(23, 9, 21, 7, 10000.00, '2025-10-26', '1761479614_money.jpeg', 'Ongoing'),
(24, 9, 21, 7, 91000.00, '2025-10-26', '1761479639_money.jpeg', 'Ongoing'),
(25, 10, 26, 7, 123000.00, '2025-10-26', '1761479747_money.jpeg', 'Ongoing'),
(26, 10, 26, 7, 123000.00, '2025-10-26', '1761479755_money.jpeg', 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `property_tbl`
--

CREATE TABLE `property_tbl` (
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `property_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_tbl`
--

INSERT INTO `property_tbl` (`property_id`, `user_id`, `location`, `property_name`) VALUES
(4, 20, 'Sabang', 'GDR-2'),
(6, 20, 'Sabang', 'GDR-1'),
(7, 20, 'Sabang', 'GDR-3'),
(9, 23, 'Sabang', 'GDR-1'),
(11, 20, 'Sabang', 'GDR-5');

-- --------------------------------------------------------

--
-- Table structure for table `role_tbl`
--

CREATE TABLE `role_tbl` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_tbl`
--

INSERT INTO `role_tbl` (`role_id`, `role_name`) VALUES
(1, 'Landlord'),
(2, 'Tenant'),
(3, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `unit_tbl`
--

CREATE TABLE `unit_tbl` (
  `unit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `unit_name` varchar(100) NOT NULL,
  `rent` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit_tbl`
--

INSERT INTO `unit_tbl` (`unit_id`, `user_id`, `property_id`, `unit_name`, `rent`) VALUES
(7, 20, 6, 'A-1', 1000),
(10, 23, 9, 'A-3', 5000);

-- --------------------------------------------------------

--
-- Table structure for table `user_role_tbl`
--

CREATE TABLE `user_role_tbl` (
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_role_tbl`
--

INSERT INTO `user_role_tbl` (`role_id`, `user_id`) VALUES
(1, 19),
(1, 20),
(1, 22),
(1, 23),
(2, 21),
(2, 26),
(3, 8);

-- --------------------------------------------------------

--
-- Table structure for table `user_tbl`
--

CREATE TABLE `user_tbl` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_no` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `full_name`, `password`, `phone_no`) VALUES
(8, 'System Admin', '$2y$10$80F5se/rkY5LJMIGNphgM.zyp6oB/.sQKksPVBQeGM8MWfDdE3juO', '09999999999'),
(19, 'jet', '$2y$10$jv9fBS2mVViQcQwhIlTUluZM3cxbfHli7sVUFoYqUHQnXUBRCm9w2', '09123123123'),
(20, 'jarell', '$2y$10$HY52uYJPa3iZ8oz.bkxuQuYitNup570TR3krojiEX5XvVXVxR8vcm', '09123987123'),
(21, 'jet', '$2y$10$ZyDGz.YtvFssFkyJb5Sr5.HhNGRMlZUCj95JMt4fNI7ZHv9F5iZ3C', '09876543211'),
(22, 'kyle', '$2y$10$0z/FfSad/mArBPio9xwfXutgmjHIg7EGidmBIVHX1gb8CnjvTjbq2', '09345345345'),
(23, 'emm', '$2y$10$cIBs8xKEGpWQiOYA/LumveDZuf0zON.8C9rJmFYRFfnI4s0/Bel72', '09991112221'),
(26, 'howie', '$2y$10$U080iyYMHv0cisYOmpsmLOCFZ500APq1et0HV92Crwz59fQbDOxTS', '09777888999');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lease_tbl`
--
ALTER TABLE `lease_tbl`
  ADD PRIMARY KEY (`lease_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `maintenance_tbl`
--
ALTER TABLE `maintenance_tbl`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `message_tbl`
--
ALTER TABLE `message_tbl`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `otp_tbl`
--
ALTER TABLE `otp_tbl`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_tbl`
--
ALTER TABLE `payment_tbl`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `lease_id` (`lease_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `property_tbl`
--
ALTER TABLE `property_tbl`
  ADD PRIMARY KEY (`property_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `role_tbl`
--
ALTER TABLE `role_tbl`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `unit_tbl`
--
ALTER TABLE `unit_tbl`
  ADD PRIMARY KEY (`unit_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `user_role_tbl`
--
ALTER TABLE `user_role_tbl`
  ADD PRIMARY KEY (`role_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_tbl`
--
ALTER TABLE `user_tbl`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lease_tbl`
--
ALTER TABLE `lease_tbl`
  MODIFY `lease_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `maintenance_tbl`
--
ALTER TABLE `maintenance_tbl`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `message_tbl`
--
ALTER TABLE `message_tbl`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `otp_tbl`
--
ALTER TABLE `otp_tbl`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_tbl`
--
ALTER TABLE `payment_tbl`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `property_tbl`
--
ALTER TABLE `property_tbl`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `role_tbl`
--
ALTER TABLE `role_tbl`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `unit_tbl`
--
ALTER TABLE `unit_tbl`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_tbl`
--
ALTER TABLE `user_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lease_tbl`
--
ALTER TABLE `lease_tbl`
  ADD CONSTRAINT `lease_tbl_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_tbl` (`unit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lease_tbl_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_tbl`
--
ALTER TABLE `maintenance_tbl`
  ADD CONSTRAINT `maintenance_tbl_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_tbl` (`unit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_tbl_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `message_tbl`
--
ALTER TABLE `message_tbl`
  ADD CONSTRAINT `message_tbl_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit_tbl` (`unit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_tbl_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_tbl`
--
ALTER TABLE `otp_tbl`
  ADD CONSTRAINT `otp_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_tbl`
--
ALTER TABLE `payment_tbl`
  ADD CONSTRAINT `payment_tbl_ibfk_1` FOREIGN KEY (`lease_id`) REFERENCES `lease_tbl` (`lease_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_tbl_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_tbl_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `unit_tbl` (`unit_id`) ON DELETE CASCADE;

--
-- Constraints for table `property_tbl`
--
ALTER TABLE `property_tbl`
  ADD CONSTRAINT `property_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `unit_tbl`
--
ALTER TABLE `unit_tbl`
  ADD CONSTRAINT `unit_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `unit_tbl_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `property_tbl` (`property_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_role_tbl`
--
ALTER TABLE `user_role_tbl`
  ADD CONSTRAINT `user_role_tbl_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role_tbl` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_role_tbl_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
