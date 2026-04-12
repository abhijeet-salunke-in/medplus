-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 06:43 AM
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
-- Database: `medpro_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `store_id` int(10) UNSIGNED NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `store_id`, `product_name`, `price`, `image_path`, `created_at`) VALUES
(1, 2, 'AVELIA WALNUT FACE SCRUB 50GM', 99.00, 'uploads/3bf0cbc34d02aa22de8d78dd39460378.jpg', '2026-03-25 10:34:51'),
(2, 2, 'SUHAGRA 100MG TABLET 4\'S', 42.00, 'uploads/50d926d374fb5c88334d49a75fb206d1.webp', '2026-03-25 10:37:11'),
(3, 2, 'PARACETAMOL 500MG TABLET 20\'S', 16.81, 'uploads/5ba02511b306b7c14a2ea96ea31d2261.webp', '2026-03-25 10:37:46'),
(4, 2, 'Neurobion Forte Strip Of 30 Tablets', 39.45, 'uploads/b63e45f7b7e0e532010f9ef5492fe1b3.webp', '2026-03-25 10:39:26'),
(5, 2, 'Himalaya Pilex Forte Strip Of 30 Tablets', 127.82, 'uploads/0f8ed4d7d8dd4782ffced54ea4e10627.webp', '2026-03-25 10:40:37'),
(6, 3, 'Everherb (By Pharmeasy) Aloe Vera Juice With Pulp - Rejuvenates Skin & Hair - 1 Litre Bottle', 134.64, 'uploads/415018cfc9b05044b9a088da1496e8d6.webp', '2026-03-25 10:41:50'),
(7, 3, 'Krishna\'S Thyro Balance Juice 1 Litre', 515.00, 'uploads/2acc628ac9f59c027d0a84e26eeb826a.webp', '2026-03-25 10:42:13'),
(8, 3, 'Reunion Strip Of 10 Tablets', 316.45, 'uploads/dd5d7f977cd46e120fffaea6b068cf4b.webp', '2026-03-25 10:43:11'),
(9, 3, 'Liveasy Essentials Bamboo Cotton Buds - 80 Sticks/160 Swabs', 60.52, 'uploads/b6686e61d60d284064faef2dd11795da.webp', '2026-03-25 10:43:59'),
(10, 3, 'Vicks Roll On Inhaler 2 In 1 Relief For Headache And Blocked Nose', 77.29, 'uploads/c6df0b2f2c77a780a29e6a0e6ca506de.webp', '2026-03-25 10:44:28'),
(11, 3, 'Vicks Vaporub Classic Relieves Cough & Cold 10 Ml', 37.35, 'uploads/42b87e97f2861ae5cdf47641355af92c.webp', '2026-03-25 10:45:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `license_no` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `phone`, `license_no`, `address`, `status`, `created_at`) VALUES
(1, 'System Admin', 'admin@gmail.com', '$2y$10$6zIy1hniHAtwe7ndND/Vvum/EgRaLEXlGBmIO8qDi/Z38ulXCK7e2', 'admin', '1234567890', NULL, NULL, 'approved', '2026-03-25 10:22:57'),
(2, 'Om Sai Medical', 'omsaimedical@gmail.com', '$2y$10$U9LpF9iNBvMsdFlrQj0b9..E5Q4qDsdaXxG.bkF8ynfZt.aSHfVSC', 'store', '1234567891', 'DL-MH-VAI-123456/21', 'Tilak Road, Vaijapur, Chatrapati Sambhajinagar, Maharashtra-423701, india', 'approved', '2026-03-25 10:28:28'),
(3, 'Sakhre Medical', 'sakhremedical@gmail.com', '$2y$10$otzU4jBnG0dhPgmDqE61IeGD5bfQyEHWQu6XYkKnNCiWvlI/MGqz6', 'store', '1234567892', 'DL-MH-VAI-123456/22', 'Tilak Road, Vaijapur, Chatrapati Sambhajinagar, Maharashtra-423701, india', 'approved', '2026-03-25 10:31:23'),
(4, 'user1', 'user1@gmail.com', '$2y$10$gARDseFS9KW3GEcXM7KQG.Y7foXlaLuaHbnyZ/nUtAGWrLNj9a49C', 'patient', '1234567893', NULL, NULL, 'approved', '2026-03-25 10:46:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_store` (`store_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role_status` (`role`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_store` FOREIGN KEY (`store_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
