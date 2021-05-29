-- phpMyAdmin SQL Dump
-- version 4.7.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 25, 2018 at 10:29 AM
-- Server version: 5.7.22-22-log
-- PHP Version: 5.6.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shopify`
--

-- --------------------------------------------------------

--
-- Table structure for table `custom_variants`
--

CREATE TABLE `custom_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` varchar(15) NOT NULL,
  `group_id` text,
  `shop` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `price_break_group`
--

CREATE TABLE `price_break_group` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `shop` varchar(500) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `price_groups`
--

CREATE TABLE `price_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `price` float NOT NULL,
  `percent` float NOT NULL,
  `number` int(10) NOT NULL,
  `custom_variant` varchar(15) NOT NULL,
  `variant_id` varchar(15) NOT NULL,
  `product_group` int(11) NOT NULL,
  `shop` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `variant_limit`
--

CREATE TABLE `variant_limit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` varchar(15) NOT NULL,
  `min` int(5) NOT NULL,
  `max` int(5) NOT NULL DEFAULT '0',
  `shop` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `custom_variants`
--
ALTER TABLE `custom_variants`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `shop` (`shop`);

--
-- Indexes for table `price_break_group`
--
ALTER TABLE `price_break_group`
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `title` (`title`),
  ADD KEY `shop` (`shop`);

--
-- Indexes for table `price_groups`
--
ALTER TABLE `price_groups`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `shop` (`shop`);

--
-- Indexes for table `variant_limit`
--
ALTER TABLE `variant_limit`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `shop` (`shop`),
  ADD KEY `variant_id` (`variant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `custom_variants`
--
ALTER TABLE `custom_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12095;
--
-- AUTO_INCREMENT for table `price_break_group`
--
ALTER TABLE `price_break_group`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=734;
--
-- AUTO_INCREMENT for table `price_groups`
--
ALTER TABLE `price_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47638;
--
-- AUTO_INCREMENT for table `variant_limit`
--
ALTER TABLE `variant_limit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100671;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
