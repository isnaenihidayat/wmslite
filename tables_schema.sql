-- SQL schema generated from adminer.sql data-only dump analysis
-- MySQL / MariaDB compatible

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- --------------------------------------------------------

--
-- Table structure for table `el_apk`
--

DROP TABLE IF EXISTS `el_apk`;
CREATE TABLE `el_apk` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `token` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_apk_s`
--

DROP TABLE IF EXISTS `el_apk_s`;
CREATE TABLE `el_apk_s` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `token` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_loc`
--

DROP TABLE IF EXISTS `el_loc`;
CREATE TABLE `el_loc` (
  `loc_id` INT(14) NOT NULL AUTO_INCREMENT,
  `loc_name` VARCHAR(255) NOT NULL,
  `loc_descr` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`loc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_loc_s`
--

DROP TABLE IF EXISTS `el_loc_s`;
CREATE TABLE `el_loc_s` (
  `loc_id` INT(14) NOT NULL AUTO_INCREMENT,
  `loc_name` VARCHAR(255) NOT NULL,
  `loc_descr` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`loc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_user`
--

DROP TABLE IF EXISTS `el_user`;
CREATE TABLE `el_user` (
  `user_id` INT(14) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `mobile_number` VARCHAR(50) DEFAULT NULL,
  `email_address` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'active',
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `ip_address` VARCHAR(255) DEFAULT NULL,
  `token` VARCHAR(255) DEFAULT NULL,
  `admin` INT(1) NOT NULL DEFAULT 0,
  `module` INT(11) NOT NULL DEFAULT 1,
  `type` INT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`),
  KEY `email_address` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_recipient`
--

DROP TABLE IF EXISTS `el_recipient`;
CREATE TABLE `el_recipient` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `created_by` INT(14) NOT NULL,
  `updated_by` INT(14) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_product_category`
--

DROP TABLE IF EXISTS `el_product_category`;
CREATE TABLE `el_product_category` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `created_by` INT(14) NOT NULL,
  `updated_by` INT(14) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_option`
--

DROP TABLE IF EXISTS `el_option`;
CREATE TABLE `el_option` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `option_name` VARCHAR(255) NOT NULL,
  `option_value` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_inbound_header`
--

DROP TABLE IF EXISTS `el_inbound_header`;
CREATE TABLE `el_inbound_header` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `hawb` VARCHAR(255) NOT NULL,
  `descr` VARCHAR(255) NOT NULL,
  `product_category_id` INT(14) DEFAULT NULL,
  `modality` VARCHAR(255) DEFAULT NULL,
  `delivery_id` INT(14) DEFAULT NULL,
  `qty` VARCHAR(255) DEFAULT NULL,
  `po` VARCHAR(255) DEFAULT NULL,
  `locator` VARCHAR(255) NOT NULL DEFAULT '',
  `docfile` VARCHAR(255) NOT NULL DEFAULT '',
  `checker` VARCHAR(255) NOT NULL,
  `date_created` DATETIME NOT NULL,
  `status` VARCHAR(100) NOT NULL DEFAULT 'inprogress',
  `ship_method` VARCHAR(255) DEFAULT NULL,
  `etd` DATETIME DEFAULT NULL,
  `eta` DATETIME DEFAULT NULL,
  `ata` DATETIME DEFAULT NULL,
  `pib_number` VARCHAR(255) DEFAULT NULL,
  `sppb_date` DATETIME DEFAULT NULL,
  `date_updated` DATETIME DEFAULT NULL,
  `created_by` INT(14) DEFAULT NULL,
  `updated_by` INT(14) DEFAULT NULL,
  `from_shipment` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_inbound_header_s`
--

DROP TABLE IF EXISTS `el_inbound_header_s`;
CREATE TABLE `el_inbound_header_s` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `hawb` VARCHAR(255) NOT NULL,
  `po` VARCHAR(255) NOT NULL DEFAULT '',
  `locator` VARCHAR(255) NOT NULL DEFAULT '',
  `docfile` VARCHAR(255) NOT NULL DEFAULT '',
  `checker` VARCHAR(255) NOT NULL,
  `date_created` DATETIME NOT NULL,
  `status` VARCHAR(100) NOT NULL DEFAULT 'inprogress',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_inbound_details`
--

DROP TABLE IF EXISTS `el_inbound_details`;
CREATE TABLE `el_inbound_details` (
  `hawb` VARCHAR(255) NOT NULL,
  `descr` VARCHAR(255) NOT NULL,
  `loc` VARCHAR(255) NOT NULL,
  `weight` DECIMAL(10,2) DEFAULT NULL,
  `long` INT(11) DEFAULT NULL,
  `wide` INT(11) DEFAULT NULL,
  `high` INT(11) DEFAULT NULL,
  `flag` INT(1) NOT NULL DEFAULT 0,
  `scan_time` DATETIME DEFAULT NULL,
  `demo_movement_id` INT(14) DEFAULT NULL,
  `created_by` INT(14) DEFAULT NULL,
  `updated_by` INT(14) DEFAULT NULL,
  `date_created` DATETIME DEFAULT NULL,
  `date_updated` DATETIME DEFAULT NULL,
  KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_inbound_details_s`
--

DROP TABLE IF EXISTS `el_inbound_details_s`;
CREATE TABLE `el_inbound_details_s` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `hawb` VARCHAR(255) NOT NULL,
  `partnumber` VARCHAR(255) NOT NULL,
  `sonumber` VARCHAR(255) NOT NULL DEFAULT '',
  `descr` VARCHAR(255) NOT NULL,
  `qty` VARCHAR(255) DEFAULT NULL,
  `scan_time` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_inbound_details_backup`
--

DROP TABLE IF EXISTS `el_inbound_details_backup`;
CREATE TABLE `el_inbound_details_backup` (
  `hawb` VARCHAR(255) NOT NULL,
  `descr` VARCHAR(255) NOT NULL,
  `loc` VARCHAR(255) NOT NULL DEFAULT '',
  `weight` DECIMAL(10,2) DEFAULT NULL,
  `long` INT(11) DEFAULT NULL,
  `wide` INT(11) DEFAULT NULL,
  `high` INT(11) DEFAULT NULL,
  `flag` INT(1) NOT NULL DEFAULT 0,
  `scan_time` DATETIME DEFAULT NULL,
  KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_inbound_lots`
--

DROP TABLE IF EXISTS `el_inbound_lots`;
CREATE TABLE `el_inbound_lots` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `hawb` VARCHAR(255) NOT NULL,
  `partnumber` VARCHAR(255) NOT NULL,
  `lotnumber` VARCHAR(255) NOT NULL,
  `loc` VARCHAR(255) NOT NULL,
  `flag` INT(1) NOT NULL DEFAULT 0,
  `scan_time` DATETIME DEFAULT NULL,
  `id_detail` INT(14) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_outbound_header`
--

DROP TABLE IF EXISTS `el_outbound_header`;
CREATE TABLE `el_outbound_header` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `qty` VARCHAR(255) DEFAULT NULL,
  `po` VARCHAR(255) DEFAULT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `checker` VARCHAR(255) NOT NULL,
  `docfile` VARCHAR(255) DEFAULT NULL,
  `date_created` DATETIME NOT NULL,
  `status` VARCHAR(100) NOT NULL DEFAULT 'inprogress',
  `created_by` INT(14) DEFAULT NULL,
  `updated_by` INT(14) DEFAULT NULL,
  `date_updated` DATETIME DEFAULT NULL,
  `delivery_id` INT(14) DEFAULT NULL,
  `transporter` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_outbound_header_s`
--

DROP TABLE IF EXISTS `el_outbound_header_s`;
CREATE TABLE `el_outbound_header_s` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `noship` VARCHAR(255) DEFAULT NULL,
  `qty` VARCHAR(255) DEFAULT NULL,
  `po` VARCHAR(255) DEFAULT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `checker` VARCHAR(255) NOT NULL,
  `date_created` DATETIME NOT NULL,
  `delivery` VARCHAR(255) DEFAULT NULL,
  `cp` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(100) NOT NULL DEFAULT 'inprogress',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_outbound_details`
--

DROP TABLE IF EXISTS `el_outbound_details`;
CREATE TABLE `el_outbound_details` (
  `hawb` VARCHAR(255) NOT NULL,
  `descr` VARCHAR(255) NOT NULL,
  `loc` VARCHAR(255) NOT NULL,
  `id` INT(14) NOT NULL,
  `flag` INT(1) NOT NULL DEFAULT 0,
  `scan_time` DATETIME DEFAULT NULL,
  `created_by` INT(14) DEFAULT NULL,
  `updated_by` INT(14) DEFAULT NULL,
  `date_created` DATETIME DEFAULT NULL,
  `date_updated` DATETIME DEFAULT NULL,
  KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_outbound_details_s`
--

DROP TABLE IF EXISTS `el_outbound_details_s`;
CREATE TABLE `el_outbound_details_s` (
  `hawb` VARCHAR(255) NOT NULL,
  `partnumber` VARCHAR(255) NOT NULL,
  `lotnumber` VARCHAR(255) NOT NULL,
  `po` VARCHAR(255) DEFAULT NULL,
  `loc` VARCHAR(255) NOT NULL,
  `id` INT(15) NOT NULL,
  `flag` INT(1) NOT NULL DEFAULT 0,
  `scan_time` DATETIME NOT NULL,
  KEY `hawb` (`hawb`),
  KEY `partnumber` (`partnumber`),
  KEY `lotnumber` (`lotnumber`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_moving`
--

DROP TABLE IF EXISTS `el_moving`;
CREATE TABLE `el_moving` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `hawb` VARCHAR(255) NOT NULL,
  `hawb_descr` VARCHAR(255) NOT NULL,
  `loc_before` VARCHAR(255) NOT NULL,
  `loc_after` VARCHAR(255) NOT NULL,
  `date_created` DATETIME NOT NULL,
  `users` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_moving_s`
--

DROP TABLE IF EXISTS `el_moving_s`;
CREATE TABLE `el_moving_s` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `hawb` VARCHAR(255) NOT NULL,
  `partnumber` VARCHAR(255) NOT NULL,
  `lotnumber` VARCHAR(255) NOT NULL,
  `loc_before` VARCHAR(255) NOT NULL,
  `loc_after` VARCHAR(255) NOT NULL,
  `date_created` DATETIME NOT NULL,
  `users` VARCHAR(255) NOT NULL,
  `id_detail` INT(14) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_demo_movement`
--

DROP TABLE IF EXISTS `el_demo_movement`;
CREATE TABLE `el_demo_movement` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `ref` VARCHAR(50) NOT NULL,
  `requested_by` VARCHAR(255) NOT NULL,
  `from_loc` VARCHAR(255) NOT NULL,
  `to_loc` VARCHAR(255) NOT NULL,
  `status` VARCHAR(100) NOT NULL DEFAULT 'Requested',
  `checker` VARCHAR(255) DEFAULT NULL,
  `is_return` INT(1) NOT NULL DEFAULT 0,
  `returned_from` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `created_by` INT(14) NOT NULL,
  `updated_by` INT(14) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_demo_movement_detail`
--

DROP TABLE IF EXISTS `el_demo_movement_detail`;
CREATE TABLE `el_demo_movement_detail` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `demo_movement_id` INT(14) NOT NULL,
  `hawb` VARCHAR(255) NOT NULL,
  `loc` VARCHAR(255) NOT NULL,
  `flag` INT(1) NOT NULL DEFAULT 0,
  `scan_time` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `created_by` INT(14) NOT NULL,
  `updated_by` INT(14) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `demo_movement_id` (`demo_movement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `el_log`
--

DROP TABLE IF EXISTS `el_log`;
CREATE TABLE `el_log` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `hawb` VARCHAR(255) NOT NULL,
  `hawb_descr` VARCHAR(255) NOT NULL,
  `loc_name` VARCHAR(255) NOT NULL,
  `date_created` DATETIME NOT NULL,
  `status` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- View structure for view `vw_inbound`
--

DROP TABLE IF EXISTS `vw_inbound`;
DROP VIEW IF EXISTS `vw_inbound`;
CREATE OR REPLACE VIEW `vw_inbound` AS
SELECT
  `a`.`hawb` AS `hawb`,
  `a`.`descr` AS `descr`,
  `b`.`descr` AS `koli`,
  `a`.`checker` AS `checker`,
  `b`.`loc` AS `loc`,
  `a`.`date_created` AS `date_created`
FROM (`el_inbound_header` `a` JOIN `el_inbound_details` `b` ON (`a`.`hawb` = `b`.`hawb`));

SET foreign_key_checks = 1;
