-- Adminer 4.7.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `acm_category`;
CREATE TABLE `acm_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `acm_category_hierarchy` FOREIGN KEY (`parent_id`) REFERENCES `acm_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `annotation`;
CREATE TABLE `annotation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `publication_id` int(10) unsigned NOT NULL,
  `submitter_id` int(10) unsigned NOT NULL,
  `text` text COLLATE utf8_czech_ci,
  `global_scope` tinyint(1) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`,`publication_id`),
  KEY `annotation_FKIndex1` (`submitter_id`),
  KEY `annotation_FKIndex2` (`publication_id`),
  FULLTEXT KEY `idx_annotation_text` (`text`),
  CONSTRAINT `annotation_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `annotation_ibfk_2` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `attributes`;
CREATE TABLE `attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `confirmed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `attributes_FKIndex1` (`submitter_id`),
  CONSTRAINT `attributes_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `attrib_storage`;
CREATE TABLE `attrib_storage` (
  `publication_id` int(10) unsigned NOT NULL,
  `attributes_id` int(10) unsigned NOT NULL,
  `submitter_id` int(10) unsigned DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`publication_id`,`attributes_id`),
  KEY `attrib_storage_FKIndex2` (`submitter_id`),
  KEY `attrib_storage_FKIndex3` (`attributes_id`),
  CONSTRAINT `attrib_storage_ibfk_1` FOREIGN KEY (`attributes_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `attrib_storage_ibfk_2` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `attrib_storage_ibfk_3` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `author`;
CREATE TABLE `author` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `surname` varchar(50) DEFAULT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `author_FKIndex1` (`submitter_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `author_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `author_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `submitter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `author_has_publication`;
CREATE TABLE `author_has_publication` (
  `author_id` int(10) unsigned NOT NULL,
  `publication_id` int(10) unsigned NOT NULL,
  `priority` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`author_id`,`publication_id`),
  KEY `author_has_publication_FKIndex2` (`publication_id`),
  CONSTRAINT `author_has_publication_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `author_has_publication_ibfk_2` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `auth_ldap`;
CREATE TABLE `auth_ldap` (
  `submitter_id` int(10) unsigned NOT NULL,
  `login` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`login`),
  UNIQUE KEY `submitter_id` (`submitter_id`),
  CONSTRAINT `auth_ldap_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `auth_login_password`;
CREATE TABLE `auth_login_password` (
  `submitter_id` int(10) unsigned NOT NULL,
  `login` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`submitter_id`),
  UNIQUE KEY `login` (`login`),
  CONSTRAINT `fk_auth_login_password_submitter_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `auth_shibboleth`;
CREATE TABLE `auth_shibboleth` (
  `submitter_id` int(10) unsigned NOT NULL,
  `username` varchar(120) NOT NULL,
  `email` varchar(240) NOT NULL,
  PRIMARY KEY (`submitter_id`),
  UNIQUE KEY `username` (`username`,`email`),
  CONSTRAINT `auth_shibboleth_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `categories_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_FKIndex1` (`submitter_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `categories_has_publication`;
CREATE TABLE `categories_has_publication` (
  `publication_id` int(10) unsigned NOT NULL,
  `categories_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`publication_id`,`categories_id`),
  KEY `publication_has_categories_FKIndex2` (`categories_id`),
  CONSTRAINT `categories_has_publication_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `categories_has_publication_ibfk_2` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `conference`;
CREATE TABLE `conference` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(500) CHARACTER SET utf8 NOT NULL,
  `abbreviation` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `submitter_id` int(10) unsigned DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  `first_year` year(4) DEFAULT NULL,
  `state` enum('alive','dead') COLLATE utf8_czech_ci NOT NULL DEFAULT 'alive',
  `lastedit_submitter_id` int(10) unsigned DEFAULT NULL,
  `lastedit_timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conference2_FKIndex1` (`submitter_id`),
  KEY `state` (`state`),
  KEY `lastedit_submitter_id` (`lastedit_submitter_id`),
  CONSTRAINT `conference_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `conference_ibfk_2` FOREIGN KEY (`lastedit_submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `conference_lastedit_submitter_id` FOREIGN KEY (`lastedit_submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci ROW_FORMAT=COMPACT;


DROP TABLE IF EXISTS `conference_category`;
CREATE TABLE `conference_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `conference_category_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `conference_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `conference_has_acm_category`;
CREATE TABLE `conference_has_acm_category` (
  `acm_category_id` int(10) unsigned NOT NULL,
  `conference_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`acm_category_id`,`conference_id`),
  KEY `idx_kategoriekonf_id_kategorie` (`acm_category_id`),
  KEY `idx_kategoriekonf_id_konf` (`conference_id`),
  CONSTRAINT `kategoriekonf_ibfk_1` FOREIGN KEY (`acm_category_id`) REFERENCES `acm_category` (`id`),
  CONSTRAINT `kategoriekonf_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conference` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `conference_has_category`;
CREATE TABLE `conference_has_category` (
  `conference_id` int(10) unsigned NOT NULL,
  `conference_category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`conference_id`,`conference_category_id`),
  KEY `conference_category_id` (`conference_category_id`),
  CONSTRAINT `conference_has_category_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conference` (`id`),
  CONSTRAINT `conference_has_category_ibfk_2` FOREIGN KEY (`conference_category_id`) REFERENCES `conference_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `conference_year`;
CREATE TABLE `conference_year` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conference_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'not null .. its a workshop',
  `submitter_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(500) CHARACTER SET utf8 NOT NULL,
  `abbreviation` varchar(100) CHARACTER SET utf8 NOT NULL,
  `w_year` year(4) DEFAULT NULL,
  `w_from` date DEFAULT NULL,
  `w_to` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `notification` date DEFAULT NULL,
  `finalversion` date DEFAULT NULL,
  `location` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  `publisher_id` int(10) unsigned DEFAULT NULL,
  `state` enum('alive','archived') CHARACTER SET utf8 NOT NULL DEFAULT 'alive',
  `doi` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `web` varchar(500) COLLATE latin2_czech_cs DEFAULT NULL,
  `lastedit_submitter_id` int(10) unsigned DEFAULT NULL,
  `lastedit_timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conference_year_FKIndex1` (`conference_id`),
  KEY `conference_year_FKIndex2` (`submitter_id`),
  KEY `publisher_id` (`publisher_id`),
  KEY `parent_id` (`parent_id`),
  KEY `lastedit_submitter_id` (`lastedit_submitter_id`),
  CONSTRAINT `conference_year_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `conference_year_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conference` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `conference_year_ibfk_4` FOREIGN KEY (`parent_id`) REFERENCES `conference_year` (`id`),
  CONSTRAINT `conference_year_ibfk_5` FOREIGN KEY (`publisher_id`) REFERENCES `publisher` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `conference_year_ibfk_6` FOREIGN KEY (`lastedit_submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `conference_year_lastedit_submitter_id` FOREIGN KEY (`lastedit_submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_czech_cs ROW_FORMAT=COMPACT;


DROP TABLE IF EXISTS `conference_year_isbn`;
CREATE TABLE `conference_year_isbn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conference_year_id` int(10) unsigned DEFAULT NULL,
  `isbn` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `note` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `type` enum('ISBN','ISSN') COLLATE utf8_czech_ci NOT NULL DEFAULT 'ISBN',
  PRIMARY KEY (`id`),
  KEY `conference_year_id` (`conference_year_id`),
  CONSTRAINT `conference_year_isbn_ibfk_3` FOREIGN KEY (`conference_year_id`) REFERENCES `conference_year` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `conference_year_is_indexed`;
CREATE TABLE `conference_year_is_indexed` (
  `conference_year_id` int(10) unsigned NOT NULL,
  `document_index_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`conference_year_id`,`document_index_id`),
  KEY `document_index_id` (`document_index_id`),
  CONSTRAINT `conference_year_is_indexed_ibfk_1` FOREIGN KEY (`conference_year_id`) REFERENCES `conference_year` (`id`),
  CONSTRAINT `conference_year_is_indexed_ibfk_2` FOREIGN KEY (`document_index_id`) REFERENCES `document_index` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `cu_group`;
CREATE TABLE `cu_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `cu_group_has_conference_category`;
CREATE TABLE `cu_group_has_conference_category` (
  `cu_group_id` int(10) unsigned NOT NULL,
  `conference_category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cu_group_id`,`conference_category_id`),
  KEY `conference_category_id` (`conference_category_id`),
  CONSTRAINT `cu_group_has_conference_category_ibfk_1` FOREIGN KEY (`cu_group_id`) REFERENCES `cu_group` (`id`),
  CONSTRAINT `cu_group_has_conference_category_ibfk_2` FOREIGN KEY (`conference_category_id`) REFERENCES `conference_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `publication_id` int(10) unsigned NOT NULL,
  `title` text COLLATE utf8_czech_ci,
  `content` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`publication_id`),
  FULLTEXT KEY `content` (`content`),
  FULLTEXT KEY `title` (`title`),
  CONSTRAINT `publication_id` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `document_index`;
CREATE TABLE `document_index` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `web` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `general_settings`;
CREATE TABLE `general_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pagination` int(10) unsigned DEFAULT NULL,
  `spring_token` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `deadline_notification_advance` smallint(6) NOT NULL DEFAULT '14' COMMENT '[days]',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `submitter_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `group_has_publication`;
CREATE TABLE `group_has_publication` (
  `group_id` int(10) unsigned NOT NULL,
  `publication_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`publication_id`),
  KEY `group_has_publication_FKIndex1` (`publication_id`),
  KEY `group_has_publication_FKIndex2` (`group_id`),
  CONSTRAINT `group_has_publication_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `group_has_publication_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci ROW_FORMAT=COMPACT;


DROP TABLE IF EXISTS `journal`;
CREATE TABLE `journal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(500) COLLATE utf8_czech_ci NOT NULL,
  `doi` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  `abbreviation` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `magazine_FKIndex1` (`submitter_id`),
  CONSTRAINT `journal_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `journal_isbn`;
CREATE TABLE `journal_isbn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `journal_id` int(10) unsigned DEFAULT NULL,
  `isbn` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `note` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `type` enum('ISBN','ISSN') COLLATE utf8_czech_ci NOT NULL DEFAULT 'ISBN',
  PRIMARY KEY (`id`),
  KEY `journal_id` (`journal_id`),
  CONSTRAINT `journal_isbn_ibfk_3` FOREIGN KEY (`journal_id`) REFERENCES `journal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `publication`;
CREATE TABLE `publication` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `journal_id` int(10) unsigned DEFAULT NULL,
  `submitter_id` int(10) unsigned DEFAULT NULL COMMENT 'user id of entry author',
  `publisher_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(500) NOT NULL,
  `booktitle` varchar(500) DEFAULT NULL,
  `abstract` text,
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `issue_date` date DEFAULT NULL,
  `issue_year` int(11) DEFAULT NULL,
  `issue_month` int(11) DEFAULT NULL,
  `volume` varchar(50) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `pages` varchar(50) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `chapter` varchar(200) DEFAULT NULL,
  `edition` varchar(200) DEFAULT NULL,
  `editor` varchar(200) DEFAULT NULL,
  `howpublished` varchar(200) DEFAULT NULL,
  `institution` varchar(200) DEFAULT NULL,
  `organization` varchar(200) DEFAULT NULL,
  `school` varchar(200) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `type_of_report` varchar(100) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `pub_type` varchar(50) DEFAULT NULL,
  `conference_year_id` int(10) unsigned DEFAULT NULL,
  `doi` varchar(100) DEFAULT NULL,
  `lastedit_submitter_id` int(10) unsigned DEFAULT NULL,
  `lastedit_timestamp` datetime DEFAULT NULL,
  `title_search` varchar(500) NOT NULL COMMENT 'title without special character, used for search',
  PRIMARY KEY (`id`),
  KEY `publication_FKIndex1` (`submitter_id`),
  KEY `publication_FKIndex2` (`publisher_id`),
  KEY `publication_FKIndex4` (`journal_id`),
  KEY `publication_FKIndex5` (`conference_year_id`),
  KEY `lastedit_submitter_id` (`lastedit_submitter_id`),
  FULLTEXT KEY `title` (`title`),
  CONSTRAINT `publication_conference_year_id_FK` FOREIGN KEY (`conference_year_id`) REFERENCES `conference_year` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `publication_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `publication_ibfk_2` FOREIGN KEY (`lastedit_submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `publication_journal_id_FK` FOREIGN KEY (`journal_id`) REFERENCES `journal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `publication_lastedit_submitter_id` FOREIGN KEY (`lastedit_submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `publication_publisher_id_FK` FOREIGN KEY (`publisher_id`) REFERENCES `publisher` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `publication_has_tag`;
CREATE TABLE `publication_has_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publication_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `publication_id` (`publication_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `publication_has_tag_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`),
  CONSTRAINT `publication_has_tag_ibfk_3` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`),
  CONSTRAINT `publication_has_tag_ibfk_4` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`),
  CONSTRAINT `publication_has_tag_ibfk_5` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `publication_isbn`;
CREATE TABLE `publication_isbn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `publication_id` int(10) unsigned DEFAULT NULL,
  `isbn` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `note` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `type` enum('ISBN','ISSN') COLLATE utf8_czech_ci NOT NULL DEFAULT 'ISBN',
  PRIMARY KEY (`id`),
  KEY `publication_id` (`publication_id`),
  CONSTRAINT `publication_isbn_ibfk_3` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `publisher`;
CREATE TABLE `publisher` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(500) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `publisher_FKIndex1` (`submitter_id`),
  CONSTRAINT `publisher_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `reference`;
CREATE TABLE `reference` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `publication_id` int(10) unsigned NOT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `submitter_id` int(10) unsigned NOT NULL,
  `text` text COLLATE utf8_czech_ci,
  `title` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `confirmed` tinyint(4) NOT NULL DEFAULT '0',
  `max_refused_id` int(11) DEFAULT NULL,
  `processed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `publication_id` (`publication_id`),
  KEY `reference_id` (`reference_id`),
  KEY `submitter_id` (`submitter_id`),
  FULLTEXT KEY `text` (`text`),
  CONSTRAINT `reference_ibfk_1` FOREIGN KEY (`reference_id`) REFERENCES `publication` (`id`),
  CONSTRAINT `reference_ibfk_2` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`),
  CONSTRAINT `reference_ibfk_3` FOREIGN KEY (`reference_id`) REFERENCES `publication` (`id`),
  CONSTRAINT `reference_ibfk_4` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `reference2`;
CREATE TABLE `reference2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `publication_id` int(10) unsigned NOT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `submitter_id` int(10) unsigned NOT NULL,
  `text` text COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `equal_id` int(11) NOT NULL,
  `fulltext_id` int(11) NOT NULL,
  `fulltext_weight` double NOT NULL,
  `fulltext_title_id` int(11) NOT NULL,
  `fulltext_title_weidht` double NOT NULL,
  `correct_id` int(11) NOT NULL,
  `confirmed` tinyint(4) NOT NULL DEFAULT '0',
  `max_refused_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`),
  KEY `submitter_id` (`submitter_id`),
  KEY `reference_ibfk_3` (`publication_id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `retrieve`;
CREATE TABLE `retrieve` (
  `submitter_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid_hash` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`submitter_id`),
  CONSTRAINT `retrieve_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `submitter`;
CREATE TABLE `submitter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nickname` varchar(50) NOT NULL,
  `surname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `submitter_favourite_conference`;
CREATE TABLE `submitter_favourite_conference` (
  `submitter_id` int(10) unsigned NOT NULL,
  `conference_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`submitter_id`,`conference_id`),
  KEY `conference_id` (`conference_id`),
  CONSTRAINT `submitter_favourite_conference_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`),
  CONSTRAINT `submitter_favourite_conference_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conference` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `submitter_has_cu_group`;
CREATE TABLE `submitter_has_cu_group` (
  `submitter_id` int(10) unsigned NOT NULL,
  `cu_group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`submitter_id`,`cu_group_id`),
  KEY `cu_group_id` (`cu_group_id`),
  CONSTRAINT `submitter_has_cu_group_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`),
  CONSTRAINT `submitter_has_cu_group_ibfk_2` FOREIGN KEY (`cu_group_id`) REFERENCES `cu_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `submitter_has_group`;
CREATE TABLE `submitter_has_group` (
  `submitter_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`submitter_id`,`group_id`),
  KEY `submitter_has_group_FKIndex1` (`group_id`),
  KEY `submitter_has_group_FKIndex2` (`submitter_id`),
  CONSTRAINT `submitter_has_group_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `submitter_has_group_ibfk_2` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci ROW_FORMAT=COMPACT;


DROP TABLE IF EXISTS `submitter_has_publication`;
CREATE TABLE `submitter_has_publication` (
  `submitter_id` int(10) unsigned NOT NULL,
  `publication_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`submitter_id`,`publication_id`),
  KEY `submitter_has_publication_FKIndex1` (`publication_id`),
  KEY `submitter_has_publication_FKIndex2` (`submitter_id`),
  CONSTRAINT `submitter_has_publication_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publication` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `submitter_has_publication_ibfk_2` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci ROW_FORMAT=COMPACT;


DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `global_scope` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag_FKIndex1` (`submitter_id`),
  CONSTRAINT `tag_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `submitter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `user_id` int(10) unsigned NOT NULL,
  `role` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`user_id`,`role`),
  CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `submitter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `user_settings`;
CREATE TABLE `user_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(11) unsigned NOT NULL,
  `pagination` int(11) NOT NULL,
  `deadline_notification_advance` smallint(6) NOT NULL DEFAULT '14' COMMENT '[days]',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2019-04-22 09:39:56
