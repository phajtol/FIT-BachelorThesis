ALTER TABLE `reference` ADD FOREIGN KEY (`reference_id`) REFERENCES `publication` (`id`);

ALTER TABLE `reference` CHANGE `text` `text` text COLLATE 'utf8_czech_ci' NULL AFTER `submitter_id`;

ALTER TABLE `reference` ADD `confirmed` tinyint NOT NULL DEFAULT '0';

ALTER TABLE `reference` ADD FULLTEXT `text` (`text`);

ALTER TABLE `publication` ADD FULLTEXT `title` (`title`);

ALTER TABLE `reference` ADD `max_refused_id` int NULL;

ALTER TABLE `reference` ADD `title` varchar(255) COLLATE 'utf8_czech_ci' NULL AFTER `text`;

ALTER TABLE `reference` ADD INDEX `title` (`title`);
