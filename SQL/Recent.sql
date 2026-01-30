-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2026 at 06:57 PM
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

INSERT INTO `schools` (`id`, `name`, `logo_path`, `address`, `city`, `boards`, `subdomain`, `email`, `password`, `contact_no`, `estimated_students`, `total_student`, `plan`, `status`, `storage_used`, `db_size`, `start_date`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'Test School', NULL, NULL, NULL, NULL, 'testschool', 'test@gmail.com', '$2y$10$lgqaKyAf5uJ652fVslomfe6QuJkbJpao0M87Jiap/WDxPmZl0zKWq', '03272352752', 1000, NULL, 'Student', 'active', 0, 0, '2026-01-23 00:00:00', '2027-01-27 00:00:00', '2026-01-22 17:30:18', '2026-01-27 09:25:03'),
(2, 'Iqra School', NULL, NULL, NULL, NULL, 'iqra-school', 'iqra@gmail.com', '$2y$10$VhQezHPbvVIrzNHlS.IZKOdnNhRkMIf1bOdK.Zxnqz9vahfItGFlC', '23131241', 500, NULL, 'hosted', 'active', 0, 0, '2026-01-24 00:00:00', '2028-02-23 00:00:00', '2026-01-24 07:55:46', '2026-01-24 08:45:05'),
(3, 'check', NULL, NULL, NULL, NULL, 'check.com', 'khan@gmail.com', '$2y$10$ePSILathFE1hl8cMWv1/k.fTgb/LIQhF.SqxtL.T0jrs2NAEl7re6', '121212', 800, NULL, 'Student', 'active', 0, 0, '2026-01-24 00:00:00', '2027-01-24 00:00:00', '2026-01-24 15:26:09', '2026-01-24 15:26:09'),
(4, 'Ali Raza', NULL, NULL, NULL, NULL, 'Inventoryhub', 'ali@gmail.com', '$2y$10$aYyuD3tCsjLktQGgPczqtehtVV.OWfOUL/F4DyH/iZUQ.niyItikq', '12123', 300, NULL, 'hh', 'active', 0, 0, '2026-01-24 00:00:00', '2027-01-24 00:00:00', '2026-01-24 15:33:04', '2026-01-24 15:37:21'),
(5, 'najeeb hassan', NULL, NULL, NULL, NULL, 'nn', 'Najeeb@gmail.com', '$2y$10$vm5SqIdpM0QGRVqeHlTtPeIskaGk4WC5rKUifFNHsNtAgBrWDac8u', '11', 300, NULL, 'hh', 'active', 0, 0, '2026-01-25 00:00:00', '2027-01-25 00:00:00', '2026-01-25 07:52:37', '2026-01-25 07:52:37'),
(6, 'ik jahan', NULL, NULL, NULL, NULL, 'test', 'to@gmail.com', '$2y$10$VZalxUYfFxhERpKLQ5hDLeYh1dJ6EilNLOOGM4FjBDq2YBX2SF0Mi', '123', 300, NULL, 'hh', 'active', 0, 0, '2026-01-25 00:00:00', '2027-01-25 00:00:00', '2026-01-25 08:18:41', '2026-01-25 08:18:41'),
(7, 'pp', NULL, NULL, NULL, NULL, 'pp', 'pp@gmail.com', '$2y$10$o.UVdOcgwl8Cah/2nejqWeldeTpwt0BQHGXGLW1pRv3ue5z1am9Ka', '121', 400, NULL, 'hh', 'active', 0, 0, '2026-01-25 00:00:00', '2027-01-25 00:00:00', '2026-01-25 09:14:10', '2026-01-25 09:14:10'),
(8, 'discounted test', NULL, NULL, NULL, NULL, 'tt', 'ttt@gmail.com', '$2y$10$aY8bCUt1/SiAU4OxQ.9L.e6qS1xoHsbykWKjr0xychBiT8sg/z5oK', '121', 20, NULL, 'hh', 'active', 0, 0, '2026-01-25 00:00:00', '2027-01-26 00:00:00', '2026-01-25 10:38:18', '2026-01-26 09:55:19'),
(9, 'debug', NULL, NULL, NULL, NULL, 'debug', 'lol@mail.com', '$2y$10$J7/a137jEPWJXybJJThHJuXmFMQKGif5zcHkkZbdk/9sKlfs5ibP.', '12', 10, NULL, 'hh', 'blocked', 0, 0, '2026-01-26 00:00:00', '2027-01-26 00:00:00', '2026-01-26 06:20:06', '2026-01-26 13:30:17'),
(10, 'AAMS', 'logo_1769535001.png', 'Quaidabad', 'Karachi', 'Karachi Board', 'AAMS.com', 'aams@gmail.com', '$2y$12$Pbeulj3D9VYizMiL.IoKsuPbWa5oLKATaShYilnO9E00X7yUSFSHi', '1213', 300, NULL, 'hh', 'active', 20, 10, '2026-01-27 00:00:00', '2027-01-27 00:00:00', '2026-01-27 11:19:50', '2026-01-27 22:48:14');

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
(1, 10, '2025-2026', '2026-01-30', '2027-01-02', 1, 10, 10, '2026-01-30 17:02:47', '2026-01-30 17:46:01', NULL),
(2, 9, '2022-2023', '2026-01-01', '2026-01-31', 1, 9, NULL, '2026-01-30 17:47:20', '2026-01-30 17:47:39', NULL);

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
(1, 10, 'employee', 3, 1, 25000.00, 3000.00, 0.00, '2026-01-01', 1, 10, 10, '2026-01-30 13:17:15', '2026-01-30 13:17:15', NULL);

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
(1, 10, 'khan', 'khann@gmail.com', '1023213', '42501', 'Storage/uploads/schools/school_10/faculty/faculty_10_teacher_1769665176_05b87ff0.jpeg', 'teacher', NULL, 1, NULL, '2026-01-29 05:39:36', '2026-01-29 06:09:37');

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
  ADD UNIQUE KEY `subdomain` (`subdomain`),
  ADD UNIQUE KEY `email` (`email`);

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
-- Indexes for table `school_staff_salaries`
--
ALTER TABLE `school_staff_salaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_school_staff_session` (`school_id`,`staff_type`,`staff_id`,`session_id`),
  ADD KEY `idx_school_staff` (`school_id`,`staff_id`),
  ADD KEY `idx_school_session` (`school_id`,`session_id`);

--
-- Indexes for table `school_teachers`
--
ALTER TABLE `school_teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_school_teacher_email` (`school_id`,`email`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_role` (`role`);

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
-- AUTO_INCREMENT for table `school_payruns`
--
ALTER TABLE `school_payruns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_payrun_items`
--
ALTER TABLE `school_payrun_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_sessions`
--
ALTER TABLE `school_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_staff_salaries`
--
ALTER TABLE `school_staff_salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `school_teachers`
--
ALTER TABLE `school_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Constraints for table `school_payrun_items`
--
ALTER TABLE `school_payrun_items`
  ADD CONSTRAINT `school_payrun_items_ibfk_1` FOREIGN KEY (`payrun_id`) REFERENCES `school_payruns` (`id`);

--
-- Constraints for table `school_teachers`
--
ALTER TABLE `school_teachers`
  ADD CONSTRAINT `fk_school_teachers_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
