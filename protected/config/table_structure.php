<?php
$tbl['el_inbound_header']="
CREATE TABLE `el_inbound_header` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `hawb` varchar(255) NOT NULL,
  `descr` varchar(255) NOT NULL,
  `qty` varchar(255) NOT NULL,
  `checker` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'inprogress',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$tbl['el_inbound_details']="
CREATE TABLE `el_inbound_details_s` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `hawb` varchar(255) NOT NULL,
  `partnumber` varchar(255) NOT NULL,
  `sonumber` varchar(255) NOT NULL,
  `descr` varchar(255) NOT NULL,
  `qty` varchar(255) NOT NULL,
  `scan_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$tbl['el_outbound_header']="
CREATE TABLE `el_outbound_header` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `hawb` varchar(255) NOT NULL,
  `descr` varchar(255) NOT NULL,
  `qty` varchar(255) NOT NULL,
  `checker` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'inprogress',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$tbl['el_outbound_details']="
CREATE TABLE `el_outbound_details` (
  `hawb` varchar(255) NOT NULL,
  `descr` varchar(255) NOT NULL,
  `loc` int(14) NOT NULL,
  KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$tbl['el_log']="
CREATE TABLE `el_log` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `hawb` varchar(255) NOT NULL,
  `hawb_descr` varchar(255) NOT NULL,
  `loc_name` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$tbl['el_loc']="
CREATE TABLE `el_loc` (
  `loc_id` int(14) NOT NULL AUTO_INCREMENT,
  `loc_name` varchar(255) NOT NULL,
  `loc_descr` varchar(255) NOT NULL,
  PRIMARY KEY (`loc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$tbl['el_apk']="
CREATE TABLE `el_apk` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=INNODB DEFAULT CHARSET=utf8
";

$tbl['el_moving']="
CREATE TABLE `el_moving` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `hawb` VARCHAR(255) NOT NULL,
  `hawb_descr` VARCHAR(255) NOT NULL,
  `loc_before` VARCHAR(255) NOT NULL,
  `loc_after` VARCHAR(255) NOT NULL,
  `date_created` DATETIME NOT NULL,
  `users` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8
";

$tbl['vw_inbound']="
CREATE OR REPLACE VIEW `vw_inbound` AS (
  select
    `a`.`hawb`         AS `hawb`,
    `a`.`descr`        AS `descr`,
    `b`.`descr`        AS `koli`,
    `a`.`checker`      AS `checker`,
    `b`.`loc`          AS `loc`,
    `a`.`date_created` AS `date_created`
  from (`el_inbound_header` `a`
     join `el_inbound_details` `b`)
  where (`a`.`hawb` = `b`.`hawb`))
  ";

$tbl['el_loc_s']="
  CREATE TABLE `el_loc_s` (
    `loc_id` int(14) NOT NULL AUTO_INCREMENT,
    `loc_name` varchar(255) NOT NULL,
    `loc_descr` varchar(255) NOT NULL,
    PRIMARY KEY (`loc_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8
  ";

$tbl['el_moving_s']="
  CREATE TABLE `el_moving_s` (
    `id` INT(14) NOT NULL AUTO_INCREMENT,
    `hawb` VARCHAR(255) NOT NULL,
    `partnumber` VARCHAR(255) NOT NULL,
    `lotnumber` VARCHAR(255) NOT NULL,
    `loc_before` VARCHAR(255) NOT NULL,
    `loc_after` VARCHAR(255) NOT NULL,
    `date_created` DATETIME NOT NULL,
    `users` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=INNODB DEFAULT CHARSET=utf8
  ";

$tbl['el_inbound_header_s']="
CREATE TABLE `el_inbound_header_s` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `hawb` VARCHAR(255) NOT NULL,
  `descr` VARCHAR(255) NOT NULL,
  `po` VARCHAR(35) NOT NULL,
  `locator` VARCHAR(255) NOT NULL,
  `docfile` VARCHAR(255) NOT NULL,
  `checker` VARCHAR(255) NOT NULL,
  `date_created` DATETIME NOT NULL,
  `status` VARCHAR(100) NOT NULL DEFAULT 'inprogress',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hawb` (`hawb`)
) ENGINE=INNODB DEFAULT CHARSET=utf8
";

$tbl['el_inbound_details_s']="
CREATE TABLE `el_inbound_details_s` (
  `hawb` varchar(255) NOT NULL,
  `partnumber` varchar(255) NOT NULL,
  `qty` varchar(255) NOT NULL,
  `scan_time` datetime NOT NULL,
  KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";


$tbl['el_inbound_lots']="
CREATE TABLE `el_inbound_lots` (
  `hawb` varchar(255) NOT NULL,
  `partnumber` varchar(255) NOT NULL,
  `lotnumber` varchar(255) NOT NULL,
  `loc` varchar(255) NOT NULL,
  `scan_time` datetime NOT NULL,
  KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$tbl['el_loc_s']="
CREATE TABLE `el_loc_s` (
  `loc_id` int(14) NOT NULL AUTO_INCREMENT,
  `loc_name` varchar(255) NOT NULL,
  `loc_descr` varchar(255) NOT NULL,
  PRIMARY KEY (`loc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$tbl['el_outbound_header_s']="
CREATE TABLE `el_outbound_header_s` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `hawb` varchar(255) NOT NULL,
  `descr` varchar(255) NOT NULL,
  `qty` varchar(255) NOT NULL,
  `checker` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'inprogress',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hawb` (`hawb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$tbl['el_outbound_details_s']="
CREATE TABLE `el_outbound_details_s` (
  `hawb` VARCHAR(255) NOT NULL,
  `partnumber` VARCHAR(255) NOT NULL,
  `lotnumber` VARCHAR(255) NOT NULL,
  `loc` VARCHAR(255) NOT NULL,
  `id` INT(15) NOT NULL,
  `flag` INT(1) NOT NULL DEFAULT '0',
  `scan_time` DATETIME NOT NULL,
  KEY `hawb` (`hawb`),
  KEY `partnumber` (`partnumber`),
  KEY `lotnumber` (`lotnumber`),
  KEY `id` (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8
";

$tbl['el_apk_s']="
CREATE TABLE `el_apk_s` (
  `id` INT(14) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=INNODB DEFAULT CHARSET=utf8
";