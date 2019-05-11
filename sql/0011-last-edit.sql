ALTER TABLE `publication`
ADD `lastedit_submitter_id` int(10) unsigned NULL,
ADD `lastedit_timestamp` DATETIME NULL DEFAULT NULL,
ADD CONSTRAINT `publication_lastedit_submitter_id` FOREIGN KEY (`lastedit_submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `conference`
ADD `lastedit_submitter_id` int(10) unsigned NULL,
ADD `lastedit_timestamp` DATETIME NULL DEFAULT NULL,
ADD CONSTRAINT `conference_lastedit_submitter_id` FOREIGN KEY (`lastedit_submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `conference_year`
ADD `lastedit_submitter_id` int(10) unsigned NULL,
ADD `lastedit_timestamp` DATETIME NULL DEFAULT NULL,
ADD CONSTRAINT `conference_year_lastedit_submitter_id` FOREIGN KEY (`lastedit_submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;