-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2025 at 06:15 AM
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
-- Database: `hostel_recommendation`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'admin',
  `hostel_id` int(11) DEFAULT NULL,
  `assigned_hostel_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`, `hostel_id`, `assigned_hostel_id`) VALUES
(1, 'admin10', 'admin10@hostelnow.com', 'admin10123', 'super_admin', NULL, NULL),
(4, 'citylight admin', 'admin@citylight.com', 'citylight', 'admin', 8, 8),
(5, 'Scholar Admin', 'admin@scholar.com', 'scholaradmin', 'admin', 3, 3),
(7, 'Starlight admin', 'admin@starlight.com', 'starlight', 'admin', 4, 4),
(8, 'Lotus admin', 'lotus@admin.com', 'lotusadmin', 'admin', 5, 5),
(9, 'Skylark admin', 'admin@skylark.com', 'skylark', 'admin', 10, 10),
(10, 'Himalayan Admin', 'admin@himalayan.com', 'himalayan', 'admin', 11, 11),
(11, 'Serenity admin', 'admin@serenity.com', 'serenity', 'admin', 12, 12),
(12, 'Destiny hostel', 'admin@destiny.com', 'destiny', 'admin', 13, 13),
(13, 'Enhypen Admin', 'admin@enhypen.com', 'enhypen', 'admin', 14, 14),
(14, 'Happynest admin', 'admin@happynest.com', 'happynest', 'admin', 16, 16),
(16, 'sunflower admin', 'admin@sunflower.com', 'sunflower', 'admin', 1, 1),
(19, 'greenvalley1 admin', 'admin1@greenvalley.com', 'greenvalley11', 'admin', 2, 2),
(20, 'Campus comforts1 admin', 'admin11@campuscomforts.com', 'campuscomforts11', 'admin', 18, 18),
(21, 'Central Girls Admin', 'admin@centralgirls.com', 'central girls', 'admin', 19, 19);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `student_phone` varchar(20) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `booking_date` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `hostel_id`, `student_name`, `student_email`, `student_phone`, `duration`, `booking_date`, `user_id`, `status`) VALUES
(5, 5, 'mina', 'mina@gmail.com', '9811111110', 90, '2025-05-06 12:09:04', 6, 'approved'),
(7, 14, 'Minisha', 'minisha@gmail.com', '9822223322', 120, '2025-05-07 09:33:03', 8, 'approved'),
(8, 4, 'Minisha', 'minisha@gmail.com', '9822223322', 60, '2025-05-07 12:00:05', 8, 'approved'),
(9, 14, 'basant karki', 'basant@gnail.com', '9822223322', 60, '2025-05-07 14:00:45', 10, 'approved'),
(10, 11, 'Minisha', 'minisha@gmail.com', '9888882211', 90, '2025-05-07 21:06:30', 8, 'approved'),
(11, 8, 'Test', 'Test@gmail.com', '9876542211', 150, '2025-05-08 11:57:36', 11, 'approved'),
(12, 12, 'Test', 'Test@gmail.com', '9845231122', 90, '2025-05-08 12:26:04', 11, 'rejected'),
(13, 5, 'Test', 'Test@gmail.com', '9800000011', 120, '2025-05-08 12:43:24', 11, 'approved'),
(14, 2, 'Test', 'Test@gmail.com', '9800000011', 120, '2025-05-08 12:45:40', 11, 'approved'),
(15, 16, 'Test', 'Test@gmail.com', '9876543211', 90, '2025-05-17 21:23:16', 11, 'approved'),
(16, 3, 'Test', 'Test@gmail.com', '9822223322', 80, '2025-05-17 22:02:03', 11, 'approved'),
(17, 3, 'mina', 'mina@gmail.com', '9807070707', 70, '2025-05-17 22:02:41', 6, 'approved'),
(18, 3, 'Minisha', 'minisha@gmail.com', '9888887766', 90, '2025-05-17 22:03:21', 8, 'approved'),
(20, 3, 'Test3', 'test3@gmail.com', '9800776655', 60, '2025-05-17 22:12:00', 13, 'approved'),
(21, 3, 'test4', 'test4@gmail.com', '9877660055', 95, '2025-05-17 22:12:40', 14, 'approved'),
(22, 11, 'test5', 'test5@gmail.com', '9877006622', 30, '2025-05-17 22:16:10', 15, 'approved'),
(23, 10, 'test5', 'test5@gmail.com', '9876541231', 80, '2025-05-17 22:18:58', 15, 'rejected'),
(24, 16, 'test5', 'test5@gmail.com', '9801020304', NULL, '2025-05-27 21:03:23', 15, 'approved'),
(25, 1, 'test5', 'test5@gmail.com', '9877665544', 120, '2025-06-01 17:35:21', 15, 'approved'),
(26, 8, 'admin10', 'admin10@hostelnow.com', '9822223322', 120, '2025-06-01 18:23:08', 9, 'approved'),
(27, 1, 'test4', 'test4@gmail.com', '9801020333', NULL, '2025-06-01 18:56:12', 14, 'approved'),
(28, 18, 'test4', 'test4@gmail.com', '9867542311', 70, '2025-06-02 19:24:13', 14, 'approved'),
(29, 5, 'test4', 'test4@gmail.com', '9870605040', 150, '2025-06-03 22:00:08', 14, 'approved'),
(30, 3, 'admin10', 'admin10@hostelnow.com', '9800776655', 120, '2025-06-04 09:15:19', 9, 'approved'),
(31, 3, 'Test7', 'test7@gmail.com', '9800001122', 150, '2025-06-04 09:22:14', 16, 'approved'),
(32, 1, 'Test7', 'test7@gmail.com', '9800001122', 150, '2025-06-04 09:28:57', 16, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE `features` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `features`
--

INSERT INTO `features` (`id`, `name`) VALUES
(4, '24/7 Security'),
(9, '4-Sharing Room'),
(2, 'Air Conditioning'),
(5, 'Canteen'),
(7, 'Double Room'),
(3, 'Laundry'),
(6, 'Single Room'),
(8, 'Triple Room'),
(1, 'Wi-Fi');

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `rating` float DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_url` varchar(255) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 10,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`id`, `name`, `location`, `price`, `description`, `rating`, `created_at`, `image_url`, `capacity`, `admin_id`) VALUES
(1, 'Sunflower Girls hostel', 'Dhapakhel,Lalitpur', 13000.00, 'comfortable environment with proper food and accomodation', 5, '2025-05-04 09:12:47', 'uploads/6835d9cbd67bf_synflowerhostelmain.jpg', 10, 16),
(2, 'Green Valley Boys Hostel', 'Buddhanagar,Kathmandu', 12000.00, 'Spacious rooms with garden view, study lounge, and 24/7 security', 4.8, '2025-05-04 16:30:09', 'uploads/6835e0d88a870_greenvalleymain.jpg', 10, 19),
(3, 'Scholar\'s Nest', 'Kirtipur,Kathmandu', 15000.00, 'Quiet study-friendly environment, library access, and high-speed internet', 4.7, '2025-05-04 16:31:44', 'uploads/6835e2b500fe4_scholarnestmain.jpg', 7, 5),
(4, 'StarLight Girl\'s Hostel', 'Jawalakhel,Lalitpur', 14500.00, 'Secure girls-only facility with study room and balcony garden', 4.7, '2025-05-04 16:36:17', 'uploads/6835e3f264b3a_starlightmain.jpg', 10, 7),
(5, 'Lotus Girls Hostel', 'Kumaripati,Lalitpur', 15200.00, 'Eco-friendly hostel with yoga terrace and organic garden', 4.9, '2025-05-04 16:38:03', 'uploads/6835e4ed41514_lotusmain.jpg', 10, 8),
(8, 'CityLight Boys Hostel', 'Lagankhel,Lalitpur', 9800.00, 'Spacious hostel with free Wi-Fi, daily cleaning, and easy access to public transport. Ideal for students looking for a convenient and affordable stay.', 4.2, '2025-05-05 16:25:14', 'uploads/6835e5cc791c0_citylightmain.jpg', 10, 4),
(10, 'Skylark Girls hostel', 'Kirtpur,Kathmandu', 11200.00, 'Safe and friendly environment for girls with homely food, secure premises, and a supportive atmosphere ideal for academic focus.', 4.8, '2025-05-05 16:43:15', 'uploads/6835e6d796280_skylarkmain.jpg', 10, 9),
(11, 'Himalayan Heights Girls Hostel', 'Dhobighat,Lalitpur', 15000.00, 'Safe and secure girls\' hostel with CCTV, study room, and indoor games.', 4.9, '2025-05-05 17:44:38', 'uploads/6835e77eeaa6e_himalayanmain.jpg', 10, 10),
(12, 'Serenity Hostel', 'Swayambhu,Kathmandu', 17000.00, 'Peaceful environment with meditation space, library, and garden seating.\r\n\r\nRating:', 4.7, '2025-05-05 17:45:50', 'uploads/6835e87141d4b_serenitymain.jpg', 10, 11),
(13, 'Destiny hostel', 'nakhipot,lalitpur', 20000.00, 'amazing environment with comfortable rooms', 4.9, '2025-05-06 17:23:58', 'uploads/6835ea534bc8d_destinymain.jpg', 10, 12),
(14, 'Enhypen Hostel', 'Baneshwor,Kathmandu', 25000.00, 'Student-friendly hostel: cozy, artsy, and cheap with great coffee.', 5, '2025-05-06 17:35:53', 'uploads/6835ed0c5fee7_enhypennmainn.jpg', 10, 13),
(16, 'Happynest Inspiring Hostel', 'Balaju,Kathmandu', 20000.00, 'proper space with easy access to transportation and comfortable environment for students.', 0, '2025-05-15 15:44:03', 'uploads/68260bc3462cd_HOSTEL1 COVER.jpg', 15, 14),
(18, 'Campus Comforts Hostel', ',Gatthaghar,Bhaktapur', 27000.00, 'Located just minutes from major universities, Campus Comforts offers clean, secure, and affordable living tailored for students. Enjoy fully furnished rooms, high-speed Wi-Fi, 24/7 security, and common areas designed for study and relaxation. Your home away from home.', 0, '2025-05-31 15:21:38', 'uploads/683b1e8255e2b_campus main.jpg', 20, 20),
(19, 'Central Girls Hostel', 'New Baneshwor,Kathmandu', 26000.00, 'Central Girls Hostel provides a safe, clean, and comfortable stay for female students and professionals. Located near major institutions, it offers furnished rooms, Wi-Fi, meals, and 24/7 security in a peaceful, homely environment.', 0, '2025-06-03 16:36:24', 'uploads/683f2488ca995_centralmain.jpg', 15, 21);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_features`
--

CREATE TABLE `hostel_features` (
  `hostel_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_features`
--

INSERT INTO `hostel_features` (`hostel_id`, `feature_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(2, 1),
(2, 2),
(2, 3),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(4, 1),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(4, 7),
(4, 8),
(4, 9),
(5, 1),
(5, 2),
(5, 3),
(5, 4),
(5, 5),
(5, 6),
(5, 7),
(5, 8),
(8, 1),
(8, 3),
(8, 4),
(8, 5),
(8, 6),
(8, 7),
(10, 1),
(10, 2),
(10, 3),
(10, 4),
(10, 5),
(10, 6),
(10, 7),
(10, 8),
(10, 9),
(11, 1),
(11, 2),
(11, 3),
(11, 4),
(11, 5),
(11, 6),
(11, 7),
(12, 1),
(12, 2),
(12, 3),
(12, 4),
(12, 5),
(12, 6),
(12, 7),
(12, 8),
(13, 1),
(13, 3),
(13, 4),
(13, 5),
(13, 6),
(13, 7),
(13, 8),
(14, 1),
(14, 2),
(14, 3),
(14, 4),
(14, 5),
(14, 6),
(14, 7),
(16, 1),
(16, 2),
(16, 3),
(16, 4),
(16, 5),
(16, 6),
(16, 7),
(16, 8),
(18, 1),
(18, 2),
(18, 3),
(18, 4),
(18, 5),
(18, 6),
(18, 7),
(18, 8),
(18, 9),
(19, 1),
(19, 2),
(19, 3),
(19, 4),
(19, 5),
(19, 6),
(19, 7);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_images`
--

CREATE TABLE `hostel_images` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_images`
--

INSERT INTO `hostel_images` (`id`, `hostel_id`, `image_path`, `uploaded_at`) VALUES
(1, 16, 'uploads/68260bc3466e0_hostell1111.jpg', '2025-05-15 15:44:03'),
(2, 16, 'uploads/68260bc3469bc_hostell11.jpg', '2025-05-15 15:44:03'),
(4, 16, 'uploads/68260c1bd4739_hostellll1.jpg', '2025-05-15 15:45:31'),
(10, 1, 'uploads/6835d9cbd79a7_synflowerhostel5.jpg', '2025-05-27 15:27:07'),
(11, 1, 'uploads/6835d9cbd87e5_synflowerhostel4.jpg', '2025-05-27 15:27:07'),
(12, 1, 'uploads/6835d9cbd9be1_synflowerhostel3.jpg', '2025-05-27 15:27:07'),
(13, 1, 'uploads/6835d9cbda757_synflowerhostel2.webp', '2025-05-27 15:27:07'),
(14, 1, 'uploads/6835d9cbdb23f_synflowerhostel1.jpg', '2025-05-27 15:27:07'),
(15, 2, 'uploads/6835e0d88b7af_greenvalley4.jpg', '2025-05-27 15:57:12'),
(16, 2, 'uploads/6835e0d88c2af_greenvalley3.jpg', '2025-05-27 15:57:12'),
(17, 2, 'uploads/6835e0d88d4d7_greenvalley1.jpg', '2025-05-27 15:57:12'),
(18, 3, 'uploads/6835e2b502459_scholarnest4.jpg', '2025-05-27 16:05:09'),
(19, 3, 'uploads/6835e2b503f36_scholarnest3.jpg', '2025-05-27 16:05:09'),
(20, 3, 'uploads/6835e2b504d14_scholarnest2.jpg', '2025-05-27 16:05:09'),
(21, 4, 'uploads/6835e3f26917e_starlight4.jpg', '2025-05-27 16:10:26'),
(22, 4, 'uploads/6835e3f26a3bb_starlight3.jpg', '2025-05-27 16:10:26'),
(23, 4, 'uploads/6835e3f26af57_starlight2.jpg', '2025-05-27 16:10:26'),
(24, 4, 'uploads/6835e3f26bb88_starlight1.jpg', '2025-05-27 16:10:26'),
(25, 5, 'uploads/6835e4ed42ab8_lotus5.jpg', '2025-05-27 16:14:37'),
(26, 5, 'uploads/6835e4ed43a75_lotus4.jpg', '2025-05-27 16:14:37'),
(27, 5, 'uploads/6835e4ed44b9c_lotus3.jpg', '2025-05-27 16:14:37'),
(28, 5, 'uploads/6835e4ed45875_lotus2.jpg', '2025-05-27 16:14:37'),
(29, 5, 'uploads/6835e4ed46407_lotus1.jpg', '2025-05-27 16:14:37'),
(30, 8, 'uploads/6835e5cc7a575_citylight2.webp', '2025-05-27 16:18:20'),
(31, 8, 'uploads/6835e5cc7badb_citylight1.jpg', '2025-05-27 16:18:20'),
(32, 10, 'uploads/6835e6d797e5d_skylark3.jpg', '2025-05-27 16:22:47'),
(33, 10, 'uploads/6835e6d798ea2_skylark2.jpg', '2025-05-27 16:22:47'),
(34, 10, 'uploads/6835e6d79999c_skylark1.jpg', '2025-05-27 16:22:47'),
(35, 11, 'uploads/6835e77eefab9_himalayan5.jpg', '2025-05-27 16:25:34'),
(36, 11, 'uploads/6835e77ef09e4_himalayan4.jpg', '2025-05-27 16:25:34'),
(37, 11, 'uploads/6835e77ef1858_himalayan3.jpg', '2025-05-27 16:25:34'),
(38, 11, 'uploads/6835e77ef2913_himalayan2.jpg', '2025-05-27 16:25:34'),
(39, 11, 'uploads/6835e77ef343b_himalayan1.jpg', '2025-05-27 16:25:34'),
(40, 12, 'uploads/6835e871442f8_serenity4.jpg', '2025-05-27 16:29:37'),
(41, 12, 'uploads/6835e871450c5_serenity3.jpg', '2025-05-27 16:29:37'),
(42, 12, 'uploads/6835e87145c10_serenity2.jpg', '2025-05-27 16:29:37'),
(43, 12, 'uploads/6835e87146a5e_serenity1.jpg', '2025-05-27 16:29:37'),
(44, 13, 'uploads/6835ea534cfd6_destiny4.jpg', '2025-05-27 16:37:39'),
(45, 13, 'uploads/6835ea534dda8_destiny3.jpg', '2025-05-27 16:37:39'),
(46, 13, 'uploads/6835ea534e899_destiny2.jpg', '2025-05-27 16:37:39'),
(47, 13, 'uploads/6835ea534f503_destiny1.jpg', '2025-05-27 16:37:39'),
(48, 14, 'uploads/6835ed0c615b2_enhypenn55.jpg', '2025-05-27 16:49:16'),
(49, 14, 'uploads/6835ed0c62ce3_enhypenn44.jpg', '2025-05-27 16:49:16'),
(50, 14, 'uploads/6835ed0c63a79_enhypenn33.jpg', '2025-05-27 16:49:16'),
(51, 14, 'uploads/6835ed0c64880_enhypenn22.jpg', '2025-05-27 16:49:16'),
(52, 14, 'uploads/6835ed0c652ae_enhypenn11.jpg', '2025-05-27 16:49:16'),
(59, 18, 'uploads/683b1e8256a5e_campus6.jpg', '2025-05-31 15:21:38'),
(60, 18, 'uploads/683b1e8256f99_campus5.jpg', '2025-05-31 15:21:38'),
(61, 18, 'uploads/683b1e82573e4_campus4.jpg', '2025-05-31 15:21:38'),
(62, 18, 'uploads/683b1e8257855_campus3.jpg', '2025-05-31 15:21:38'),
(63, 18, 'uploads/683b1e8257ca5_campus2.jpg', '2025-05-31 15:21:38'),
(64, 18, 'uploads/683b1e82582d4_campus1.jpg', '2025-05-31 15:21:38'),
(65, 19, 'uploads/683f2488cb71e_central5.jpg', '2025-06-03 16:36:24'),
(66, 19, 'uploads/683f2488cbd28_central4.jpg', '2025-06-03 16:36:24'),
(67, 19, 'uploads/683f2488cc233_central3.jpg', '2025-06-03 16:36:24'),
(68, 19, 'uploads/683f2488cc87c_central2.jpg', '2025-06-03 16:36:24'),
(69, 19, 'uploads/683f2488ccf13_central1.jpg', '2025-06-03 16:36:24');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_reviews`
--

CREATE TABLE `hostel_reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL CHECK (`rating` between 0 and 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `role`) VALUES
(6, 'mina', 'mina@gmail.com', 'mina123', '2025-05-06 05:55:22', 'user'),
(8, 'Minisha', 'minisha@gmail.com', 'minisha123', '2025-05-07 03:05:03', 'user'),
(9, 'admin10', 'admin10@hostelnow.com', 'admin10123', '2025-05-07 03:26:24', 'admin'),
(10, 'basant karki', 'basant@gnail.com', '1234567', '2025-05-07 08:11:56', 'user'),
(11, 'Test', 'Test@gmail.com', 'test123', '2025-05-08 06:11:56', 'user'),
(13, 'Test3', 'test3@gmail.com', 'test3', '2025-05-17 16:26:45', 'user'),
(14, 'test4', 'test4@gmail.com', 'test4', '2025-05-17 16:27:22', 'user'),
(15, 'test5', 'test5@gmail.com', 'test5', '2025-05-17 16:28:43', 'user'),
(16, 'Test7', 'test7@gmail.com', 'test7', '2025-06-04 03:36:36', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hostels_admin` (`admin_id`);

--
-- Indexes for table `hostel_features`
--
ALTER TABLE `hostel_features`
  ADD PRIMARY KEY (`hostel_id`,`feature_id`),
  ADD KEY `feature_id` (`feature_id`);

--
-- Indexes for table `hostel_images`
--
ALTER TABLE `hostel_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `hostel_reviews`
--
ALTER TABLE `hostel_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `hostel_images`
--
ALTER TABLE `hostel_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `hostel_reviews`
--
ALTER TABLE `hostel_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `hostels`
--
ALTER TABLE `hostels`
  ADD CONSTRAINT `fk_hostels_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hostel_features`
--
ALTER TABLE `hostel_features`
  ADD CONSTRAINT `hostel_features_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_features_ibfk_2` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_images`
--
ALTER TABLE `hostel_images`
  ADD CONSTRAINT `hostel_images_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_reviews`
--
ALTER TABLE `hostel_reviews`
  ADD CONSTRAINT `hostel_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `hostel_reviews_ibfk_2` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
