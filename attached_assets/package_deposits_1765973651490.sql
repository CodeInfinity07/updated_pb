-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 17, 2025 at 12:13 PM
-- Server version: 5.7.41
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blackrock_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `package_deposits`
--

CREATE TABLE `package_deposits` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method_id` int(4) NOT NULL,
  `txn_id` varchar(200) NOT NULL,
  `amount` varchar(20) NOT NULL,
  `fee` varchar(20) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `address` varchar(80) NOT NULL,
  `confs` tinyint(4) NOT NULL,
  `last_earningDateTime` timestamp NULL DEFAULT NULL,
  `avail` int(11) NOT NULL DEFAULT '0',
  `auto_reinvest` int(11) NOT NULL,
  `compound` int(11) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(100) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `package_deposits`
--
ALTER TABLE `package_deposits`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `package_deposits`
--
ALTER TABLE `package_deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
