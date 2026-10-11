-- ESP32 ration node tables. Import after project.sql (MySQL/phpMyAdmin).

ALTER TABLE `tbl_stock` ADD `market_price` int(11) NOT NULL DEFAULT 0;
UPDATE `tbl_stock` SET `market_price` = 30 WHERE `stock_name` = 'Wheat';
UPDATE `tbl_stock` SET `market_price` = 45 WHERE `stock_name` = 'Rice';
UPDATE `tbl_stock` SET `market_price` = 45 WHERE `stock_name` = 'Sugar';

CREATE TABLE `tbl_node` (
  `pds_no` int(11) NOT NULL PRIMARY KEY,
  `node_key` varchar(64) NOT NULL
);

-- Fingerprint sensor slot -> ration card (slots are local to each shop's sensor).
CREATE TABLE `tbl_fingerprint` (
  `pds_no` int(11) NOT NULL,
  `finger_id` int(11) NOT NULL,
  `rationcard_no` varchar(20) NOT NULL,
  PRIMARY KEY (`pds_no`, `finger_id`)
);

-- One row per node interaction. status: identified, approved, dispensing, done, cancelled, enroll, enrolled, failed
CREATE TABLE `tbl_node_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `pds_no` int(11) NOT NULL,
  `rationcard_no` varchar(20) NOT NULL,
  `status` varchar(12) NOT NULL,
  `items` text,
  `created` datetime NOT NULL
);

CREATE TABLE `tbl_dispense` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `session_id` int(11) NOT NULL,
  `rationcard_no` varchar(20) NOT NULL,
  `pds_no` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `stock_name` varchar(50) NOT NULL,
  `kg` double NOT NULL,
  `quota_kg` double NOT NULL,
  `extra_kg` double NOT NULL,
  `amount` double NOT NULL,
  `date` date NOT NULL
);
