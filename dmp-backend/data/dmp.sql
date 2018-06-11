# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.13)
# Database: dmp
# Generation Time: 2017-07-17 07:44:50 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table cv_analysis
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cv_analysis`;

CREATE TABLE `cv_analysis` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `is_selected` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `cv_analysis` WRITE;
/*!40000 ALTER TABLE `cv_analysis` DISABLE KEYS */;

INSERT INTO `cv_analysis` (`id`, `type`, `name`, `is_selected`)
VALUES
	(1,'r','R','N'),
	(2,'matlab','MATLAB','N'),
	(3,'code','Programming code','N'),
	(4,'software','Software','N'),
	(5,'xls','Spreadsheet files','N');

/*!40000 ALTER TABLE `cv_analysis` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table cv_datatypes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cv_datatypes`;

CREATE TABLE `cv_datatypes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) DEFAULT NULL,
  `is_selected` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `cv_datatypes` WRITE;
/*!40000 ALTER TABLE `cv_datatypes` DISABLE KEYS */;

INSERT INTO `cv_datatypes` (`id`, `name`, `is_selected`)
VALUES
	(1,'Genomic data','N'),
	(2,'Proteomic data','N'),
	(3,'Transcriptomic data','N'),
	(4,'Lipidomic data','N'),
	(5,'Metabolomic data','N'),
	(6,'Microscopy images','N');

/*!40000 ALTER TABLE `cv_datatypes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table cv_documentation
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cv_documentation`;

CREATE TABLE `cv_documentation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `is_selected` enum('Y','N') DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `cv_documentation` WRITE;
/*!40000 ALTER TABLE `cv_documentation` DISABLE KEYS */;

INSERT INTO `cv_documentation` (`id`, `type`, `name`, `is_selected`)
VALUES
	(1,'calibration','Instrument calibrati','Y'),
	(2,'repeats','Repeated measurement','Y'),
	(3,'recording','Data recording stand','Y'),
	(4,'entry_valid','Data entry validatio','Y'),
	(5,'peer_review','Data peer review','Y'),
	(6,'db','Database','Y'),
	(7,'filename','File naming conventi','Y'),
	(8,'folders','Folder structure','Y');

/*!40000 ALTER TABLE `cv_documentation` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table cv_permissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cv_permissions`;

CREATE TABLE `cv_permissions` (
  `permission_id` int(1) unsigned NOT NULL,
  `description` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `cv_permissions` WRITE;
/*!40000 ALTER TABLE `cv_permissions` DISABLE KEYS */;

INSERT INTO `cv_permissions` (`permission_id`, `description`)
VALUES
	(0,'none'),
	(1,'read'),
	(2,'read+write');

/*!40000 ALTER TABLE `cv_permissions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table cv_version_control
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cv_version_control`;

CREATE TABLE `cv_version_control` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `is_selected` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `cv_version_control` WRITE;
/*!40000 ALTER TABLE `cv_version_control` DISABLE KEYS */;

INSERT INTO `cv_version_control` (`id`, `type`, `name`, `is_selected`)
VALUES
	(1,'git','name','Y'),
	(2,'svn','SVN','N'),
	(3,'locally','Locally','N'),
	(4,'other','Other','N');

/*!40000 ALTER TABLE `cv_version_control` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table datasets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `datasets`;

CREATE TABLE `datasets` (
  `dataset_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `project_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `group_id` int(11) unsigned DEFAULT NULL,
  `project_permissions` int(1) unsigned NOT NULL,
  `group_permissions` int(1) unsigned NOT NULL,
  `public_permissions` int(1) unsigned NOT NULL,
  `request_download` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` text NOT NULL,
  PRIMARY KEY (`dataset_id`),
  KEY `group_id` (`group_id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `datasets_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `datasets_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `datasets_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `datasets` WRITE;
/*!40000 ALTER TABLE `datasets` DISABLE KEYS */;

INSERT INTO `datasets` (`dataset_id`, `name`, `description`, `project_id`, `user_id`, `group_id`, `project_permissions`, `group_permissions`, `public_permissions`, `request_download`, `timestamp`, `status`)
VALUES
	(1,'test','test',1,1,1,0,0,0,'N','2015-10-01 07:59:34','valid');

