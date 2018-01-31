CREATE TABLE `conference_year_isbn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conference_year_id` int(10) unsigned DEFAULT NULL,
  `isbn` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `note` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `type` enum('ISBN','ISSN') COLLATE utf8_czech_ci NOT NULL DEFAULT 'ISBN',
  PRIMARY KEY (`id`),
  KEY `conference_year_id` (`conference_year_id`),
  CONSTRAINT `conference_year_isbn_ibfk_2` FOREIGN KEY (`conference_year_id`) REFERENCES `conference_year` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


insert into conference_year_isbn (conference_year_id, isbn, type) select id, isbn, 'ISBN' from conference_year where isbn is not null and isbn!='';
insert into conference_year_isbn (conference_year_id, isbn, type) select id, issn, 'ISSN' from conference_year where issn is not null and issn!='';

ALTER TABLE `conference_year` DROP `isbn`;
ALTER TABLE `conference_year` DROP `issn`;
