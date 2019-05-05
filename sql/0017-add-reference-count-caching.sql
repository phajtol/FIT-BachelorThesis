CREATE TABLE `reference_count` (
  `reference_count_id` INT NOT NULL AUTO_INCREMENT,
  `count` INT(10) UNSIGNED NULL,
  `timestamp` DATETIME NULL,
  PRIMARY KEY (`reference_count_id`)
);
  
INSERT INTO `reference_count` (`reference_count_id`, `count`, `timestamp`) VALUES ('1', '0', '2019-05-05 12:00:00');
