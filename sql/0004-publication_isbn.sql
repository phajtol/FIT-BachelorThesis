CREATE TABLE `publication_isbn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `publication_id` int(10) unsigned DEFAULT NULL,
  `isbn` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `note` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `type` enum('ISBN','ISSN') COLLATE utf8_czech_ci NOT NULL DEFAULT 'ISBN',
  PRIMARY KEY (`id`),
  KEY `publication_id` (`publication_id`),
  CONSTRAINT `publication_isbn_ibfk_2` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


insert into publication_isbn (publication_id, isbn) select id, isbn from publication where isbn is not null and isbn!='';

ALTER TABLE `publication` DROP `isbn`;
