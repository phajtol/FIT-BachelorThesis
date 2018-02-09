CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `global_scope` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag_FKIndex1` (`submitter_id`),
  CONSTRAINT `tag_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `publication_has_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publication_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `publication_id` (`publication_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `publication_has_tag_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`),
  CONSTRAINT `publication_has_tag_ibfk_3` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

ALTER TABLE `publication_has_tag`
ADD FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`),
ADD FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`);
