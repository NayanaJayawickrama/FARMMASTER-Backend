-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2025 at 08:11 PM
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
-- Table structure for table `cart_orders`
--

CREATE TABLE `cart_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `order_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_orders`
--

INSERT INTO `cart_orders` (`id`, `user_id`, `order_number`, `total_amount`, `order_status`, `payment_status`, `shipping_address`, `order_notes`, `created_at`, `updated_at`) VALUES
(2, 33, 'ORD20250916994983', 1038.00, 'pending', 'completed', 'nmdcm', 'Order placed through checkout', '2025-09-16 17:31:47', '2025-09-16 17:31:50'),
(3, 33, 'ORD20250916834673', 420.00, 'pending', 'completed', 'nnm', 'Order placed through checkout', '2025-09-16 17:36:25', '2025-09-16 17:36:28'),
(4, 33, 'ORD20250916127284', 420.00, 'pending', 'completed', 'bhjsd', 'Order placed through checkout', '2025-09-16 17:45:04', '2025-09-16 17:45:07'),
(5, 33, 'ORD20250917389589', 400.00, 'pending', 'completed', 'ayuhsu', 'Order placed through checkout', '2025-09-17 03:58:25', '2025-09-17 03:58:30'),
(6, 33, 'ORD20250917044232', 439.00, 'pending', 'completed', 'klsakldjakl', 'Order placed through checkout', '2025-09-17 03:59:26', '2025-09-17 03:59:30'),
(7, 32, 'ORD20250919408820', 439.00, 'pending', 'completed', 'dfjdffdjk', 'Order placed through checkout', '2025-09-19 11:16:15', '2025-09-19 11:16:18'),
(8, 42, 'ORD20250919022599', 4260.00, 'pending', 'completed', 'hdjhjss', 'Order placed through checkout', '2025-09-19 11:24:16', '2025-09-19 11:24:19'),
(9, 33, 'ORD20250919515334', 439.00, 'pending', 'completed', 'djkfdjkf', 'Order placed through checkout', '2025-09-19 12:40:34', '2025-09-19 12:40:38'),
(10, 32, 'ORD20250919585597', 3085.00, 'pending', 'completed', 'snjksnjks', 'Order placed through checkout', '2025-09-19 12:41:55', '2025-09-19 12:41:59'),
(11, 32, 'ORD20250919266879', 870.00, 'pending', 'completed', 'hhjds', 'Order placed through checkout', '2025-09-19 12:43:57', '2025-09-19 12:44:00'),
(12, 32, 'ORD20250919840261', 1195.00, 'pending', 'completed', 'gaharva', 'Order placed through checkout', '2025-09-19 14:17:25', '2025-09-19 14:17:28'),
(13, 32, 'ORD20250919434414', 589.00, 'pending', 'completed', 'dsjghj', 'Order placed through checkout', '2025-09-19 14:30:55', '2025-09-19 14:30:59'),
(14, 32, 'ORD20250919605545', 817.00, 'pending', 'completed', 'fafas', 'Order placed through checkout', '2025-09-19 14:35:51', '2025-09-19 14:35:55'),
(15, 32, 'ORD20250919486037', 1573.00, 'pending', 'completed', 'dsiui', 'Order placed through checkout', '2025-09-19 15:10:33', '2025-09-19 15:10:37'),
(16, 33, 'ORD20250919441531', 439.00, 'pending', 'completed', 'jjk', 'Order placed through checkout', '2025-09-19 15:12:27', '2025-09-19 15:12:30'),
(17, 32, 'ORD20250919270952', 600.00, 'pending', 'completed', 'reer', 'Order placed through checkout', '2025-09-19 15:22:19', '2025-09-19 15:22:23'),
(18, 32, 'ORD20250920990387', 589.00, 'pending', 'completed', 'nm,n,m', 'Order placed through checkout', '2025-09-20 06:35:13', '2025-09-20 06:35:16'),
(19, 33, 'ORD20250920813235', 600.00, 'pending', 'completed', 'hsfihsiu', 'Order placed through checkout', '2025-09-20 08:19:57', '2025-09-20 08:20:00'),
(20, 32, 'ORD20250920135853', 2000.00, 'pending', 'completed', 'dsjbdsj', 'Order placed through checkout', '2025-09-20 12:39:08', '2025-09-20 12:39:11');

-- --------------------------------------------------------

--
-- Table structure for table `cart_order_items`
--

CREATE TABLE `cart_order_items` (
  `id` int(11) NOT NULL,
  `cart_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_order_items`
--

INSERT INTO `cart_order_items` (`id`, `cart_order_id`, `product_id`, `product_name`, `product_image`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 2, 3, 'Tomato', 'uploads/img_68c7aee9f11d80.98098740.jpg', 1, 170.00, 170.00, '2025-09-16 17:31:47'),
(2, 2, 2, 'Leeks', 'uploads/img_68c7af00b87057.53721180.jpg', 1, 318.00, 318.00, '2025-09-16 17:31:47'),
(3, 2, 1, 'Carrot', 'uploads/img_68c7b2c4365a69.97012947.jpg', 1, 300.00, 300.00, '2025-09-16 17:31:47'),
(4, 3, 3, 'Tomato', 'uploads/img_68c7aee9f11d80.98098740.jpg', 1, 170.00, 170.00, '2025-09-16 17:36:25'),
(5, 4, 3, 'Tomato', 'uploads/img_68c7aee9f11d80.98098740.jpg', 1, 170.00, 170.00, '2025-09-16 17:45:04'),
(6, 5, 3, 'Tomato', 'uploads/img_68ca33e95d8fa0.23034483.png', 1, 150.00, 150.00, '2025-09-17 03:58:25'),
(7, 6, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 1, 189.00, 189.00, '2025-09-17 03:59:26'),
(8, 7, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 1, 189.00, 189.00, '2025-09-19 11:16:15'),
(9, 8, 1, 'Carrot', 'uploads/img_68ca3549b0ff38.45299625.png', 1, 350.00, 350.00, '2025-09-19 11:24:16'),
(10, 8, 3, 'Tomato', 'uploads/img_68ca33e95d8fa0.23034483.png', 1, 150.00, 150.00, '2025-09-19 11:24:16'),
(11, 8, 2, 'Leeks', 'uploads/img_68ca33a59c5135.35900479.png', 13, 270.00, 3510.00, '2025-09-19 11:24:16'),
(12, 9, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 1, 189.00, 189.00, '2025-09-19 12:40:34'),
(13, 10, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 15, 189.00, 2835.00, '2025-09-19 12:41:55'),
(14, 11, 2, 'Leeks', 'uploads/img_68ca33a59c5135.35900479.png', 1, 270.00, 270.00, '2025-09-19 12:43:57'),
(15, 11, 1, 'Carrot', 'uploads/img_68ca3549b0ff38.45299625.png', 1, 350.00, 350.00, '2025-09-19 12:43:57'),
(16, 12, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 5, 189.00, 945.00, '2025-09-19 14:17:25'),
(17, 13, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 1, 189.00, 189.00, '2025-09-19 14:30:55'),
(18, 13, 3, 'Tomato', 'uploads/img_68ca33e95d8fa0.23034483.png', 1, 150.00, 150.00, '2025-09-19 14:30:55'),
(19, 14, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 3, 189.00, 567.00, '2025-09-19 14:35:51'),
(20, 15, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 7, 189.00, 1323.00, '2025-09-19 15:10:33'),
(21, 16, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 1, 189.00, 189.00, '2025-09-19 15:12:27'),
(22, 17, 1, 'Carrot', 'uploads/img_68ca3549b0ff38.45299625.png', 1, 350.00, 350.00, '2025-09-19 15:22:19'),
(23, 18, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 1, 189.00, 189.00, '2025-09-20 06:35:13'),
(24, 18, 3, 'Tomato', 'uploads/img_68ca33e95d8fa0.23034483.png', 1, 150.00, 150.00, '2025-09-20 06:35:13'),
(25, 19, 1, 'Carrot', 'uploads/img_68ca3549b0ff38.45299625.png', 1, 350.00, 350.00, '2025-09-20 08:19:57'),
(26, 20, 1, 'Carrot', 'uploads/img_68ca3549b0ff38.45299625.png', 5, 350.00, 1750.00, '2025-09-20 12:39:08');

-- --------------------------------------------------------

--
-- Table structure for table `crop_inventory`
--

CREATE TABLE `crop_inventory` (
  `crop_id` int(100) NOT NULL,
  `crop_name` varchar(100) NOT NULL,
  `quantity` decimal(10,3) DEFAULT NULL,
  `status` enum('Available','Unavailable','Sold') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop_inventory`
