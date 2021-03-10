-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 10, 2021 at 11:42 AM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `2ch_yenten_faucet`
--

-- --------------------------------------------------------

--
-- Table structure for table `rolls`
--

CREATE TABLE `rolls` (
  `ID` int(11) NOT NULL,
  `Wallet` text NOT NULL,
  `Amount` int(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `rollsarchive`
--

CREATE TABLE `rollsarchive` (
  `ID` int(11) NOT NULL,
  `Wallet` text NOT NULL,
  `SumAmount` int(16) NOT NULL,
  `TransactionTimestamp` int(11) DEFAULT NULL,
  `TransactionID` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `walletsonline`
--

CREATE TABLE `walletsonline` (
  `ID` int(11) NOT NULL,
  `Wallet` text NOT NULL,
  `LastActive` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rolls`
--
ALTER TABLE `rolls`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `ID` (`ID`);

--
-- Indexes for table `rollsarchive`
--
ALTER TABLE `rollsarchive`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `RollArchiveID` (`ID`),
  ADD KEY `ID` (`ID`);

--
-- Indexes for table `walletsonline`
--
ALTER TABLE `walletsonline`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rolls`
--
ALTER TABLE `rolls`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rollsarchive`
--
ALTER TABLE `rollsarchive`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `walletsonline`
--
ALTER TABLE `walletsonline`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
