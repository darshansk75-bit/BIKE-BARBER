-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 18, 2026 at 05:15 PM
-- Server version: 8.4.7
-- PHP Version: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dboss`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1926 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `email`, `password`, `created_at`) VALUES
(1925, 'Admin@123', 'darshanyt75@gmail.com', '$2y$12$3BkC8rak9x/VZj563mlJ3.R8qZUmxEGgJEo0ec3ItsUsYf46hlahu', '2026-03-18 05:03:43');

-- --------------------------------------------------------

DROP TABLE IF EXISTS `amc_contracts`;
CREATE TABLE IF NOT EXISTS `amc_contracts` (
  `amc_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `service_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  PRIMARY KEY (`amc_id`),
  KEY `customer_id` (`customer_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `email`, `phone`, `password`, `address`, `profile_picture`, `created_at`) VALUES
(1, 'Krishna', 'darshansk323@gmail.com', '9876543210', 'LSkart@123', NULL, NULL, '2026-03-18 05:05:25');

-- --------------------------------------------------------

--
-- Table structure for table `gst_settings`
--

DROP TABLE IF EXISTS `gst_settings`;
CREATE TABLE IF NOT EXISTS `gst_settings` (
  `gst_id` int NOT NULL AUTO_INCREMENT,
  `cgst_percent` decimal(5,2) DEFAULT '9.00',
  `sgst_percent` decimal(5,2) DEFAULT '9.00',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`gst_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gst_settings`
--

INSERT INTO `gst_settings` (`gst_id`, `cgst_percent`, `sgst_percent`, `updated_at`) VALUES
(1, 9.00, 9.00, '2026-03-18 05:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `manufacturers`
--

DROP TABLE IF EXISTS `manufacturers`;
CREATE TABLE IF NOT EXISTS `manufacturers` (
  `manufacturer_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`manufacturer_id`),
  UNIQUE KEY `gst_number` (`gst_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `order_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `idx_orders_status` (`order_status`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `product_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `idx_products_name` (`product_name`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE IF NOT EXISTS `purchases` (
  `purchase_id` int NOT NULL AUTO_INCREMENT,
  `manufacturer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `request_status` enum('REQUESTED','ACCEPTED','DECLINED','COMPLETED') COLLATE utf8mb4_unicode_ci DEFAULT 'REQUESTED',
  `manufacturer_response_date` datetime DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `purchase_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`purchase_id`),
  KEY `idx_purchase_date` (`purchase_date`),
  KEY `idx_purchase_manufacturer` (`manufacturer_id`,`purchase_date`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `service_id` int NOT NULL AUTO_INCREMENT,
  `service_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_bookings`
--

DROP TABLE IF EXISTS `service_bookings`;
CREATE TABLE IF NOT EXISTS `service_bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `service_id` int NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  PRIMARY KEY (`booking_id`),
  KEY `customer_id` (`customer_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------


DROP TABLE IF EXISTS `warranties`;
CREATE TABLE IF NOT EXISTS `warranties` (
  `warranty_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `warranty_start` date NOT NULL,
  `warranty_end` date NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  PRIMARY KEY (`warranty_id`),
  KEY `customer_id` (`customer_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for dumped tables
--

ALTER TABLE `amc_contracts`
  ADD CONSTRAINT `amc_contracts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `amc_contracts_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`manufacturer_id`),
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `service_bookings`
--
ALTER TABLE `service_bookings`
  ADD CONSTRAINT `service_bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `service_bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

ALTER TABLE `warranties`
  ADD CONSTRAINT `warranties_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `warranties_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
