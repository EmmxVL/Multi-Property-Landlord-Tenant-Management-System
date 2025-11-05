-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 04:30 AM
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
(19, '2026-10-26', '2027-10-26', 100000.00, 24, 55, 'Active'),
(22, '2025-11-04', '2025-11-26', 10000000.00, 28, 59, 'Pending'),
(23, '2025-11-04', '2025-11-11', 100000.00, 29, 60, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `location_tbl`
--

CREATE TABLE `location_tbl` (
  `location_id` int(11) NOT NULL,
  `location_name` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location_tbl`
--

INSERT INTO `location_tbl` (`location_id`, `location_name`, `address`, `latitude`, `longitude`) VALUES
(1, 'SABANG', NULL, 13.95569003, 121.16496305),
(2, 'SAAN DARATING', NULL, 13.94783033, 121.16087140),
(3, 'Batangas', NULL, 13.75569223, 121.05822588),
(4, 'Tagaytay', NULL, 14.00962957, 120.99448746);

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
  `date_sent` datetime DEFAULT current_timestamp(),
  `message_status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  `send_time` datetime DEFAULT NULL,
  `delivered_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_tbl`
--

INSERT INTO `message_tbl` (`message_id`, `unit_id`, `user_id`, `message`, `date_sent`, `message_status`, `send_time`, `delivered_time`) VALUES
(9, 13, 38, 'Hello', '2025-11-02 20:33:47', 'Pending', NULL, NULL),
(11, 13, 38, 'check yo balls', '2025-11-02 21:06:10', 'Pending', NULL, NULL),
(13, 24, 55, 'check yo balls', '2025-11-02 21:06:10', 'Pending', NULL, NULL),
(14, 13, 38, 'check yo balls', '2025-11-02 21:07:10', 'Pending', NULL, NULL),
(16, 24, 55, 'check yo balls', '2025-11-02 21:07:10', 'Pending', NULL, NULL),
(17, 13, 38, 'check yo balls', '2025-11-02 21:07:31', 'Pending', NULL, NULL),
(19, 24, 55, 'check yo balls', '2025-11-02 21:07:31', 'Pending', NULL, NULL);

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
(31, 13, 38, 13, 100000.00, '2025-10-29', '1761736240_money.jpeg', 'Confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `property_tbl`
--

CREATE TABLE `property_tbl` (
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `property_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_tbl`
--

INSERT INTO `property_tbl` (`property_id`, `user_id`, `location`, `location_id`, `property_name`) VALUES
(14, 37, 'Sabang', NULL, 'GDR-1'),
(20, 37, 'Sabang', NULL, 'GDR-2'),
(23, 57, NULL, 2, 'tae'),
(24, 57, NULL, 3, 'LEMON'),
(25, 57, NULL, 4, 'Taal');

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
-- Table structure for table `tenant_info_tbl`
--

CREATE TABLE `tenant_info_tbl` (
  `user_id` int(11) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
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
  `monthly_rent` decimal(10,2) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_info_tbl`
--

INSERT INTO `tenant_info_tbl` (`user_id`, `birthdate`, `age`, `gender`, `email`, `id_type`, `id_number`, `id_photo`, `birth_certificate`, `tenant_photo`, `occupation`, `employer_name`, `monthly_income`, `proof_of_income`, `monthly_rent`, `emergency_name`, `emergency_contact`, `relationship`, `created_at`, `updated_at`) VALUES
(41, '2010-10-10', 15, 'Male', 'lele@gmail.com', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'Monmon', 40000.00, NULL, NULL, 'Fifi', '09987987987', 'Wife', '2025-10-29 12:58:40', '2025-10-29 13:29:50'),
(50, '2020-02-01', 5, 'Male', 'rig@gmail.com', NULL, NULL, NULL, NULL, NULL, 'Exotic Dancer', 'Bato Dela Rosa', 1000000.00, NULL, NULL, 'Bruce Wayne', '09169584751', 'Sweetheart', '2025-10-29 16:12:12', '2025-10-30 13:33:55'),
(58, '2020-07-16', 5, 'Male', 'k@gmail.com', NULL, NULL, 'uploads/1762227232_5-45-4x_BSRGAN.png', 'uploads/1762227232_5-45-4x_BSRGAN.png', 'uploads/1762227232_5-45-4x_BSRGAN.png', 'swnsn', 'ndwind', 100000.00, 'uploads/1762227232_5-45-4x_BSRGAN.png', NULL, 'nkdwkdnwk', '10288121212', '1skns', '2025-11-04 03:33:52', '2025-11-04 03:33:52');

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
(23, 37, 20, 'A-1', 100000),
(24, 37, 20, 'A-2', 1000000),
(28, 57, 24, 'l1', 1000000),
(29, 57, 25, 't1', 100000);

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
(1, 57),
(2, 38),
(2, 40),
(2, 41),
(2, 50),
(2, 55),
(2, 58),
(2, 59),
(2, 60),
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
(40, 'laurel', '$2y$10$Xe9AAN2hS7lnM34faBSas.XkqC0YOPC9ay/Zku437/hvrVGH70S2W', '09333444555', 39),
(41, 'lele', '$2y$10$Oh6c8GoBOZjMp.vFfhd.OeCoJus7ESxo.ursG2zN5P0cr6V05GOLK', '09444555666', 39),
(50, 'Ryan Gosling', '$2y$10$2LPK2xt67Lg7o6ypQmVd3.LkOq444QbRMi1beUcRM4EtDJTgpHATW', '84878787872', 43),
(55, 'Sean Martin', '$2y$10$hmPWhYGp82CU0rtTO9SUrOTw2Nngvcn0OXV8FU95ilhtSs5r0/Wh6', '09065816503', 37),
(57, 'Filemon Mendoza', '$2y$10$wrLKGxjE39kU3HCeLqA.7eJemEUgvDIqW0xW7JmP3xjUBf6bWgGoC', '09166805211', NULL),
(58, 'Kiko Barzaga', '$2y$10$fLkSkVquY2MwStYKH.hZ4uf9ufAJtTbw7.fDss2d5uh6LI3nA8882', '69696969696', 57),
(59, 'John doe', '$2y$10$qzHgbMndE.HEQeuet4VnYu.2OzcuV9xPnCkDQEzcrujoX2iRms73i', '78978978978', 57),
(60, 'zelzel', '$2y$10$COcPagGiAGRPhfjHNG92u.9/VBYnOrywJkTABd88o1aYyWWlieGNW', '87984516121', 57);

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
-- Indexes for table `location_tbl`
--
ALTER TABLE `location_tbl`
  ADD PRIMARY KEY (`location_id`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_property_location` (`location_id`);

--
-- Indexes for table `role_tbl`
--
ALTER TABLE `role_tbl`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `tenant_info_tbl`
--
ALTER TABLE `tenant_info_tbl`
  ADD PRIMARY KEY (`user_id`);

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
  MODIFY `lease_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `location_tbl`
--
ALTER TABLE `location_tbl`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `maintenance_tbl`
--
ALTER TABLE `maintenance_tbl`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `message_tbl`
--
ALTER TABLE `message_tbl`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `otp_tbl`
--
ALTER TABLE `otp_tbl`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_tbl`
--
ALTER TABLE `payment_tbl`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `property_tbl`
--
ALTER TABLE `property_tbl`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `role_tbl`
--
ALTER TABLE `role_tbl`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `unit_tbl`
--
ALTER TABLE `unit_tbl`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `user_tbl`
--
ALTER TABLE `user_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

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
  ADD CONSTRAINT `fk_property_location` FOREIGN KEY (`location_id`) REFERENCES `location_tbl` (`location_id`),
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
