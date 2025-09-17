-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2025 at 07:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farm_master#`
--

-- --------------------------------------------------------

--
-- Table structure for table `agreement`
--

CREATE TABLE `agreement` (
  `agreement_id` int(100) NOT NULL,
  `land_id` int(100) NOT NULL,
  `signed_date` date NOT NULL,
  `terms_and_condition` varchar(255) NOT NULL,
  `rent_amount` decimal(6,2) NOT NULL,
  `user_id` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crop_inventory`
--

CREATE TABLE `crop_inventory` (
  `crop_id` int(100) NOT NULL,
  `crop_name` varchar(100) NOT NULL,
  `crop_duration` int(11) NOT NULL,
  `quantity` decimal(6,3) NOT NULL,
  `status` enum('Available','Unavailable','Sold') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop_inventory`
--

INSERT INTO `crop_inventory` (`crop_id`, `crop_name`, `crop_duration`, `quantity`, `status`) VALUES
(1, 'Leeks', 90, 200.000, 'Available'),
(3, 'Carrot', 90, 300.000, 'Available'),
(5, 'Tomato', 60, 180.000, 'Available'),
(6, 'Cabbage', 80, 200.000, 'Available'),
(9, 'Potato', 500, 0.000, 'Available'),
(10, 'Beet Root', 100, 400.000, 'Available'),
(11, 'Eggplant', 90, 300.000, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `harvest`
--

CREATE TABLE `harvest` (
  `harvest_id` int(11) NOT NULL,
  `land_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `proposal_id` int(11) DEFAULT NULL,
  `harvest_date` date NOT NULL,
  `product_type` varchar(255) NOT NULL,
  `harvest_amount` decimal(10,2) NOT NULL,
  `income` decimal(12,2) NOT NULL,
  `expenses` decimal(12,2) NOT NULL,
  `land_rent` decimal(12,2) NOT NULL,
  `net_profit` decimal(12,2) NOT NULL,
  `landowner_share` decimal(12,2) NOT NULL,
  `farmmaster_share` decimal(12,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `harvest`
--

INSERT INTO `harvest` (`harvest_id`, `land_id`, `user_id`, `proposal_id`, `harvest_date`, `product_type`, `harvest_amount`, `income`, `expenses`, `land_rent`, `net_profit`, `landowner_share`, `farmmaster_share`, `notes`, `created_at`, `updated_at`) VALUES
(1, 19, 32, 1, '2025-07-15', 'Tomato', 500.00, 100000.00, 20000.00, 10000.00, 70000.00, 28000.00, 42000.00, 'Good quality harvest', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(2, 19, 32, 1, '2025-06-20', 'Carrot', 300.00, 60000.00, 12000.00, 6000.00, 42000.00, 16800.00, 25200.00, 'Average harvest', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(3, 21, 32, NULL, '2025-05-25', 'Rice', 400.00, 50000.00, 10000.00, 5000.00, 35000.00, 14000.00, 21000.00, 'Good yield', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(4, 42, 32, NULL, '2025-04-30', 'Mixed Vegetables', 200.00, 30000.00, 6000.00, 3000.00, 21000.00, 8400.00, 12600.00, 'Small harvest', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(5, 19, 32, 1, '2025-03-05', 'Tomato', 100.00, 15000.00, 3000.00, 2000.00, 10000.00, 4000.00, 6000.00, 'Early harvest', '2025-08-28 06:54:03', '2025-09-12 18:35:42');

-- --------------------------------------------------------

--
-- Table structure for table `land`
--

CREATE TABLE `land` (
  `land_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `size` decimal(6,3) NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `land`
--

INSERT INTO `land` (`land_id`, `user_id`, `location`, `size`, `payment_status`, `payment_date`, `created_at`, `updated_at`) VALUES
(19, 32, 'Galle', 12.000, 'paid', NULL, '2025-09-13 02:29:33', '2025-09-13 02:29:33'),
(21, 32, 'Nikawatawana, Matale District, Central Province, Sri Lanka', 111.000, 'paid', NULL, '2025-09-13 02:29:33', '2025-09-13 02:29:33'),
(31, 32, 'kala, Ampara District, Eastern Province, Sri Lanka', 34.000, 'pending', NULL, '2025-08-27 08:45:00', '2025-08-27 08:45:00'),
(32, 32, 'kala, Ampara District, Eastern Province, Sri Lanka', 34.000, 'paid', '2025-08-27 08:45:17', '2025-08-27 08:45:14', '2025-08-27 08:45:17'),
(33, 32, 'Dambulla, Matale District, Central Province, 21000, Sri Lanka', 12.000, 'pending', NULL, '2025-08-27 08:49:44', '2025-08-27 08:49:44'),
(41, 32, 'Polonnaruwa District, North Central Province, Sri Lanka', 12.000, 'paid', '2025-08-27 11:11:22', '2025-08-27 11:11:19', '2025-08-27 11:11:22'),
(42, 32, 'Anuradhapura District, North Central Province, Sri Lanka', 12.000, 'paid', '2025-08-27 11:17:36', '2025-08-27 11:17:34', '2025-08-27 11:17:36'),
(46, 32, 'Pahala Giribawa, Kurunegala District, North Western Province, Sri Lanka', 12.000, 'paid', '2025-09-12 18:37:58', '2025-09-12 18:37:53', '2025-09-12 18:37:58');

-- --------------------------------------------------------

--
-- Table structure for table `land_report`
--

CREATE TABLE `land_report` (
  `report_id` int(100) NOT NULL,
  `land_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `report_date` date NOT NULL,
  `land_description` varchar(255) NOT NULL,
  `crop_recomendation` varchar(255) NOT NULL,
  `ph_value` decimal(3,1) DEFAULT NULL,
  `organic_matter` decimal(5,2) DEFAULT NULL,
  `nitrogen_level` varchar(50) DEFAULT NULL,
  `phosphorus_level` varchar(50) DEFAULT NULL,
  `potassium_level` varchar(50) DEFAULT NULL,
  `environmental_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Rejected','Approved','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `land_report`
--

INSERT INTO `land_report` (`report_id`, `land_id`, `user_id`, `report_date`, `land_description`, `crop_recomendation`, `ph_value`, `organic_matter`, `nitrogen_level`, `phosphorus_level`, `potassium_level`, `environmental_notes`, `created_at`, `updated_at`, `status`) VALUES
(1, 19, 32, '2025-08-20', 'Well-drained sandy loam soil with good organic content. The land has adequate water access and receives good sunlight throughout the day. Suitable for various crop types.', 'Based on soil analysis and environmental conditions:\r\n\r\n1. Rice cultivation (recommended for wet season)\r\n   - Expected yield: 4-5 tons per hectare\r\n   - Market price: Rs. 80-100 per kg\r\n\r\n2. Vegetable cultivation (year-round)\r\n   - Tomatoes, beans, cabba', 6.5, 4.20, 'Medium', 'High', 'Medium', 'Well-drained soil with good water retention. Average annual rainfall 1500mm. Temperature range 24-32°C.', '2025-08-28 06:37:15', '2025-08-28 06:37:15', 'Approved'),
(2, 21, 32, '2025-08-22', 'Clay-rich soil with moderate drainage. The land requires some soil improvement for optimal cultivation. Water retention is good but may need drainage during heavy rains.', 'Soil improvement recommendations:\n\n1. Add organic compost to improve soil structure\n2. Install proper drainage systems\n3. Consider raised bed cultivation\n\nCrop recommendations:\n- Rice (primary recommendation)\n- Root vegetables (potatoes, carrots)\n', 7.1, 3.80, 'Low', 'Medium', 'High', 'Clay-rich soil requiring drainage improvement. Good water retention but may waterlog during heavy rains.', '2025-08-28 06:37:15', '2025-08-28 06:37:15', 'Approved'),
(3, 42, 32, '2025-08-25', 'Premium agricultural land with excellent soil composition and natural water sources. Ideal conditions for diverse crop cultivation.', 'This land has premium potential for:\r\n\r\n1. Organic farming certification possible\r\n2. High-value crops recommended:\r\n   - Organic vegetables for export\r\n   - Medicinal plants cultivation\r\n   - Fruit orchards\r\n\r\n3. Estimated annual income: Rs. 200,000-300,', 6.8, 5.10, 'High', 'High', 'Medium', 'Premium agricultural land with excellent conditions for organic farming.', '2025-08-28 06:37:15', '2025-08-28 06:37:15', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `product_id` int(100) NOT NULL,
  `order_date` date NOT NULL,
  `total_price` decimal(6,2) NOT NULL,
  `quantity` decimal(6,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`) VALUES
(24, 'radeeshapraneeth531@gmail.com', '7ffe65827bac8cd3a77c75ecebc7327773d9bed61493b171d27f2568d98e742f', '2025-09-07 13:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `land_id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','debit_card','bank_transfer') NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `gateway_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `land_id`, `transaction_id`, `amount`, `payment_method`, `payment_status`, `gateway_response`, `created_at`, `updated_at`, `stripe_payment_intent_id`) VALUES
(12, 32, 22, 'stripe_pi_3S0eI3C523WS3olJ0XN2Qp9q', 5000.00, '', 'completed', NULL, '2025-08-27 08:01:09', '2025-08-27 08:01:09', 'pi_3S0eI3C523WS3olJ0XN2Qp9q'),
(17, 32, 28, 'stripe_pi_3S0evjC523WS3olJ0C1Sz8CN', 5000.00, '', 'completed', NULL, '2025-08-27 08:42:11', '2025-08-27 08:42:11', 'pi_3S0evjC523WS3olJ0C1Sz8CN'),
(18, 32, 32, 'stripe_pi_3S0eykC523WS3olJ1rM5v2he', 5000.00, '', 'completed', NULL, '2025-08-27 08:45:17', '2025-08-27 08:45:17', 'pi_3S0eykC523WS3olJ1rM5v2he'),
(19, 32, 41, 'stripe_pi_3S0hG8C523WS3olJ1G4hinyH', 5000.00, '', 'completed', NULL, '2025-08-27 11:11:22', '2025-08-27 11:11:22', 'pi_3S0hG8C523WS3olJ1G4hinyH'),
(20, 32, 42, 'stripe_pi_3S0hMAC523WS3olJ1unWLL7A', 5000.00, '', 'completed', NULL, '2025-08-27 11:17:36', '2025-08-27 11:17:36', 'pi_3S0hMAC523WS3olJ1unWLL7A'),
(21, 32, 44, 'stripe_pi_3S0z9oC523WS3olJ0GtxCWm7', 5000.00, '', 'completed', NULL, '2025-08-28 06:18:02', '2025-08-28 06:18:02', 'pi_3S0z9oC523WS3olJ0GtxCWm7'),
(22, 32, 45, 'stripe_pi_3S4bOQC523WS3olJ2vTYZJ8c', 5000.00, '', 'completed', NULL, '2025-09-07 05:44:06', '2025-09-07 05:44:06', 'pi_3S4bOQC523WS3olJ2vTYZJ8c'),
(23, 32, 46, 'stripe_pi_3S6br2C523WS3olJ2hBUR6k5', 5000.00, '', 'completed', NULL, '2025-09-12 18:37:57', '2025-09-12 18:37:57', 'pi_3S6br2C523WS3olJ2hBUR6k5');

-- --------------------------------------------------------

--
-- Table structure for table `payment_intents`
--

CREATE TABLE `payment_intents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `land_id` int(11) NOT NULL,
  `stripe_payment_intent_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('created','succeeded','failed','cancelled') DEFAULT 'created',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_intents`
--

INSERT INTO `payment_intents` (`id`, `user_id`, `land_id`, `stripe_payment_intent_id`, `amount`, `status`, `created_at`, `updated_at`) VALUES
(3, 32, 19, 'pi_3S0d3qC523WS3olJ0iJDEEKv', 5000.00, 'succeeded', '2025-08-27 06:42:22', '2025-08-27 06:42:24'),
(4, 32, 20, 'pi_3S0dAzC523WS3olJ0ylxmacn', 5000.00, 'succeeded', '2025-08-27 06:49:45', '2025-08-27 06:49:47'),
(19, 32, 41, 'pi_3S0hG8C523WS3olJ1G4hinyH', 5000.00, 'succeeded', '2025-08-27 11:11:20', '2025-08-27 11:11:22'),
(20, 32, 42, 'pi_3S0hMAC523WS3olJ1unWLL7A', 5000.00, 'succeeded', '2025-08-27 11:17:35', '2025-08-27 11:17:36'),
(22, 32, 44, 'pi_3S0z9oC523WS3olJ0GtxCWm7', 5000.00, 'succeeded', '2025-08-28 06:18:00', '2025-08-28 06:18:02'),
(23, 32, 45, 'pi_3S4bOQC523WS3olJ2vTYZJ8c', 5000.00, 'succeeded', '2025-09-07 05:44:02', '2025-09-07 05:44:06'),
(24, 32, 46, 'pi_3S6br2C523WS3olJ2hBUR6k5', 5000.00, 'succeeded', '2025-09-12 18:37:55', '2025-09-12 18:37:58');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(100) NOT NULL,
  `crop_id` int(100) NOT NULL,
  `price_per_unit` decimal(6,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `crop_id`, `price_per_unit`, `description`, `image_url`, `is_featured`) VALUES
(1, 3, 350.00, 'Fresh and sweet farm carrots', 'uploads/img_68ca3549b0ff38.45299625.png', 1),
(2, 1, 270.00, 'Fresh, mild-flavored, and pesticide-free', 'uploads/img_68ca33a59c5135.35900479.png', 0),
(3, 5, 150.00, 'Juicy, ripe, and naturally grown', 'uploads/img_68ca33e95d8fa0.23034483.png', 1),
(4, 6, 230.00, 'Crisp, green and pesticide-free', 'uploads/img_68ca32ee7ed905.12651273.png', 0),
(5, 9, 150.00, 'nice', 'uploads/img_68c9cab7bea755.55286263.png', 1),
(7, 10, 150.00, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `proposal_id` int(11) NOT NULL,
  `land_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `crop_type` varchar(255) NOT NULL,
  `estimated_yield` decimal(10,2) NOT NULL,
  `lease_duration_years` int(11) NOT NULL,
  `rental_value` decimal(12,2) NOT NULL,
  `profit_sharing_farmmaster` decimal(5,2) NOT NULL DEFAULT 60.00,
  `profit_sharing_landowner` decimal(5,2) NOT NULL DEFAULT 40.00,
  `estimated_profit_landowner` decimal(12,2) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `proposal_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`proposal_id`, `land_id`, `user_id`, `crop_type`, `estimated_yield`, `lease_duration_years`, `rental_value`, `profit_sharing_farmmaster`, `profit_sharing_landowner`, `estimated_profit_landowner`, `status`, `proposal_date`, `created_at`, `updated_at`) VALUES
(1, 19, 32, 'Organic Vegetables (Tomato, Carrot)', 10000.00, 3, 50000.00, 60.00, 40.00, 80000.00, 'Accepted', '2025-08-15', '2025-08-28 06:53:15', '2025-09-12 18:35:42'),
(2, 21, 32, 'Rice and Root Vegetables', 8000.00, 2, 45000.00, 65.00, 35.00, 70000.00, 'Accepted', '2025-08-20', '2025-08-28 06:53:15', '2025-09-12 18:35:42'),
(3, 42, 32, 'Premium Organic Crops', 12000.00, 4, 60000.00, 55.00, 45.00, 100000.00, 'Pending', '2025-08-25', '2025-08-28 06:53:15', '2025-09-12 18:35:42'),
(4, 19, 32, 'Organic Vegetables (Tomato, Carrot)', 10000.00, 3, 50000.00, 60.00, 40.00, 80000.00, 'Accepted', '2025-08-15', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(5, 21, 32, 'Rice and Root Vegetables', 8000.00, 2, 45000.00, 65.00, 35.00, 70000.00, 'Pending', '2025-08-20', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(6, 42, 32, 'Premium Organic Crops', 12000.00, 4, 60000.00, 55.00, 45.00, 100000.00, 'Pending', '2025-08-25', '2025-08-28 06:54:03', '2025-09-12 18:35:42');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(100) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `user_role` enum('Landowner','Supervisor','Buyer','Financial_Manager','Operational_Manager') NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` int(12) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `email`, `user_role`, `password`, `phone`, `is_active`) VALUES
(29, 'Hashani', 'Silva', 'om@gmail.com', 'Operational_Manager', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', 776544333, 1),
(30, 'Gimhani', 'Perera', 'fm@gmail.com', 'Financial_Manager', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', 723314561, 1),
(31, 'Kanchana', 'Almeda', 'fs@gmail.com', 'Supervisor', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', 765543212, 1),
(32, 'Nuwani', 'Silva', 'lo@gmail.com', 'Landowner', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', 774352566, 1),
(33, 'Dasuni', 'Peris', 'by@gmail.com', 'Buyer', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', 712243567, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agreement`
--
ALTER TABLE `agreement`
  ADD PRIMARY KEY (`agreement_id`),
  ADD KEY `land_id` (`land_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `crop_inventory`
--
ALTER TABLE `crop_inventory`
  ADD PRIMARY KEY (`crop_id`);

--
-- Indexes for table `harvest`
--
ALTER TABLE `harvest`
  ADD PRIMARY KEY (`harvest_id`),
  ADD KEY `land_id` (`land_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `proposal_id` (`proposal_id`);

--
-- Indexes for table `land`
--
ALTER TABLE `land`
  ADD PRIMARY KEY (`land_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_land_user_id` (`user_id`);

--
-- Indexes for table `land_report`
--
ALTER TABLE `land_report`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `land_id` (`land_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_payments_user_id` (`user_id`),
  ADD KEY `idx_payments_transaction_id` (`transaction_id`),
  ADD KEY `idx_payments_payment_status` (`payment_status`),
  ADD KEY `idx_stripe_payment_intent_id` (`stripe_payment_intent_id`);

--
-- Indexes for table `payment_intents`
--
ALTER TABLE `payment_intents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_payment_intent_id` (`stripe_payment_intent_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_land_id` (`land_id`),
  ADD KEY `idx_stripe_payment_intent_id` (`stripe_payment_intent_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `crop_id` (`crop_id`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`proposal_id`),
  ADD KEY `land_id` (`land_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement`
  MODIFY `agreement_id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crop_inventory`
--
ALTER TABLE `crop_inventory`
  MODIFY `crop_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `harvest`
--
ALTER TABLE `harvest`
  MODIFY `harvest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `land`
--
ALTER TABLE `land`
  MODIFY `land_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `land_report`
--
ALTER TABLE `land_report`
  MODIFY `report_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `payment_intents`
--
ALTER TABLE `payment_intents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agreement`
--
ALTER TABLE `agreement`
  ADD CONSTRAINT `agreement_ibfk_1` FOREIGN KEY (`land_id`) REFERENCES `land` (`land_id`),
  ADD CONSTRAINT `agreement_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `harvest`
--
ALTER TABLE `harvest`
  ADD CONSTRAINT `harvest_ibfk_1` FOREIGN KEY (`land_id`) REFERENCES `land` (`land_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `harvest_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `harvest_ibfk_3` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`) ON DELETE SET NULL;

--
-- Constraints for table `land`
--
ALTER TABLE `land`
  ADD CONSTRAINT `land_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `land_report`
--
ALTER TABLE `land_report`
  ADD CONSTRAINT `land_report_ibfk_1` FOREIGN KEY (`land_id`) REFERENCES `land` (`land_id`),
  ADD CONSTRAINT `land_report_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`crop_id`) REFERENCES `crop_inventory` (`crop_id`);

-- Remove the trigger that updates the same table (causes error #1442).
-- Instead, use a BEFORE UPDATE trigger to set status directly in NEW row.

DROP TRIGGER IF EXISTS crop_inventory_set_sold;

DELIMITER $$
CREATE TRIGGER crop_inventory_set_sold
BEFORE UPDATE ON crop_inventory
FOR EACH ROW
BEGIN
  IF NEW.quantity = 0 THEN
    SET NEW.status = 'Sold';
  ELSEIF NEW.quantity > 0 AND NEW.status != 'Unavailable' THEN
    SET NEW.status = 'Available';
  END IF;
END$$
DELIMITER ;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
