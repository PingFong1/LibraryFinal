-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2025 at 05:53 PM
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
-- Database: `library management data`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `action_description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action_type`, `action_description`, `ip_address`, `timestamp`) VALUES
(279, 29, 'book_borrow', 'Borrowed 	book: Clean Code', '192.168.1.100', '2025-05-12 14:50:57'),
(280, 10, 'book_approval', 'Approved book borrowing for user 29', '192.168.1.200', '2025-05-12 14:50:57'),
(286, 31, 'book_borrow', 'Borrowed book: Advanced Machine Learning', '192.168.1.120', '2025-05-12 14:59:28'),
(287, 32, 'book_borrow', 'Borrowed book: Digital Transformation', '192.168.1.125', '2025-05-12 14:59:28'),
(288, 35, 'book_borrow', 'Borrowed book: Renewable Energy', '192.168.1.130', '2025-05-12 14:59:28'),
(289, 36, 'book_borrow', 'Borrowed book: Artificial Intelligence Ethics', '192.168.1.135', '2025-05-12 14:59:28'),
(291, 10, 'logout', 'User logged out', '::1', '2025-05-12 14:52:04'),
(292, 28, 'login', 'User logged in successfully', '::1', '2025-05-12 14:52:12'),
(293, 28, 'logout', 'User logged out', '::1', '2025-05-12 14:54:33'),
(294, 10, 'login', 'User logged in successfully', '::1', '2025-05-12 14:54:45'),
(500, 39, 'book_borrow', 'Borrowed book: Sustainable Urban Planning', '192.168.1.140', '2024-03-17 03:15:00'),
(501, 40, 'book_borrow', 'Borrowed book: Cybersecurity Fundamentals', '192.168.1.145', '2024-04-16 07:45:00'),
(502, 9, 'book_borrow', 'Borrowed book: Climate Change and Economics', '192.168.1.150', '2024-05-15 01:30:00'),
(503, 30, 'book_borrow', 'Borrowed book: Neuroscience Advances', '192.168.1.155', '2024-06-14 05:20:00'),
(504, 28, 'book_borrow', 'Borrowed book: Global Health Trends', '192.168.1.160', '2024-07-13 08:40:00'),
(505, 32, 'periodical_borrow', 'Borrowed periodical: Scientific American', '192.168.1.165', '2024-08-12 02:10:00'),
(506, 35, 'periodical_borrow', 'Borrowed periodical: Wired Magazine', '192.168.1.170', '2024-09-11 06:50:00'),
(507, 31, 'media_borrow', 'Borrowed media: History Channel Documentary', '192.168.1.175', '2024-10-10 08:30:00'),
(508, 36, 'media_borrow', 'Borrowed media: Space Exploration Series', '192.168.1.180', '2024-11-09 03:45:00'),
(600, 39, 'book_borrow', 'Borrowed book: Sustainable Urban Planning', '192.168.1.140', '2024-02-05 02:30:00'),
(601, 39, 'book_return', 'Returned book: Sustainable Urban Planning', '192.168.1.140', '2024-02-18 08:30:00'),
(602, 40, 'book_borrow', 'Borrowed book: Cybersecurity Fundamentals', '192.168.1.145', '2024-03-15 06:45:00'),
(603, 9, 'book_borrow', 'Borrowed book: Climate Change and Economics', '192.168.1.150', '2024-04-02 01:15:00'),
(604, 30, 'book_borrow', 'Borrowed book: Neuroscience Advances', '192.168.1.155', '2024-04-22 08:20:00'),
(605, 28, 'book_borrow', 'Borrowed book: Global Health Trends', '192.168.1.160', '2024-05-10 03:40:00'),
(606, 28, 'book_return', 'Returned book: Global Health Trends', '192.168.1.160', '2024-05-22 06:45:00'),
(607, 32, 'periodical_borrow', 'Borrowed periodical: Scientific American', '192.168.1.165', '2024-05-25 05:55:00'),
(608, 35, 'periodical_borrow', 'Borrowed periodical: Wired Magazine', '192.168.1.170', '2024-06-07 07:10:00'),
(609, 31, 'media_borrow', 'Borrowed media: History Channel Documentary', '192.168.1.175', '2024-06-28 09:25:00'),
(610, 36, 'media_borrow', 'Borrowed media: Space Exploration Series', '192.168.1.180', '2024-07-12 00:50:00'),
(650, 39, 'book_borrow', 'Borrowed book: Sustainable Urban Planning', '192.168.1.140', '2024-01-05 02:30:00'),
(651, 39, 'book_return', 'Returned book: Sustainable Urban Planning', '192.168.1.140', '2024-01-18 08:30:00'),
(652, 40, 'book_borrow', 'Borrowed book: Cybersecurity Fundamentals', '192.168.1.145', '2024-02-15 06:45:00'),
(653, 9, 'book_borrow', 'Borrowed book: Climate Change and Economics', '192.168.1.150', '2024-03-02 01:15:00'),
(654, 30, 'book_borrow', 'Borrowed book: Neuroscience Advances', '192.168.1.155', '2024-04-10 08:20:00'),
(655, 28, 'book_borrow', 'Borrowed book: Global Health Trends', '192.168.1.160', '2024-05-15 03:40:00'),
(656, 28, 'book_return', 'Returned book: Global Health Trends', '192.168.1.160', '2024-05-25 06:45:00'),
(657, 32, 'periodical_borrow', 'Borrowed periodical: Scientific American', '192.168.1.165', '2024-06-20 05:55:00'),
(658, 35, 'periodical_borrow', 'Borrowed periodical: Wired Magazine', '192.168.1.170', '2024-07-07 07:10:00'),
(659, 31, 'media_borrow', 'Borrowed media: History Channel Documentary', '192.168.1.175', '2024-08-15 09:25:00'),
(660, 36, 'media_borrow', 'Borrowed media: Space Exploration Series', '192.168.1.180', '2024-09-03 00:50:00'),
(661, 31, 'book_borrow', 'Borrowed book: Emerging Tech Trends', '192.168.1.185', '2024-10-05 03:30:00'),
(662, 35, 'book_borrow', 'Borrowed book: Climate Policy Analysis', '192.168.1.190', '2024-11-15 06:45:00'),
(663, 36, 'book_borrow', 'Borrowed book: Digital Transformation', '192.168.1.195', '2024-12-02 01:15:00'),
(664, 32, 'periodical_borrow', 'Borrowed periodical: Forbes Magazine', '192.168.1.200', '2024-10-20 08:20:00'),
(665, 39, 'media_borrow', 'Borrowed media: Discovery Channel Documentary', '192.168.1.205', '2024-11-10 05:55:00'),
(666, 10, 'logout', 'User logged out', '::1', '2025-05-12 15:19:43'),
(667, 28, 'login', 'User logged in successfully', '::1', '2025-05-12 15:19:51'),
(668, 28, 'logout', 'User logged out', '::1', '2025-05-12 15:32:08'),
(669, 29, 'login', 'User logged in successfully', '::1', '2025-05-12 15:32:21'),
(670, 29, 'logout', 'User logged out', '::1', '2025-05-12 15:33:02'),
(671, 10, 'login', 'User logged in successfully', '::1', '2025-05-12 15:35:07'),
(672, 10, 'logout', 'User logged out', '::1', '2025-05-12 15:49:23'),
(673, 28, 'login', 'User logged in successfully', '::1', '2025-05-12 15:49:35'),
(674, 28, 'logout', 'User logged out', '::1', '2025-05-12 15:51:00'),
(675, 28, 'login', 'User logged in successfully', '::1', '2025-05-12 15:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `author` varchar(100) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `publisher` varchar(100) NOT NULL,
  `edition` varchar(20) DEFAULT NULL,
  `publication_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `resource_id`, `author`, `isbn`, `publisher`, `edition`, `publication_date`) VALUES
(21, 50, 'Thomas H. Cormen', '9780262033848', 'MIT Press', '3rd Edition', '2009-01-15'),
(22, 51, 'Robert C. Martin', '9780132350884', 'Pearson', '1st Edition', '2008-08-01'),
(26, 62, 'Jane Smith', '9781234567890', 'Tech Publications', '2nd Edition', '2022-03-15'),
(27, 63, 'John Anderson', '9789876543210', 'Innovation Press', '1st Edition', '2021-11-01'),
(28, 64, 'Elena Rodriguez', '9785432167890', 'Green Science Publishing', '3rd Edition', '2023-01-20'),
(29, 65, 'David Chen', '9786543217890', 'Ethics in Tech Series', '1st Edition', '2022-09-10'),
(30, 66, 'Emma Thompson', '9781567890123', 'Urban Press', '1st Edition', '2023-06-15'),
(31, 67, 'Alex Rodriguez', '9782345678901', 'Tech Security Publishers', '2nd Edition', '2022-11-20'),
(32, 68, 'Dr. Rachel Green', '9783456789012', 'Global Economics Review', '1st Edition', '2023-02-10'),
(33, 69, 'Prof. Michael Chen', '9784567890123', 'Neuroscience Quarterly', '3rd Edition', '2022-09-05'),
(34, 70, 'Dr. Sarah Williams', '9785678901234', 'Global Health Institute', '1st Edition', '2023-04-25'),
(35, 86, 'Alex Johnson', '9781234567891', 'Tech Insights Press', '1st Edition', '2023-11-15'),
(36, 87, 'Dr. Emily Chen', '9789876543211', 'Global Policy Publishers', '2nd Edition', '2023-12-20'),
(37, 88, 'Michael Rodriguez', '9785432167891', 'Innovation Publishing', '3rd Edition', '2024-01-10'),
(38, 91, 'Dr. Karen Quantum', '9781234567892', 'Tech Innovations Press', '1st Edition', '2024-02-15'),
(39, 92, 'Prof. Michael Aerospace', '9789876543212', 'Space Science Publishing', '2nd Edition', '2024-03-20'),
(40, 93, 'Dr. Emily Biotech', '9785432167892', 'Global Biotechnology Institute', '3rd Edition', '2024-04-10');

-- --------------------------------------------------------

--
-- Table structure for table `borrowings`
--

CREATE TABLE `borrowings` (
  `borrowing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `borrow_date` datetime DEFAULT current_timestamp(),
  `due_date` datetime DEFAULT NULL,
  `return_date` datetime DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','active','returned','overdue') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `returned_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowings`
--

INSERT INTO `borrowings` (`borrowing_id`, `user_id`, `resource_id`, `borrow_date`, `due_date`, `return_date`, `fine_amount`, `status`, `approved_by`, `approved_at`, `returned_by`) VALUES
(167, 29, 51, '2025-05-12 22:50:57', '2025-05-26 22:50:57', '2025-05-12 17:50:32', 0.00, 'returned', 10, '2025-05-12 22:50:57', 28),
(168, 30, 50, NULL, NULL, '2025-05-12 17:49:50', 30331.50, 'returned', 10, NULL, 28),
(173, 31, 62, '2025-05-12 22:59:28', '2025-05-26 22:59:28', '2025-05-12 17:50:24', 0.00, 'returned', 10, '2025-05-12 22:59:28', 28),
(174, 32, 63, '2025-05-12 22:59:28', '2025-05-26 22:59:28', '2025-05-12 17:50:28', 0.00, 'returned', 10, '2025-05-12 22:59:28', 28),
(175, 35, 64, '2025-05-12 22:59:28', '2025-05-26 22:59:28', '2025-05-12 17:51:27', 0.00, 'returned', 10, '2025-05-12 22:59:28', 28),
(176, 36, 65, '2025-05-12 22:59:28', '2025-05-26 22:59:28', '2025-05-12 17:51:30', 0.00, 'returned', 10, '2025-05-12 22:59:28', 28),
(177, 39, 66, '2024-01-05 10:30:00', '2024-01-19 10:30:00', '2024-01-18 16:30:00', 0.00, 'returned', 10, '2024-01-05 10:30:00', 10),
(178, 40, 67, '2024-02-15 14:45:00', '2024-02-29 14:45:00', '2025-05-12 17:49:53', 658.50, 'returned', 10, '2024-02-15 14:45:00', 28),
(179, 9, 68, '2024-03-02 09:15:00', '2024-03-16 09:15:00', '2025-05-12 17:49:55', 634.50, 'returned', 10, '2024-03-02 09:15:00', 28),
(180, 30, 69, '2024-04-10 16:20:00', '2024-04-24 16:20:00', '2025-05-12 17:49:59', 576.00, 'returned', 10, '2024-04-10 16:20:00', 28),
(181, 28, 70, '2024-05-10 11:40:00', '2024-05-24 11:40:00', '2024-05-22 14:45:00', 0.00, 'returned', 10, '2024-05-10 11:40:00', 10),
(182, 32, 71, '2024-06-20 13:55:00', '2024-07-04 23:59:59', NULL, 0.00, 'active', 10, '2024-06-20 13:55:00', NULL),
(183, 35, 72, '2024-07-07 15:10:00', '2024-07-21 23:59:59', NULL, 0.00, 'active', 10, '2024-07-07 15:10:00', NULL),
(184, 31, 73, '2024-08-15 17:25:00', '2024-08-29 23:59:59', NULL, 0.00, 'active', 10, '2024-08-15 17:25:00', NULL),
(185, 36, 74, '2024-09-03 08:50:00', '2024-09-17 23:59:59', NULL, 0.00, 'active', 10, '2024-09-03 08:50:00', NULL),
(186, 31, 86, '2024-10-05 11:30:00', '2024-10-19 11:30:00', NULL, 0.00, 'active', 10, '2024-10-05 11:30:00', NULL),
(187, 35, 87, '2024-11-15 14:45:00', '2024-11-29 14:45:00', NULL, 0.00, 'active', 10, '2024-11-15 14:45:00', NULL),
(188, 36, 88, '2024-12-02 09:15:00', '2024-12-16 09:15:00', NULL, 0.00, 'active', 10, '2024-12-02 09:15:00', NULL),
(189, 32, 89, '2024-10-20 16:20:00', '2024-11-03 23:59:59', NULL, 0.00, 'active', 10, '2024-10-20 16:20:00', NULL),
(190, 39, 90, '2024-11-10 13:55:00', '2024-11-24 23:59:59', NULL, 0.00, 'active', 10, '2024-11-10 13:55:00', NULL),
(191, 41, 91, NULL, NULL, NULL, 0.00, 'pending', NULL, NULL, NULL),
(192, 42, 92, NULL, NULL, NULL, 0.00, 'pending', NULL, NULL, NULL),
(193, 43, 93, NULL, NULL, NULL, 0.00, 'pending', NULL, NULL, NULL),
(194, 29, 91, '2025-05-12 23:32:35', NULL, NULL, 0.00, 'pending', NULL, NULL, NULL),
(195, 29, 50, '2025-05-12 23:32:43', NULL, NULL, 0.00, 'pending', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fine_configurations`
--

CREATE TABLE `fine_configurations` (
  `config_id` int(11) NOT NULL,
  `resource_type` enum('book','periodical','media') NOT NULL,
  `fine_amount` decimal(10,2) NOT NULL DEFAULT 1.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine_configurations`
--

INSERT INTO `fine_configurations` (`config_id`, `resource_type`, `fine_amount`, `updated_at`) VALUES
(4, 'book', 1.50, '2025-05-12 14:48:56'),
(5, 'periodical', 1.00, '2025-05-12 14:48:56'),
(6, 'media', 2.00, '2025-05-12 14:48:56');

-- --------------------------------------------------------

--
-- Table structure for table `fine_payments`
--

CREATE TABLE `fine_payments` (
  `payment_id` int(11) NOT NULL,
  `borrowing_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `processed_by` int(11) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `cash_received` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine_payments`
--

INSERT INTO `fine_payments` (`payment_id`, `borrowing_id`, `amount_paid`, `payment_date`, `payment_status`, `processed_by`, `payment_notes`, `cash_received`, `change_amount`) VALUES
(49, 167, 0.00, '2025-05-12 22:50:57', 'pending', 10, NULL, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `library_resources`
--

CREATE TABLE `library_resources` (
  `resource_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `accession_number` varchar(20) NOT NULL,
  `category` enum('book','periodical','media') NOT NULL,
  `status` enum('available','borrowed','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cover_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library_resources`
--

INSERT INTO `library_resources` (`resource_id`, `title`, `accession_number`, `category`, `status`, `created_at`, `updated_at`, `cover_image`) VALUES
(50, 'Introduction to Algorithms', 'BOOK001', 'book', 'available', '2025-05-12 14:48:56', '2025-05-12 15:49:50', NULL),
(51, 'Clean Code', 'BOOK002', 'book', 'available', '2025-05-12 14:48:56', '2025-05-12 15:50:32', NULL),
(52, 'National Geographic', 'PER001', 'periodical', 'available', '2025-05-12 14:48:56', '2025-05-12 14:48:56', NULL),
(53, 'Nature Journal', 'PER002', 'periodical', 'available', '2025-05-12 14:48:56', '2025-05-12 14:48:56', NULL),
(54, 'Documentary: World Wonders', 'MEDIA001', 'media', 'available', '2025-05-12 14:48:56', '2025-05-12 14:48:56', NULL),
(62, 'Advanced Machine Learning', 'BOOK006', 'book', 'available', '2025-05-12 14:59:28', '2025-05-12 15:50:24', NULL),
(63, 'Digital Transformation', 'BOOK007', 'book', 'available', '2025-05-12 14:59:28', '2025-05-12 15:50:28', NULL),
(64, 'Renewable Energy', 'BOOK008', 'book', 'available', '2025-05-12 14:59:28', '2025-05-12 15:51:27', NULL),
(65, 'Artificial Intelligence Ethics', 'BOOK009', 'book', 'available', '2025-05-12 14:59:28', '2025-05-12 15:51:30', NULL),
(66, 'Sustainable Urban Planning', 'BOOK010', 'book', 'borrowed', '2025-05-12 15:01:22', '2025-05-12 15:01:22', NULL),
(67, 'Cybersecurity Fundamentals', 'BOOK011', 'book', 'available', '2025-05-12 15:01:22', '2025-05-12 15:49:53', NULL),
(68, 'Climate Change and Economics', 'BOOK012', 'book', 'available', '2025-05-12 15:01:22', '2025-05-12 15:49:56', NULL),
(69, 'Neuroscience Advances', 'BOOK013', 'book', 'available', '2025-05-12 15:01:22', '2025-05-12 15:49:59', NULL),
(70, 'Global Health Trends', 'BOOK014', 'book', 'borrowed', '2025-05-12 15:01:22', '2025-05-12 15:01:22', NULL),
(71, 'Scientific American', 'PER005', 'periodical', 'borrowed', '2025-05-12 15:01:22', '2025-05-12 15:01:22', NULL),
(72, 'Wired Magazine', 'PER006', 'periodical', 'borrowed', '2025-05-12 15:01:22', '2025-05-12 15:01:22', NULL),
(73, 'History Channel Documentary', 'MEDIA004', 'media', 'borrowed', '2025-05-12 15:01:22', '2025-05-12 15:01:22', NULL),
(74, 'Space Exploration Series', 'MEDIA005', 'media', 'borrowed', '2025-05-12 15:01:22', '2025-05-12 15:01:22', NULL),
(86, 'Emerging Tech Trends', 'BOOK015', 'book', 'borrowed', '2025-05-12 15:08:22', '2025-05-12 15:08:22', NULL),
(87, 'Climate Policy Analysis', 'BOOK016', 'book', 'borrowed', '2025-05-12 15:08:22', '2025-05-12 15:08:22', NULL),
(88, 'Digital Transformation', 'BOOK017', 'book', 'borrowed', '2025-05-12 15:08:22', '2025-05-12 15:08:22', NULL),
(89, 'Forbes Magazine', 'PER007', 'periodical', 'borrowed', '2025-05-12 15:08:22', '2025-05-12 15:08:22', NULL),
(90, 'Discovery Channel Documentary', 'MEDIA006', 'media', 'borrowed', '2025-05-12 15:08:22', '2025-05-12 15:08:22', NULL),
(91, 'Quantum Computing Fundamentals', 'BOOK018', 'book', '', '2025-05-12 15:27:05', '2025-05-12 15:32:35', NULL),
(92, 'Space Technology Innovations', 'BOOK019', 'book', 'available', '2025-05-12 15:27:05', '2025-05-12 15:27:05', NULL),
(93, 'Biotechnology Advances', 'BOOK020', 'book', 'available', '2025-05-12 15:27:05', '2025-05-12 15:27:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `media_resources`
--

CREATE TABLE `media_resources` (
  `media_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `format` varchar(50) NOT NULL,
  `runtime` int(11) DEFAULT NULL,
  `media_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_resources`
--

INSERT INTO `media_resources` (`media_id`, `resource_id`, `format`, `runtime`, `media_type`) VALUES
(15, 54, 'DVD', 120, 'Documentary'),
(18, 73, 'Blu-ray', 240, 'Historical Documentary'),
(19, 74, 'DVD', 180, 'Space Exploration Series'),
(20, 90, 'Blu-ray', 150, 'Science Documentary');

-- --------------------------------------------------------

--
-- Table structure for table `periodicals`
--

CREATE TABLE `periodicals` (
  `periodical_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `issn` varchar(8) NOT NULL,
  `volume` varchar(20) DEFAULT NULL,
  `issue` varchar(20) DEFAULT NULL,
  `publication_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `periodicals`
--

INSERT INTO `periodicals` (`periodical_id`, `resource_id`, `issn`, `volume`, `issue`, `publication_date`) VALUES
(15, 52, '00280836', 'Vol 245', 'Issue 3', '2024-05-01'),
(16, 53, '00280844', 'Vol 620', 'Issue 7891', '2024-05-15'),
(19, 71, '00368083', 'Vol 351', 'Issue 6', '2024-06-15'),
(20, 72, '14325764', 'Vol 46', 'Issue 5', '2024-05-30'),
(21, 89, '17486521', 'Vol 205', 'Issue 10', '2024-10-01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `membership_id` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','student','faculty','staff') NOT NULL,
  `max_books` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `borrowing_days_limit` int(11) DEFAULT 7
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `membership_id`, `username`, `password`, `first_name`, `last_name`, `email`, `role`, `max_books`, `created_at`, `updated_at`, `borrowing_days_limit`) VALUES
(9, NULL, 'user1', '$2y$10$ikFVY6FlstJFUvrG.lL9seZd7QEvwa0EPZHShzYK2ovBONcmGdh9q', 'John', 'Doe', 'user1@gmail.com', 'faculty', 5, '2024-11-23 03:14:11', '2024-12-08 00:15:35', 7),
(10, NULL, 'admin1', '$2y$10$EOgSJ.NhTX4KtWmPK45aVeF3ylHV5WQCsIw5MDc8Y95vfbBZf0uyi', 'Jhon', 'Doe', 'admin1@gmail.com', 'admin', 10, '2024-11-23 03:19:06', '2024-12-06 06:07:40', 7),
(28, 'S20244337', 'staff1', '$2y$10$zYxXz2B58es5nT/itaiF7Oo8yujpQwYruvgHb99UVP2Jg/7KvswJa', 'Jhon', 'Doe', 'staff1@gmail.com', 'staff', 4, '2024-12-09 22:54:07', '2024-12-12 06:52:20', 7),
(29, 'S20249490', 'student1', '$2y$10$SZVcfAfudYUZ1BTcrW0XSuOKwVJVv1uzDw56kQgNF9L49lqiFtXme', 'Juan', 'Dela', 'student1@gmail.com', 'student', 4, '2024-12-09 22:54:32', '2024-12-15 21:04:36', 2),
(30, 'F20248589', 'faculty1', '$2y$10$.6M.jyeMPjv1tsKVpcLkquGhWSeCchnZKTVVA1r00nmoM9m71CPnq', 'Juan', 'Dela', 'faculty1@gmail.com', 'faculty', 1, '2024-12-09 22:54:59', '2024-12-19 07:00:43', 7),
(31, 'S20245678', 'student2', '$2y$10$SZVcfAfudYUZ1BTcrW0XSuOKwVJVv1uzDw56kQgNF9L49lqiFtXme', 'Maria', 'Garcia', 'student2@gmail.com', 'student', 3, '2025-05-12 14:59:28', '2025-05-12 14:59:28', 2),
(32, 'S20246789', 'student3', '$2y$10$ikFVY6FlstJFUvrG.lL9seZd7QEvwa0EPZHShzYK2ovBONcmGdh9q', 'Carlos', 'Rodriguez', 'student3@gmail.com', 'student', 4, '2025-05-12 14:59:28', '2025-05-12 14:59:28', 2),
(33, 'S20247890', 'student4', '$2y$10$EOgSJ.NhTX4KtWmPK45aVeF3ylHV5WQCsIw5MDc8Y95vfbBZf0uyi', 'Ana', 'Lopez', 'student4@gmail.com', 'student', 3, '2025-05-12 14:59:28', '2025-05-12 14:59:28', 2),
(34, 'S20248901', 'student5', '$2y$10$zYxXz2B58es5nT/itaiF7Oo8yujpQwYruvgHb99UVP2Jg/7KvswJa', 'Luis', 'Hernandez', 'student5@gmail.com', 'student', 4, '2025-05-12 14:59:28', '2025-05-12 14:59:28', 2),
(35, 'F20249012', 'faculty2', '$2y$10$SZVcfAfudYUZ1BTcrW0XSuOKwVJVv1uzDw56kQgNF9L49lqiFtXme', 'Elena', 'Martinez', 'faculty2@gmail.com', 'faculty', 6, '2025-05-12 14:59:28', '2025-05-12 14:59:28', 14),
(36, 'F20250123', 'faculty3', '$2y$10$ikFVY6FlstJFUvrG.lL9seZd7QEvwa0EPZHShzYK2ovBONcmGdh9q', 'Diego', 'Sanchez', 'faculty3@gmail.com', 'faculty', 5, '2025-05-12 14:59:28', '2025-05-12 14:59:28', 14),
(37, 'F20251234', 'faculty4', '$2y$10$EOgSJ.NhTX4KtWmPK45aVeF3ylHV5WQCsIw5MDc8Y95vfbBZf0uyi', 'Sofia', 'Gonzalez', 'faculty4@gmail.com', 'faculty', 7, '2025-05-12 14:59:28', '2025-05-12 14:59:28', 14),
(38, 'F20252345', 'faculty5', '$2y$10$zYxXz2B58es5nT/itaiF7Oo8yujpQwYruvgHb99UVP2Jg/7KvswJa', 'Miguel', 'Torres', 'faculty5@gmail.com', 'faculty', 6, '2025-05-12 14:59:28', '2025-05-12 14:59:28', 14),
(39, 'S20253456', 'student6', '$2y$10$SZVcfAfudYUZ1BTcrW0XSuOKwVJVv1uzDw56kQgNF9L49lqiFtXme', 'Isabella', 'Cruz', 'student6@gmail.com', 'student', 3, '2025-05-12 15:01:22', '2025-05-12 15:01:22', 2),
(40, 'F20254567', 'faculty6', '$2y$10$ikFVY6FlstJFUvrG.lL9seZd7QEvwa0EPZHShzYK2ovBONcmGdh9q', 'Daniel', 'Kim', 'faculty6@gmail.com', 'faculty', 6, '2025-05-12 15:01:22', '2025-05-12 15:01:22', 14),
(41, 'F20255678', 'faculty7', '$2y$10$zYxXz2B58es5nT/itaiF7Oo8yujpQwYruvgHb99UVP2Jg/7KvswJa', 'William', 'Zhang', 'faculty7@gmail.com', 'faculty', 5, '2025-05-12 15:27:05', '2025-05-12 15:27:05', 14),
(42, 'F20256789', 'faculty8', '$2y$10$ikFVY6FlstJFUvrG.lL9seZd7QEvwa0EPZHShzYK2ovBONcmGdh9q', 'Olivia', 'Chen', 'faculty8@gmail.com', 'faculty', 6, '2025-05-12 15:27:05', '2025-05-12 15:27:05', 14),
(43, 'F20257890', 'faculty9', '$2y$10$EOgSJ.NhTX4KtWmPK45aVeF3ylHV5WQCsIw5MDc8Y95vfbBZf0uyi', 'Ethan', 'Wong', 'faculty9@gmail.com', 'faculty', 7, '2025-05-12 15:27:05', '2025-05-12 15:27:05', 14),
(44, 'S20259012', 'student7', '$2y$10$SZVcfAfudYUZ1BTcrW0XSuOKwVJVv1uzDw56kQgNF9L49lqiFtXme', 'Sophia', 'Lee', 'student7@gmail.com', 'student', 3, '2025-05-12 15:27:05', '2025-05-12 15:27:05', 2),
(45, 'S20260123', 'student8', '$2y$10$ikFVY6FlstJFUvrG.lL9seZd7QEvwa0EPZHShzYK2ovBONcmGdh9q', 'Liam', 'Wang', 'student8@gmail.com', 'student', 4, '2025-05-12 15:27:05', '2025-05-12 15:27:05', 2),
(46, 'S20261234', 'student9', '$2y$10$EOgSJ.NhTX4KtWmPK45aVeF3ylHV5WQCsIw5MDc8Y95vfbBZf0uyi', 'Emma', 'Liu', 'student9@gmail.com', 'student', 3, '2025-05-12 15:27:05', '2025-05-12 15:27:05', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id_activity_logs` (`user_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD PRIMARY KEY (`borrowing_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `returned_by` (`returned_by`);

--
-- Indexes for table `fine_configurations`
--
ALTER TABLE `fine_configurations`
  ADD PRIMARY KEY (`config_id`),
  ADD UNIQUE KEY `resource_type` (`resource_type`);

--
-- Indexes for table `fine_payments`
--
ALTER TABLE `fine_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `borrowing_id` (`borrowing_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `library_resources`
--
ALTER TABLE `library_resources`
  ADD PRIMARY KEY (`resource_id`),
  ADD UNIQUE KEY `accession_number` (`accession_number`);

--
-- Indexes for table `media_resources`
--
ALTER TABLE `media_resources`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `periodicals`
--
ALTER TABLE `periodicals`
  ADD PRIMARY KEY (`periodical_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `membership_id` (`membership_id`),
  ADD KEY `idx_user_id_users` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=676;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `borrowings`
--
ALTER TABLE `borrowings`
  MODIFY `borrowing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT for table `fine_configurations`
--
ALTER TABLE `fine_configurations`
  MODIFY `config_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `fine_payments`
--
ALTER TABLE `fine_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `library_resources`
--
ALTER TABLE `library_resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `media_resources`
--
ALTER TABLE `media_resources`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `periodicals`
--
ALTER TABLE `periodicals`
  MODIFY `periodical_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `library_resources` (`resource_id`) ON DELETE CASCADE;

--
-- Constraints for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD CONSTRAINT `borrowings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrowings_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `library_resources` (`resource_id`),
  ADD CONSTRAINT `borrowings_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrowings_ibfk_4` FOREIGN KEY (`returned_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
