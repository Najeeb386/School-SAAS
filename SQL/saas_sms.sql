-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 13, 2026 at 09:04 AM
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
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `school_id`, `name`, `email`, `password`, `role_id`, `phone`, `permissions`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(3, 10, 'umer', 'umer@gmail.com', '$2y$10$GXko8Jw5ByRYw3wkE/5EvuBRZ4fXGnDEvfsnhDdlGgethA//kjLOm', 'cashier', NULL, '[\"finance:full_control\",\"fees:full_control\"]', 1, NULL, '2026-01-28 16:14:12', '2026-01-29 04:42:28'),
(5, 10, 'najeeb', 'najeeb@gmail.com', '$2y$10$Mz4QesjpBvtxvNEgs9vcJOxEankRwP.1I1KHIoL5xnv5JeTDVUy4W', 'teacher', NULL, '[\"classes:view\",\"attendance:view\",\"exams:view\",\"announcements:view\"]', 1, NULL, '2026-01-29 04:54:34', '2026-01-29 04:54:34');

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `school_id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 10, 'electricity bill', NULL, 1, '2026-01-31 08:47:14', '2026-01-31 08:47:14'),
(3, 10, 'water', NULL, 1, '2026-01-31 08:51:26', '2026-01-31 08:51:26'),
(5, 10, 'Gas', NULL, 1, '2026-01-31 08:53:35', '2026-01-31 08:53:35'),
(6, 10, 'testing', NULL, 1, '2026-01-31 09:06:05', '2026-01-31 09:06:05'),
(7, 10, 'hello', NULL, 1, '2026-01-31 09:07:41', '2026-01-31 09:07:41'),
(8, 10, 'ter', NULL, 1, '2026-01-31 09:09:16', '2026-01-31 09:09:16');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_counters`
--