--

INSERT INTO `crop_inventory` (`crop_id`, `crop_name`, `quantity`, `status`) VALUES
(1, 'Leeks', 200.000, 'Available'),
(3, 'Carrot', 300.000, 'Available'),
(5, 'Tomato', 180.000, 'Available'),
(6, 'Cabbage', 200.000, 'Unavailable'),
(9, 'Potato', 0.000, 'Sold'),
(10, 'Beet Root', 400.000, 'Available'),
(11, 'Eggplant', 304.000, 'Available'),
(12, 'Pumpkin', 1000.000, 'Available'),
(13, 'Beans', 1000.000, 'Available'),
(14, 'Brinjol', 555.000, 'Available');

--
-- Triggers `crop_inventory`
--
DELIMITER $$
CREATE TRIGGER `crop_inventory_set_sold` BEFORE UPDATE ON `crop_inventory` FOR EACH ROW BEGIN
  IF NEW.quantity = 0 THEN
    SET NEW.status = 'Sold';
  ELSEIF NEW.quantity > 0 AND NEW.status != 'Unavailable' THEN
    SET NEW.status = 'Available';
  END IF;
END
$$
DELIMITER ;

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
-- Table structure for table `interest_requests`
--

CREATE TABLE `interest_requests` (
  `request_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `land_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','under_review','approved','rejected') DEFAULT 'pending',
  `financial_manager_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interest_requests`
--

INSERT INTO `interest_requests` (`request_id`, `report_id`, `land_id`, `user_id`, `status`, `financial_manager_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 19, 32, 'approved', 'Test via API', '2025-09-20 10:52:56', '2025-09-20 11:20:19'),
(2, 3, 42, 32, 'approved', 'Proposal generated and sent to landowner', '2025-09-20 10:58:16', '2025-09-20 11:21:32'),
(3, 2, 21, 32, 'rejected', 'Request rejected after review', '2025-09-20 11:40:18', '2025-09-20 14:13:06');

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
(46, 32, 'Pahala Giribawa, Kurunegala District, North Western Province, Sri Lanka', 12.000, 'paid', '2025-09-12 18:37:58', '2025-09-12 18:37:53', '2025-09-12 18:37:58'),
(47, 32, 'Andigama, Puttalam District, North Western Province, Sri Lanka', 12.000, 'pending', NULL, '2025-09-13 12:16:17', '2025-09-13 15:46:17'),
(48, 32, 'Horana-Kaluthara Road, Galpatha, Millaniya DS Division, Kalutara District, Western Province, 12412, Sri Lanka', 12.000, 'pending', NULL, '2025-09-13 12:18:39', '2025-09-13 15:48:39'),
(49, 32, 'Passara - Madulsima - Metigahatenne Road, Galloola, Madulsima, Badulla District, Uva Province, Sri Lanka', 12.000, 'pending', NULL, '2025-09-13 13:57:25', '2025-09-13 17:27:25'),
(50, 32, 'Passara - Madulsima - Metigahatenne Road, Galloola, Madulsima, Badulla District, Uva Province, Sri Lanka', 12.000, 'pending', NULL, '2025-09-13 14:02:53', '2025-09-13 17:32:53'),
(51, 32, 'Kalutara', 12.000, 'pending', NULL, '2025-09-13 14:13:39', '2025-09-13 17:43:39'),
(52, 32, 'Nikawatawana, Matale District, Central Province, Sri Lanka', 12.000, 'pending', NULL, '2025-09-13 14:40:57', '2025-09-13 18:10:57'),
(53, 32, 'Nikawatawana, Matale District, Central Province, Sri Lanka', 12.000, 'pending', NULL, '2025-09-13 14:46:38', '2025-09-13 18:16:38'),
(54, 32, 'Ratnapura District, Sabaragamuwa Province, Sri Lanka', 56.000, 'pending', NULL, '2025-09-13 14:48:16', '2025-09-13 18:18:16'),
(55, 32, 'Maldeniya, Ampara District, Eastern Province, Sri Lanka', 34.000, 'paid', '2025-09-13 15:00:33', '2025-09-13 15:00:29', '2025-09-13 18:30:33'),
(56, 32, 'Bathgampola, Hettipola, Matale District, Central Province, Sri Lanka', 999.999, 'pending', NULL, '2025-09-13 15:04:36', '2025-09-13 18:34:36'),
(57, 32, 'Bathgampola, Hettipola, Matale District, Central Province, Sri Lanka', 999.999, 'paid', '2025-09-13 15:05:01', '2025-09-13 15:04:58', '2025-09-13 18:35:01'),
(58, 32, 'Rambukkana-Polgahawela Via Denagamuwa, Rambukkana, Kegalle District, Sabaragamuwa Province, 71100, Sri Lanka', 34.000, 'paid', '2025-09-15 14:22:28', '2025-09-15 14:22:24', '2025-09-15 17:52:28'),
(59, 32, 'Malwaththai, Ampara District, Eastern Province, Sri Lanka', 56.000, 'paid', '2025-09-16 01:10:52', '2025-09-16 01:10:48', '2025-09-16 04:40:52'),
(60, 33, 'Puttalam District, North Western Province, Sri Lanka', 12.000, 'paid', '2025-09-19 11:17:59', '2025-09-19 11:17:55', '2025-09-19 14:47:59'),
(61, 42, 'Hantana Rajamaha Viharaya, Hanthana Road, Deiyannewela, Hanthana, Kandy, Kandy District, Central Province, 85129, Sri Lanka', 34.000, 'paid', '2025-09-19 11:23:46', '2025-09-19 11:23:43', '2025-09-19 14:53:46'),
(62, 32, 'g, Delathura - Ja Ela Road, Highway Access Junction Ja-Ela, Ja-ela, Gampaha District, Western Province, 11350, Sri Lanka', 123.000, 'paid', '2025-09-19 12:39:59', '2025-09-19 12:39:56', '2025-09-19 16:09:59'),
(63, 33, 'Harasgala, Kekirihena, Ampara District, Eastern Province, Sri Lanka', 45.000, 'paid', '2025-09-19 12:41:14', '2025-09-19 12:41:11', '2025-09-19 16:11:14'),
(64, 32, 'Polonnaruwa District, North Central Province, Sri Lanka', 34.000, 'paid', '2025-09-19 13:18:05', '2025-09-19 13:18:02', '2025-09-19 16:48:05'),
(65, 33, 'Nikawatawana, Matale District, Central Province, Sri Lanka', 12.000, 'paid', '2025-09-19 15:13:19', '2025-09-19 15:13:15', '2025-09-19 18:43:19'),
(66, 32, 'Nikawewa, Puttalam District, North Western Province, Sri Lanka', 12.000, 'paid', '2025-09-20 12:38:15', '2025-09-20 12:38:12', '2025-09-20 16:08:15');

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
  `status` enum('Rejected','Approved','','') NOT NULL,
  `conclusion` text DEFAULT NULL,
  `suitability_status` enum('suitable','not_suitable','pending') DEFAULT 'pending',
  `completion_status` enum('In Progress','Completed') DEFAULT 'In Progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `land_report`
--

INSERT INTO `land_report` (`report_id`, `land_id`, `user_id`, `report_date`, `land_description`, `crop_recomendation`, `ph_value`, `organic_matter`, `nitrogen_level`, `phosphorus_level`, `potassium_level`, `environmental_notes`, `created_at`, `updated_at`, `status`, `conclusion`, `suitability_status`, `completion_status`) VALUES
(1, 19, 32, '2025-08-20', 'Well-drained sandy loam soil with good organic content. The land has adequate water access and receives good sunlight throughout the day. Suitable for various crop types.', 'Based on soil analysis and environmental conditions:\r\n\r\n1. Rice cultivation (recommended for wet season)\r\n   - Expected yield: 4-5 tons per hectare\r\n   - Market price: Rs. 80-100 per kg\r\n\r\n2. Vegetable cultivation (year-round)\r\n   - Tomatoes, beans, cabba', 6.5, 4.20, 'Medium', 'High', 'Medium', 'Well-drained soil with good water retention. Average annual rainfall 1500mm. Temperature range 24-32°C.\nAssigned to: Kanchana Almeda (ID: 31)', '2025-08-28 06:37:15', '2025-09-23 17:15:17', '', '{\"is_good_for_organic\":true,\"conclusion_text\":\"SUITABLE - Good for organic farming - Based on your soil data, we can recommend suitable crops for organic farming on your land.\",\"recommended_crops\":[\"Tomatoes\",\"Lettuce\",\"Carrots\",\"Beans\",\"Cabbage\",\"Spinach\"],\"status\":\"good\"}', 'suitable', 'Completed'),
(2, 21, 32, '2025-08-22', 'Clay-rich soil with moderate drainage. The land requires some soil improvement for optimal cultivation. Water retention is good but may need drainage during heavy rains.', 'Soil improvement recommendations:\n\n1. Add organic compost to improve soil structure\n2. Install proper drainage systems\n3. Consider raised bed cultivation\n\nCrop recommendations:\n- Rice (primary recommendation)\n- Root vegetables (potatoes, carrots)\n', 7.1, 3.78, 'Low', 'Medium', 'High', 'Clay-rich soil requiring drainage improvement. Good water retention but may waterlog during heavy rains.\nAssigned to: njk njkhjhj (ID: 40)\nAssigned to: njk njkhjhj (ID: 40)', '2025-08-28 06:37:15', '2025-09-23 17:12:12', '', '{\"is_good_for_organic\":true,\"conclusion_text\":\"\\u2705 Good for organic farming - Based on your soil data, we can recommend suitable crops for organic farming on your land.\",\"recommended_crops\":[\"Tomatoes\",\"Lettuce\",\"Carrots\",\"Beans\",\"Cabbage\",\"Spinach\"],\"status\":\"good\"}', 'suitable', 'Completed'),
(3, 42, 32, '2025-09-23', 'Premium agricultural land with excellent soil composition and natural water sources. Ideal conditions for diverse crop cultivation.', 'This land has premium potential for:\r\n\r\n1. Organic farming certification possible\r\n2. High-value crops recommended:\r\n   - Organic vegetables for export\r\n   - Medicinal plants cultivation\r\n   - Fruit orchards\r\n\r\n3. Estimated annual income: Rs. 200,000-300,', 6.8, 5.10, 'High', 'High', 'Medium', 'Premium agricultural land with excellent conditions for organic farming.\nAssigned to: mkf sdk (ID: 41)', '2025-08-28 06:37:15', '2025-09-23 17:17:15', '', '{\"is_good_for_organic\":true,\"conclusion_text\":\"SUITABLE - Good for organic farming - Based on your soil data, we can recommend suitable crops for organic farming on your land.\",\"recommended_crops\":[\"Tomatoes\",\"Lettuce\",\"Carrots\",\"Beans\",\"Cabbage\",\"Spinach\"],\"status\":\"good\"}', 'suitable', 'Completed'),
(5, 19, 32, '2025-09-23', 'Test land for new assignment', '', NULL, NULL, NULL, NULL, NULL, 'Test soil conditions for farming. Assigned to: Kanchana Almeda (ID: 31)', '2025-09-23 17:17:45', '2025-09-23 17:42:09', 'Rejected', NULL, 'pending', 'Completed'),
(6, 19, 32, '2025-09-23', 'Test land for connection testing', '', NULL, NULL, NULL, NULL, NULL, 'Test soil conditions for connection testing.                                Assigned to: mkf sdk (ID: 41)', '2025-09-23 17:31:58', '2025-09-23 17:32:34', 'Rejected', NULL, 'pending', 'In Progress');

-- --------------------------------------------------------

--
-- Table structure for table `leasing_proposals`
--

CREATE TABLE `leasing_proposals` (
  `proposal_id` int(11) NOT NULL,
  `interest_request_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `land_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `financial_manager_id` int(11) NOT NULL,
  `lease_duration_months` int(11) NOT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL,
  `profit_share_percentage` decimal(5,2) NOT NULL,
  `expected_investment` decimal(12,2) NOT NULL,
  `expected_roi_percentage` decimal(5,2) DEFAULT NULL,
  `terms_and_conditions` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` enum('draft','sent_to_landowner','accepted','rejected','negotiating') DEFAULT 'draft',
  `landowner_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `payment_type` enum('land_report','cart_purchase') NOT NULL DEFAULT 'land_report',
  `land_id` int(11) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `cart_items` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `total_items` int(11) DEFAULT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','debit_card','bank_transfer','stripe') NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `gateway_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `payment_type`, `land_id`, `order_id`, `cart_items`, `shipping_address`, `total_items`, `transaction_id`, `amount`, `payment_method`, `payment_status`, `gateway_response`, `created_at`, `updated_at`, `stripe_payment_intent_id`) VALUES
(12, 32, 'land_report', 22, NULL, NULL, NULL, NULL, 'stripe_pi_3S0eI3C523WS3olJ0XN2Qp9q', 5000.00, '', 'completed', NULL, '2025-08-27 08:01:09', '2025-08-27 08:01:09', 'pi_3S0eI3C523WS3olJ0XN2Qp9q'),
(17, 32, 'land_report', 28, NULL, NULL, NULL, NULL, 'stripe_pi_3S0evjC523WS3olJ0C1Sz8CN', 5000.00, '', 'completed', NULL, '2025-08-27 08:42:11', '2025-08-27 08:42:11', 'pi_3S0evjC523WS3olJ0C1Sz8CN'),
(18, 32, 'land_report', 32, NULL, NULL, NULL, NULL, 'stripe_pi_3S0eykC523WS3olJ1rM5v2he', 5000.00, '', 'completed', NULL, '2025-08-27 08:45:17', '2025-08-27 08:45:17', 'pi_3S0eykC523WS3olJ1rM5v2he'),
(19, 32, 'land_report', 41, NULL, NULL, NULL, NULL, 'stripe_pi_3S0hG8C523WS3olJ1G4hinyH', 5000.00, '', 'completed', NULL, '2025-08-27 11:11:22', '2025-08-27 11:11:22', 'pi_3S0hG8C523WS3olJ1G4hinyH'),
(20, 32, 'land_report', 42, NULL, NULL, NULL, NULL, 'stripe_pi_3S0hMAC523WS3olJ1unWLL7A', 5000.00, '', 'completed', NULL, '2025-08-27 11:17:36', '2025-08-27 11:17:36', 'pi_3S0hMAC523WS3olJ1unWLL7A'),
(21, 32, 'land_report', 44, NULL, NULL, NULL, NULL, 'stripe_pi_3S0z9oC523WS3olJ0GtxCWm7', 5000.00, '', 'completed', NULL, '2025-08-28 06:18:02', '2025-08-28 06:18:02', 'pi_3S0z9oC523WS3olJ0GtxCWm7'),
(22, 32, 'land_report', 45, NULL, NULL, NULL, NULL, 'stripe_pi_3S4bOQC523WS3olJ2vTYZJ8c', 5000.00, '', 'completed', NULL, '2025-09-07 05:44:06', '2025-09-07 05:44:06', 'pi_3S4bOQC523WS3olJ2vTYZJ8c'),
(23, 32, 'land_report', 46, NULL, NULL, NULL, NULL, 'stripe_pi_3S6br2C523WS3olJ2hBUR6k5', 5000.00, '', 'completed', NULL, '2025-09-12 18:37:57', '2025-09-12 18:37:57', 'pi_3S6br2C523WS3olJ2hBUR6k5'),
(24, 32, 'land_report', 55, NULL, NULL, NULL, NULL, 'stripe_pi_3S6yDTC523WS3olJ0HbhlWgy', 4999.50, '', 'completed', NULL, '2025-09-13 15:00:33', '2025-09-13 18:30:33', 'pi_3S6yDTC523WS3olJ0HbhlWgy'),
(25, 32, 'land_report', 57, NULL, NULL, NULL, NULL, 'stripe_pi_3S6yHnC523WS3olJ1npHOITr', 4999.50, '', 'completed', NULL, '2025-09-13 15:05:01', '2025-09-13 18:35:01', 'pi_3S6yHnC523WS3olJ1npHOITr'),
(26, 32, 'land_report', 58, NULL, NULL, NULL, NULL, 'stripe_pi_3S7gZiC523WS3olJ25UUDtcy', 4999.50, 'stripe', 'completed', NULL, '2025-09-15 14:22:28', '2025-09-15 17:52:28', 'pi_3S7gZiC523WS3olJ25UUDtcy'),
(27, 32, 'land_report', 59, NULL, NULL, NULL, NULL, 'stripe_pi_3S7qhAC523WS3olJ1Ce40oAM', 4999.50, 'stripe', 'completed', NULL, '2025-09-16 01:10:52', '2025-09-16 04:40:52', 'pi_3S7qhAC523WS3olJ1Ce40oAM'),
(28, 33, 'cart_purchase', NULL, 'ORD20250916994983', NULL, NULL, NULL, 'stripe_pi_3S860UC523WS3olJ19QDm0tx', 1039.50, 'stripe', 'completed', NULL, '2025-09-16 17:31:50', '2025-09-16 21:01:50', 'pi_3S860UC523WS3olJ19QDm0tx'),
(29, 33, 'cart_purchase', NULL, 'ORD20250916834673', NULL, NULL, NULL, 'stripe_pi_3S864yC523WS3olJ0jiWZMJE', 419.10, 'stripe', 'completed', NULL, '2025-09-16 17:36:28', '2025-09-16 21:06:28', 'pi_3S864yC523WS3olJ0jiWZMJE'),
(30, 33, 'cart_purchase', NULL, 'ORD20250916127284', NULL, NULL, NULL, 'stripe_pi_3S86DMC523WS3olJ2W6boeDb', 419.10, 'stripe', 'completed', NULL, '2025-09-16 17:45:07', '2025-09-16 21:15:07', 'pi_3S86DMC523WS3olJ2W6boeDb'),
(31, 33, 'cart_purchase', NULL, 'ORD20250917389589', NULL, NULL, NULL, 'stripe_pi_3S8FmxC523WS3olJ2ZT4pJ1R', 399.30, 'stripe', 'completed', NULL, '2025-09-17 03:58:30', '2025-09-17 07:28:30', 'pi_3S8FmxC523WS3olJ2ZT4pJ1R'),
(32, 33, 'cart_purchase', NULL, 'ORD20250917044232', NULL, NULL, NULL, 'stripe_pi_3S8FnvC523WS3olJ0NcGs3vd', 438.90, 'stripe', 'completed', NULL, '2025-09-17 03:59:30', '2025-09-17 07:29:30', 'pi_3S8FnvC523WS3olJ0NcGs3vd'),
(33, 32, 'cart_purchase', NULL, 'ORD20250919408820', NULL, NULL, NULL, 'stripe_pi_3S95ZjC523WS3olJ2Ltd209Z', 438.90, 'stripe', 'completed', NULL, '2025-09-19 11:16:18', '2025-09-19 14:46:18', 'pi_3S95ZjC523WS3olJ2Ltd209Z'),
(34, 33, 'land_report', 60, NULL, NULL, NULL, NULL, 'stripe_pi_3S95bMC523WS3olJ1nji0RTv', 4999.50, 'stripe', 'completed', NULL, '2025-09-19 11:17:59', '2025-09-19 14:47:59', 'pi_3S95bMC523WS3olJ1nji0RTv'),
(35, 42, 'land_report', 61, NULL, NULL, NULL, NULL, 'stripe_pi_3S95gyC523WS3olJ0nyzo7xR', 4999.50, 'stripe', 'completed', NULL, '2025-09-19 11:23:46', '2025-09-19 14:53:46', 'pi_3S95gyC523WS3olJ0nyzo7xR'),
(36, 42, 'cart_purchase', NULL, 'ORD20250919022599', NULL, NULL, NULL, 'stripe_pi_3S95hVC523WS3olJ1Y6HCAIN', 4260.30, 'stripe', 'completed', NULL, '2025-09-19 11:24:19', '2025-09-19 14:54:19', 'pi_3S95hVC523WS3olJ1Y6HCAIN'),
(37, 32, 'land_report', 62, NULL, NULL, NULL, NULL, 'stripe_pi_3S96sjC523WS3olJ0bpOUGoW', 4999.50, 'stripe', 'completed', NULL, '2025-09-19 12:39:59', '2025-09-19 16:09:59', 'pi_3S96sjC523WS3olJ0bpOUGoW'),
(38, 33, 'cart_purchase', NULL, 'ORD20250919515334', NULL, NULL, NULL, 'stripe_pi_3S96tLC523WS3olJ27zhaBXY', 438.90, 'stripe', 'completed', NULL, '2025-09-19 12:40:38', '2025-09-19 16:10:38', 'pi_3S96tLC523WS3olJ27zhaBXY'),
(39, 33, 'land_report', 63, NULL, NULL, NULL, NULL, 'stripe_pi_3S96tvC523WS3olJ1LhOz2Zq', 4999.50, 'stripe', 'completed', NULL, '2025-09-19 12:41:14', '2025-09-19 16:11:14', 'pi_3S96tvC523WS3olJ1LhOz2Zq'),
(40, 32, 'cart_purchase', NULL, 'ORD20250919585597', NULL, NULL, NULL, 'stripe_pi_3S96ueC523WS3olJ0DcLeeeQ', 3085.50, 'stripe', 'completed', NULL, '2025-09-19 12:41:59', '2025-09-19 16:11:59', 'pi_3S96ueC523WS3olJ0DcLeeeQ'),
(41, 32, 'cart_purchase', NULL, 'ORD20250919266879', NULL, NULL, NULL, 'stripe_pi_3S96wbC523WS3olJ05dtELw1', 871.20, 'stripe', 'completed', NULL, '2025-09-19 12:44:00', '2025-09-19 16:14:00', 'pi_3S96wbC523WS3olJ05dtELw1'),
(42, 32, 'land_report', 64, NULL, NULL, NULL, NULL, 'stripe_pi_3S97TaC523WS3olJ1TnZdCEH', 4999.50, 'stripe', 'completed', NULL, '2025-09-19 13:18:05', '2025-09-19 16:48:05', 'pi_3S97TaC523WS3olJ1TnZdCEH'),
(43, 32, 'cart_purchase', NULL, 'ORD20250919840261', NULL, NULL, NULL, 'stripe_pi_3S98P4C523WS3olJ134JtUKb', 1194.60, 'stripe', 'completed', NULL, '2025-09-19 14:17:28', '2025-09-19 17:47:28', 'pi_3S98P4C523WS3olJ134JtUKb'),
(44, 32, 'cart_purchase', NULL, 'ORD20250919434414', NULL, NULL, NULL, 'stripe_pi_3S98c8C523WS3olJ2Et1fNRz', 587.40, 'stripe', 'completed', NULL, '2025-09-19 14:30:59', '2025-09-19 18:00:59', 'pi_3S98c8C523WS3olJ2Et1fNRz'),
(45, 32, 'cart_purchase', NULL, 'ORD20250919605545', NULL, NULL, NULL, 'stripe_pi_3S98guC523WS3olJ2TwCtxp3', 818.40, 'stripe', 'completed', NULL, '2025-09-19 14:35:55', '2025-09-19 18:05:55', 'pi_3S98guC523WS3olJ2TwCtxp3'),
(46, 32, 'cart_purchase', NULL, 'ORD20250919486037', NULL, NULL, NULL, 'stripe_pi_3S99EUC523WS3olJ0fnlTmc5', 1574.10, 'stripe', 'completed', NULL, '2025-09-19 15:10:37', '2025-09-19 18:40:37', 'pi_3S99EUC523WS3olJ0fnlTmc5'),
(47, 33, 'cart_purchase', NULL, 'ORD20250919441531', NULL, NULL, NULL, 'stripe_pi_3S99GJC523WS3olJ04ZoJTjv', 438.90, 'stripe', 'completed', NULL, '2025-09-19 15:12:30', '2025-09-19 18:42:30', 'pi_3S99GJC523WS3olJ04ZoJTjv'),
(48, 33, 'land_report', 65, NULL, NULL, NULL, NULL, 'stripe_pi_3S99H6C523WS3olJ2yAulUFj', 4999.50, 'stripe', 'completed', NULL, '2025-09-19 15:13:19', '2025-09-19 18:43:19', 'pi_3S99H6C523WS3olJ2yAulUFj'),
(49, 32, 'cart_purchase', NULL, 'ORD20250919270952', NULL, NULL, NULL, 'stripe_pi_3S99PsC523WS3olJ2oh6G4YR', 600.60, 'stripe', 'completed', NULL, '2025-09-19 15:22:23', '2025-09-19 18:52:23', 'pi_3S99PsC523WS3olJ2oh6G4YR'),
(50, 32, 'cart_purchase', NULL, 'ORD20250920990387', NULL, NULL, NULL, 'stripe_pi_3S9NfKC523WS3olJ1Hm4QntD', 587.40, 'stripe', 'completed', NULL, '2025-09-20 06:35:16', '2025-09-20 10:05:16', 'pi_3S9NfKC523WS3olJ1Hm4QntD'),
(51, 33, 'cart_purchase', NULL, 'ORD20250920813235', NULL, NULL, NULL, 'stripe_pi_3S9PIgC523WS3olJ0TpKoGth', 600.60, 'stripe', 'completed', NULL, '2025-09-20 08:20:00', '2025-09-20 11:50:00', 'pi_3S9PIgC523WS3olJ0TpKoGth'),
(52, 32, 'land_report', 66, NULL, NULL, NULL, NULL, 'stripe_pi_3S9TKaC523WS3olJ0KxPhopB', 4999.50, 'stripe', 'completed', NULL, '2025-09-20 12:38:15', '2025-09-20 16:08:15', 'pi_3S9TKaC523WS3olJ0KxPhopB'),
(53, 32, 'cart_purchase', NULL, 'ORD20250920135853', NULL, NULL, NULL, 'stripe_pi_3S9TLVC523WS3olJ2YarwpaF', 1999.80, 'stripe', 'completed', NULL, '2025-09-20 12:39:11', '2025-09-20 16:09:11', 'pi_3S9TLVC523WS3olJ2YarwpaF');

-- --------------------------------------------------------

--
-- Table structure for table `payment_intents`
--

CREATE TABLE `payment_intents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_type` enum('land_report','cart_purchase') NOT NULL DEFAULT 'land_report',
  `land_id` int(11) DEFAULT NULL,
  `cart_order_id` int(11) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('created','succeeded','failed','cancelled') DEFAULT 'created',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_intents`
--

INSERT INTO `payment_intents` (`id`, `user_id`, `payment_type`, `land_id`, `cart_order_id`, `stripe_payment_intent_id`, `amount`, `status`, `created_at`, `updated_at`) VALUES
(3, 32, 'land_report', 19, NULL, 'pi_3S0d3qC523WS3olJ0iJDEEKv', 5000.00, 'succeeded', '2025-08-27 06:42:22', '2025-08-27 06:42:24'),
(4, 32, 'land_report', 20, NULL, 'pi_3S0dAzC523WS3olJ0ylxmacn', 5000.00, 'succeeded', '2025-08-27 06:49:45', '2025-08-27 06:49:47'),
(19, 32, 'land_report', 41, NULL, 'pi_3S0hG8C523WS3olJ1G4hinyH', 5000.00, 'succeeded', '2025-08-27 11:11:20', '2025-08-27 11:11:22'),
(20, 32, 'land_report', 42, NULL, 'pi_3S0hMAC523WS3olJ1unWLL7A', 5000.00, 'succeeded', '2025-08-27 11:17:35', '2025-08-27 11:17:36'),
(22, 32, 'land_report', 44, NULL, 'pi_3S0z9oC523WS3olJ0GtxCWm7', 5000.00, 'succeeded', '2025-08-28 06:18:00', '2025-08-28 06:18:02'),
(23, 32, 'land_report', 45, NULL, 'pi_3S4bOQC523WS3olJ2vTYZJ8c', 5000.00, 'succeeded', '2025-09-07 05:44:02', '2025-09-07 05:44:06'),
(24, 32, 'land_report', 46, NULL, 'pi_3S6br2C523WS3olJ2hBUR6k5', 5000.00, 'succeeded', '2025-09-12 18:37:55', '2025-09-12 18:37:58'),
(25, 32, 'land_report', 47, NULL, 'pi_3S6veYC523WS3olJ0U6NlhYF', 5000.00, 'created', '2025-09-13 12:16:18', '2025-09-13 15:46:18'),
(26, 32, 'land_report', 48, NULL, 'pi_3S6vgqC523WS3olJ1fqPcfQZ', 5000.00, 'created', '2025-09-13 12:18:41', '2025-09-13 15:48:41'),
(27, 32, 'land_report', 49, NULL, 'pi_3S6xERC523WS3olJ2fFY7d5G', 5000.00, 'created', '2025-09-13 13:57:26', '2025-09-13 17:27:26'),
(28, 32, 'land_report', 51, NULL, 'pi_3S6xU8C523WS3olJ053bq4l6', 15.00, 'created', '2025-09-13 14:13:40', '2025-09-13 17:43:40'),
(29, 32, 'land_report', 52, NULL, 'pi_3S6xuYC523WS3olJ1SHwTehs', 5000.00, 'created', '2025-09-13 14:40:58', '2025-09-13 18:10:58'),
(30, 32, 'land_report', 55, NULL, 'pi_3S6yDTC523WS3olJ0HbhlWgy', 5000.00, 'succeeded', '2025-09-13 15:00:31', '2025-09-13 18:30:33'),
(31, 32, 'land_report', 57, NULL, 'pi_3S6yHnC523WS3olJ1npHOITr', 5000.00, 'succeeded', '2025-09-13 15:04:59', '2025-09-13 18:35:01'),
(32, 32, 'land_report', 58, NULL, 'pi_3S7gZiC523WS3olJ25UUDtcy', 5000.00, 'succeeded', '2025-09-15 14:22:26', '2025-09-15 17:52:28'),
(33, 32, 'land_report', 59, NULL, 'pi_3S7qhAC523WS3olJ1Ce40oAM', 5000.00, 'succeeded', '2025-09-16 01:10:49', '2025-09-16 04:40:52'),
(34, 33, 'cart_purchase', NULL, 2, 'pi_3S860UC523WS3olJ19QDm0tx', 1038.00, 'succeeded', '2025-09-16 17:31:48', '2025-09-16 21:01:50'),
(35, 33, 'cart_purchase', NULL, 3, 'pi_3S864yC523WS3olJ0jiWZMJE', 420.00, 'succeeded', '2025-09-16 17:36:26', '2025-09-16 21:06:28'),
(36, 33, 'cart_purchase', NULL, 4, 'pi_3S86DMC523WS3olJ2W6boeDb', 420.00, 'succeeded', '2025-09-16 17:45:05', '2025-09-16 21:15:07'),
(37, 33, 'cart_purchase', NULL, 5, 'pi_3S8FmxC523WS3olJ2ZT4pJ1R', 400.00, 'succeeded', '2025-09-17 03:58:27', '2025-09-17 07:28:30'),
(38, 33, 'cart_purchase', NULL, 6, 'pi_3S8FnvC523WS3olJ0NcGs3vd', 439.00, 'succeeded', '2025-09-17 03:59:27', '2025-09-17 07:29:30'),
(39, 32, 'cart_purchase', NULL, 7, 'pi_3S95ZjC523WS3olJ2Ltd209Z', 439.00, 'succeeded', '2025-09-19 11:16:16', '2025-09-19 14:46:18'),
(40, 33, 'land_report', 60, NULL, 'pi_3S95bMC523WS3olJ1nji0RTv', 5000.00, 'succeeded', '2025-09-19 11:17:56', '2025-09-19 14:47:59'),
(41, 42, 'land_report', 61, NULL, 'pi_3S95gyC523WS3olJ0nyzo7xR', 5000.00, 'succeeded', '2025-09-19 11:23:44', '2025-09-19 14:53:46'),
(42, 42, 'cart_purchase', NULL, 8, 'pi_3S95hVC523WS3olJ1Y6HCAIN', 4260.00, 'succeeded', '2025-09-19 11:24:17', '2025-09-19 14:54:19'),
(43, 32, 'land_report', 62, NULL, 'pi_3S96sjC523WS3olJ0bpOUGoW', 5000.00, 'succeeded', '2025-09-19 12:39:57', '2025-09-19 16:09:59'),
(44, 33, 'cart_purchase', NULL, 9, 'pi_3S96tLC523WS3olJ27zhaBXY', 439.00, 'succeeded', '2025-09-19 12:40:35', '2025-09-19 16:10:38'),
(45, 33, 'land_report', 63, NULL, 'pi_3S96tvC523WS3olJ1LhOz2Zq', 5000.00, 'succeeded', '2025-09-19 12:41:12', '2025-09-19 16:11:14'),
(46, 32, 'cart_purchase', NULL, 10, 'pi_3S96ueC523WS3olJ0DcLeeeQ', 3085.00, 'succeeded', '2025-09-19 12:41:57', '2025-09-19 16:11:59'),
(47, 32, 'cart_purchase', NULL, 11, 'pi_3S96wbC523WS3olJ05dtELw1', 870.00, 'succeeded', '2025-09-19 12:43:58', '2025-09-19 16:14:00'),
(48, 32, 'land_report', 64, NULL, 'pi_3S97TaC523WS3olJ1TnZdCEH', 5000.00, 'succeeded', '2025-09-19 13:18:03', '2025-09-19 16:48:05'),
(49, 32, 'cart_purchase', NULL, 12, 'pi_3S98P4C523WS3olJ134JtUKb', 1195.00, 'succeeded', '2025-09-19 14:17:26', '2025-09-19 17:47:28'),
(50, 32, 'cart_purchase', NULL, 13, 'pi_3S98c8C523WS3olJ2Et1fNRz', 589.00, 'succeeded', '2025-09-19 14:30:56', '2025-09-19 18:00:59'),
(51, 32, 'cart_purchase', NULL, 14, 'pi_3S98guC523WS3olJ2TwCtxp3', 817.00, 'succeeded', '2025-09-19 14:35:52', '2025-09-19 18:05:55'),
(52, 32, 'cart_purchase', NULL, 15, 'pi_3S99EUC523WS3olJ0fnlTmc5', 1573.00, 'succeeded', '2025-09-19 15:10:35', '2025-09-19 18:40:37'),
(53, 33, 'cart_purchase', NULL, 16, 'pi_3S99GJC523WS3olJ04ZoJTjv', 439.00, 'succeeded', '2025-09-19 15:12:28', '2025-09-19 18:42:30'),
(54, 33, 'land_report', 65, NULL, 'pi_3S99H6C523WS3olJ2yAulUFj', 5000.00, 'succeeded', '2025-09-19 15:13:17', '2025-09-19 18:43:19'),
(55, 32, 'cart_purchase', NULL, 17, 'pi_3S99PsC523WS3olJ2oh6G4YR', 600.00, 'succeeded', '2025-09-19 15:22:21', '2025-09-19 18:52:23'),
(56, 32, 'cart_purchase', NULL, 18, 'pi_3S9NfKC523WS3olJ1Hm4QntD', 589.00, 'succeeded', '2025-09-20 06:35:14', '2025-09-20 10:05:16'),
(57, 33, 'cart_purchase', NULL, 19, 'pi_3S9PIgC523WS3olJ0TpKoGth', 600.00, 'succeeded', '2025-09-20 08:19:58', '2025-09-20 11:50:00'),
(58, 32, 'land_report', 66, NULL, 'pi_3S9TKaC523WS3olJ0KxPhopB', 5000.00, 'succeeded', '2025-09-20 12:38:12', '2025-09-20 16:08:15'),
(59, 32, 'cart_purchase', NULL, 20, 'pi_3S9TLVC523WS3olJ2YarwpaF', 2000.00, 'succeeded', '2025-09-20 12:39:09', '2025-09-20 16:09:11');

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
(7, 10, 189.00, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 1);

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `generated_from_request_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`proposal_id`, `land_id`, `user_id`, `crop_type`, `estimated_yield`, `lease_duration_years`, `rental_value`, `profit_sharing_farmmaster`, `profit_sharing_landowner`, `estimated_profit_landowner`, `status`, `proposal_date`, `created_at`, `updated_at`, `generated_from_request_id`) VALUES
(1, 19, 32, 'Organic Vegetables (Tomato, Carrot)', 10000.00, 3, 50000.00, 60.00, 40.00, 80000.00, 'Accepted', '2025-08-15', '2025-08-28 06:53:15', '2025-09-12 18:35:42', NULL),
(2, 21, 32, 'Rice and Root Vegetables', 8000.00, 2, 45000.00, 65.00, 35.00, 70000.00, 'Accepted', '2025-08-20', '2025-08-28 06:53:15', '2025-09-12 18:35:42', NULL),
(3, 42, 32, 'Premium Organic Crops', 12000.00, 4, 60000.00, 55.00, 45.00, 100000.00, 'Pending', '2025-08-25', '2025-08-28 06:53:15', '2025-09-12 18:35:42', NULL),
(4, 19, 32, 'Organic Vegetables (Tomato, Carrot)', 10000.00, 3, 50000.00, 60.00, 40.00, 80000.00, 'Accepted', '2025-08-15', '2025-08-28 06:54:03', '2025-09-12 18:35:42', NULL),
(5, 21, 32, 'Rice and Root Vegetables', 8000.00, 2, 45000.00, 65.00, 35.00, 70000.00, 'Pending', '2025-08-20', '2025-08-28 06:54:03', '2025-09-12 18:35:42', NULL),
(6, 42, 32, 'Premium Organic Crops', 12000.00, 4, 60000.00, 55.00, 45.00, 100000.00, 'Pending', '2025-08-25', '2025-08-28 06:54:03', '2025-09-12 18:35:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `proposal_requests`
--

CREATE TABLE `proposal_requests` (
  `request_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `land_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL,
  `status` enum('pending_review','under_review','proposal_generated','rejected') DEFAULT 'pending_review',
  `crop_recommendations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`crop_recommendations`)),
  `suitability_score` decimal(5,2) DEFAULT NULL,
  `financial_manager_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `current_active_role` enum('Landowner','Supervisor','Buyer','Financial_Manager','Operational_Manager') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `email`, `user_role`, `password`, `phone`, `is_active`, `current_active_role`) VALUES
(29, 'Thejani', 'Janiya', 'om@gmail.com', 'Operational_Manager', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', '9876543210', 1, NULL),
(30, 'Gimhani', 'Perera', 'fm@gmail.com', 'Financial_Manager', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', '2147483647', 1, NULL),
(31, 'Kanchana', 'Almeda', 'fs@gmail.com', 'Supervisor', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', '+94274836477', 1, NULL),
(32, 'Nuwani', 'Silva', 'lo@gmail.com', 'Landowner', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', '774352566', 1, 'Buyer'),
(33, 'Dasuni', 'Peris', 'by@gmail.com', 'Buyer', '$2y$10$NvFPBMmbLGc4X0.Urw0Pl.ILVKOAe3oz6/x0msuXcmtuIlqfLqOdq', '712243567', 1, 'Landowner'),
(40, 'njk', 'njkhjhj', 'd@gmail.com', 'Supervisor', '$2y$10$AQ4pTsCNscX6Nn0CTljul.LLrQLXVL/4LnEB25VG3v4SLYkOld9zy', '+94776654323', 1, NULL),
(41, 'mkf', 'sdk', 'nsa@gmail.com', 'Supervisor', '$2y$10$wygGNEHwCF75Hh1TG4tlQetmYkFBpOaxkG142Y7Le0.MPehJblc9W', '1245461256', 1, NULL),
(42, 'tharaka', 'dhananjaya', 'td@gmail.com', 'Landowner', '$2y$10$xdrVQxzdfaCx2vAS4aoane1X/7l4fiq/bv8qkCvccisdpGPxIderO', '+94765545243', 1, 'Buyer'),
(43, 'cmxxc', 'jdksjsd', 'asmkl@gmail.com', 'Buyer', '$2y$10$CrTkQfyQw6wbYJ4YPWPSeO1LtF1XV/pIR8r.hmQrS2GwIlZzM6qL2', '+94634767347', 1, NULL),
(44, 'gh', 'kl', 'kl@gmail.com', 'Landowner', '$2y$10$DLjDsUGK0o1YM7XXLIRwi.CobCcsAwFiMFOWhVRLsW5ELMG9tOeCO', '+94765544342', 1, 'Buyer'),
(45, 'fd', 'gs', 'ds@gmail.com', 'Buyer', '$2y$10$BzFFE8PlT8pPde9SFdqn2u6O1pjb2Q93ehTWspq7dueClzUmBihVC', '+94765545248', 1, NULL),
(46, 'tharaka', 'dhananjaya', 'by8@gmail.com', 'Landowner', '$2y$10$8M75hSBshXDhwBSxDGL8euEHdGoqQKod0lbp62C97zQLpgNnqoIuq', '+94765545247', 1, NULL),
(47, 'tha', 'fa', 'lou@gmail.com', 'Landowner', '$2y$10$FH2ErkuxIcTGJgy7pblZ3Obvigg3FEmVpWDPwu8VrDKHyqOB4QgWq', '+94765545242', 1, NULL),
(48, 'gha', 'sdg', 'sd@gmail.com', 'Buyer', '$2y$10$dpx8vTla.HHbGVLgeXxCT.dYxcJM/muLkwh66svC1pnAyWio41UbC', '+94675543123', 1, NULL),
(49, 'tharaha', 'abhjs', 'bb@gmail.com', 'Landowner', '$2y$10$crYeHSb/ZR4kySN0qwLn7.Qc3zOhhVSTbCVi3gs1hk34VDuZh5.Da', '+94765545246', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('Buyer','Landowner') NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role`, `is_active`, `created_date`) VALUES
(1, 32, 'Buyer', 1, '2025-09-19 14:44:22'),
(2, 33, 'Landowner', 1, '2025-09-19 14:44:22'),
(3, 32, 'Landowner', 0, '2025-09-19 14:45:23'),
(4, 33, 'Buyer', 0, '2025-09-19 14:48:14'),
(5, 42, 'Buyer', 1, '2025-09-19 14:53:50'),
(6, 44, 'Buyer', 1, '2025-09-19 15:19:13');

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
-- Indexes for table `cart_orders`
--
ALTER TABLE `cart_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart_orders_user` (`user_id`),
  ADD KEY `idx_cart_orders_status` (`order_status`),
  ADD KEY `idx_cart_orders_payment` (`payment_status`);

--
-- Indexes for table `cart_order_items`
--
ALTER TABLE `cart_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart_order_items` (`cart_order_id`),
  ADD KEY `idx_cart_order_items_product` (`product_id`);

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
-- Indexes for table `interest_requests`
--
ALTER TABLE `interest_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `unique_request_per_report` (`report_id`),
  ADD KEY `land_id` (`land_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `leasing_proposals`
--
ALTER TABLE `leasing_proposals`
  ADD PRIMARY KEY (`proposal_id`),
  ADD KEY `interest_request_id` (`interest_request_id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `land_id` (`land_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `financial_manager_id` (`financial_manager_id`);

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
  ADD KEY `idx_stripe_payment_intent_id` (`stripe_payment_intent_id`),
  ADD KEY `idx_payment_type` (`payment_type`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_user_payment_type` (`user_id`,`payment_type`);

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
-- Indexes for table `proposal_requests`
--
ALTER TABLE `proposal_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `land_id` (`land_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_report_id` (`report_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement`
  MODIFY `agreement_id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_orders`
--
ALTER TABLE `cart_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `cart_order_items`
--
ALTER TABLE `cart_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `crop_inventory`
--
ALTER TABLE `crop_inventory`
  MODIFY `crop_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `harvest`
--
ALTER TABLE `harvest`
  MODIFY `harvest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `interest_requests`
--
ALTER TABLE `interest_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `land`
--
ALTER TABLE `land`
  MODIFY `land_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `land_report`
--
ALTER TABLE `land_report`
  MODIFY `report_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `leasing_proposals`
--
ALTER TABLE `leasing_proposals`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `payment_intents`
--
ALTER TABLE `payment_intents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

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
-- AUTO_INCREMENT for table `proposal_requests`
--
ALTER TABLE `proposal_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Constraints for table `cart_order_items`
--
ALTER TABLE `cart_order_items`
  ADD CONSTRAINT `cart_order_items_ibfk_1` FOREIGN KEY (`cart_order_id`) REFERENCES `cart_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `harvest`
--
ALTER TABLE `harvest`
  ADD CONSTRAINT `harvest_ibfk_1` FOREIGN KEY (`land_id`) REFERENCES `land` (`land_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `harvest_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `harvest_ibfk_3` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`) ON DELETE SET NULL;

--
-- Constraints for table `interest_requests`
--
ALTER TABLE `interest_requests`
  ADD CONSTRAINT `interest_requests_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `land_report` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interest_requests_ibfk_2` FOREIGN KEY (`land_id`) REFERENCES `land` (`land_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interest_requests_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

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
-- Constraints for table `leasing_proposals`
--
ALTER TABLE `leasing_proposals`
  ADD CONSTRAINT `leasing_proposals_ibfk_1` FOREIGN KEY (`interest_request_id`) REFERENCES `interest_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leasing_proposals_ibfk_2` FOREIGN KEY (`report_id`) REFERENCES `land_report` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leasing_proposals_ibfk_3` FOREIGN KEY (`land_id`) REFERENCES `land` (`land_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leasing_proposals_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leasing_proposals_ibfk_5` FOREIGN KEY (`financial_manager_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

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

--
-- Constraints for table `proposal_requests`
--
ALTER TABLE `proposal_requests`
  ADD CONSTRAINT `proposal_requests_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `land_report` (`report_id`),
  ADD CONSTRAINT `proposal_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `proposal_requests_ibfk_3` FOREIGN KEY (`land_id`) REFERENCES `land` (`land_id`);

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
