CREATE TABLE `reference` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `publication_id` int(10) unsigned NOT NULL,
  `reference_id` int(10) unsigned NOT NULL,
  `submitter_id` int(10) unsigned NOT NULL,
  `text` text,
  FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`),
  FOREIGN KEY (`reference_id`) REFERENCES `publication` (`id`),
  FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`),
  UNIQUE `publication_id_reference_id` (`publication_id`, `reference_id`)
) ENGINE='InnoDB' COLLATE 'utf8_czech_ci';