CREATE TABLE `invoice_counters` (
  `id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `session_id` bigint(20) NOT NULL,
  `prefix` varchar(20) DEFAULT 'INV',
  `current_counter` int(11) NOT NULL DEFAULT 0,
  `reset_type` enum('yearly','session') DEFAULT 'session',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_counters`
--

INSERT INTO `invoice_counters` (`id`, `school_id`, `session_id`, `prefix`, `current_counter`, `reset_type`, `created_at`, `updated_at`) VALUES
(1, 10, 1, 'INV', 22, 'session', '2026-02-04 09:15:15', '2026-02-04 17:32:43'),
(2, 10, 5, 'INV', 32, 'session', '2026-02-04 18:21:34', '2026-02-05 14:16:22');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price_per_student_year` int(11) NOT NULL,
  `hosting_type` varchar(100) NOT NULL,
  `features` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `price_per_student_year`, `hosting_type`, `features`, `status`, `created_at`) VALUES
(3, 'Student', 0, 'dedicated', 'nothing', 'active', '2026-01-22 17:11:33'),
(4, 'hh', 400, 'shared', 'nothing', 'active', '2026-01-22 17:13:03');

-- --------------------------------------------------------

--
-- Table structure for table `saas_billing_cycles`
--

CREATE TABLE `saas_billing_cycles` (
  `billing_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `discounted_amount` decimal(10,0) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('paid','partial','due','overdue') DEFAULT 'due',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saas_billing_cycles`
--

INSERT INTO `saas_billing_cycles` (`billing_id`, `school_id`, `subscription_id`, `period_start`, `period_end`, `due_date`, `total_amount`, `discounted_amount`, `paid_amount`, `status`, `created_at`) VALUES
(1, 4, 2, '2026-01-25', '2027-01-25', '2026-01-01', 30000.00, 0, 30000.00, 'paid', '2026-01-25 05:05:07'),
(2, 4, 2, '2026-01-25', '2027-01-25', '2026-01-01', 35000.00, 0, 0.00, 'due', '2026-01-25 05:05:21'),
(3, 6, 4, '2026-01-25', '2027-01-25', '2026-02-01', 109500.00, 0, 19000.00, 'partial', '2026-01-25 03:18:41'),
(4, 7, 5, '2026-01-25', '2027-01-25', '2026-02-01', 146000.00, 0, 146000.00, 'paid', '2026-01-25 04:14:10'),
(5, 8, 6, '2026-01-25', '2027-01-25', '2026-01-01', 7300.00, 365, 6935.00, 'partial', '2026-01-25 05:38:18'),
(6, 9, 7, '2026-01-26', '2027-01-26', '2026-01-02', 3650.00, 0, 0.00, 'due', '2026-01-26 01:20:06'),
(14, 1, 15, '2026-01-27', '2027-01-27', '2026-02-26', 400000.00, 40000, 360000.00, 'paid', '2026-01-27 04:25:03'),
(15, 10, 16, '2026-01-27', '2027-01-27', '2026-02-03', 120000.00, 0, 120000.00, 'partial', '2026-01-27 06:19:50');

-- --------------------------------------------------------

--
-- Table structure for table `saas_expenses`
--

CREATE TABLE `saas_expenses` (
  `expense_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('hosting','salary','marketing','maintenance','software','office','misc') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cash','bank','card','online') NOT NULL,
  `expense_date` date NOT NULL,
  `is_recurring` tinyint(1) DEFAULT 0,
  `recurring_cycle` enum('monthly','yearly') DEFAULT NULL,
  `vendor_name` varchar(150) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `status` enum('paid','pending') DEFAULT 'paid',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saas_expenses`
--

INSERT INTO `saas_expenses` (`expense_id`, `title`, `description`, `category`, `amount`, `payment_method`, `expense_date`, `is_recurring`, `recurring_cycle`, `vendor_name`, `invoice_no`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'test', 'nothing', 'marketing', 0.00, 'card', '2026-01-24', 1, 'yearly', 'ali raza', 'NA', 'pending', 1, '2026-01-24 08:30:14', '2026-01-24 09:06:55'),
(3, 'test2', 'debuging', 'maintenance', 1000.00, 'cash', '2026-01-24', 0, NULL, 'h', 'Na', 'paid', 1, '2026-01-24 09:07:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `saas_payments`
--

CREATE TABLE `saas_payments` (
  `payment_id` int(11) NOT NULL,
  `billing_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `total_amount` decimal(10,0) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` enum('cash','bank','online') DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `received_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saas_payments`
--

INSERT INTO `saas_payments` (`payment_id`, `billing_id`, `school_id`, `total_amount`, `paid_amount`, `payment_date`, `payment_method`, `reference_no`, `received_by`, `created_at`) VALUES
(1, 3, 6, 109500, 10000.00, '2026-01-25', 'cash', '', 'najeeb', '2026-01-25 07:18:41'),
(2, 4, 7, 146000, 140000.00, '2026-01-25', 'cash', '-', '-', '2026-01-25 08:14:10'),
(3, 4, 7, 146000, 6000.00, '2026-01-25', 'cash', '', 'najeeb', '2026-01-25 09:02:55'),
(4, 4, 7, 146000, 6000.00, '2026-01-25', 'cash', '-', 'najeeb', '2026-01-25 09:03:03'),
(5, 4, 7, 146000, 6000.00, '2026-01-25', 'cash', '-', 'najeeb', '2026-01-25 09:03:11'),
(6, 4, 7, 146000, 6000.00, '2026-01-25', 'cash', '-', '', '2026-01-25 09:03:25'),
(7, 4, 7, 146000, 6000.00, '2026-01-25', 'cash', '-', 'najeeb', '2026-01-25 09:03:36'),
(8, 4, 7, 146000, 6000.00, '2026-01-25', 'cash', '-', 'najeeb', '2026-01-25 09:10:10'),
(9, 3, 6, 99500, 9000.00, '2026-01-25', 'cash', '-', 'najeeb', '2026-01-25 09:15:19'),
(10, 5, 8, 6935, 6935.00, '2026-01-25', 'cash', '', 'najeeb', '2026-01-25 09:38:18'),
(11, 1, 4, 30000, 30000.00, '2026-01-26', 'cash', '', 'Najeeb Hassan', '2026-01-26 04:57:12'),
(12, 14, 1, 360000, 360000.00, '2026-01-27', 'cash', '-', 'najeeb', '2026-01-27 04:25:03'),
(13, 15, 10, 120000, 120000.00, '2026-01-27', 'cash', '', '', '2026-01-27 10:19:50');

-- --------------------------------------------------------

--
-- Table structure for table `saas_school_requests`
--

CREATE TABLE `saas_school_requests` (
  `request_id` int(11) NOT NULL,
  `school_name` varchar(150) NOT NULL,
  `school_email` varchar(120) NOT NULL,
  `school_phone` varchar(20) NOT NULL,
  `estimated_students` int(11) NOT NULL,
  `plan_type` enum('hosted','offline') NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `actioned_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saas_school_requests`
--

INSERT INTO `saas_school_requests` (`request_id`, `school_name`, `school_email`, `school_phone`, `estimated_students`, `plan_type`, `status`, `rejection_reason`, `ip_address`, `user_agent`, `requested_at`, `actioned_at`) VALUES
(1, 'Iqra School', 'iqra@gmail.com', '23131241', 500, 'hosted', 'approved', NULL, NULL, NULL, '2026-01-24 05:59:00', '2026-01-24 02:55:46');

-- --------------------------------------------------------

--
-- Table structure for table `saas_school_subscriptions`
--

CREATE TABLE `saas_school_subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `price_per_student` decimal(10,2) DEFAULT NULL,
  `students_count` int(11) DEFAULT NULL,
  `billing_cycle` enum('yearly','monthly') DEFAULT 'yearly',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saas_school_subscriptions`
--

INSERT INTO `saas_school_subscriptions` (`subscription_id`, `school_id`, `plan_name`, `price_per_student`, `students_count`, `billing_cycle`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 3, 'Student', 180.00, 800, 'yearly', '2026-01-24', '2026-01-29', 'active', '2026-01-24 10:26:09'),
(2, 4, 'hh', 0.00, 300, 'yearly', '2026-01-24', '2027-01-24', 'active', '2026-01-24 10:33:04'),
(3, 5, 'hh', 0.00, 300, 'yearly', '2026-01-25', '2027-01-25', 'active', '2026-01-25 02:52:37'),
(4, 6, 'hh', 365.00, 300, 'yearly', '2026-01-25', '2027-01-25', 'active', '2026-01-25 03:18:41'),
(5, 7, 'hh', 365.00, 400, 'yearly', '2026-01-25', '2027-01-25', 'active', '2026-01-25 04:14:10'),
(6, 8, 'hh', 365.00, 20, 'yearly', '2026-01-25', '2027-01-26', 'active', '2026-01-25 05:38:18'),
(7, 9, 'hh', 365.00, 10, 'yearly', '2026-01-26', '2027-01-26', 'active', '2026-01-26 01:20:06'),
(15, 1, 'Student', 400.00, 1000, 'yearly', '2026-01-27', '2027-01-27', 'active', '2026-01-27 04:25:03'),
(16, 10, 'hh', 400.00, 300, 'yearly', '2026-01-27', '2027-01-27', 'active', '2026-01-27 06:19:50');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `school_code` varchar(100) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `boards` varchar(255) DEFAULT NULL,
  `subdomain` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `contact_no` varchar(20) NOT NULL,
  `estimated_students` int(11) NOT NULL,
  `total_student` int(11) DEFAULT NULL,
  `plan` varchar(100) NOT NULL,
  `status` enum('active','blocked','block','inactive','pending','suspended','expired','rejected') DEFAULT 'active',
  `storage_used` bigint(20) DEFAULT 0,
  `db_size` bigint(20) DEFAULT 0,
  `start_date` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `name`, `school_code`, `logo_path`, `address`, `city`, `boards`, `subdomain`, `email`, `password`, `contact_no`, `estimated_students`, `total_student`, `plan`, `status`, `storage_used`, `db_size`, `start_date`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'Test School', NULL, NULL, NULL, NULL, NULL, 'testschool', 'test@gmail.com', '$2y$10$lgqaKyAf5uJ652fVslomfe6QuJkbJpao0M87Jiap/WDxPmZl0zKWq', '03272352752', 1000, NULL, 'Student', 'active', 0, 0, '2026-01-23 00:00:00', '2027-01-27 00:00:00', '2026-01-22 17:30:18', '2026-01-27 09:25:03'),
(2, 'Iqra School', NULL, NULL, NULL, NULL, NULL, 'iqra-school', 'iqra@gmail.com', '$2y$10$VhQezHPbvVIrzNHlS.IZKOdnNhRkMIf1bOdK.Zxnqz9vahfItGFlC', '23131241', 500, NULL, 'hosted', 'active', 0, 0, '2026-01-24 00:00:00', '2028-02-23 00:00:00', '2026-01-24 07:55:46', '2026-01-24 08:45:05'),
(3, 'check', NULL, NULL, NULL, NULL, NULL, 'check.com', 'khan@gmail.com', '$2y$10$ePSILathFE1hl8cMWv1/k.fTgb/LIQhF.SqxtL.T0jrs2NAEl7re6', '121212', 800, NULL, 'Student', 'active', 0, 0, '2026-01-24 00:00:00', '2027-01-24 00:00:00', '2026-01-24 15:26:09', '2026-01-24 15:26:09'),
(4, 'Ali Raza', NULL, NULL, NULL, NULL, NULL, 'Inventoryhub', 'ali@gmail.com', '$2y$10$aYyuD3tCsjLktQGgPczqtehtVV.OWfOUL/F4DyH/iZUQ.niyItikq', '12123', 300, NULL, 'hh', 'active', 0, 0, '2026-01-24 00:00:00', '2027-01-24 00:00:00', '2026-01-24 15:33:04', '2026-01-24 15:37:21'),
(5, 'najeeb hassan', NULL, NULL, NULL, NULL, NULL, 'nn', 'Najeeb@gmail.com', '$2y$10$vm5SqIdpM0QGRVqeHlTtPeIskaGk4WC5rKUifFNHsNtAgBrWDac8u', '11', 300, NULL, 'hh', 'active', 0, 0, '2026-01-25 00:00:00', '2027-01-25 00:00:00', '2026-01-25 07:52:37', '2026-01-25 07:52:37'),
(6, 'ik jahan', NULL, NULL, NULL, NULL, NULL, 'test', 'to@gmail.com', '$2y$10$VZalxUYfFxhERpKLQ5hDLeYh1dJ6EilNLOOGM4FjBDq2YBX2SF0Mi', '123', 300, NULL, 'hh', 'active', 0, 0, '2026-01-25 00:00:00', '2027-01-25 00:00:00', '2026-01-25 08:18:41', '2026-01-25 08:18:41'),
(7, 'pp', NULL, NULL, NULL, NULL, NULL, 'pp', 'pp@gmail.com', '$2y$10$o.UVdOcgwl8Cah/2nejqWeldeTpwt0BQHGXGLW1pRv3ue5z1am9Ka', '121', 400, NULL, 'hh', 'active', 0, 0, '2026-01-25 00:00:00', '2027-01-25 00:00:00', '2026-01-25 09:14:10', '2026-01-25 09:14:10'),
(8, 'discounted test', NULL, NULL, NULL, NULL, NULL, 'tt', 'ttt@gmail.com', '$2y$10$aY8bCUt1/SiAU4OxQ.9L.e6qS1xoHsbykWKjr0xychBiT8sg/z5oK', '121', 20, NULL, 'hh', 'active', 0, 0, '2026-01-25 00:00:00', '2027-01-26 00:00:00', '2026-01-25 10:38:18', '2026-01-26 09:55:19'),
(9, 'debug', NULL, NULL, NULL, NULL, NULL, 'debug', 'lol@mail.com', '$2y$10$J7/a137jEPWJXybJJThHJuXmFMQKGif5zcHkkZbdk/9sKlfs5ibP.', '12', 10, NULL, 'hh', 'blocked', 0, 0, '2026-01-26 00:00:00', '2027-01-26 00:00:00', '2026-01-26 06:20:06', '2026-01-26 13:30:17'),
(10, 'AAMS', 'aams', 'logo_1770466106.png', 'Quaidabad', 'Karachi', 'Karachi Board', 'AAMS.com', 'aams@gmail.com', '$2y$12$Pbeulj3D9VYizMiL.IoKsuPbWa5oLKATaShYilnO9E00X7yUSFSHi', '1213', 300, NULL, 'hh', 'active', 20, 10, '2026-01-27 00:00:00', '2027-01-27 00:00:00', '2026-01-27 11:19:50', '2026-02-07 17:08:26');

-- --------------------------------------------------------

--
-- Table structure for table `school_admission_counters`
--

CREATE TABLE `school_admission_counters` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_admission_counters`
--

INSERT INTO `school_admission_counters` (`id`, `school_id`, `session_id`, `last_number`) VALUES
(2, 10, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `school_classes`
--

CREATE TABLE `school_classes` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `class_code` varchar(20) NOT NULL,
  `grade_level` int(11) DEFAULT NULL,
  `class_order` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_classes`
--

INSERT INTO `school_classes` (`id`, `school_id`, `session_id`, `class_name`, `class_code`, `grade_level`, `class_order`, `description`, `status`, `created_at`, `updated_at`) VALUES
(14, 10, 5, 'Class-1', 'class-1', 0, 0, 'Here we have class one', 1, '2026-02-04 17:50:32', '2026-02-06 16:30:13'),
(15, 10, 5, 'Class-2', 'class-2', 0, 0, '', 1, '2026-02-06 17:06:34', '2026-02-06 17:11:12');

-- --------------------------------------------------------

--
-- Table structure for table `school_class_sections`
--

CREATE TABLE `school_class_sections` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `section_code` varchar(20) NOT NULL,
  `class_teacher_id` int(11) DEFAULT NULL,
  `room_number` varchar(50) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `current_enrollment` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_class_sections`
--

INSERT INTO `school_class_sections` (`id`, `school_id`, `session_id`, `class_id`, `section_name`, `section_code`, `class_teacher_id`, `room_number`, `capacity`, `current_enrollment`, `status`, `created_at`, `updated_at`) VALUES
(14, 10, 5, 14, 'A', 'class-1-a', NULL, '01', 30, 2, 1, '2026-02-04 17:50:32', '2026-02-08 19:06:03'),
(15, 10, 5, 15, 'A', 'class-2-a', NULL, '', 30, 0, 1, '2026-02-06 17:06:34', '2026-02-06 17:11:22'),
(16, 10, 5, 15, 'B', 'class-2-b', NULL, '', 30, 0, 1, '2026-02-06 17:06:34', '2026-02-06 17:11:26');

-- --------------------------------------------------------

--
-- Table structure for table `school_exams`
--

CREATE TABLE `school_exams` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `exam_name` varchar(50) NOT NULL,
  `exam_type` enum('midterm','final','annual','board_prep','monthly') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','completed') DEFAULT 'draft',
  `marks_locked` tinyint(1) DEFAULT 0,
  `result_generated` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_exams`
--

INSERT INTO `school_exams` (`id`, `school_id`, `session_id`, `exam_name`, `exam_type`, `start_date`, `end_date`, `description`, `status`, `marks_locked`, `result_generated`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 10, 5, 'Mid Term Exam 2026', 'midterm', '2026-02-16', '2026-02-23', '', 'published', 0, 0, 10, '2026-02-08 12:02:23', '2026-02-13 08:03:11'),
(6, 10, 5, 'monthly test', 'monthly', '2026-02-13', '2026-02-13', '', 'published', 0, 0, 10, '2026-02-12 08:13:59', '2026-02-13 08:03:18');

-- --------------------------------------------------------

--
-- Table structure for table `school_exam_classes`
--

CREATE TABLE `school_exam_classes` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_exam_classes`
--

INSERT INTO `school_exam_classes` (`id`, `exam_id`, `class_id`, `section_id`, `start_date`, `end_date`, `status`) VALUES
(4, 2, 14, 14, '0000-00-00', '0000-00-00', 0),
(8, 2, 14, 14, '0000-00-00', '0000-00-00', 0),
(9, 2, 14, 14, '0000-00-00', '0000-00-00', 0),
(10, 2, 15, 15, '0000-00-00', '0000-00-00', 0),
(11, 5, 14, 14, '0000-00-00', '0000-00-00', 0),
(12, 5, 15, 15, '0000-00-00', '0000-00-00', 0),
(13, 5, 15, 16, '0000-00-00', '0000-00-00', 0),
(14, 6, 14, 14, '0000-00-00', '0000-00-00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `school_exam_marks`
--

CREATE TABLE `school_exam_marks` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `exam_subject_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `obtained_marks` decimal(5,2) DEFAULT NULL,
  `is_absent` tinyint(1) DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_exam_marks`
--

INSERT INTO `school_exam_marks` (`id`, `school_id`, `exam_id`, `exam_subject_id`, `student_id`, `obtained_marks`, `is_absent`, `remarks`, `created_at`, `updated_at`) VALUES
(16, 10, 5, 11, 7, 70.00, 0, '', '2026-02-13 07:46:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_exam_results`
--

CREATE TABLE `school_exam_results` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `total_obtained` decimal(8,2) DEFAULT NULL,
  `total_marks` decimal(8,2) DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `result_status` enum('pass','fail') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_exam_results`
--

INSERT INTO `school_exam_results` (`id`, `school_id`, `exam_id`, `student_id`, `total_obtained`, `total_marks`, `percentage`, `grade`, `position`, `result_status`, `created_at`) VALUES
(13, 10, 5, 7, 70.00, 300.00, 23.33, 'F', NULL, 'fail', '2026-02-13 07:46:21');

-- --------------------------------------------------------

--
-- Table structure for table `school_exam_subjects`
--

CREATE TABLE `school_exam_subjects` (
  `id` int(11) NOT NULL,
  `exam_class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `Room_no` varchar(100) DEFAULT NULL,
  `total_marks` int(11) NOT NULL,
  `passing_marks` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `exam_time` time NOT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_exam_subjects`
--

INSERT INTO `school_exam_subjects` (`id`, `exam_class_id`, `subject_id`, `Room_no`, `total_marks`, `passing_marks`, `exam_date`, `exam_time`, `status`) VALUES
(5, 4, 5, NULL, 30, 13, '2026-02-09', '23:00:00', 1),
(6, 9, 7, NULL, 30, 14, '2026-02-09', '15:04:00', 1),
(7, 9, 6, NULL, 30, 13, '2026-02-09', '15:10:00', 1),
(8, 10, 8, NULL, 30, 13, '2026-02-09', '15:22:00', 1),
(9, 10, 10, NULL, 30, 13, '2026-02-09', '15:22:00', 1),
(10, 10, 9, NULL, 30, 13, '2026-02-09', '15:23:00', 1),
(11, 11, 5, NULL, 100, 45, '2026-02-16', '09:00:00', 1),
(12, 11, 7, NULL, 100, 45, '2026-02-17', '09:00:00', 1),
(13, 11, 6, NULL, 100, 45, '2026-02-18', '09:00:00', 1),
(14, 12, 8, NULL, 100, 45, '2026-02-16', '13:00:00', 1),
(15, 12, 10, NULL, 100, 45, '2026-02-17', '13:00:00', 1),
(16, 12, 9, NULL, 100, 45, '2026-02-18', '13:00:00', 1),
(17, 13, 8, NULL, 100, 45, '2026-02-16', '13:00:00', 1),
(18, 13, 10, NULL, 100, 45, '2026-02-17', '13:00:00', 1),
(19, 13, 9, NULL, 100, 45, '2026-02-18', '13:00:00', 1),
(20, 14, 5, NULL, 25, 7, '2026-02-12', '09:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `school_expenses`
--

CREATE TABLE `school_expenses` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `expense_category_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `vendor_name` varchar(150) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` enum('cash','bank','online','cheque') DEFAULT 'cash',
  `reference_no` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approval_notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_grading_criteria`
--

CREATE TABLE `school_grading_criteria` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `grade_name` varchar(20) NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `remarks` varchar(100) DEFAULT NULL,
  `is_pass` tinyint(1) DEFAULT 1,
  `grading_system` enum('percentage','gpa','both') DEFAULT 'percentage',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_grading_criteria`
--

INSERT INTO `school_grading_criteria` (`id`, `school_id`, `grade_name`, `min_percentage`, `max_percentage`, `gpa`, `remarks`, `is_pass`, `grading_system`, `status`, `created_at`, `updated_at`) VALUES
(1, 10, 'F', 1.00, 45.00, 0.00, 'Fail', 0, 'percentage', 1, '2026-02-07 11:44:59', '2026-02-07 11:44:59'),
(2, 10, 'D', 46.00, 55.00, 0.00, 'Pass', 1, 'percentage', 1, '2026-02-07 11:46:52', '2026-02-07 11:46:52'),
(3, 10, 'D+', 56.00, 60.00, 0.00, 'Average', 1, 'percentage', 1, '2026-02-07 11:54:14', '2026-02-07 11:54:30'),
(4, 10, 'C', 61.00, 68.00, 0.00, 'Good', 1, 'percentage', 1, '2026-02-13 06:54:32', '2026-02-13 06:54:32'),
(5, 10, 'B', 69.00, 72.00, 0.00, 'Very good', 1, 'percentage', 1, '2026-02-13 06:55:04', '2026-02-13 06:55:04'),
(6, 10, 'B+', 73.00, 80.00, 0.00, 'Excelent', 1, 'percentage', 1, '2026-02-13 06:55:33', '2026-02-13 06:55:33'),
(7, 10, 'A', 81.00, 90.00, 0.00, 'Marvilous', 1, 'percentage', 1, '2026-02-13 06:56:30', '2026-02-13 06:56:30'),
(8, 10, 'A+', 91.00, 100.00, 0.00, 'Un defeatable', 1, 'percentage', 1, '2026-02-13 06:56:55', '2026-02-13 06:56:55');

-- --------------------------------------------------------

--
-- Table structure for table `school_holliday_calendar`
--

CREATE TABLE `school_holliday_calendar` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `event_type` enum('WEEKLY_OFF','HOLIDAY','VACATION','EVENT') NOT NULL,
  `day_of_week` tinyint(4) DEFAULT NULL COMMENT '1=Mon ... 7=Sun',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `applies_to` enum('ALL','STUDENTS','STAFF') DEFAULT 'ALL',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_holliday_calendar`
--

INSERT INTO `school_holliday_calendar` (`id`, `school_id`, `title`, `description`, `event_type`, `day_of_week`, `start_date`, `end_date`, `applies_to`, `created_by`, `created_at`, `updated_at`) VALUES
(8, 10, 'weekly  Off', '', 'WEEKLY_OFF', 7, NULL, NULL, 'ALL', 10, '2026-02-07 06:18:16', '2026-02-07 06:18:16');

-- --------------------------------------------------------

--
-- Table structure for table `school_payruns`
--

CREATE TABLE `school_payruns` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `pay_month` tinyint(4) NOT NULL,
  `pay_year` year(4) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `status` enum('draft','processed','approved','paid') DEFAULT 'draft',
  `total_employees` int(11) DEFAULT 0,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_payruns`
--

INSERT INTO `school_payruns` (`id`, `school_id`, `session_id`, `pay_month`, `pay_year`, `pay_period_start`, `pay_period_end`, `status`, `total_employees`, `total_amount`, `created_by`, `approved_by`, `approval_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 10, 1, 1, '2026', '2026-01-01', '2026-01-31', 'paid', 1, 28000.00, 10, 10, '2026-01-30 23:07:35', '2026-01-30 18:06:44', '2026-01-30 18:08:35', NULL),
(2, 10, 1, 2, '2026', '2026-02-01', '2026-02-28', 'paid', 3, 113000.00, 10, 10, '2026-01-31 11:50:48', '2026-01-31 06:49:42', '2026-01-31 06:51:24', NULL),
(3, 10, 1, 3, '2026', '2026-03-01', '2026-03-31', 'paid', 3, 113000.00, 10, 10, '2026-02-06 12:21:00', '2026-02-06 07:20:10', '2026-02-06 07:21:29', NULL),
(4, 10, 1, 4, '2026', '2026-02-01', '2026-02-28', 'paid', 3, 113000.00, 10, 10, '2026-02-10 11:51:36', '2026-02-10 06:50:53', '2026-02-10 06:51:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_payrun_items`
--

CREATE TABLE `school_payrun_items` (
  `id` int(11) NOT NULL,
  `payrun_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `staff_type` enum('teacher','employee') NOT NULL,
  `staff_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `allowance` decimal(10,2) DEFAULT 0.00,
  `deduction` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_payrun_items`
--

INSERT INTO `school_payrun_items` (`id`, `payrun_id`, `school_id`, `staff_type`, `staff_id`, `session_id`, `basic_salary`, `allowance`, `deduction`, `net_salary`, `payment_status`, `payment_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 10, 'employee', 3, 1, 25000.00, 3000.00, 0.00, 28000.00, 'paid', '2026-01-30', '2026-01-30 18:06:44', '2026-01-30 18:08:35', NULL),
(2, 2, 10, 'teacher', 1, 1, 40000.00, 0.00, 0.00, 40000.00, 'paid', '2026-01-31', '2026-01-31 06:49:42', '2026-01-31 06:51:24', NULL),
(3, 2, 10, 'employee', 5, 1, 35000.00, 10000.00, 0.00, 45000.00, 'paid', '2026-01-31', '2026-01-31 06:49:42', '2026-01-31 06:51:24', NULL),
(4, 2, 10, 'employee', 3, 1, 25000.00, 3000.00, 0.00, 28000.00, 'paid', '2026-01-31', '2026-01-31 06:49:42', '2026-01-31 06:51:24', NULL),
(5, 3, 10, 'teacher', 1, 1, 40000.00, 0.00, 0.00, 40000.00, 'paid', '2026-02-06', '2026-02-06 07:20:10', '2026-02-06 07:21:29', NULL),
(6, 3, 10, 'employee', 5, 1, 35000.00, 10000.00, 0.00, 45000.00, 'paid', '2026-02-06', '2026-02-06 07:20:10', '2026-02-06 07:21:29', NULL),
(7, 3, 10, 'employee', 3, 1, 25000.00, 3000.00, 0.00, 28000.00, 'paid', '2026-02-06', '2026-02-06 07:20:10', '2026-02-06 07:21:29', NULL),
(8, 4, 10, 'teacher', 1, 1, 40000.00, 0.00, 0.00, 40000.00, 'paid', '2026-02-10', '2026-02-10 06:50:53', '2026-02-10 06:51:53', NULL),
(9, 4, 10, 'employee', 5, 1, 35000.00, 10000.00, 0.00, 45000.00, 'paid', '2026-02-10', '2026-02-10 06:50:53', '2026-02-10 06:51:53', NULL),
(10, 4, 10, 'employee', 3, 1, 25000.00, 3000.00, 0.00, 28000.00, 'paid', '2026-02-10', '2026-02-10 06:50:53', '2026-02-10 06:51:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_sessions`
--

CREATE TABLE `school_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_sessions`
--

INSERT INTO `school_sessions` (`id`, `school_id`, `name`, `start_date`, `end_date`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 10, '2025-2026', '2026-02-15', '2027-02-15', 1, 10, 10, '2026-02-04 22:44:49', '2026-02-04 22:44:49', NULL),
(6, 1, '2025-2026', '2025-01-01', '2025-12-31', 1, 1, 1, '2026-02-07 20:17:34', '2026-02-07 20:17:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_staff_attendance`
--

CREATE TABLE `school_staff_attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `staff_type` enum('employee','teacher') NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('P','A','L','HD') NOT NULL COMMENT 'P=Present, A=Absent, L=Leave, HD=Half Day',
  `remarks` varchar(255) DEFAULT NULL,
  `marked_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'User/Employee who marked attendance',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_staff_attendance`
--

INSERT INTO `school_staff_attendance` (`id`, `school_id`, `staff_type`, `staff_id`, `attendance_date`, `status`, `remarks`, `marked_by`, `created_at`, `updated_at`) VALUES
(44, 10, 'teacher', 1, '2026-02-05', 'P', '', 10, '2026-02-05 16:11:31', '2026-02-05 16:11:31'),
(45, 10, 'employee', 3, '2026-02-05', 'P', '', 10, '2026-02-05 16:11:31', '2026-02-05 16:11:31'),
(46, 10, 'employee', 5, '2026-02-05', 'P', '', 10, '2026-02-05 16:11:31', '2026-02-05 16:11:31'),
(47, 10, 'employee', 5, '2026-02-04', 'P', NULL, NULL, '2026-02-05 16:13:55', '2026-02-05 16:13:55'),
(48, 10, 'teacher', 1, '2026-02-06', 'A', '', 10, '2026-02-06 10:17:09', '2026-02-06 10:17:09'),
(49, 10, 'employee', 3, '2026-02-06', 'A', '', 10, '2026-02-06 10:17:09', '2026-02-06 10:17:09'),
(50, 10, 'employee', 5, '2026-02-06', 'A', '', 10, '2026-02-06 10:17:09', '2026-02-06 10:17:09');

-- --------------------------------------------------------

--
-- Table structure for table `school_staff_salaries`
--

CREATE TABLE `school_staff_salaries` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `staff_type` enum('teacher','employee') NOT NULL,
  `staff_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `allowance` decimal(10,2) DEFAULT 0.00,
  `deduction` decimal(10,2) DEFAULT 0.00,
  `effective_from` date NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_staff_salaries`
--

INSERT INTO `school_staff_salaries` (`id`, `school_id`, `staff_type`, `staff_id`, `session_id`, `basic_salary`, `allowance`, `deduction`, `effective_from`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 10, 'employee', 3, 1, 25000.00, 3000.00, 0.00, '2026-01-01', 1, 10, 10, '2026-01-30 13:17:15', '2026-01-30 13:17:15', NULL),
(2, 10, 'employee', 5, 1, 35000.00, 10000.00, 0.00, '2026-01-31', 1, 10, 10, '2026-01-31 06:44:00', '2026-01-31 06:44:00', NULL),
(3, 10, 'teacher', 1, 1, 40000.00, 0.00, 0.00, '2026-01-31', 1, 10, 10, '2026-01-31 06:44:15', '2026-01-31 06:44:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_students`
--

CREATE TABLE `school_students` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `admission_no` varchar(64) DEFAULT NULL,
  `first_name` varchar(150) NOT NULL,
  `last_name` varchar(150) DEFAULT NULL,
  `father_names` varchar(255) DEFAULT NULL,
  `father_contact` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(16) DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `religion` varchar(64) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_students`
--

INSERT INTO `school_students` (`id`, `school_id`, `admission_no`, `first_name`, `last_name`, `father_names`, `father_contact`, `dob`, `gender`, `admission_date`, `religion`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 10, 'aams-2026-000001', 'najeeb', 'Hassan', 'Fazal UR Rehman', '03032923475', '2005-11-05', 'male', '2026-02-01', 'Islam', 1, '2026-02-04 22:49:00', '2026-02-10 11:44:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_student_academics`
--

CREATE TABLE `school_student_academics` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED DEFAULT NULL,
  `class_id` int(10) UNSIGNED DEFAULT NULL,
  `section_id` int(10) UNSIGNED DEFAULT NULL,
  `is_transferred` tinyint(1) NOT NULL DEFAULT 0,
  `previous_school` varchar(255) DEFAULT NULL,
  `previous_class` varchar(128) DEFAULT NULL,
  `previous_admission_no` varchar(64) DEFAULT NULL,
  `previous_result` varchar(255) DEFAULT NULL,
  `enrolled_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_student_academics`
--

INSERT INTO `school_student_academics` (`id`, `student_id`, `school_id`, `session_id`, `class_id`, `section_id`, `is_transferred`, `previous_school`, `previous_class`, `previous_admission_no`, `previous_result`, `enrolled_at`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 7, 10, 5, NULL, NULL, 0, '', '', '', '', '2026-02-04 22:49:00', 1, '2026-02-04 22:49:00', '2026-02-04 22:50:54', '2026-02-04 22:50:54'),
(10, 7, 10, 5, 14, 14, 0, '', '', '', '', '2026-02-04 22:50:54', 1, '2026-02-04 22:50:54', '2026-02-09 00:06:03', '2026-02-09 00:06:03'),
(11, 7, 10, 5, 14, 14, 0, '', '', '', '', '2026-02-09 00:06:03', 1, '2026-02-09 00:06:03', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_student_attendance`
--

CREATE TABLE `school_student_attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `session_id` int(11) NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('P','A','L','HD') NOT NULL COMMENT 'P=Present, A=Absent, L=Leave, HD=Half Day',
  `remarks` varchar(255) DEFAULT NULL,
  `marked_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'Employee/Teacher ID',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_student_attendance`
--

INSERT INTO `school_student_attendance` (`id`, `school_id`, `session_id`, `student_id`, `class_id`, `section_id`, `attendance_date`, `status`, `remarks`, `marked_by`, `created_at`, `updated_at`) VALUES
(1, 10, 5, 5, 14, 1, '2026-02-06', 'P', NULL, NULL, '2026-02-06 16:27:32', '2026-02-06 17:40:06'),
(3, 10, 5, 7, 14, 14, '2026-02-06', 'P', NULL, 10, '2026-02-07 05:08:59', '2026-02-07 05:08:59'),
(4, 10, 5, 7, 14, 14, '2026-02-05', 'P', NULL, 10, '2026-02-07 05:09:22', '2026-02-07 05:09:22'),
(5, 10, 5, 7, 14, 14, '2026-02-03', 'A', NULL, 10, '2026-02-03 05:14:20', '2026-02-07 05:16:11'),
(6, 10, 5, 7, 14, 14, '2026-02-04', 'A', NULL, 10, '2026-02-07 05:24:19', '2026-02-07 05:24:19'),
(7, 10, 5, 7, 14, 14, '2026-02-02', 'P', NULL, 10, '2026-02-07 05:27:34', '2026-02-07 05:27:34'),
(8, 10, 5, 7, 14, 14, '2026-02-07', 'P', NULL, 10, '2026-02-07 11:12:32', '2026-02-07 11:12:32'),
(9, 10, 5, 7, 14, 14, '2026-02-10', 'P', NULL, 10, '2026-02-10 06:35:11', '2026-02-10 06:35:11'),
(10, 10, 5, 7, 14, 14, '2026-02-09', 'P', NULL, 10, '2026-02-10 06:46:26', '2026-02-10 06:46:26');

-- --------------------------------------------------------

--
-- Table structure for table `school_student_documents`
--

CREATE TABLE `school_student_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `doc_type` varchar(64) NOT NULL,
  `file_path` varchar(1024) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_student_documents`
--

INSERT INTO `school_student_documents` (`id`, `student_id`, `school_id`, `doc_type`, `file_path`, `original_name`, `notes`, `uploaded_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(13, 7, 10, 'photo', 'Storage/uploads/schools/school_10/students/7/10_photo_7.jpg', 'hnhlogo.jpg', NULL, '2026-02-04 22:49:00', '2026-02-04 22:49:00', NULL, NULL),
(14, 7, 10, 'guardian_cnic', 'Storage/uploads/schools/school_10/students/7/10_guardian_cnic_7.pdf', 'NajeebCV.pdf', NULL, '2026-02-04 22:49:00', '2026-02-04 22:49:00', NULL, NULL),
(15, 7, 10, 'birth_certificate', 'Storage/uploads/schools/school_10/students/7/10_birth_certificate_7.pdf', 'Professional_School_Management_Quotation.pdf', NULL, '2026-02-04 22:49:00', '2026-02-04 22:49:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_student_enrollments`
--

CREATE TABLE `school_student_enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `roll_no` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `admission_no` varchar(64) NOT NULL,
  `admission_date` date DEFAULT NULL,
  `status` enum('active','promoted','passed_out','left','failed') NOT NULL DEFAULT 'active',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_student_enrollments`
--

INSERT INTO `school_student_enrollments` (`id`, `school_id`, `student_id`, `session_id`, `class_id`, `section_id`, `roll_no`, `admission_no`, `admission_date`, `status`, `remarks`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 10, 7, 5, 14, 14, 1, 'aams-2026-000001', '2026-02-01', 'active', NULL, '2026-02-04 17:50:54', '2026-02-04 17:50:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_student_fees_concessions`
--

CREATE TABLE `school_student_fees_concessions` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `admission_no` varchar(255) NOT NULL,
  `session_id` int(11) NOT NULL,
  `type` enum('discount','scholarship','concession') NOT NULL,
  `value_type` enum('fixed','percentage') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `applies_to` enum('all','tuition_only') DEFAULT 'tuition_only',
  `start_month` date NOT NULL,
  `end_month` date DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_student_fees_concessions`
--

INSERT INTO `school_student_fees_concessions` (`id`, `school_id`, `admission_no`, `session_id`, `type`, `value_type`, `value`, `applies_to`, `start_month`, `end_month`, `status`, `created_at`, `updated_at`) VALUES
(12, 10, 'aams-2026-000001', 2025, 'scholarship', 'percentage', 10.00, 'tuition_only', '2026-02-01', '2027-02-01', 1, '2026-02-05 14:13:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_student_guardians`
--

CREATE TABLE `school_student_guardians` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `relation` varchar(64) DEFAULT NULL,
  `cnic_passport` varchar(64) DEFAULT NULL,
  `occupation` varchar(128) DEFAULT NULL,
  `mobile` varchar(32) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_student_guardians`
--

INSERT INTO `school_student_guardians` (`id`, `student_id`, `school_id`, `name`, `relation`, `cnic_passport`, `occupation`, `mobile`, `address`, `is_primary`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 7, 10, 'Fazal Ur Rehman', 'Father', '42501-1609050-7', 'Engineer', '03032923475', 'Malir Karachi', 1, '2026-02-04 22:49:00', '2026-02-04 22:50:54', '2026-02-04 22:50:54'),
(10, 7, 10, 'Fazal Ur Rehman', 'Father', '42501-1609050-7', 'Engineer', '03032923475', 'Malir Karachi', 1, '2026-02-04 22:50:54', '2026-02-09 00:06:03', '2026-02-09 00:06:03'),
(11, 7, 10, 'Fazal Ur Rehman', 'Father', '42501-1609050-7', 'Engineer', '03032923475', 'Malir Karachi', 1, '2026-02-09 00:06:03', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_subjects`
--

CREATE TABLE `school_subjects` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_subjects`
--

INSERT INTO `school_subjects` (`id`, `school_id`, `name`, `teacher_id`, `status`, `created_at`, `updated_at`) VALUES
(5, 10, 'English', 1, 'active', '2026-02-08 06:32:59', '2026-02-08 06:32:59'),
(6, 10, 'Maths', 1, 'active', '2026-02-08 09:05:44', '2026-02-08 09:05:44'),
(7, 10, 'Islamiat', 1, 'active', '2026-02-08 09:05:59', '2026-02-08 09:05:59'),
(8, 10, 'English', 1, 'active', '2026-02-08 09:06:23', '2026-02-08 09:06:23'),
(9, 10, 'Maths', 1, 'active', '2026-02-08 09:06:31', '2026-02-08 09:06:31'),
(10, 10, 'Islamiat', 1, 'active', '2026-02-08 09:06:39', '2026-02-08 09:06:39');

-- --------------------------------------------------------

--
-- Table structure for table `school_subject_assignments`
--

CREATE TABLE `school_subject_assignments` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_subject_assignments`
--

INSERT INTO `school_subject_assignments` (`id`, `school_id`, `subject_id`, `class_id`, `section_id`, `teacher_id`, `session_id`, `created_at`, `updated_at`) VALUES
(7, 10, 5, 14, 14, 1, NULL, '2026-02-08 06:32:59', '2026-02-08 06:32:59'),
(8, 10, 6, 14, 14, 1, NULL, '2026-02-08 09:05:44', '2026-02-08 09:05:44'),
(9, 10, 7, 14, 14, 1, NULL, '2026-02-08 09:05:59', '2026-02-08 09:05:59'),
(10, 10, 8, 15, 15, 1, NULL, '2026-02-08 09:06:23', '2026-02-08 09:06:23'),
(11, 10, 8, 15, 16, 1, NULL, '2026-02-08 09:06:23', '2026-02-08 09:06:23'),
(12, 10, 9, 15, 15, 1, NULL, '2026-02-08 09:06:31', '2026-02-08 09:06:31'),
(13, 10, 9, 15, 16, 1, NULL, '2026-02-08 09:06:31', '2026-02-08 09:06:31'),
(14, 10, 10, 15, 15, 1, NULL, '2026-02-08 09:06:39', '2026-02-08 09:06:39'),
(15, 10, 10, 15, 16, 1, NULL, '2026-02-08 09:06:39', '2026-02-08 09:06:39');

-- --------------------------------------------------------

--
-- Table structure for table `school_teachers`
--

CREATE TABLE `school_teachers` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `id_no` varchar(100) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `role` varchar(100) DEFAULT 'teacher',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active,0=inactive',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_teachers`
--

INSERT INTO `school_teachers` (`id`, `school_id`, `name`, `email`, `phone`, `id_no`, `photo_path`, `role`, `permissions`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 10, 'khan', 'khann@gmail.com', '1023213', '42501', 'Storage/uploads/schools/school_10/faculty/faculty_10_teacher_1770268834_ea24479e.jpeg', 'teacher', NULL, 1, NULL, '2026-01-29 05:39:36', '2026-02-05 05:20:34');

-- --------------------------------------------------------

--
-- Table structure for table `schoo_fee_assignments`
--

CREATE TABLE `schoo_fee_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `fee_item_id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED DEFAULT NULL,
  `section_id` int(10) UNSIGNED DEFAULT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `due_day` tinyint(4) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schoo_fee_assignments`
--

INSERT INTO `schoo_fee_assignments` (`id`, `school_id`, `fee_item_id`, `class_id`, `section_id`, `student_id`, `session_id`, `amount`, `due_day`, `created_at`, `updated_at`) VALUES
(8, 10, 10, 14, NULL, NULL, 5, 1400.00, 10, '2026-02-04 18:19:48', '2026-02-04 18:19:48'),
(9, 10, 11, 14, NULL, NULL, 5, 1000.00, 10, '2026-02-04 18:20:24', '2026-02-04 18:20:24');

-- --------------------------------------------------------

--
-- Table structure for table `schoo_fee_categories`
--

CREATE TABLE `schoo_fee_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `code` varchar(64) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schoo_fee_categories`
--

INSERT INTO `schoo_fee_categories` (`id`, `school_id`, `name`, `code`, `description`, `status`, `created_at`, `updated_at`) VALUES
(4, 10, 'class-1 tuition Fee', '', '', 1, '2026-02-04 18:18:35', '2026-02-04 18:19:00'),
(5, 10, 'Examination Fees', '', '', 1, '2026-02-04 18:18:48', '2026-02-04 18:18:48');

-- --------------------------------------------------------

--
-- Table structure for table `schoo_fee_invoices`
--

CREATE TABLE `schoo_fee_invoices` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `invoice_no` varchar(64) NOT NULL,
  `billing_month` date NOT NULL,
  `gross_amount` decimal(12,2) DEFAULT 0.00,
  `concession_amount` decimal(12,2) DEFAULT 0.00,
  `net_payable` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','issued','partially_paid','paid','cancelled') DEFAULT 'issued',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schoo_fee_invoices`
--

INSERT INTO `schoo_fee_invoices` (`id`, `school_id`, `student_id`, `session_id`, `invoice_no`, `billing_month`, `gross_amount`, `concession_amount`, `net_payable`, `total_amount`, `status`, `due_date`, `created_at`, `updated_at`) VALUES
(69, 10, 7, 5, 'INV-10-2026-00029', '2026-03-01', 2400.00, 0.00, 2400.00, 2400.00, 'paid', '2026-01-20', '2026-02-05 08:59:46', '2026-02-05 09:00:00'),
(70, 10, 7, 5, 'INV-10-2026-00030', '2026-02-01', 16800.00, 0.00, 16800.00, 16800.00, 'paid', '2026-02-05', '2026-02-05 09:00:18', '2026-02-05 09:01:25'),
(71, 10, 7, 5, 'INV-10-2026-00031', '2026-02-01', 15400.00, 0.00, 15400.00, 15400.00, 'issued', '2026-02-05', '2026-02-05 09:01:37', '2026-02-05 09:01:37'),
(72, 10, 7, 5, 'INV-10-2026-00032', '2026-03-01', 2400.00, 140.00, 2260.00, 2260.00, 'issued', '2026-02-05', '2026-02-05 14:16:22', '2026-02-05 14:16:22');

-- --------------------------------------------------------

--
-- Table structure for table `schoo_fee_invoice_items`
--

CREATE TABLE `schoo_fee_invoice_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `fee_item_id` int(10) UNSIGNED NOT NULL,
  `description` varchar(191) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schoo_fee_invoice_items`
--

INSERT INTO `schoo_fee_invoice_items` (`id`, `invoice_id`, `fee_item_id`, `description`, `amount`, `created_at`) VALUES
(316, 69, 10, 'Class-1 tuition Fees', 1400.00, '2026-02-05 08:59:46'),
(317, 69, 11, 'Examination Fees (Once per Session)', 1000.00, '2026-02-05 08:59:46'),
(318, 70, 10, 'Class-1 tuition Fees - February 2026', 1400.00, '2026-02-05 09:00:18'),
(319, 70, 10, 'Class-1 tuition Fees - April 2026', 1400.00, '2026-02-05 09:00:18'),
(320, 70, 10, 'Class-1 tuition Fees - May 2026', 1400.00, '2026-02-05 09:00:18'),
(321, 70, 10, 'Class-1 tuition Fees - June 2026', 1400.00, '2026-02-05 09:00:18'),
(322, 70, 10, 'Class-1 tuition Fees - July 2026', 1400.00, '2026-02-05 09:00:18'),
(323, 70, 10, 'Class-1 tuition Fees - August 2026', 1400.00, '2026-02-05 09:00:18'),
(324, 70, 10, 'Class-1 tuition Fees - September 2026', 1400.00, '2026-02-05 09:00:18'),
(325, 70, 10, 'Class-1 tuition Fees - October 2026', 1400.00, '2026-02-05 09:00:18'),
(326, 70, 10, 'Class-1 tuition Fees - November 2026', 1400.00, '2026-02-05 09:00:18'),
(327, 70, 10, 'Class-1 tuition Fees - December 2026', 1400.00, '2026-02-05 09:00:18'),
(328, 70, 10, 'Class-1 tuition Fees - January 2027', 1400.00, '2026-02-05 09:00:18'),
(329, 70, 10, 'Class-1 tuition Fees - February 2027', 1400.00, '2026-02-05 09:00:18'),
(330, 71, 10, 'Class-1 tuition Fees - April 2026', 1400.00, '2026-02-05 09:01:37'),
(331, 71, 10, 'Class-1 tuition Fees - May 2026', 1400.00, '2026-02-05 09:01:37'),
(332, 71, 10, 'Class-1 tuition Fees - June 2026', 1400.00, '2026-02-05 09:01:37'),
(333, 71, 10, 'Class-1 tuition Fees - July 2026', 1400.00, '2026-02-05 09:01:37'),
(334, 71, 10, 'Class-1 tuition Fees - August 2026', 1400.00, '2026-02-05 09:01:37'),
(335, 71, 10, 'Class-1 tuition Fees - September 2026', 1400.00, '2026-02-05 09:01:37'),
(336, 71, 10, 'Class-1 tuition Fees - October 2026', 1400.00, '2026-02-05 09:01:37'),
(337, 71, 10, 'Class-1 tuition Fees - November 2026', 1400.00, '2026-02-05 09:01:37'),
(338, 71, 10, 'Class-1 tuition Fees - December 2026', 1400.00, '2026-02-05 09:01:37'),
(339, 71, 10, 'Class-1 tuition Fees - January 2027', 1400.00, '2026-02-05 09:01:37'),
(340, 71, 10, 'Class-1 tuition Fees - February 2027', 1400.00, '2026-02-05 09:01:37'),
(341, 72, 10, 'Class-1 tuition Fees', 1400.00, '2026-02-05 14:16:22'),
(342, 72, 11, 'Examination Fees (Once per Session)', 1000.00, '2026-02-05 14:16:22'),
(343, 72, 0, 'Concessions / Scholarships', -140.00, '2026-02-05 14:16:22');

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
  `billing_cycle` enum('once_per_session','monthly','quarterly','yearly','one_time') DEFAULT 'one_time',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schoo_fee_items`
--

INSERT INTO `schoo_fee_items` (`id`, `school_id`, `category_id`, `name`, `code`, `amount`, `billing_cycle`, `status`, `created_at`, `updated_at`) VALUES
(10, 10, 4, 'Class-1 tuition Fees', '', 1400.00, 'monthly', 1, '2026-02-04 18:19:28', '2026-02-04 18:19:28'),
(11, 10, 5, 'Examination Fees', '', 1000.00, 'once_per_session', 1, '2026-02-04 18:20:16', '2026-02-04 18:20:16');

-- --------------------------------------------------------

--
-- Table structure for table `schoo_fee_payments`
--

CREATE TABLE `schoo_fee_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(50) DEFAULT 'cash',
  `reference` varchar(128) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `super_admin`
--

CREATE TABLE `super_admin` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `super_admin`
--

INSERT INTO `super_admin` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'najeeb hassan', 'najeebhassan386@gmail.com', '$2y$10$r0M3..tMnHINIe.0LkeFeORlrcU2nz14TAhIWKIEk.bCJrplY5Dl.', '2026-01-22 07:51:37', '2026-01-22 08:51:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_school_email` (`school_id`,`email`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_role` (`role_id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_category` (`school_id`,`name`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `invoice_counters`
--
ALTER TABLE `invoice_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_school_session` (`school_id`,`session_id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saas_billing_cycles`
--
ALTER TABLE `saas_billing_cycles`
  ADD PRIMARY KEY (`billing_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `saas_expenses`
--
ALTER TABLE `saas_expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `category` (`category`),
  ADD KEY `expense_date` (`expense_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `saas_payments`
--
ALTER TABLE `saas_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `billing_id` (`billing_id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `saas_school_requests`
--
ALTER TABLE `saas_school_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `uniq_email` (`school_email`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `saas_school_subscriptions`
--
ALTER TABLE `saas_school_subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `subdomain` (`subdomain`),
  ADD UNIQUE KEY `school_code` (`school_code`);

--
-- Indexes for table `school_admission_counters`
--
ALTER TABLE `school_admission_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_id` (`school_id`,`session_id`);

--
-- Indexes for table `school_classes`
--
ALTER TABLE `school_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `school_class_sections`
--
ALTER TABLE `school_class_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section` (`class_id`,`section_name`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `class_teacher_id` (`class_teacher_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `school_exams`
--
ALTER TABLE `school_exams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_exam` (`school_id`,`session_id`,`exam_name`);

--
-- Indexes for table `school_exam_classes`
--
ALTER TABLE `school_exam_classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_exam_marks`
--
ALTER TABLE `school_exam_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_mark` (`school_id`,`exam_subject_id`,`student_id`),
  ADD KEY `exam_subject_id` (`exam_subject_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `idx_sem_school_id` (`school_id`),
  ADD KEY `idx_sem_exam_id` (`exam_id`);

--
-- Indexes for table `school_exam_results`
--
ALTER TABLE `school_exam_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_exam_result` (`school_id`,`exam_id`,`student_id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `idx_ser_school_id` (`school_id`);

--
-- Indexes for table `school_exam_subjects`
--
ALTER TABLE `school_exam_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_expenses`
--
ALTER TABLE `school_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `expense_category_id` (`expense_category_id`),
  ADD KEY `expense_date` (`expense_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `school_grading_criteria`
--
ALTER TABLE `school_grading_criteria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_school_grade` (`school_id`,`grade_name`),
  ADD UNIQUE KEY `uniq_school_range` (`school_id`,`min_percentage`,`max_percentage`);

--
-- Indexes for table `school_holliday_calendar`
--
ALTER TABLE `school_holliday_calendar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `event_type` (`event_type`),
  ADD KEY `start_date` (`start_date`,`end_date`),
  ADD KEY `day_of_week` (`day_of_week`);

--
-- Indexes for table `school_payruns`
--
ALTER TABLE `school_payruns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_payrun` (`school_id`,`session_id`,`pay_month`,`pay_year`),
  ADD KEY `idx_school_id` (`school_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `school_payrun_items`
--
ALTER TABLE `school_payrun_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payrun_id` (`payrun_id`),
  ADD KEY `idx_staff` (`staff_type`,`staff_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indexes for table `school_sessions`
--
ALTER TABLE `school_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_sessions_school_name` (`school_id`,`name`),
  ADD KEY `idx_sessions_school` (`school_id`);

--
-- Indexes for table `school_staff_attendance`
--
ALTER TABLE `school_staff_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_staff_attendance` (`school_id`,`staff_type`,`staff_id`,`attendance_date`),
  ADD KEY `idx_school_date` (`school_id`,`attendance_date`),
  ADD KEY `idx_staff` (`staff_type`,`staff_id`);

--
-- Indexes for table `school_staff_salaries`
--
ALTER TABLE `school_staff_salaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_school_staff_session` (`school_id`,`staff_type`,`staff_id`,`session_id`),
  ADD KEY `idx_school_staff` (`school_id`,`staff_id`),
  ADD KEY `idx_school_session` (`school_id`,`session_id`);

--
-- Indexes for table `school_students`
--
ALTER TABLE `school_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_students_school` (`school_id`),
  ADD KEY `idx_school_students_adm` (`admission_no`);

--
-- Indexes for table `school_student_academics`
--
ALTER TABLE `school_student_academics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_acad_student` (`student_id`),
  ADD KEY `idx_acad_school` (`school_id`),
  ADD KEY `idx_acad_session` (`session_id`);

--
-- Indexes for table `school_student_attendance`
--
ALTER TABLE `school_student_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_student_attendance` (`school_id`,`student_id`,`attendance_date`),
  ADD KEY `idx_school_date` (`school_id`,`attendance_date`),
  ADD KEY `idx_class_section` (`class_id`,`section_id`);

--
-- Indexes for table `school_student_documents`
--
ALTER TABLE `school_student_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_docs_student` (`student_id`),
  ADD KEY `idx_docs_school` (`school_id`),
  ADD KEY `idx_docs_type` (`doc_type`);

--
-- Indexes for table `school_student_enrollments`
--
ALTER TABLE `school_student_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admission_no` (`school_id`,`admission_no`),
  ADD UNIQUE KEY `uq_enrollment_student_session` (`school_id`,`student_id`,`session_id`),
  ADD UNIQUE KEY `uq_roll_per_class` (`school_id`,`session_id`,`class_id`,`section_id`,`roll_no`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_class` (`class_id`),
  ADD KEY `idx_section` (`section_id`),
  ADD KEY `idx_admission_no` (`admission_no`);

--
-- Indexes for table `school_student_fees_concessions`
--
ALTER TABLE `school_student_fees_concessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_student_guardians`
--
ALTER TABLE `school_student_guardians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_guardians_student` (`student_id`),
  ADD KEY `idx_guardians_school` (`school_id`);

--
-- Indexes for table `school_subjects`
--
ALTER TABLE `school_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `fk_subjects_teacher` (`teacher_id`);

--
-- Indexes for table `school_subject_assignments`
--
ALTER TABLE `school_subject_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_assign` (`school_id`,`subject_id`,`class_id`,`section_id`,`session_id`),
  ADD KEY `idx_school_class` (`school_id`,`class_id`),
  ADD KEY `fk_assign_subject` (`subject_id`),
  ADD KEY `fk_assign_class` (`class_id`),
  ADD KEY `fk_assign_teacher` (`teacher_id`);

--
-- Indexes for table `school_teachers`
--
ALTER TABLE `school_teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_school_teacher_email` (`school_id`,`email`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `schoo_fee_assignments`
--
ALTER TABLE `schoo_fee_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_assign` (`school_id`,`fee_item_id`,`class_id`,`section_id`,`student_id`,`session_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `fee_item_id` (`fee_item_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `schoo_fee_categories`
--
ALTER TABLE `schoo_fee_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_school_category` (`school_id`,`name`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `schoo_fee_invoices`
--
ALTER TABLE `schoo_fee_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_school_invoice` (`school_id`,`invoice_no`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `schoo_fee_invoice_items`
--
ALTER TABLE `schoo_fee_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `fee_item_id` (`fee_item_id`);

--
-- Indexes for table `schoo_fee_items`
--
ALTER TABLE `schoo_fee_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `schoo_fee_payments`
--
ALTER TABLE `schoo_fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `super_admin`
--
ALTER TABLE `super_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `invoice_counters`
--
ALTER TABLE `invoice_counters`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `saas_billing_cycles`
--
ALTER TABLE `saas_billing_cycles`
  MODIFY `billing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `saas_expenses`
--
ALTER TABLE `saas_expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `saas_payments`
--
ALTER TABLE `saas_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `saas_school_requests`
--
ALTER TABLE `saas_school_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `saas_school_subscriptions`
--
ALTER TABLE `saas_school_subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `school_admission_counters`
--
ALTER TABLE `school_admission_counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `school_classes`
--
ALTER TABLE `school_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `school_class_sections`
--
ALTER TABLE `school_class_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `school_exams`
--
ALTER TABLE `school_exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `school_exam_classes`
--
ALTER TABLE `school_exam_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `school_exam_marks`
--
ALTER TABLE `school_exam_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `school_exam_results`
--
ALTER TABLE `school_exam_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `school_exam_subjects`
--
ALTER TABLE `school_exam_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `school_expenses`
--
ALTER TABLE `school_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `school_grading_criteria`
--
ALTER TABLE `school_grading_criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `school_holliday_calendar`
--
ALTER TABLE `school_holliday_calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `school_payruns`
--
ALTER TABLE `school_payruns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `school_payrun_items`
--
ALTER TABLE `school_payrun_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `school_sessions`
--
ALTER TABLE `school_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `school_staff_attendance`
--
ALTER TABLE `school_staff_attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `school_staff_salaries`
--
ALTER TABLE `school_staff_salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_students`
--
ALTER TABLE `school_students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `school_student_academics`
--
ALTER TABLE `school_student_academics`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `school_student_attendance`
--
ALTER TABLE `school_student_attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `school_student_documents`
--
ALTER TABLE `school_student_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `school_student_enrollments`
--
ALTER TABLE `school_student_enrollments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `school_student_fees_concessions`
--
ALTER TABLE `school_student_fees_concessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `school_student_guardians`
--
ALTER TABLE `school_student_guardians`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `school_subjects`
--
ALTER TABLE `school_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `school_subject_assignments`
--
ALTER TABLE `school_subject_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `school_teachers`
--
ALTER TABLE `school_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schoo_fee_assignments`
--
ALTER TABLE `schoo_fee_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `schoo_fee_categories`
--
ALTER TABLE `schoo_fee_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `schoo_fee_invoices`
--
ALTER TABLE `schoo_fee_invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `schoo_fee_invoice_items`
--
ALTER TABLE `schoo_fee_invoice_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=344;

--
-- AUTO_INCREMENT for table `schoo_fee_items`
--
ALTER TABLE `schoo_fee_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `schoo_fee_payments`
--
ALTER TABLE `schoo_fee_payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `super_admin`
--
ALTER TABLE `super_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `saas_billing_cycles`
--
ALTER TABLE `saas_billing_cycles`
  ADD CONSTRAINT `saas_billing_cycles_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  ADD CONSTRAINT `saas_billing_cycles_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `saas_school_subscriptions` (`subscription_id`);

--
-- Constraints for table `saas_payments`
--
ALTER TABLE `saas_payments`
  ADD CONSTRAINT `saas_payments_ibfk_1` FOREIGN KEY (`billing_id`) REFERENCES `saas_billing_cycles` (`billing_id`),
  ADD CONSTRAINT `saas_payments_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

--
-- Constraints for table `saas_school_subscriptions`
--
ALTER TABLE `saas_school_subscriptions`
  ADD CONSTRAINT `saas_school_subscriptions_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

--
-- Constraints for table `school_expenses`
--
ALTER TABLE `school_expenses`
  ADD CONSTRAINT `fk_expenses_category` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`);

--
-- Constraints for table `school_payrun_items`
--
ALTER TABLE `school_payrun_items`
  ADD CONSTRAINT `school_payrun_items_ibfk_1` FOREIGN KEY (`payrun_id`) REFERENCES `school_payruns` (`id`);

--
-- Constraints for table `school_subjects`
--
ALTER TABLE `school_subjects`
  ADD CONSTRAINT `fk_subjects_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_subjects_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `school_teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `school_subject_assignments`
--
ALTER TABLE `school_subject_assignments`
  ADD CONSTRAINT `fk_assign_class` FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assign_subject` FOREIGN KEY (`subject_id`) REFERENCES `school_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assign_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `school_teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `school_teachers`
--
ALTER TABLE `school_teachers`
  ADD CONSTRAINT `fk_school_teachers_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
