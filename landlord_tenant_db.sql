-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2025 at 06:51 PM
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
-- Table structure for table `landlord_info_tbl`
--

CREATE TABLE `landlord_info_tbl` (
  `user_id` int(11) NOT NULL,
  `age` int(3) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `land_title` varchar(255) DEFAULT NULL,
  `building_permit` varchar(255) DEFAULT NULL,
  `business_permit` varchar(255) DEFAULT NULL,
  `mayors_permit` varchar(255) DEFAULT NULL,
  `fire_safety_permit` varchar(255) DEFAULT NULL,
  `barangay_cert` varchar(255) DEFAULT NULL,
  `occupancy_permit` varchar(255) DEFAULT NULL,
  `sanitary_permit` varchar(255) DEFAULT NULL,
  `dti_permit` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `landlord_info_tbl`
--

INSERT INTO `landlord_info_tbl` (`user_id`, `age`, `address`, `occupation`, `land_title`, `building_permit`, `business_permit`, `mayors_permit`, `fire_safety_permit`, `barangay_cert`, `occupancy_permit`, `sanitary_permit`, `dti_permit`, `created_at`, `updated_at`) VALUES
(69, 20, '123 bahay', 'teach', 'uploads/landlord_docs/user_69/land_title_691370b6a3b2a.png', 'uploads/landlord_docs/user_69/building_permit_691370b6a430b.png', 'uploads/landlord_docs/user_69/business_permit_691370b6a4796.png', 'uploads/landlord_docs/user_69/mayors_permit_691370b6a4d3b.png', 'uploads/landlord_docs/user_69/fire_safety_permit_691370b6a5223.png', 'uploads/landlord_docs/user_69/barangay_cert_691370b6a5711.png', 'uploads/landlord_docs/user_69/occupancy_permit_691370b6a5bca.png', 'uploads/landlord_docs/user_69/sanitary_permit_691370b6a6016.png', 'uploads/landlord_docs/user_69/dti_permit_691370b6a63b1.png', '2025-11-11 17:21:58', '2025-11-11 17:21:58');

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
(5, 'Sabang', NULL, 13.94081400, 121.16343200),
(6, 'Sabang', NULL, 13.94128336, 121.16266266);

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
(1, 69),
(3, 65);

-- --------------------------------------------------------

--
-- Table structure for table `user_tbl`
--

CREATE TABLE `user_tbl` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_no` varchar(15) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `landlord_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `full_name`, `password`, `phone_no`, `status`, `created_at`, `landlord_id`) VALUES
(65, 'System Admin', '$2y$10$ieUWWUHxL/qWk6xE/n//k.r/fzo7jGB76HWtF9UoVLgRJANg/6L8W', '09999999999', 'approved', '2025-11-11 16:18:29', NULL),
(69, 'Ralph Lucero', '$2y$10$0IaE31ex.Anazj3iGTEa2OdqitMuzz/G4M9Bw5gdgJqonq6rwSPSG', '09664677459', 'approved', '2025-11-11 17:21:58', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `landlord_info_tbl`
--
ALTER TABLE `landlord_info_tbl`
  ADD PRIMARY KEY (`user_id`);

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
  MODIFY `lease_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `location_tbl`
--
ALTER TABLE `location_tbl`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `maintenance_tbl`
--
ALTER TABLE `maintenance_tbl`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `message_tbl`
--
ALTER TABLE `message_tbl`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `otp_tbl`
--
ALTER TABLE `otp_tbl`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_tbl`
--
ALTER TABLE `payment_tbl`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `property_tbl`
--
ALTER TABLE `property_tbl`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `role_tbl`
--
ALTER TABLE `role_tbl`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `unit_tbl`
--
ALTER TABLE `unit_tbl`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user_tbl`
--
ALTER TABLE `user_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `landlord_info_tbl`
--
ALTER TABLE `landlord_info_tbl`
  ADD CONSTRAINT `fk_landlord_info_user` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