/*!40000 ALTER TABLE `datasets` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table download_requests
# ------------------------------------------------------------

DROP TABLE IF EXISTS `download_requests`;

CREATE TABLE `download_requests` (
  `download_request_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `dataset_id` int(10) unsigned NOT NULL,
  `request_date` datetime NOT NULL,
  `referer_id` int(11) unsigned DEFAULT NULL,
  `accept_date` text,
  `reject_date` text,
  `code` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`download_request_id`),
  KEY `user_id` (`user_id`),
  KEY `referer_id` (`referer_id`),
  KEY `experiment_id` (`dataset_id`),
  CONSTRAINT `download_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `download_requests_ibfk_2` FOREIGN KEY (`referer_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `download_requests_ibfk_3` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table files
# ------------------------------------------------------------

DROP TABLE IF EXISTS `files`;

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) unsigned DEFAULT NULL,
  `dataset_id` int(11) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `mime_type` varchar(50) DEFAULT NULL,
  `size` int(11) NOT NULL,
  `is_deleted` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`file_id`),
  KEY `experiment_id` (`dataset_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`),
  CONSTRAINT `files_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `groups`;

CREATE TABLE `groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `institution` varchar(255) DEFAULT NULL,
  `leader_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  KEY `leader_id` (`leader_id`),
  CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`leader_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;

INSERT INTO `groups` (`group_id`, `name`, `institution`, `leader_id`)
VALUES
	(1,'dev','SIB',1);

/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table logs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `logs`;

CREATE TABLE `logs` (
  `user_id` int(11) unsigned DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment` varchar(255) DEFAULT NULL,
  KEY `user_id` (`user_id`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table news
# ------------------------------------------------------------

DROP TABLE IF EXISTS `news`;

CREATE TABLE `news` (
  `news_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` enum('Y','N') NOT NULL DEFAULT 'N',
  `project_id` int(10) unsigned DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  PRIMARY KEY (`news_id`),
  KEY `fk_news_user_idx` (`user_id`),
  KEY `fk_news_project_idx` (`project_id`),
  CONSTRAINT `fk_news_project_idx` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_news_user_idx` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table project_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `project_groups`;

CREATE TABLE `project_groups` (
  `project_id` int(10) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`project_id`,`group_id`),
  KEY `fk_project_groups_group_idx` (`group_id`),
  CONSTRAINT `fk_project_groups_group_idx` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_project_groups_project_idx` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `project_groups` WRITE;
/*!40000 ALTER TABLE `project_groups` DISABLE KEYS */;

INSERT INTO `project_groups` (`project_id`, `group_id`)
VALUES
	(1,1);

/*!40000 ALTER TABLE `project_groups` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table projects
# ------------------------------------------------------------

DROP TABLE IF EXISTS `projects`;

CREATE TABLE `projects` (
  `project_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;

INSERT INTO `projects` (`project_id`, `name`, `description`, `user_id`)
VALUES
	(1,'demo','Demo project',1);

/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table publications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `publications`;

CREATE TABLE `publications` (
  `publication_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `authors` varchar(255) DEFAULT NULL,
  `journal` varchar(55) DEFAULT NULL,
  `volume` varchar(55) DEFAULT NULL,
  `page` varchar(55) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `doi` varchar(55) DEFAULT NULL,
  `pmid` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `abstract` text,
  `user_id` int(11) unsigned DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`publication_id`),
  KEY `fk_publications_user_idx` (`user_id`),
  CONSTRAINT `fk_publications_user_idx` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table user_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_groups`;

CREATE TABLE `user_groups` (
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `fk_user_groups_group_idx` (`group_id`),
  CONSTRAINT `fk_user_groups_group_idx` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_groups_user_idx` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `user_groups` WRITE;
/*!40000 ALTER TABLE `user_groups` DISABLE KEYS */;

INSERT INTO `user_groups` (`user_id`, `group_id`)
VALUES
	(1,1);

/*!40000 ALTER TABLE `user_groups` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(25) NOT NULL DEFAULT '',
  `firstname` varchar(255) NOT NULL DEFAULT '',
  `lastname` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `is_admin` enum('Y','N') NOT NULL DEFAULT 'N',
  `is_active` enum('Y','N') NOT NULL DEFAULT 'N',
  `code` varchar(25) DEFAULT NULL,
  `activation_code` varchar(25) DEFAULT NULL,
  `is_password_reset` enum('Y','N') NOT NULL DEFAULT 'N',
  `newsletter` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`user_id`, `login`, `firstname`, `lastname`, `password`, `phone`, `email`, `is_admin`, `is_active`, `code`, `activation_code`, `is_password_reset`, `newsletter`)
VALUES
	(1,'admin','Admin','User','$2y$10$MYtuDVD2RR5.tyXiad94R.nzFtrzVAYgOZa1RXxOjbYDxTuNGROUS','123456','first.last@email.com','Y','Y','pFST9C1bJhAHYPsFO8xd6WRws',NULL,'N','N');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
