-- 
-- `tag` table structure
-- 

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `id` int(11) NOT NULL auto_increment,
  `strip` varchar(30) collate utf8_unicode_ci NOT NULL,
  `description` mediumtext collate utf8_unicode_ci,
  `recordcount` int(11) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- `tag` table content
-- 


-- --------------------------------------------------------

-- 
-- `tag_history` table structure
-- 

DROP TABLE IF EXISTS `tag_history`;
CREATE TABLE `tag_history` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `tag_id` int(10) unsigned NOT NULL,
  `record_id` varchar(36) collate utf8_unicode_ci NOT NULL,
  `tagged_table` varchar(50) collate utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `direction` char(3) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tag_id` (`tag_id`),
  KEY `tagged_table` (`tagged_table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- `tag_history` table content
-- 


-- --------------------------------------------------------

-- 
-- `tag_record` table structure
-- 

DROP TABLE IF EXISTS `tag_record`;
CREATE TABLE `tag_record` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `tag_id` int(10) unsigned NOT NULL,
  `record_id` varchar(36) collate utf8_unicode_ci NOT NULL,
  `tagged_table` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tag_id` (`tag_id`),
  KEY `tagged_table` (`tagged_table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- `tag_record` table content
-- 