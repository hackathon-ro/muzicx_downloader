-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 27, 2012 at 05:19 PM
-- Server version: 5.5.27
-- PHP Version: 5.4.6-1ubuntu1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `muzicx_downloader`
--

-- --------------------------------------------------------

--
-- Table structure for table `videos_vds`
--

CREATE TABLE IF NOT EXISTS `videos_vds` (
  `id_vds` int(11) NOT NULL AUTO_INCREMENT,
  `idyt_vds` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `videotype_vds` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `added_vds` datetime DEFAULT NULL,
  `worker_vds` int(11) DEFAULT NULL,
  `status_vds` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - not assigned; 1 - downloading; 2 - error; 3  - success',
  PRIMARY KEY (`id_vds`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
