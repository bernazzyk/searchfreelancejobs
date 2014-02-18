-- phpMyAdmin SQL Dump
-- version 3.4.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 25, 2012 at 11:39 AM
-- Server version: 5.1.37
-- PHP Version: 5.2.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `aesthetics_odsk`
--

-- --------------------------------------------------------

--
-- Table structure for table `od_contracts`
--

CREATE TABLE IF NOT EXISTS `od_contracts` (
  `engagement_id` varchar(50) CHARACTER SET utf8 NOT NULL,
  `contractor_id` varchar(225) CHARACTER SET utf8 NOT NULL,
  `salt` varchar(32) CHARACTER SET utf8 NOT NULL,
  `engagement` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `p_id` bigint(20) DEFAULT NULL,
  `paid_amount` float DEFAULT NULL,
  `synchronized_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `requested_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`engagement_id`,`contractor_id`,`salt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
