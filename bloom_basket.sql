-- XAMPP-Lite
-- version 8.5.5
-- https://xampplite.sf.net/
--
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2026 at 08:18 PM
-- Server version: 11.4.10-MariaDB-log
-- PHP Version: 8.5.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bloom_basket`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line` varchar(255) NOT NULL,
  `area` varchar(100) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `recipient_name`, `phone`, `address_line`, `area`, `city`, `postal_code`, `is_default`, `created_at`) VALUES
(1, 2, 'Rahim Ahmed', '01710000002', 'House 12, Road 5', 'Mirpur', 'Dhaka', '1216', 1, '2026-11-04 14:23:59'),
(2, 2, 'Rahim Ahmed', '01710000002', 'House 20, Road 2', 'Dhanmondi', 'Dhaka', '1205', 0, '2026-11-04 14:23:59'),
(3, 3, 'Karim Hasan', '01710000003', 'House 15, Road 7', 'Uttara', 'Dhaka', '1230', 1, '2026-11-04 14:23:59'),
(4, 4, 'Nusrat Jahan', '01710000004', 'House 8, Road 3', 'Banani', 'Dhaka', '1213', 1, '2026-11-04 14:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `is_active`, `created_at`) VALUES
(1, 'Roses', 'roses', 'Beautiful fresh roses for every occasion.', 1, '2026-11-04 14:23:59'),
(2, 'Bouquets', 'bouquets', 'Specially arranged flower bouquets.', 1, '2026-11-04 14:23:59'),
(3, 'Wedding Flowers', 'wedding-flowers', 'Flowers for weddings and ceremonies.', 1, '2026-11-04 14:23:59'),
(4, 'Indoor Plants', 'indoor-plants', 'Beautiful plants for home decoration.', 1, '2026-11-04 14:23:59'),
(5, 'Gift Flowers', 'gift-flowers', 'Special flower gifts for loved ones.', 1, '2026-11-04 14:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `area` varchar(100) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `recipient_name`, `phone`, `shipping_address`, `area`, `city`, `postal_code`, `subtotal`, `delivery_fee`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'BB-2026-0001', 'Rahim Ahmed', '01710000002', 'House 12, Road 5', 'Mirpur', 'Dhaka', '1216', 1200.00, 80.00, 1280.00, 'delivered', '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(2, 3, 'BB-2026-0002', 'Karim Hasan', '01710000003', 'House 15, Road 7', 'Uttara', 'Dhaka', '1230', 1500.00, 100.00, 1600.00, 'processing', '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(3, 4, 'BB-2026-0003', 'Nusrat Jahan', '01710000004', 'House 8, Road 3', 'Banani', 'Dhaka', '1213', 2200.00, 80.00, 2280.00, 'pending', '2026-11-04 14:23:59', '2026-11-04 14:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(150) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `line_total`) VALUES
(1, 1, 4, 'Romantic Rose Bouquet', 1200.00, 1, 1200.00),
(2, 2, 5, 'Mixed Flower Bouquet', 1500.00, 1, 1500.00),
(3, 3, 6, 'Premium Flower Bouquet', 2200.00, 1, 2200.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `changed_by`, `old_status`, `new_status`, `note`, `created_at`) VALUES
(1, 1, 1, NULL, 'pending', 'Order placed by customer.', '2026-11-04 14:23:59'),
(2, 1, 1, 'pending', 'confirmed', 'Admin confirmed the order.', '2026-11-04 14:23:59'),
(3, 1, 1, 'confirmed', 'processing', 'Preparing the flowers.', '2026-11-04 14:23:59'),
(4, 1, 1, 'processing', 'shipped', 'Order handed to delivery service.', '2026-11-04 14:23:59'),
(5, 1, 1, 'shipped', 'delivered', 'Order successfully delivered.', '2026-11-04 14:23:59'),
(6, 2, 1, NULL, 'pending', 'Order placed by customer.', '2026-11-04 14:23:59'),
(7, 2, 1, 'pending', 'confirmed', 'Admin confirmed the order.', '2026-11-04 14:23:59'),
(8, 2, 1, 'confirmed', 'processing', 'Order is being prepared.', '2026-11-04 14:23:59'),
(9, 3, 1, NULL, 'pending', 'New order received.', '2026-11-04 14:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method` enum('bkash','nagad','rocket','cod') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `transaction_id`, `amount`, `status`, `paid_at`, `created_at`) VALUES
(1, 1, 'bkash', 'BKASH-DEMO-10001', 1280.00, 'success', '2026-11-04 20:23:59', '2026-11-04 14:23:59'),
(2, 2, 'nagad', 'NAGAD-DEMO-10002', 1600.00, 'success', '2026-11-04 20:23:59', '2026-11-04 14:23:59'),
(3, 3, 'cod', NULL, 2280.00, 'pending', NULL, '2026-11-04 14:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(170) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `slug`, `description`, `price`, `stock`, `image_path`, `is_active`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 1, 'ROS-001', 'Red Rose', 'red-rose', 'Beautiful fresh red rose.', 150.00, 100, 'products/red-rose.jpg', 1, 1, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(2, 1, 'ROS-002', 'White Rose', 'white-rose', 'Elegant white rose.', 160.00, 80, 'products/white-rose.jpg', 1, 0, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(3, 1, 'ROS-003', 'Pink Rose', 'pink-rose', 'Lovely pink rose.', 170.00, 70, 'products/pink-rose.jpg', 1, 1, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(4, 2, 'BOU-001', 'Romantic Rose Bouquet', 'romantic-rose-bouquet', 'A romantic bouquet of fresh roses.', 1200.00, 25, 'products/romantic-bouquet.jpg', 1, 1, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(5, 2, 'BOU-002', 'Mixed Flower Bouquet', 'mixed-flower-bouquet', 'Colorful mixed flower bouquet.', 1500.00, 20, 'products/mixed-bouquet.jpg', 1, 1, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(6, 2, 'BOU-003', 'Premium Flower Bouquet', 'premium-flower-bouquet', 'Premium collection of fresh flowers.', 2200.00, 15, 'products/premium-bouquet.jpg', 1, 0, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(7, 3, 'WED-001', 'Wedding Rose Set', 'wedding-rose-set', 'Beautiful roses for wedding decoration.', 5000.00, 10, 'products/wedding-rose.jpg', 1, 1, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(8, 3, 'WED-002', 'Wedding Flower Garland', 'wedding-flower-garland', 'Traditional flower garland.', 1800.00, 30, 'products/wedding-garland.jpg', 1, 0, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(9, 4, 'PLT-001', 'Peace Lily', 'peace-lily', 'Beautiful indoor peace lily plant.', 850.00, 35, 'products/peace-lily.jpg', 1, 1, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(10, 4, 'PLT-002', 'Snake Plant', 'snake-plant', 'Low maintenance indoor plant.', 650.00, 40, 'products/snake-plant.jpg', 1, 0, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(11, 5, 'GFT-001', 'Birthday Flower Box', 'birthday-flower-box', 'Special flower box for birthdays.', 1800.00, 20, 'products/birthday-box.jpg', 1, 1, '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(12, 5, 'GFT-002', 'Anniversary Flower Box', 'anniversary-flower-box', 'Beautiful anniversary flower gift.', 2500.00, 12, 'products/anniversary-box.jpg', 1, 0, '2026-11-04 14:23:59', '2026-11-04 14:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Bloom Basket Admin', 'admin@bloombasket.com', '$2y$12$.0KXzmTp6QxrnHlu5jAyO.hyEUxlKFZIkrPFwGuP03ujB2mLlpyFe', '01710000001', 'admin', '2026-11-04 14:23:59', '2026-11-04 16:12:13'),
(2, 'Rahim Ahmed', 'rahim@gmail.com', 'CHANGE_ME', '01710000002', 'customer', '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(3, 'Karim Hasan', 'karim@gmail.com', 'CHANGE_ME', '01710000003', 'customer', '2026-11-04 14:23:59', '2026-11-04 14:23:59'),
(4, 'Nusrat Jahan', 'nusrat@gmail.com', 'CHANGE_ME', '01710000004', 'customer', '2026-11-04 14:23:59', '2026-11-04 14:23:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_addresses_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created` (`created_at`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_product` (`product_id`),
  ADD KEY `idx_order_items_order` (`order_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_history_user` (`changed_by`),
  ADD KEY `idx_history_order` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_payments_order` (`order_id`),
  ADD KEY `idx_payments_status` (`status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_products_name` (`name`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_active` (`is_active`);

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
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `fk_history_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
