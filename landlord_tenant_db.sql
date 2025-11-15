-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 09:35 AM
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
(69, 20, '123 bahay', 'teach', 'uploads/landlord_docs/user_69/land_title_691370b6a3b2a.png', 'uploads/landlord_docs/user_69/building_permit_691370b6a430b.png', 'uploads/landlord_docs/user_69/business_permit_691370b6a4796.png', 'uploads/landlord_docs/user_69/mayors_permit_691370b6a4d3b.png', 'uploads/landlord_docs/user_69/fire_safety_permit_691370b6a5223.png', 'uploads/landlord_docs/user_69/barangay_cert_691370b6a5711.png', 'uploads/landlord_docs/user_69/occupancy_permit_691370b6a5bca.png', 'uploads/landlord_docs/user_69/sanitary_permit_691370b6a6016.png', 'uploads/landlord_docs/user_69/dti_permit_691370b6a63b1.png', '2025-11-11 17:21:58', '2025-11-11 17:21:58'),
(79, 20, 'asdasdasdasd', 'asdasdasd', 'uploads/landlord_docs/user_79/land_title_69144461be3c8.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 08:25:05', '2025-11-12 08:25:05');

-- --------------------------------------------------------

--
-- Table structure for table `lease_tbl`
--

CREATE TABLE `lease_tbl` (
  `lease_id` int(11) NOT NULL,
  `lease_start_date` date NOT NULL,
  `lease_end_date` date DEFAULT NULL,
  `balance` decimal(12,2) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lease_status` enum('Pending','Active','Terminated') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lease_tbl`
--

INSERT INTO `lease_tbl` (`lease_id`, `lease_start_date`, `lease_end_date`, `balance`, `unit_id`, `user_id`, `lease_status`) VALUES
(31, '2025-11-12', NULL, 19700.00, 35, 78, 'Active');

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
(8, 'Sabang', NULL, 13.94164781, 121.16197620);

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
  `notes` text DEFAULT NULL,
  `balance_after_payment` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('Confirmed','Ongoing','Late') DEFAULT 'Ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_tbl`
--

INSERT INTO `payment_tbl` (`payment_id`, `lease_id`, `user_id`, `unit_id`, `amount`, `payment_date`, `receipt_upload`, `notes`, `balance_after_payment`, `payment_status`) VALUES
(52, 31, 78, 35, 200.00, '2025-11-12', 'uploads/receipts/receipt_lease-31_6914451c72f0e.jpeg', 'Tenant submitted payment.', 19700.00, 'Confirmed');

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
(29, 69, NULL, 8, 'GDR-1');

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
  `requested_unit_id` int(11) DEFAULT NULL,
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

INSERT INTO `tenant_info_tbl` (`user_id`, `requested_unit_id`, `birthdate`, `age`, `gender`, `email`, `id_type`, `id_number`, `id_photo`, `birth_certificate`, `tenant_photo`, `occupation`, `employer_name`, `monthly_income`, `proof_of_income`, `monthly_rent`, `emergency_name`, `emergency_contact`, `relationship`, `created_at`, `updated_at`) VALUES
(70, 32, '2004-10-10', 21, 'Male', 'lemon@gmail.com', 'Driver', '123123123', 'uploads/tenant_docs/user_70/tenant_id_photo_69140bc330426.png', 'uploads/tenant_docs/user_70/tenant_birth_certificate_69140bc330ab8.png', 'uploads/tenant_docs/user_70/tenant_photo_69140bc330e3c.png', 'utuber', 'lele', 20000.00, 'uploads/tenant_docs/user_70/tenant_proof_of_income_69140bc33121c.png', 4500.00, 'fifi', '09111222333', 'lala', '2025-11-12 04:23:31', '2025-11-12 04:23:31'),
(71, 32, '2005-10-10', 20, 'Male', 'lemon@gmail.com', 'aa', '123', 'uploads/tenant_docs/user_71/tenant_id_photo_691410bb2271a.png', 'uploads/tenant_docs/user_71/tenant_birth_certificate_691410bb22c8c.png', 'uploads/tenant_docs/user_71/tenant_photo_691410bb22f10.png', 'asd', 'asd', 1000.00, 'uploads/tenant_docs/user_71/tenant_proof_of_income_691410bb232cf.png', 1000.00, 'asdasd', '09123123', 'asdasd', '2025-11-12 04:44:43', '2025-11-12 04:44:43'),
(72, 32, '2005-01-01', 20, 'Male', 'lemon@gmail.com', 'asdasdasd', '123123', 'uploads/tenant_docs/user_72/tenant_id_photo_691415d1e602f.png', 'uploads/tenant_docs/user_72/tenant_birth_certificate_691415d1e67ad.png', 'uploads/tenant_docs/user_72/tenant_photo_691415d1e6c5d.png', 'asdasd', 'asdasd', 2000.00, NULL, 1000.00, 'asdasd', '123123123', 'asdasd', '2025-11-12 05:06:25', '2025-11-12 07:27:29'),
(73, 33, '2005-01-01', 20, 'Male', 'sean@gmail.com', 'asdasd', '123123', 'uploads/tenant_docs/user_73/tenant_id_photo_6914252576a56.png', 'uploads/tenant_docs/user_73/tenant_birth_certificate_691425257737f.png', 'uploads/tenant_docs/user_73/tenant_photo_691425257763a.png', 'asdasd', 'asdasd', 10000.00, 'uploads/tenant_docs/user_73/tenant_proof_of_income_6914252577a51.png', 100000.00, 'asdasdas', '123123123', 'asdasdasd', '2025-11-12 06:11:49', '2025-11-12 06:11:49'),
(74, 34, '2025-11-11', 0, 'Male', 'emm@gmail.com', 'asdadas', '123123', 'uploads/tenant_docs/user_74/id_photo_6914409aa540d.jpeg', 'uploads/tenant_docs/user_74/birth_certificate_691440a2b8b67.png', 'uploads/tenant_docs/user_74/tenant_photo_69143a83244ae.jpeg', '123123', 'asdasdads', 10000.00, 'uploads/tenant_docs/user_74/proof_of_income_691440ac00057.jpeg', 1000.00, 'asdasdaasd', '111111', 'asdasdasd', '2025-11-12 06:17:03', '2025-11-12 08:09:16'),
(76, 33, '2005-10-10', 20, 'Male', 'gian@gmail.com', 'asdasdasda', '123123123', 'uploads/tenant_docs/user_76/tenant_id_photo_6914419a66ef8.png', 'uploads/tenant_docs/user_76/tenant_birth_certificate_6914419a676b0.png', 'uploads/tenant_docs/user_76/tenant_photo_6914419a67aaa.png', 'asdasdasd', 'asdasdasd', 10000.00, 'uploads/tenant_docs/user_76/tenant_proof_of_income_6914419a68177.png', 10000.00, 'asdasdasd', '12312312', 'adasdad', '2025-11-12 08:13:14', '2025-11-12 08:13:14'),
(77, 33, '2005-10-11', 20, 'Male', 'gian@gmail.com', 'asdasdas', '123123', 'uploads/tenant_docs/user_77/tenant_id_photo_6914427408f6d.png', 'uploads/tenant_docs/user_77/tenant_birth_certificate_6914427409634.png', 'uploads/tenant_docs/user_77/tenant_photo_69144274098df.png', 'asdasdasd', 'asdasda', 10000.00, 'uploads/tenant_docs/user_77/tenant_proof_of_income_6914427409c07.png', 10000.00, 'asdasdasd', '123123123', 'asasdasd', '2025-11-12 08:16:52', '2025-11-12 08:16:52'),
(78, 35, '2002-11-12', 23, 'Male', 'sean@gmail.com', 'asdasdasdasd', '123123123', 'uploads/tenant_docs/user_78/tenant_id_photo_6914441002e2b.png', 'uploads/tenant_docs/user_78/tenant_birth_certificate_691444100340e.png', 'uploads/tenant_docs/user_78/tenant_photo_69144410037fa.png', 'asdasdasdasd', 'asdasdasdasd', 1000.00, 'uploads/tenant_docs/user_78/tenant_proof_of_income_6914441003c08.png', 20000.00, 'asdasdasd', '0981231231', 'asdasdasd', '2025-11-12 08:23:44', '2025-11-12 08:23:44'),
(80, 36, '2001-11-11', 24, 'Male', 'asdasdas@gmail.com', 'asdasdasd', '12313123', 'uploads/tenant_docs/user_80/tenant_id_photo_6914467b12d1c.png', 'uploads/tenant_docs/user_80/tenant_birth_certificate_6914467b13356.png', 'uploads/tenant_docs/user_80/tenant_photo_6914467b136cd.png', '123123123', '123123132', 10000.00, 'uploads/tenant_docs/user_80/tenant_proof_of_income_6914467b13ad9.png', 20000.00, 'asdasdasd', '1231231231', '123123123', '2025-11-12 08:34:03', '2025-11-12 08:34:03');

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
(35, 69, 29, 'Unit-1', 1000),
(36, 69, 29, 'Unit-2', 1000);

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
(1, 79),
(2, 78),
(2, 80),
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
(69, 'Ralph Luceroooo', '$2y$10$0IaE31ex.Anazj3iGTEa2OdqitMuzz/G4M9Bw5gdgJqonq6rwSPSG', '09664677459', 'approved', '2025-11-11 17:21:58', NULL),
(78, 'sean', '$2y$10$SbECpvI3bR7DaKpUhP4Op.g1zdjrlaonLP7Tyhc4UYBGEgijaganC', '09222222222', 'approved', '2025-11-12 08:23:44', 69),
(79, 'lele', '$2y$10$9acUOy4WAyS1Aoa3DN6ulOauVMNVi6wkQ72sGUHhZQVXEMPCkXI1W', '09333333333', 'approved', '2025-11-12 08:25:05', NULL),
(80, 'jarell', '$2y$10$iSbUeVyKPvyH/SOZleafO.AzAvg6t1hSmVhwMQMDBZ3Y6RhrV87.O', '09444444444', 'pending', '2025-11-12 08:34:03', NULL);

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
  MODIFY `lease_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `location_tbl`
--
ALTER TABLE `location_tbl`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `property_tbl`
--
ALTER TABLE `property_tbl`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `role_tbl`
--
ALTER TABLE `role_tbl`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `unit_tbl`
--
ALTER TABLE `unit_tbl`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `user_tbl`
--
ALTER TABLE `user_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

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
