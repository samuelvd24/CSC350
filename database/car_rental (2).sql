-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2025 at 04:57 PM
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
-- Database: `car_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `make` varchar(80) NOT NULL,
  `model` varchar(80) NOT NULL,
  `year` smallint(6) NOT NULL,
  `seats` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `luggage_capacity` tinyint(3) UNSIGNED NOT NULL DEFAULT 4,
  `transmission` enum('automatic','manual') NOT NULL DEFAULT 'automatic',
  `daily_rate` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT '/image/corolla.avif',
  `available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `make`, `model`, `year`, `seats`, `luggage_capacity`, `transmission`, `daily_rate`, `image_url`, `available`, `created_at`) VALUES
(1, 'Toyota', 'Corolla', 2023, 5, 3, 'automatic', 49.99, 'image/corolla.avif', 1, '2025-11-13 15:05:15'),
(2, 'Honda', 'Civic', 2022, 5, 3, 'automatic', 48.99, 'image/civic.png', 1, '2025-11-13 15:05:15'),
(3, 'Tesla', 'Model 3', 2023, 5, 3, 'automatic', 109.99, 'image/tesla.png', 1, '2025-11-13 15:05:15'),
(4, 'Ford', 'Escape', 2021, 5, 4, 'automatic', 64.99, 'image/ford.png', 1, '2025-11-13 15:05:15'),
(5, 'Toyota', 'Supra', 2022, 2, 2, 'manual', 99.99, 'image/supra.png', 1, '2025-11-13 20:05:15'),
(6, 'Hyundai', 'Elantra', 2023, 5, 3, 'automatic', 46.99, 'image/hyundai.png', 1, '2025-11-16 05:52:12'),
(7, 'BMW', '3 Series', 2022, 5, 3, 'automatic', 94.99, 'image/bmw.png', 1, '2025-11-16 05:52:12'),
(8, 'Kia', 'Sorento', 2021, 7, 4, 'automatic', 79.99, 'image/kia.png', 1, '2025-11-16 05:52:12'),
(9, 'Subaru', 'Outback', 2022, 5, 5, 'automatic', 69.99, 'image/subaru.png', 1, '2025-11-16 05:52:12'),
(10, 'Mazda', 'MX-5 Miata', 2023, 2, 1, 'manual', 84.99, 'image/mazda.png', 1, '2025-11-16 05:52:12'),
(11, 'Nissan', 'Altima', 2023, 5, 4, 'automatic', 54.99, 'image/nissan.png', 1, '2025-11-16 05:53:20'),
(12, 'Chevrolet', 'Tahoe', 2022, 7, 5, 'automatic', 104.99, 'image/tahoe.png', 1, '2025-11-16 05:53:20');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','confirmed','returned','canceled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`id`, `user_id`, `car_id`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(3, 2, 5, '2025-11-23', '2025-11-24', 'pending', '2025-11-17 06:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(2, 'Samuel', 'Dewangga', 'samuelvalentinodewangga@gmail.com', '$2y$10$DvYzWwGh8E1zoRTmHgv6.O81Vhxd/UFRxNliDhtanUQFbPJbWMEtG', 'user', '2025-11-17 05:22:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rentals_car_dates` (`car_id`,`start_date`,`end_date`),
  ADD KEY `fk_rentals_user` (`user_id`);

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
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `fk_rentals_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rentals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
