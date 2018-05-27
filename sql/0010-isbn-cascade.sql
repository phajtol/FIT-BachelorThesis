ALTER TABLE `conference_year_isbn`
DROP FOREIGN KEY `conference_year_isbn_ibfk_2`,
ADD FOREIGN KEY (`conference_year_id`) REFERENCES `conference_year` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `publication_isbn`
DROP FOREIGN KEY `publication_isbn_ibfk_2`,
ADD FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `journal_isbn`
DROP FOREIGN KEY `journal_isbn_ibfk_2`,
ADD FOREIGN KEY (`journal_id`) REFERENCES `journal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
