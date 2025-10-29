-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 02:37 PM
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
(13, '2026-10-26', '2027-10-26', 9900000.00, 13, 38, 'Active'),
(14, '2026-10-26', '2027-10-26', 1930000.00, 14, 41, 'Active');

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
(31, 13, 38, 13, 100000.00, '2025-10-29', '1761736240_money.jpeg', 'Confirmed'),
(32, 14, 41, 14, 20000.00, '2025-10-29', '1761744685_PBI_LuceroRalph.png', 'Ongoing'),
(33, 14, 41, 14, 50000.00, '2025-10-29', '1761744733_PBI_LuceroRalph.png', 'Ongoing');

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
(14, 37, 'Sabang', 'GDR-1'),
(15, 39, 'Sabang', 'GDR-1');

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
-- Table structure for table `tenant_info`
--

CREATE TABLE `tenant_info` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `id_type` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `id_photo` varchar(255) DEFAULT NULL,
  `birth_certificate` varchar(255) DEFAULT NULL,
  `tenant_photo` varchar(255) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `employer_name` varchar(100) DEFAULT NULL,
  `monthly_income` decimal(10,2) DEFAULT NULL,
  `proof_of_income` varchar(255) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent` decimal(10,2) DEFAULT NULL,
  `lease_status` enum('Active','Expired','Pending','Terminated') DEFAULT 'Pending',
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_info`
--

INSERT INTO `tenant_info` (`user_id`, `full_name`, `birthdate`, `age`, `gender`, `contact_number`, `email`, `id_type`, `id_number`, `id_photo`, `birth_certificate`, `tenant_photo`, `occupation`, `employer_name`, `monthly_income`, `proof_of_income`, `property_id`, `unit_id`, `lease_start_date`, `lease_end_date`, `monthly_rent`, `lease_status`, `emergency_name`, `emergency_contact`, `relationship`, `created_at`, `updated_at`) VALUES
(41, 'Filemon Laurel', '2010-10-10', 15, 'Male', '09444555666', 'lele@gmail.com', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'Monmon', 40000.00, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', 'Fifi', '09987987987', 'Wife', '2025-10-29 12:58:40', '2025-10-29 13:29:50');

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
(13, 37, 14, 'A-1', 100000),
(14, 39, 15, 'A-1', 100000);

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
(1, 37),
(1, 39),
(1, 42),
(2, 38),
(2, 40),
(2, 41),
(3, 8);

-- --------------------------------------------------------

--
-- Table structure for table `user_tbl`
--

CREATE TABLE `user_tbl` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_no` varchar(15) NOT NULL,
  `landlord_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `full_name`, `password`, `phone_no`, `landlord_id`) VALUES
(8, 'System Admin', '$2y$10$80F5se/rkY5LJMIGNphgM.zyp6oB/.sQKksPVBQeGM8MWfDdE3juO', '09999999999', NULL),
(37, 'Ralph Lucero', '$2y$10$9Aqxrkpy8wIdwD3xH0XOeuV18Yl00W3TJEF6cNK.SEJlS1xoiayBm', '09664677459', NULL),
(38, 'emm', '$2y$10$.LrtDjynRMXR4PwHP1NLoOiGTWROvB.14tXZkTfs/ZZO03TvHgrBO', '09936467748', 37),
(39, 'Lemon', '$2y$10$XitGcPD7PEd59FBNrjhtFOwdxH5LcLCzl8F8v/bVVu3EtMMoMU6JS', '09222333444', NULL),
(40, 'laurel', '$2y$10$Xe9AAN2hS7lnM34faBSas.XkqC0YOPC9ay/Zku437/hvrVGH70S2W', '09333444555', 39),
(41, 'lele', '$2y$10$Oh6c8GoBOZjMp.vFfhd.OeCoJus7ESxo.ursG2zN5P0cr6V05GOLK', '09444555666', 39),
(42, 'Sean Martin', '$2y$10$vD9h5QBn4.V.P3GiUdrWoONBxTfwXzUGDUcg4DwZd1YVa3LG3rsBy', '09065816503', NULL);

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
-- Indexes for table `tenant_info`
--
ALTER TABLE `tenant_info`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `unit_id` (`unit_id`);

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
  MODIFY `lease_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `property_tbl`
--
ALTER TABLE `property_tbl`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `role_tbl`
--
ALTER TABLE `role_tbl`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `unit_tbl`
--
ALTER TABLE `unit_tbl`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_tbl`
--
ALTER TABLE `user_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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
-- Constraints for table `tenant_info`
--
ALTER TABLE `tenant_info`
  ADD CONSTRAINT `tenant_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_info_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `property_tbl` (`property_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tenant_info_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `unit_tbl` (`unit_id`) ON DELETE SET NULL;

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
