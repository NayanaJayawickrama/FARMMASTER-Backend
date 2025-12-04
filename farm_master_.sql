-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 05:38 PM
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
(21, 32, 'ORD20251201868358', 739.00, 'pending', 'completed', 'Mawanalla', 'Order placed through checkout', '2025-12-01 12:25:25', '2025-12-01 12:25:31'),
(22, 33, 'ORD20251201147389', 2700.00, 'pending', 'completed', 'Galle', 'Order placed through checkout', '2025-12-01 12:29:07', '2025-12-01 12:29:13'),
(23, 33, 'ORD20251201753970', 2620.00, 'pending', 'completed', 'Ja-Ela', 'Order placed through checkout', '2025-12-01 12:30:12', '2025-12-01 12:30:17'),
(24, 33, 'ORD20251201660299', 3085.00, 'pending', 'completed', 'Kandy', 'Order placed through checkout', '2025-12-01 12:31:12', '2025-12-01 12:31:17');

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
(27, 21, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 1, 189.00, 189.00, '2025-12-01 12:25:25'),
(28, 21, 3, 'Tomato', 'uploads/img_68ca33e95d8fa0.23034483.png', 2, 150.00, 300.00, '2025-12-01 12:25:25'),
(29, 22, 1, 'Carrot', 'uploads/img_68ca3549b0ff38.45299625.png', 7, 350.00, 2450.00, '2025-12-01 12:29:07'),
(30, 23, 3, 'Tomato', 'uploads/img_68ca33e95d8fa0.23034483.png', 5, 150.00, 750.00, '2025-12-01 12:30:12'),
(31, 23, 2, 'Leeks', 'uploads/img_68ca33a59c5135.35900479.png', 6, 270.00, 1620.00, '2025-12-01 12:30:12'),
(32, 24, 7, 'Beet Root', 'uploads/img_68ca35192aeb38.67159611.jpg', 15, 189.00, 2835.00, '2025-12-01 12:31:12');

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
(1, 'Leeks', 194.000, 'Available'),
(3, 'Carrot', 293.000, 'Available'),
(5, 'Tomato', 173.000, 'Available'),
(6, 'Cabbage', 200.000, 'Unavailable'),
(9, 'Potato', 0.000, 'Sold'),
(10, 'Beet Root', 384.000, 'Available'),
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
(1, 19, 32, NULL, '2025-07-15', 'Tomato', 500.00, 100000.00, 20000.00, 10000.00, 70000.00, 28000.00, 42000.00, 'Good quality harvest', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(2, 19, 32, NULL, '2025-06-20', 'Carrot', 300.00, 60000.00, 12000.00, 6000.00, 42000.00, 16800.00, 25200.00, 'Average harvest', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(3, 21, 32, NULL, '2025-05-25', 'Rice', 400.00, 50000.00, 10000.00, 5000.00, 35000.00, 14000.00, 21000.00, 'Good yield', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(4, 42, 32, NULL, '2025-04-30', 'Mixed Vegetables', 200.00, 30000.00, 6000.00, 3000.00, 21000.00, 8400.00, 12600.00, 'Small harvest', '2025-08-28 06:54:03', '2025-09-12 18:35:42'),
(5, 19, 32, NULL, '2025-03-05', 'Tomato', 100.00, 15000.00, 3000.00, 2000.00, 10000.00, 4000.00, 6000.00, 'Early harvest', '2025-08-28 06:54:03', '2025-09-12 18:35:42');

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
(67, 32, 'Passara, Badulla District, Uva Province, Sri Lanka', 12.000, 'paid', '2025-12-01 12:24:24', '2025-12-01 12:24:18', '2025-12-01 16:54:24'),
(68, 32, 'Hali-Ela, Badulla District, Uva Province, 90000, Sri Lanka', 11.000, 'paid', '2025-12-01 12:56:26', '2025-12-01 12:56:20', '2025-12-01 17:26:26');

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
(7, 67, 32, '2025-12-01', 'Land assessment assigned to field supervisor for detailed analysis', '🌱 LAND SUITABILITY ANALYSIS & CROP RECOMMENDATIONS\nGenerated on: 2025-12-01 18:04:37\n\n📊 SOIL ANALYSIS SUMMARY:\n• pH Value: 7.0\n• Organic Matter: 2.50%\n• Nitrogen Level: Medium\n• Phosphorus Level: Medium\n• Potassium Level: Medium\n\n🌾 TOP RECOMMENDED CROPS:\n', 7.0, 2.50, 'medium', 'medium', 'medium', 'Assigned to: Kanchana Almeda (ID: 31)\n\nField Assessment Notes: Assigned to: Kanchana Almeda (ID: 31)\n\nField Assessment Notes: Assigned to: Kanchana Almeda (ID: 31)', '2025-12-01 17:02:56', '2025-12-01 17:10:23', '', NULL, 'pending', 'Completed'),
(8, 68, 32, '2025-12-01', 'Land assessment assigned to field supervisor for detailed analysis', 'To be determined after field assessment by assigned supervisor', NULL, NULL, NULL, NULL, NULL, 'Assigned to: Kanchana Almeda (ID: 31)', '2025-12-01 17:28:47', '2025-12-01 17:28:47', '', NULL, 'pending', 'In Progress');

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
(54, 32, 'land_report', 67, NULL, NULL, NULL, NULL, 'stripe_pi_3SZaMiC523WS3olJ2fWKqybk', 4999.50, 'stripe', 'completed', NULL, '2025-12-01 12:24:24', '2025-12-01 16:54:24', 'pi_3SZaMiC523WS3olJ2fWKqybk'),
(55, 32, 'cart_purchase', NULL, 'ORD20251201868358', NULL, NULL, NULL, 'stripe_pi_3SZaNoC523WS3olJ0bu8qEky', 739.20, 'stripe', 'completed', NULL, '2025-12-01 12:25:31', '2025-12-01 16:55:31', 'pi_3SZaNoC523WS3olJ0bu8qEky'),
(56, 33, 'cart_purchase', NULL, 'ORD20251201147389', NULL, NULL, NULL, 'stripe_pi_3SZaRNC523WS3olJ203jPX1D', 2699.40, 'stripe', 'completed', NULL, '2025-12-01 12:29:13', '2025-12-01 16:59:13', 'pi_3SZaRNC523WS3olJ203jPX1D'),
(57, 33, 'cart_purchase', NULL, 'ORD20251201753970', NULL, NULL, NULL, 'stripe_pi_3SZaSPC523WS3olJ1RLo9kqR', 2620.20, 'stripe', 'completed', NULL, '2025-12-01 12:30:17', '2025-12-01 17:00:17', 'pi_3SZaSPC523WS3olJ1RLo9kqR'),
(58, 33, 'cart_purchase', NULL, 'ORD20251201660299', NULL, NULL, NULL, 'stripe_pi_3SZaTOC523WS3olJ1XEIurAw', 3085.50, 'stripe', 'completed', NULL, '2025-12-01 12:31:17', '2025-12-01 17:01:17', 'pi_3SZaTOC523WS3olJ1XEIurAw'),
(59, 32, 'land_report', 68, NULL, NULL, NULL, NULL, 'stripe_pi_3SZariC523WS3olJ2q2xTL2d', 4999.50, 'stripe', 'completed', NULL, '2025-12-01 12:56:26', '2025-12-01 17:26:26', 'pi_3SZariC523WS3olJ2q2xTL2d');

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
(60, 32, 'land_report', 67, NULL, 'pi_3SZaMiC523WS3olJ2fWKqybk', 5000.00, 'succeeded', '2025-12-01 12:24:20', '2025-12-01 16:54:24'),
(61, 32, 'cart_purchase', NULL, 21, 'pi_3SZaNoC523WS3olJ0bu8qEky', 739.00, 'succeeded', '2025-12-01 12:25:28', '2025-12-01 16:55:31'),
(62, 33, 'cart_purchase', NULL, 22, 'pi_3SZaRNC523WS3olJ203jPX1D', 2700.00, 'succeeded', '2025-12-01 12:29:09', '2025-12-01 16:59:13'),
(63, 33, 'cart_purchase', NULL, 23, 'pi_3SZaSPC523WS3olJ1RLo9kqR', 2620.00, 'succeeded', '2025-12-01 12:30:14', '2025-12-01 17:00:17'),
(64, 33, 'cart_purchase', NULL, 24, 'pi_3SZaTOC523WS3olJ1XEIurAw', 3085.00, 'succeeded', '2025-12-01 12:31:14', '2025-12-01 17:01:17'),
(65, 32, 'land_report', 68, NULL, 'pi_3SZariC523WS3olJ2q2xTL2d', 5000.00, 'succeeded', '2025-12-01 12:56:22', '2025-12-01 17:26:26');

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
(29, 'Thejani', 'Janiya', 'om@gmail.com', 'Operational_Manager', '$2y$10$5n2BcSMcsv024ihBgMUbhe5NtzMthHaH19UMca31qykeatW9TlCEO', '9876543210', 1, NULL),
(30, 'Gimhani', 'Perera', 'fm@gmail.com', 'Financial_Manager', '$2y$10$5n2BcSMcsv024ihBgMUbhe5NtzMthHaH19UMca31qykeatW9TlCEO', '2147483647', 1, NULL),
(31, 'Kanchana', 'Almeda', 'fs@gmail.com', 'Supervisor', '$2y$10$5n2BcSMcsv024ihBgMUbhe5NtzMthHaH19UMca31qykeatW9TlCEO', '+94274836477', 1, NULL),
(32, 'Nuwani', 'Silva', 'lo@gmail.com', 'Landowner', '$2y$10$5n2BcSMcsv024ihBgMUbhe5NtzMthHaH19UMca31qykeatW9TlCEO', '774352566', 1, NULL),
(33, 'Dasuni', 'Peris', 'by@gmail.com', 'Buyer', '$2y$10$5n2BcSMcsv024ihBgMUbhe5NtzMthHaH19UMca31qykeatW9TlCEO', '712243567', 1, NULL),
(40, 'Supun', 'Hemantha', 'd@gmail.com', 'Supervisor', '$2y$10$AQ4pTsCNscX6Nn0CTljul.LLrQLXVL/4LnEB25VG3v4SLYkOld9zy', '+94776654323', 1, NULL),
(42, 'tharaka', 'dhananjaya', 'td@gmail.com', 'Landowner', '$2y$10$xdrVQxzdfaCx2vAS4aoane1X/7l4fiq/bv8qkCvccisdpGPxIderO', '+94765545243', 1, 'Buyer'),
(48, 'Ruwan', 'Venuka', 'sd@gmail.com', 'Buyer', '$2y$10$dpx8vTla.HHbGVLgeXxCT.dYxcJM/muLkwh66svC1pnAyWio41UbC', '+94675543123', 1, NULL);

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
(5, 42, 'Buyer', 1, '2025-09-19 14:53:50');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `cart_order_items`
--
ALTER TABLE `cart_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

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
  MODIFY `land_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `land_report`
--
ALTER TABLE `land_report`
  MODIFY `report_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `payment_intents`
--
ALTER TABLE `payment_intents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

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
