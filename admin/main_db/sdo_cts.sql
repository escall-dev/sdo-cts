-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2026 at 04:44 AM
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
-- Database: `sdo_cts`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` enum('login','logout','view','create','update','delete','status_change','forward','accept','return') NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action_type`, `entity_type`, `entity_id`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:26:31'),
(2, 3, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:26:39'),
(3, 3, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:34:25'),
(4, 3, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:34:33'),
(5, 3, 'accept', 'complaint', 1, 'Accepted complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:34:43'),
(6, 3, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:34:43'),
(7, 3, 'status_change', 'complaint', 1, 'Changed status to In Progress for CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:35:08'),
(8, 3, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:35:08'),
(9, 3, 'forward', 'complaint', 1, 'Forwarded complaint CTS-2026-00001 to OSDS', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:36:08'),
(10, 3, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:36:08'),
(11, 3, 'create', 'user', 4, 'Created user: Alexander Joerenz Escallente', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:37:05'),
(12, 3, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:37:19'),
(13, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:37:32'),
(14, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 03:47:21'),
(15, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:01:01'),
(16, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:01:08'),
(17, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:03:57'),
(18, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:05:17'),
(19, 5, 'login', 'auth', NULL, 'User logged in via Google OAuth', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:05:26'),
(20, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:05:37'),
(21, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:05:48'),
(22, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:09:52'),
(23, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:10:13'),
(24, 4, 'update', 'user', 4, 'Changed profile avatar', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:10:44'),
(25, 4, 'update', 'user', 4, 'Changed profile avatar', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:10:48'),
(26, 4, 'update', 'user', 4, 'Changed profile avatar', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:11:04'),
(27, 4, 'update', 'user', 4, 'Changed profile avatar', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 04:11:07'),
(28, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:15:33'),
(29, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:15:42'),
(30, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:16:09'),
(31, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:17:41'),
(32, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:20:03'),
(33, 4, 'return', 'complaint', 6, 'Returned complaint CTS-2026-00006: pangit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:20:20'),
(34, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:20:20'),
(35, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:21:32'),
(36, 4, 'accept', 'complaint', 2, 'Accepted complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:21:50'),
(37, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:21:50'),
(38, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:22:41'),
(39, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:29:42'),
(40, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:29:57'),
(41, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:31:26'),
(42, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:44:42'),
(43, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:46:01'),
(44, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:46:06'),
(45, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:46:08'),
(46, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:46:12'),
(47, 4, 'update', 'user', 4, 'Changed profile avatar', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:52:14'),
(48, 4, 'update', 'user', 4, 'Changed profile avatar', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:52:19'),
(49, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:52:30'),
(50, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 05:52:53'),
(51, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:13:20'),
(52, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:13:29'),
(53, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:19:28'),
(54, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:34:06'),
(55, 4, 'view', 'complaint', 5, 'Viewed complaint CTS-2026-00005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:34:55'),
(56, 4, 'view', 'complaint', 5, 'Viewed complaint CTS-2026-00005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:35:07'),
(57, 4, 'accept', 'complaint', 5, 'Accepted complaint CTS-2026-00005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:35:11'),
(58, 4, 'view', 'complaint', 5, 'Viewed complaint CTS-2026-00005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:35:11'),
(59, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:35:57'),
(60, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:37:22'),
(61, 4, 'status_change', 'complaint', 1, 'Changed status to Resolved for CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:37:30'),
(62, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:37:30'),
(63, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:01'),
(64, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:20'),
(65, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:28'),
(66, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:30'),
(67, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:31'),
(68, 4, 'status_change', 'complaint', 6, 'Changed status to Pending for CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:47'),
(69, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:47'),
(70, 4, 'accept', 'complaint', 6, 'Accepted complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:50'),
(71, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:50'),
(72, 4, 'forward', 'complaint', 6, 'Forwarded complaint CTS-2026-00006 to Legal', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:54'),
(73, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:38:54'),
(74, 4, 'status_change', 'complaint', 6, 'Changed status to In Progress for CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:39:01'),
(75, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:39:01'),
(76, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:39:53'),
(77, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:40:28'),
(78, 4, 'view', 'complaint', 3, 'Viewed complaint CTS-2026-00003', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:40:55'),
(79, 4, 'accept', 'complaint', 3, 'Accepted complaint CTS-2026-00003', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:40:58'),
(80, 4, 'view', 'complaint', 3, 'Viewed complaint CTS-2026-00003', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:40:58'),
(81, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:42:01'),
(82, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:42:37'),
(83, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:45:10'),
(84, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:48:21'),
(85, 4, 'view', 'complaint', 4, 'Viewed complaint CTS-2026-00004', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:50:37'),
(86, 4, 'accept', 'complaint', 4, 'Accepted complaint CTS-2026-00004', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:51:29'),
(87, 4, 'view', 'complaint', 4, 'Viewed complaint CTS-2026-00004', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:51:29'),
(88, 4, 'status_change', 'complaint', 4, 'Changed status to In Progress for CTS-2026-00004', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:52:03'),
(89, 4, 'view', 'complaint', 4, 'Viewed complaint CTS-2026-00004', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:52:03'),
(90, 4, 'forward', 'complaint', 4, 'Forwarded complaint CTS-2026-00004 to Legal', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:52:57'),
(91, 4, 'view', 'complaint', 4, 'Viewed complaint CTS-2026-00004', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:52:57'),
(92, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:55:02'),
(93, 4, 'view', 'complaint', 5, 'Viewed complaint CTS-2026-00005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:55:14'),
(94, 4, 'view', 'complaint', 5, 'Viewed complaint CTS-2026-00005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:56:15'),
(95, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:56:24'),
(96, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:57:21'),
(97, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:58:48'),
(98, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:58:56'),
(99, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 06:59:43'),
(100, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 07:01:34'),
(101, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 07:03:04'),
(102, 4, 'view', 'complaint', 2, 'Viewed complaint CTS-2026-00002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 07:03:17'),
(103, 4, 'view', 'complaint', 7, 'Viewed complaint CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 07:04:36'),
(104, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:24:42'),
(105, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:25:26'),
(106, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:39:21'),
(107, 6, 'login', 'auth', NULL, 'User logged in via Google OAuth', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:39:34'),
(108, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:39:44'),
(109, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:39:54'),
(110, 4, 'update', 'user', 6, 'Updated user: alex', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:40:09'),
(111, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:40:11'),
(112, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:41:27'),
(113, 4, 'update', 'user', 6, 'Updated user: alex', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:41:51'),
(114, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:41:53'),
(115, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:42:19'),
(116, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:43:07'),
(117, 6, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:43:17'),
(118, 6, 'update', 'user', 6, 'Changed profile avatar', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:43:31'),
(119, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:43:34'),
(120, 7, 'login', 'auth', NULL, 'User logged in via Google OAuth', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:43:55'),
(121, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:43:59'),
(122, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:44:53'),
(123, 4, 'update', 'user', 7, 'Deactivated user: bagwis_', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:45:02'),
(124, 4, 'update', 'user', 7, 'Activated user: bagwis_', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:45:06'),
(125, 4, 'update', 'user', 7, 'Deactivated user: bagwis_', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:45:09'),
(126, 4, 'update', 'user', 5, 'Deactivated user: escall dev', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:45:24'),
(127, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 08:45:35'),
(128, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 11:37:59'),
(129, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 11:38:10'),
(130, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 11:51:59'),
(131, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 11:53:43'),
(132, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 11:54:12'),
(133, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 00:04:58'),
(134, 4, 'view', 'complaint', 7, 'Viewed complaint CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 00:05:10'),
(135, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 00:10:41'),
(136, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 00:23:17'),
(137, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 00:35:12'),
(138, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 00:35:19'),
(139, 4, 'update', 'user', 5, 'Updated user: escall dev', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:01:10'),
(140, 4, 'update', 'user', 6, 'Updated user: alex', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:01:27'),
(141, 4, 'view', 'complaint', 10, 'Viewed complaint CTS-2026-00010', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:30:09'),
(142, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:36:04'),
(143, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:36:23'),
(144, 4, 'view', 'complaint', 11, 'Viewed complaint CTS-2026-00011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:36:35'),
(145, 4, 'accept', 'complaint', 11, 'Accepted complaint CTS-2026-00011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:37:46'),
(146, 4, 'view', 'complaint', 11, 'Viewed complaint CTS-2026-00011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:37:46'),
(147, 4, 'status_change', 'complaint', 11, 'Changed status to In Progress for CTS-2026-00011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:38:38'),
(148, 4, 'view', 'complaint', 11, 'Viewed complaint CTS-2026-00011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:38:38'),
(149, 4, 'update', 'user', 7, 'Activated user: bagwis_', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:39:44'),
(150, 4, 'update', 'user', 7, 'Deactivated user: bagwis_', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:39:50'),
(151, 4, 'update', 'user', 7, 'Activated user: bagwis_', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:39:53'),
(152, 4, 'update', 'user', 6, 'Updated user: alex', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:41:57'),
(153, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:42:11'),
(154, 6, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:42:21'),
(155, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:42:59'),
(156, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:43:05'),
(157, 4, 'view', 'complaint', 11, 'Viewed complaint CTS-2026-00011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:43:18'),
(158, 4, 'view', 'complaint', 11, 'Viewed complaint CTS-2026-00011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:49:03'),
(159, 4, 'view', 'complaint', 10, 'Viewed complaint CTS-2026-00010', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 01:49:09'),
(160, 4, 'create', 'user', 8, 'Created user: sdo admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:26:16'),
(161, 4, 'update', 'user', 8, 'Updated user: sdo admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:33:15'),
(162, 8, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '192.168.10.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:33:26'),
(163, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:33:58'),
(164, 6, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:34:12'),
(165, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:37:34'),
(166, 6, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:37:44'),
(167, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:39:50'),
(168, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:39:55'),
(169, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:40:01'),
(170, 6, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:40:09'),
(171, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:45:12'),
(172, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:45:16'),
(173, 4, 'view', 'complaint', 11, 'Viewed complaint CTS-2026-00011', NULL, NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-07 03:29:38'),
(174, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:32:42'),
(175, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-07 03:35:17'),
(176, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:35:26'),
(177, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:35:34'),
(178, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:35:44'),
(179, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:39:22'),
(180, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:40:33'),
(181, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:40:51'),
(182, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:42:48'),
(183, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:42:55'),
(184, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:43:02'),
(185, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:43:41'),
(186, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:46:02'),
(187, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:46:15'),
(188, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:47:56'),
(189, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:48:56'),
(190, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:51:30'),
(191, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:51:36'),
(192, 4, 'status_change', 'complaint', 1, 'Changed status to Closed for CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:51:44'),
(193, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:51:44'),
(194, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:54:50'),
(195, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:55:34'),
(196, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:55:35'),
(197, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:55:36'),
(198, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:55:38'),
(199, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:55:46'),
(200, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:55:47'),
(201, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:56:02'),
(202, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:56:24'),
(203, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:57:47'),
(204, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:58:59'),
(205, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 03:59:20'),
(206, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 04:03:08'),
(207, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 04:03:24'),
(208, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 05:11:22'),
(209, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 05:11:55'),
(210, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 05:12:15'),
(211, 4, 'view', 'complaint', 10, 'Viewed complaint CTS-2026-00010', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 05:12:58'),
(212, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 05:13:48'),
(213, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 05:15:25'),
(214, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 05:19:12'),
(215, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:10:22'),
(216, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:10:40'),
(217, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:10:54'),
(218, 4, 'view', 'complaint', 13, 'Viewed complaint CTS-2026-00013', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:22:12'),
(219, 4, 'accept', 'complaint', 13, 'Accepted complaint CTS-2026-00013', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:23:09'),
(220, 4, 'view', 'complaint', 13, 'Viewed complaint CTS-2026-00013', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:23:09'),
(221, 4, 'status_change', 'complaint', 13, 'Changed status to In Progress for CTS-2026-00013', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:23:41'),
(222, 4, 'view', 'complaint', 13, 'Viewed complaint CTS-2026-00013', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:23:41'),
(223, 4, 'forward', 'complaint', 13, 'Forwarded complaint CTS-2026-00013 to OSDS', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:24:13'),
(224, 4, 'view', 'complaint', 13, 'Viewed complaint CTS-2026-00013', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:24:13');
INSERT INTO `activity_log` (`id`, `user_id`, `action_type`, `entity_type`, `entity_id`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(225, 4, 'view', 'complaint', 13, 'Viewed complaint CTS-2026-00013', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:32:08'),
(226, 4, 'view', 'complaint', 12, 'Viewed complaint CTS-2026-00012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:37:22'),
(227, 4, 'view', 'complaint', 14, 'Viewed complaint CTS-2026-00014', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 06:38:29'),
(228, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:15:38'),
(229, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:16:10'),
(230, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:17:15'),
(231, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:23:00'),
(232, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:26:52'),
(233, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:27:29'),
(234, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:30:39'),
(235, 4, 'view', 'complaint', 15, 'Viewed complaint CTS-2026-00015', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:30:55'),
(236, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:30:59'),
(237, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:32:10'),
(238, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:32:31'),
(239, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:33:37'),
(240, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:33:46'),
(241, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:33:46'),
(242, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:39:59'),
(243, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:40:11'),
(244, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:47'),
(245, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:52'),
(246, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:54'),
(247, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:55'),
(248, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:55'),
(249, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:55'),
(250, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:55'),
(251, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:55'),
(252, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:56'),
(253, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:42:57'),
(254, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:43:09'),
(255, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:45:18'),
(256, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:48:36'),
(257, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:49:48'),
(258, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:50:04'),
(259, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:52:33'),
(260, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:52:35'),
(261, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:52:46'),
(262, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:58:11'),
(263, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 07:58:12'),
(264, 4, 'view', 'complaint', 17, 'Viewed complaint CTS-2026-00017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:00:19'),
(265, 4, 'view', 'complaint', 17, 'Viewed complaint CTS-2026-00017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:02:00'),
(266, 4, 'view', 'complaint', 17, 'Viewed complaint CTS-2026-00017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:03:21'),
(267, 4, 'view', 'complaint', 17, 'Viewed complaint CTS-2026-00017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:05:52'),
(268, 4, 'view', 'complaint', 17, 'Viewed complaint CTS-2026-00017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:06:08'),
(269, 4, 'view', 'complaint', 17, 'Viewed complaint CTS-2026-00017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:07:47'),
(270, 4, 'view', 'complaint', 17, 'Viewed complaint CTS-2026-00017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:08:53'),
(271, 4, 'view', 'complaint', 18, 'Viewed complaint CTS-2026-00018', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:11:10'),
(272, 4, 'view', 'complaint', 18, 'Viewed complaint CTS-2026-00018', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:28:31'),
(273, 4, 'view', 'complaint', 17, 'Viewed complaint CTS-2026-00017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:28:39'),
(274, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:28:57'),
(275, 4, 'view', 'complaint', 18, 'Viewed complaint CTS-2026-00018', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:34:35'),
(276, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 08:36:14'),
(277, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 02:36:18'),
(278, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 02:36:18'),
(279, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 02:36:48'),
(280, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 02:36:52'),
(281, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 02:37:52'),
(282, 4, 'accept', 'complaint', 19, 'Accepted complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 02:37:59'),
(283, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 02:37:59'),
(284, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 02:49:00'),
(285, 4, 'view', 'complaint', 5, 'Viewed complaint CTS-2026-00005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:11:19'),
(286, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:15:36'),
(287, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:25:03'),
(288, 4, 'view', 'complaint', 6, 'Viewed complaint CTS-2026-00006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:25:23'),
(289, 4, 'view', 'complaint', 15, 'Viewed complaint CTS-2026-00015', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:25:53'),
(290, 4, 'view', 'complaint', 1, 'Viewed complaint CTS-2026-00001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:26:52'),
(291, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:44:11'),
(292, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:44:53'),
(293, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:45:37'),
(294, 4, 'accept', 'complaint', 20, 'Accepted complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:54:42'),
(295, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:54:42'),
(296, 4, 'status_change', 'complaint', 20, 'Changed status to In Progress for CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:55:01'),
(297, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:55:01'),
(298, 4, 'status_change', 'complaint', 20, 'Changed status to Resolved for CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:55:09'),
(299, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:55:09'),
(300, 4, 'status_change', 'complaint', 20, 'Changed status to Closed for CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:55:13'),
(301, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:55:13'),
(302, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 03:55:41'),
(303, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 06:43:20'),
(304, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 06:43:36'),
(305, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:46:22'),
(306, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:47:47'),
(307, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:50:06'),
(308, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:52:13'),
(309, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:52:18'),
(310, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:53:19'),
(311, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:53:29'),
(312, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:54:46'),
(313, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:10'),
(314, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:18'),
(315, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:19'),
(316, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:19'),
(317, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:19'),
(318, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:19'),
(319, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:19'),
(320, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:19'),
(321, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:19'),
(322, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:20'),
(323, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:20'),
(324, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:20'),
(325, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:20'),
(326, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:20'),
(327, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:56:20'),
(328, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:57:22'),
(329, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:58:37'),
(330, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:00:22'),
(331, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:01:40'),
(332, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:01:50'),
(333, 4, 'view', 'complaint', 15, 'Viewed complaint CTS-2026-00015', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:01:58'),
(334, 4, 'view', 'complaint', 14, 'Viewed complaint CTS-2026-00014', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:02:05'),
(335, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:02:07'),
(336, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:03:06'),
(337, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:03:15'),
(338, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:04:06'),
(339, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:04:16'),
(340, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:04:16'),
(341, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:04:16'),
(342, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:04:16'),
(343, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:04:16'),
(344, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:04:17'),
(345, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:05:00'),
(346, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 09:05:42'),
(347, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:27:55'),
(348, 4, 'view', 'complaint', 20, 'Viewed complaint CTS-2026-00020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:27:55'),
(349, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:50:55'),
(350, 6, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:51:04'),
(351, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:51:26'),
(352, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:51:32'),
(353, 4, 'update', 'user', 6, 'Updated user: alex', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:51:45'),
(354, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:51:47'),
(355, 6, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:51:58'),
(356, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:52:03'),
(357, 6, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:52:34'),
(358, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:52:43'),
(359, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:52:48'),
(360, 4, 'update', 'user', 6, 'Updated user: alex', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:52:56'),
(361, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 00:52:58'),
(362, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:03:58'),
(363, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:04:52'),
(364, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:05:49'),
(365, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:06:32'),
(366, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:10:54'),
(367, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:13:24'),
(368, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:37:58'),
(369, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:38:00'),
(370, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:46:51'),
(371, 4, 'view', 'complaint', 7, 'Viewed complaint CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:55:01'),
(372, 4, 'accept', 'complaint', 7, 'Accepted complaint CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:55:08'),
(373, 4, 'view', 'complaint', 7, 'Viewed complaint CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:55:08'),
(374, 4, 'status_change', 'complaint', 7, 'Changed status to In Progress for CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:55:16'),
(375, 4, 'view', 'complaint', 7, 'Viewed complaint CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:55:16'),
(376, 4, 'status_change', 'complaint', 7, 'Changed status to Resolved for CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:55:32'),
(377, 4, 'view', 'complaint', 7, 'Viewed complaint CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:55:32'),
(378, 4, 'status_change', 'complaint', 7, 'Changed status to Closed for CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:55:38'),
(379, 4, 'view', 'complaint', 7, 'Viewed complaint CTS-2026-00007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:55:38'),
(380, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:59:04'),
(381, 4, 'view', 'complaint', 18, 'Viewed complaint CTS-2026-00018', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:59:10'),
(382, 4, 'view', 'complaint', 16, 'Viewed complaint CTS-2026-00016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:59:15'),
(383, 4, 'view', 'complaint', 19, 'Viewed complaint CTS-2026-00019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 01:59:22'),
(384, 4, 'view', 'complaint', 21, 'Viewed complaint CTS-2026-00021', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:02:25'),
(385, 4, 'view', 'complaint', 22, 'Viewed complaint CTS-2026-00022', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:03:31'),
(386, 4, 'view', 'complaint', 23, 'Viewed complaint CTS-2026-00023', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:04:26'),
(387, 4, 'view', 'complaint', 23, 'Viewed complaint CTS-2026-00023', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:04:40'),
(388, 4, 'view', 'complaint', 23, 'Viewed complaint CTS-2026-00023', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:13:53'),
(389, 4, 'view', 'complaint', 24, 'Viewed complaint CTS-2026-00024', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:17:15'),
(390, 4, 'view', 'complaint', 24, 'Viewed complaint CTS-2026-00024', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:19:03'),
(391, 4, 'view', 'complaint', 24, 'Viewed complaint CTS-2026-00024', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:20:45'),
(392, 4, 'view', 'complaint', 25, 'Viewed complaint CTS-2026-00025', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:21:35'),
(393, 4, 'view', 'complaint', 25, 'Viewed complaint CTS-2026-00025', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:52:55'),
(394, 4, 'view', 'complaint', 25, 'Viewed complaint CTS-2026-00025', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:58:46'),
(395, 4, 'view', 'complaint', 22, 'Viewed complaint CTS-2026-00022', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:58:57'),
(396, 4, 'view', 'complaint', 22, 'Viewed complaint CTS-2026-00022', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:59:05'),
(397, 4, 'view', 'complaint', 25, 'Viewed complaint CTS-2026-00025', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:59:12'),
(398, 4, 'view', 'complaint', 17, 'Viewed complaint CTS-2026-00017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 02:59:18'),
(399, 4, 'view', 'complaint', 26, 'Viewed complaint CTS-2026-00026', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 03:00:17'),
(400, 4, 'view', 'complaint', 27, 'Viewed complaint CTS-2026-00027', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 03:20:23'),
(401, 4, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 03:20:54'),
(402, 4, 'login', 'auth', NULL, 'User logged in via email/password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 03:20:59');

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_roles`
--

INSERT INTO `admin_roles` (`id`, `name`, `description`, `permissions`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Full system access', '{\"all\": true}', 1, '2026-01-06 03:12:00', '2026-01-06 03:12:00'),
(2, 'Admin', 'Manage complaints and users', '{\"complaints\": true, \"users\": true, \"reports\": true}', 1, '2026-01-06 03:12:00', '2026-01-06 03:12:00'),
(3, 'Staff', 'Process complaints', '{\"complaints\": true, \"reports\": false}', 1, '2026-01-06 03:12:00', '2026-01-06 03:12:00'),
(4, 'Viewer', 'View only access', '{\"complaints_view\": true}', 1, '2026-01-06 03:12:00', '2026-01-06 03:12:00');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `email`, `password_hash`, `full_name`, `role_id`, `google_id`, `avatar_url`, `unit`, `is_active`, `last_login`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 'admin@deped-sanpedro.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 1, NULL, NULL, 'OSDS', 1, NULL, '2026-01-06 03:12:00', '2026-01-06 03:12:00', NULL),
(3, 'escall@deped-sanpedro.ph', '$2y$10$89WbpOITDkUUJBAmsv7CuO0.OTr9zKdQevYPBP5PX22X4nBeRk2IS', 'System Administrator', 1, NULL, NULL, 'OSDS', 1, '2026-01-06 03:26:31', '2026-01-06 03:26:19', '2026-01-06 03:26:31', NULL),
(4, 'joerenzescallente027@gmail.com', '$2y$10$3983W2Pb75U0hdf2PxWyPOQK9Plh440iM.bD.FfoPzCOq6Z92erg6', 'Alexander Joerenz Escallente', 1, NULL, '/SDO-cts/uploads/avatars/avatar_4_1767678739.jpg', 'SGOD', 1, '2026-01-09 03:20:59', '2026-01-06 03:37:05', '2026-01-09 03:20:59', 3),
(5, 'escall.dev027@gmail.com', NULL, 'escall dev', 4, '107714000475651491856', 'https://lh3.googleusercontent.com/a/ACg8ocImqRNkaj-XNIubC4kamlFKrGKdzWwKQQWW3LW3UCtGfkE2A98=s96-c', NULL, 0, '2026-01-06 04:05:26', '2026-01-06 04:05:26', '2026-01-07 01:01:10', NULL),
(6, 'joerenz.dev@gmail.com', '$2y$10$DGPpzC/weoc/rCMUhjYd.Ot.DNvwbXbJlurMK.dmjEg8M0AyCaafq', 'alex', 2, '108143253005440248236', '/SDO-cts/uploads/avatars/avatar_6_1767689011.jpg', 'SGOD', 1, '2026-01-09 00:52:34', '2026-01-06 08:39:34', '2026-01-09 00:52:56', NULL),
(7, 'bagwistv09@gmail.com', NULL, 'bagwis_', 3, '115985323403557869607', 'https://lh3.googleusercontent.com/a/ACg8ocLHNmdjfyFgJklYgbNAWvXt5iwnFAq6lqZJ44fLz06O_lBdIz8=s96-c', NULL, 1, '2026-01-06 08:43:55', '2026-01-06 08:43:55', '2026-01-07 01:39:53', NULL),
(8, 'ict.sanpedrocity@deped.com.ph', '$2y$10$fbSbmKisLpjHlpm9ykSKzei0ySmzKaRNutV1R.J72YNW3jtySpIau', 'sdo admin', 1, NULL, NULL, 'OSDS', 1, '2026-01-07 02:33:26', '2026-01-07 02:26:16', '2026-01-07 02:33:26', 4);

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(20) NOT NULL,
  `referred_to` enum('OSDS','SGOD','CID','Others') NOT NULL,
  `referred_to_other` varchar(255) DEFAULT NULL,
  `date_petsa` datetime NOT NULL,
  `name_pangalan` varchar(255) NOT NULL,
  `address_tirahan` text NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `involved_full_name` varchar(255) NOT NULL,
  `involved_position` varchar(255) NOT NULL,
  `involved_address` text NOT NULL,
  `involved_school_office_unit` varchar(255) NOT NULL,
  `narration_complaint` text NOT NULL,
  `narration_complaint_page2` text DEFAULT NULL,
  `desired_action_relief` text NOT NULL,
  `certification_agreed` tinyint(1) NOT NULL DEFAULT 0,
  `printed_name_pangalan` varchar(255) NOT NULL,
  `signature_type` enum('digital','typed') NOT NULL DEFAULT 'typed',
  `signature_data` text DEFAULT NULL,
  `date_signed` date NOT NULL,
  `status` enum('pending','accepted','in_progress','resolved','returned','closed') NOT NULL DEFAULT 'pending',
  `accepted_at` timestamp NULL DEFAULT NULL,
  `accepted_by` int(11) DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `returned_by` int(11) DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `assigned_unit` varchar(50) DEFAULT NULL,
  `handled_by` int(11) DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `reference_number`, `referred_to`, `referred_to_other`, `date_petsa`, `name_pangalan`, `address_tirahan`, `contact_number`, `email_address`, `involved_full_name`, `involved_position`, `involved_address`, `involved_school_office_unit`, `narration_complaint`, `narration_complaint_page2`, `desired_action_relief`, `certification_agreed`, `printed_name_pangalan`, `signature_type`, `signature_data`, `date_signed`, `status`, `accepted_at`, `accepted_by`, `returned_at`, `returned_by`, `return_reason`, `assigned_unit`, `handled_by`, `is_locked`, `created_at`, `updated_at`) VALUES
(1, 'CTS-2026-00001', 'Others', 'cysdddd', '2026-01-05 16:50:00', 'Alexander Joerenz Escallente', '#67 A. Olivarez St. Brgy Santo Niño\r\n', '09100668203', 'joerenzescallente027@gmail.com', 'Alexander Joerenz Escallente', 'staff', '#67 A. Olivarez St. Brgy Santo Niño\r\n', 'sdo', 'PRIVACY NOTICE: We collect the following personal information from you when you manually or electronically submit to us your inquiry/ies: Name, Address, E-mail address, Contact Number, ID information. The collected personal information will be utilized solely for documentation and processing of your request within DepEd and, when appropriate, endorsement to other government agency/ies that has/have jurisdiction over the subject of your inquiry. Only authorized DepEd personnel have access to this personal information, the exchange of which will be facilitated through email and/or hard copy. DepEd will only retain personal data as long as necessary for the fulfillment of the purpose. Only authorized DepEd personnel have access to this personal information, the exchange of which will be facilitated through email and/or hard copy. DepEd will only retain personal data as long as necessary for the fulfillment of the purpose.', NULL, '', 1, 'alexander', 'typed', 'Alexander Joerenz Escallente', '2026-01-05', 'closed', '2026-01-06 03:34:43', 3, NULL, NULL, NULL, 'OSDS', 4, 1, '2026-01-05 08:50:00', '2026-01-07 03:51:44'),
(2, 'CTS-2026-00002', 'SGOD', '', '2026-01-06 08:15:26', 'Alexander Joerenz Escallente', '#67 A. Olivarez St. Brgy Santo Niño\r\n', '09100668203', 'joerenzescallente027@gmail.com', 'Alexander Joerenz Escallente', 'staff', '#67 A. Olivarez St. Brgy Santo Niño\r\n#67 A. Olivarez St. Brgy Santo Niño', 'sdo', 'PRIVACY NOTICE: We collect the following personal information from you when you manually or electronically submit to us your inquiry/ies: Name, Address, E-mail address, Contact Number, ID information. The collected personal information will be utilized solely for documentation and processing of your request within DepEd and, when appropriate, endorsement to other government agency/ies that has/have jurisdiction over the subject of your inquiry. Only authorized DepEd personnel have access to this personal information, the exchange of which will be facilitated through email and/or hard copy. DepEd will only retain personal data as long as necessary for the fulfillment of the purpose.', NULL, '', 1, 'Alexander Joerenz Escallente', 'typed', 'Alexander Joerenz Escallente', '2026-01-06', 'accepted', '2026-01-06 05:21:50', 4, NULL, NULL, NULL, NULL, 4, 1, '2026-01-06 00:15:26', '2026-01-06 05:21:50'),
(3, 'CTS-2026-00003', 'OSDS', '', '2026-01-06 08:45:13', 'Seijun Cutie', '10 Molave St.', '09123456789', 'seijuncutie@gmail.com', 'Sei Cutie', 'Teacher', '10 Molave St.', 'School', 'PRIVACY NOTICE: We collect the following personal information from you when you manually or electronically submit to us your inquiry/ies: Name, Address, E-mail address, Contact Number, ID information. The collected personal information will be utilized solely for documentation and processing of your request within DepEd and, when appropriate, endorsement to other government agency/ies that has/have jurisdiction over the subject of your inquiry. Only authorized DepEd personnel have access to this personal information, the exchange of which will be facilitated through email and/or hard copy. DepEd will only retain personal data as long as necessary for the fulfillment of the purpose.', NULL, '', 1, 'Seijun Cutie', 'typed', 'Seijun Cutie', '2026-01-06', 'accepted', '2026-01-06 06:40:58', 4, NULL, NULL, NULL, NULL, 4, 1, '2026-01-06 00:45:13', '2026-01-06 06:40:58'),
(4, 'CTS-2026-00004', 'SGOD', '', '2026-01-06 08:48:51', 'Spongebob', '19 Tinkerbel', '09867554632', 'spongebob@gmail.com', 'Patrick', 'Staff', 'egfbjbdgjksngjksdg', 'sdo', 'jnfdsgbuieswdfnjk sadjfgnvjdgf ', NULL, '', 1, 'Spongebob', 'typed', 'Spongebob', '2026-01-06', 'in_progress', '2026-01-06 06:51:29', 4, NULL, NULL, NULL, 'Legal', 4, 1, '2026-01-06 00:48:51', '2026-01-06 06:52:57'),
(5, 'CTS-2026-00005', 'CID', '', '2026-01-06 08:52:21', 'Pepito Manaloto', '4 greatland', '09876456636', 'pepito@gmail.com', 'Elsa', 'Principal', '4 Greatland', 'Unit', 'dsnjgokjds kjdgnng', NULL, '', 1, 'Pepito Manaloto', 'typed', 'Pepito Manaloto', '2026-01-06', 'accepted', '2026-01-06 06:35:11', 4, NULL, NULL, NULL, NULL, 4, 1, '2026-01-06 00:52:21', '2026-01-06 06:35:11'),
(6, 'CTS-2026-00006', 'Others', 'natatae me', '2026-01-06 08:58:24', 'Joerenz Escallente', '10 Comfort Room', '09336543123', 'joerenz@gmail.com', 'Joven Fernandez', 'Bestfriend', '23 Venjo', 'BFF', 'HAHAHAHAHHAHAHAHAHHAHAHA', NULL, '', 1, 'Joerenz Escallente', 'typed', 'Joerenz Escallente', '2026-01-06', 'in_progress', '2026-01-06 06:38:50', 4, '2026-01-06 05:20:20', 4, 'pangit', 'Legal', 4, 1, '2026-01-06 00:58:24', '2026-01-06 06:39:01'),
(7, 'CTS-2026-00007', 'Others', 'natatae me', '2026-01-06 14:56:06', 'Al3ex', 'sfdusgfvhcdsajh', '09100668207', 'alex@gmail.com', 'Squidward', 'Octopus', '#67 A. Olivarez St. Brgy Santo Niño\r\n', 'bikini bottom', 'aabrarbwbabsxxdfdebfesgsdgdsgsPRIVACY NOTICE: We collect the following personal information from you when you manually or electronically submit to us your inquiry/ies: Name, Address, E-mail address, Contact Number, ID information. The collected personal information will be utilized solely for documentation and processing of your request within DepEd and, when appropriate, endorsement to other government agency/ies that has/have jurisdiction over the subject of your inquiry. Only authorized DepEd personnel have access to this personal information, the exchange of which will be facilitated through email and/or hard copy. DepEd will only retain personal data as long as necessary for the fulfillment of the purpose.', NULL, '', 1, 'Mr. Krabs', 'typed', 'Mr. Krabs', '2026-01-06', 'closed', '2026-01-09 01:55:08', 4, NULL, NULL, NULL, NULL, 4, 1, '2026-01-06 06:56:06', '2026-01-09 01:55:38'),
(8, 'CTS-2026-00008', 'SGOD', '', '2026-01-07 08:44:27', 'Algen Loveres', '12 Bahay', '09765774363', 'Algen@gmail.com', 'Cedrick', 'staff', '14 House', 'School', 'gfghfjhgjhgkj', 'ghvhgvhbbjh', '', 1, 'Algen Loveres', 'typed', 'Algen Loveres', '2026-01-07', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 00:44:27', '2026-01-07 00:44:27'),
(9, 'CTS-2026-00009', 'SGOD', '', '2026-01-07 09:19:39', 'Name', '23 fasfah', '09776835172', 'name@gmail.com', 'fgfsg', 'Octopus', 'gvsdhgsaf', 'bikini bottom', 'dvfhdsj', 'jsbfjknsg', '', 1, 'name', 'typed', 'name', '2026-01-07', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 01:19:39', '2026-01-07 01:19:39'),
(10, 'CTS-2026-00010', 'CID', '', '2026-01-07 09:24:53', 'sandy', 'bikini bottom', '09010203040', 'sandy@gmail.com', 'patrick', 'starfish', 'bikini bottom', 'krusty krab', 'haahhahh', 'kinain burger patty ko', '', 1, 'sandy manaloto', 'typed', 'sandy manaloto', '2026-01-07', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 01:24:53', '2026-01-07 01:24:53'),
(11, 'CTS-2026-00011', 'SGOD', '', '2026-01-07 09:35:41', 'spongebob', 'bikini bottom', '09122342414', 'spongebobb@gmail.com', 'patrick', 'star', 'bikini bottom', 'krusy', 'adsdasddads', 'dffefsdfdfsf', '', 1, 'sandy', 'typed', 'sandy', '2026-01-07', 'in_progress', '2026-01-07 01:37:46', 4, NULL, NULL, NULL, NULL, 4, 1, '2026-01-07 01:35:41', '2026-01-07 01:38:38'),
(12, 'CTS-2026-00012', 'OSDS', '', '2026-01-07 10:24:06', 'Sunny', 'earth', '09887754362', 'sunny@gmail.com', 'Rainy', 'Teacher', 'School', 'Office', 'hjsfhjdsgnnkn', 'hjkfenjkfkn', '', 1, 'Sunny', 'typed', 'Sunny', '2026-01-07', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 02:24:06', '2026-01-07 02:24:06'),
(13, 'CTS-2026-00013', 'OSDS', '', '2026-01-07 14:21:11', 'barney', 'bikini bottom', '09120202020', 'barneyy@gmail.com', 'thomas', 'staff', 'bikini bottom', 'sdo', '• Please attach your supporting documents / Certified true copies of documentary evidence and affidavits of witnesses if any.\r\n\r\n(Maaaring ilakip ang inyong mga suportang dokumento/Certified True Copies ng mga dokumentaryong ebidensya at mga sinumpaang salaysay ng mga saksi, kung mayroon)', '• Please attach your supporting documents / Certified true copies of documentary evidence and affidavits of witnesses if any.\r\n\r\n(Maaaring ilakip ang inyong mga suportang dokumento/Certified True Copies ng mga dokumentaryong ebidensya at mga sinumpaang salaysay ng mga saksi, kung mayroon)', '', 1, 'thomas and friends', 'typed', 'thomas and friends', '2026-01-07', 'in_progress', '2026-01-07 06:23:09', 4, NULL, NULL, NULL, 'OSDS', 4, 1, '2026-01-07 06:21:11', '2026-01-07 06:24:13'),
(14, 'CTS-2026-00014', 'Others', 'cysd', '2026-01-07 14:38:22', 'ggs', 'sdo', '09100668203', 'haha@gmail.com', 'sdad', 'staff', '#67 A. Olivarez St. Brgy Santo Niño\r\n#67 A. Olivarez St. Brgy Santo Niño', 'gotham', 'sdasdda', 'sdasddad', '', 1, 'hahaha', 'typed', 'hahaha', '2026-01-07', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 06:38:22', '2026-01-07 06:38:22'),
(15, 'CTS-2026-00015', 'OSDS', '', '2026-01-07 15:15:02', 'example', 'sdo', '09989898876', 'sample@gmail.com', 'sample', 'superhero', 'spl', 'bio', 'hahaahhaahahha', 'ahahahhaahaahh', '', 1, 'sample a', 'typed', 'sample a', '2026-01-07', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 07:15:02', '2026-01-07 07:15:02'),
(16, 'CTS-2026-00016', 'OSDS', '', '2026-01-07 15:15:30', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-07', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 07:15:30', '2026-01-07 07:15:30'),
(17, 'CTS-2026-00017', 'OSDS', '', '2026-01-07 15:59:54', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-07', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 07:59:54', '2026-01-07 07:59:54'),
(18, 'CTS-2026-00018', 'OSDS', '', '2026-01-07 16:10:39', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-07', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 08:10:39', '2026-01-07 08:10:39'),
(19, 'CTS-2026-00019', 'OSDS', '', '2026-01-07 16:36:04', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-07', 'accepted', '2026-01-08 02:37:59', 4, NULL, NULL, NULL, NULL, 4, 1, '2026-01-07 08:36:04', '2026-01-08 02:37:59'),
(20, 'CTS-2026-00020', 'OSDS', '', '2026-01-08 11:43:53', 'Joe', 'United', '09112536473', 'joe@gmail.com', 'Jay', 'staff', 'Ewan', 'School', 'hhsgafbasdbfg dsahfvhjb', '', '', 1, 'Joe', 'typed', 'Joe', '2026-01-08', 'closed', '2026-01-08 03:54:42', 4, NULL, NULL, NULL, NULL, 4, 1, '2026-01-08 03:43:53', '2026-01-08 03:55:13'),
(21, 'CTS-2026-00021', 'OSDS', '', '2026-01-09 10:01:41', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-09', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-09 02:01:41', '2026-01-09 02:01:41'),
(22, 'CTS-2026-00022', 'OSDS', '', '2026-01-09 10:03:23', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-09', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-09 02:03:23', '2026-01-09 02:03:23'),
(23, 'CTS-2026-00023', 'OSDS', '', '2026-01-09 10:04:21', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-09', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-09 02:04:21', '2026-01-09 02:04:21'),
(24, 'CTS-2026-00024', 'OSDS', '', '2026-01-09 10:16:54', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-09', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-09 02:16:54', '2026-01-09 02:16:54'),
(25, 'CTS-2026-00025', 'OSDS', '', '2026-01-09 10:21:30', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-09', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-09 02:21:30', '2026-01-09 02:21:30'),
(26, 'CTS-2026-00026', 'OSDS', '', '2026-01-09 11:00:12', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', NULL, '2026-01-09', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-09 03:00:12', '2026-01-09 03:00:12'),
(27, 'CTS-2026-00027', 'OSDS', '', '2026-01-09 11:20:12', 'AJ', 'Tabi tabi', '09768558375', 'aj@gmail.com', 'Poopie', 'teacher', 'Everywhere', 'School', 'dghdhjsg', 'hfjkenngjn', '', 1, 'AJ', 'typed', 'AJ', '2026-01-09', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-09 03:20:12', '2026-01-09 03:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_assignments`
--

CREATE TABLE `complaint_assignments` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `assigned_to_unit` varchar(50) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_assignments`
--

INSERT INTO `complaint_assignments` (`id`, `complaint_id`, `assigned_to_unit`, `assigned_by`, `notes`, `created_at`) VALUES
(1, 1, 'OSDS', 3, '', '2026-01-06 03:36:08'),
(2, 6, 'Legal', 4, '', '2026-01-06 06:38:54'),
(3, 4, 'Legal', 4, 'for legal na', '2026-01-06 06:52:57'),
(4, 13, 'OSDS', 4, 'for osds na', '2026-01-07 06:24:13');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_documents`
--

CREATE TABLE `complaint_documents` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `category` varchar(50) DEFAULT 'supporting',
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_documents`
--

INSERT INTO `complaint_documents` (`id`, `complaint_id`, `file_name`, `original_name`, `file_type`, `file_size`, `category`, `upload_date`) VALUES
(1, 8, 'f2d92pmq069alsmnnrhqn5m7rq_695dac6153d8c.pdf', 'ACCOMPLISHMENT-REPORT-PRACTICUM WEEK 1.pdf', 'application/pdf', 282544, 'supporting', '2026-01-07 00:44:27'),
(2, 14, '1csvm6g6cpogl026rq64r90efk_695dff56c6d94.pdf', 'WEEK-4.pdf', 'application/pdf', 734725, 'supporting', '2026-01-07 06:38:22'),
(3, 16, '1csvm6g6cpogl026rq64r90efk_695e08072d327.png', 'Christmas Party-contributions.png', 'image/png', 88355, 'supporting', '2026-01-07 07:15:30'),
(4, 17, '1csvm6g6cpogl026rq64r90efk_695e12602b3f3.pdf', 'resume-alexander-joerenz-escallente.pdf-2.pdf', 'application/pdf', 150515, 'supporting', '2026-01-07 07:59:54'),
(5, 18, '1csvm6g6cpogl026rq64r90efk_695e14f158b1e.jpg', 'COMPLAINT-ASSISTED-FORM_1.jpg', 'image/jpeg', 988915, 'supporting', '2026-01-07 08:10:39'),
(6, 19, '1csvm6g6cpogl026rq64r90efk_695e1af152384.jpg', 'COMPLAINT-ASSISTED-FORM_1 (1).jpg', 'image/jpeg', 988915, 'supporting', '2026-01-07 08:36:04'),
(7, 20, 'rmpad40ad1iheuq0ol7htfiin5_695f27f49d276.pdf', 'SIA2_WEEK2.pdf', 'application/pdf', 507310, 'supporting', '2026-01-08 03:43:53'),
(8, 21, '3dj52i5955oaf6lj7lttpai0cd_6960616d9c2c6.png', 'attendance monitoring system architecture diagram.png', 'image/png', 5796059, 'supporting', '2026-01-09 02:01:41'),
(9, 21, '3dj52i5955oaf6lj7lttpai0cd_6960616d9c4df.png', 'attendance monitoring system architecture diagram.png', 'image/png', 5796059, 'supporting', '2026-01-09 02:01:41'),
(10, 22, '3dj52i5955oaf6lj7lttpai0cd_696061e9a76b7.pdf', 'resume-alexander-joerenz-escallente.pdf-2.pdf', 'application/pdf', 150515, 'supporting', '2026-01-09 02:03:23'),
(11, 22, '3dj52i5955oaf6lj7lttpai0cd_696061e9a78bc.png', 'student enrollment system architecture diagram.png', 'image/png', 5782385, 'supporting', '2026-01-09 02:03:23'),
(12, 23, '3dj52i5955oaf6lj7lttpai0cd_69606216c0277.pdf', 'resume-alexander-joerenz-escallente.pdf-2.pdf', 'application/pdf', 150515, 'supporting', '2026-01-09 02:04:21'),
(13, 23, '3dj52i5955oaf6lj7lttpai0cd_69606216c0413.png', 'attendance monitoring system architecture diagram.png', 'image/png', 5796059, 'supporting', '2026-01-09 02:04:21'),
(14, 24, '3dj52i5955oaf6lj7lttpai0cd_696064aa483df.pdf', 'resume-alexander-joerenz-escallente.pdf-2.pdf', 'application/pdf', 150515, 'valid_id', '2026-01-09 02:16:54'),
(15, 24, '3dj52i5955oaf6lj7lttpai0cd_696064aa485b7.png', 'attendance monitoring system architecture diagram.png', 'image/png', 5796059, 'handwritten_form', '2026-01-09 02:16:54'),
(16, 25, '3dj52i5955oaf6lj7lttpai0cd_6960661f393d9.pdf', 'resume-alexander-joerenz-escallente.pdf-2.pdf', 'application/pdf', 150515, 'valid_id', '2026-01-09 02:21:30'),
(17, 25, '3dj52i5955oaf6lj7lttpai0cd_6960661f3959b.png', 'student enrollment system architecture diagram.png', 'image/png', 5782385, 'handwritten_form', '2026-01-09 02:21:30'),
(18, 26, '3dj52i5955oaf6lj7lttpai0cd_69606f39c70a5.jpg', 'powerlines.jpg', 'image/jpeg', 70436, 'valid_id', '2026-01-09 03:00:12'),
(19, 26, '3dj52i5955oaf6lj7lttpai0cd_69606f39c74c7.jpg', 'aokigahara-forest-dark-style.jpg', 'image/jpeg', 2867169, 'handwritten_form', '2026-01-09 03:00:12'),
(20, 27, '3dj52i5955oaf6lj7lttpai0cd_6960704dbb50d.jpg', 'webcam-toy-photo2.jpg', 'image/jpeg', 38756, 'supporting', '2026-01-09 03:20:12'),
(21, 27, '3dj52i5955oaf6lj7lttpai0cd_696070c1b4e3d.jpg', 'webcam-toy-photo1.jpg', 'image/jpeg', 53409, 'valid_id', '2026-01-09 03:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_history`
--

CREATE TABLE `complaint_history` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `status` enum('pending','accepted','in_progress','resolved','returned','closed') NOT NULL,
  `notes` text DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT 'System',
  `admin_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_history`
--

INSERT INTO `complaint_history` (`id`, `complaint_id`, `status`, `notes`, `updated_by`, `admin_user_id`, `created_at`) VALUES
(1, 1, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-05 08:50:00'),
(2, 2, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-06 00:15:26'),
(3, 3, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-06 00:45:13'),
(4, 4, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-06 00:48:51'),
(5, 5, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-06 00:52:21'),
(6, 6, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-06 00:58:24'),
(7, 1, 'accepted', 'ok noted', 'System Administrator', 3, '2026-01-06 03:34:43'),
(8, 1, 'in_progress', 'okk', 'System Administrator', 3, '2026-01-06 03:35:08'),
(9, 1, 'in_progress', 'Forwarded to Office of the Schools Division Superintendent: ', 'System Administrator', 3, '2026-01-06 03:36:08'),
(10, 6, 'returned', 'pangit', 'Alexander Joerenz Escallente', 4, '2026-01-06 05:20:20'),
(11, 2, 'accepted', 'tae', 'Alexander Joerenz Escallente', 4, '2026-01-06 05:21:50'),
(12, 5, 'accepted', 'Complaint accepted for processing', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:35:11'),
(13, 1, 'resolved', '', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:37:30'),
(14, 6, 'pending', '', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:38:47'),
(15, 6, 'accepted', 'Complaint accepted for processing', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:38:50'),
(16, 6, 'accepted', 'Forwarded to Legal Unit: ', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:38:54'),
(17, 6, 'in_progress', '', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:39:01'),
(18, 3, 'accepted', 'Complaint accepted for processing', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:40:58'),
(19, 4, 'accepted', 'okay sige', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:51:29'),
(20, 4, 'in_progress', '', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:52:03'),
(21, 4, 'in_progress', 'Forwarded to Legal Unit: for legal na', 'Alexander Joerenz Escallente', 4, '2026-01-06 06:52:57'),
(22, 7, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-06 06:56:06'),
(23, 8, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 00:44:27'),
(24, 9, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 01:19:39'),
(25, 10, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 01:24:53'),
(26, 11, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 01:35:41'),
(27, 11, 'accepted', 'okay na', 'Alexander Joerenz Escallente', 4, '2026-01-07 01:37:46'),
(28, 11, 'in_progress', '', 'Alexander Joerenz Escallente', 4, '2026-01-07 01:38:38'),
(29, 12, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 02:24:06'),
(30, 1, 'closed', '', 'Alexander Joerenz Escallente', 4, '2026-01-07 03:51:44'),
(31, 13, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 06:21:11'),
(32, 13, 'accepted', 'okay noted', 'Alexander Joerenz Escallente', 4, '2026-01-07 06:23:09'),
(33, 13, 'in_progress', 'forwarding', 'Alexander Joerenz Escallente', 4, '2026-01-07 06:23:41'),
(34, 13, 'in_progress', 'Forwarded to Office of the Schools Division Superintendent: for osds na', 'Alexander Joerenz Escallente', 4, '2026-01-07 06:24:13'),
(35, 14, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 06:38:22'),
(36, 15, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 07:15:02'),
(37, 16, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 07:15:30'),
(38, 17, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 07:59:54'),
(39, 18, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 08:10:39'),
(40, 19, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-07 08:36:04'),
(41, 19, 'accepted', 'okay na', 'Alexander Joerenz Escallente', 4, '2026-01-08 02:37:59'),
(42, 20, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-08 03:43:53'),
(43, 20, 'accepted', 'Complaint accepted for processing', 'Alexander Joerenz Escallente', 4, '2026-01-08 03:54:42'),
(44, 20, 'in_progress', '', 'Alexander Joerenz Escallente', 4, '2026-01-08 03:55:01'),
(45, 20, 'resolved', '', 'Alexander Joerenz Escallente', 4, '2026-01-08 03:55:09'),
(46, 20, 'closed', '', 'Alexander Joerenz Escallente', 4, '2026-01-08 03:55:13'),
(47, 7, 'accepted', 'gege', 'Alexander Joerenz Escallente', 4, '2026-01-09 01:55:08'),
(48, 7, 'in_progress', 'otw', 'Alexander Joerenz Escallente', 4, '2026-01-09 01:55:16'),
(49, 7, 'resolved', '', 'Alexander Joerenz Escallente', 4, '2026-01-09 01:55:32'),
(50, 7, 'closed', '', 'Alexander Joerenz Escallente', 4, '2026-01-09 01:55:38'),
(51, 21, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-09 02:01:41'),
(52, 22, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-09 02:03:23'),
(53, 23, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-09 02:04:21'),
(54, 24, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-09 02:16:54'),
(55, 25, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-09 02:21:30'),
(56, 26, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-09 03:00:12'),
(57, 27, 'pending', 'Complaint submitted successfully', 'System', NULL, '2026-01-09 03:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `idx_reference` (`reference_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_petsa` (`date_petsa`);

--
-- Indexes for table `complaint_assignments`
--
ALTER TABLE `complaint_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_complaint_id` (`complaint_id`);

--
-- Indexes for table `complaint_documents`
--
ALTER TABLE `complaint_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complaint_id` (`complaint_id`);

--
-- Indexes for table `complaint_history`
--
ALTER TABLE `complaint_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complaint_id` (`complaint_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=403;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `complaint_assignments`
--
ALTER TABLE `complaint_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `complaint_documents`
--
ALTER TABLE `complaint_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `complaint_history`
--
ALTER TABLE `complaint_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD CONSTRAINT `admin_users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`),
  ADD CONSTRAINT `admin_users_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_assignments`
--
ALTER TABLE `complaint_assignments`
  ADD CONSTRAINT `complaint_assignments_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_assignments_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `complaint_documents`
--
ALTER TABLE `complaint_documents`
  ADD CONSTRAINT `complaint_documents_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaint_history`
--
ALTER TABLE `complaint_history`
  ADD CONSTRAINT `complaint_history_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
