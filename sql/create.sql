-- 
-- Used to create or recreate databases.  Will remove all existing data
--
-- Usage:
--    mysql -u root database-name < create.sql 
--
DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL DEFAULT '',
  `city` varchar(24) DEFAULT '',
  `state` varchar(2) DEFAULT '',
  `county` varchar(24) DEFAULT '',
  `zip` varchar(10) DEFAULT '',
  `created` datetime DEFAULT NULL,
  `changed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB 
  AUTO_INCREMENT=1 
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

