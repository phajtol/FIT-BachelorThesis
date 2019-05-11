CREATE TABLE `rights_request` (
  `rights_request_id` INT NOT NULL AUTO_INCREMENT,
  `submitter_id` INT(10) UNSIGNED NULL,
  `verdict_submitter_id` INT(10) UNSIGNED NULL,
  `request_datetime` DATETIME NULL,
  `verdict_datetime` DATETIME NULL,
  `verdict` ENUM('waiting', 'approved', 'rejected') NULL DEFAULT 'waiting',
  `seen` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`rights_request_id`),
  CONSTRAINT `submitter_id`
    FOREIGN KEY (`submitter_id`)
    REFERENCES `submitter` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `verdict_submitter_id`
    FOREIGN KEY (`verdict_submitter_id`)
    REFERENCES `submitter` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE);
