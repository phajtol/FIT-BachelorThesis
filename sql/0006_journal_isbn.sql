CREATE TABLE `journal_isbn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `journal_id` int(10) unsigned DEFAULT NULL,
  `isbn` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `note` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `type` enum('ISBN','ISSN') COLLATE utf8_czech_ci NOT NULL DEFAULT 'ISBN',
  PRIMARY KEY (`id`),
  KEY `journal_id` (`journal_id`),
  CONSTRAINT `journal_isbn_ibfk_2` FOREIGN KEY (`journal_id`) REFERENCES `journal` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


insert into journal_isbn (journal_id, isbn, type) select id, issn, 'ISSN' from journal where issn is not null and issn!='';

ALTER TABLE `journal` DROP `issn`;
