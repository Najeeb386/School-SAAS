-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 04:54 PM
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
-- Database: `saas_sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `schoo_fee_items`
--

CREATE TABLE `schoo_fee_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `code` varchar(64) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` enum('monthly','quarterly','yearly','one_time') DEFAULT 'one_time',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schoo_fee_items`
--

INSERT INTO `schoo_fee_items` (`id`, `school_id`, `category_id`, `name`, `code`, `amount`, `billing_cycle`, `status`, `created_at`, `updated_at`) VALUES
(1, 10, 1, 'Class - 1 tuition Fee', '', 1000.00, 'monthly', 1, '2026-02-01 10:12:08', '2026-02-01 10:12:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `schoo_fee_items`
--
ALTER TABLE `schoo_fee_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_school_item_code` (`school_id`,`code`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `schoo_fee_items`
--
ALTER TABLE `schoo_fee_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
