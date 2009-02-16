SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8;
-- 
-- Base de données: `mtest`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `album`
-- 

DROP TABLE IF EXISTS `album`;
CREATE TABLE `album` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Contenu de la table `album`
-- 

INSERT INTO `album` (`id`, `title`) VALUES 
(1, 'album1');

-- --------------------------------------------------------

-- 
-- Structure de la table `album_i18n`
-- 

DROP TABLE IF EXISTS `album_i18n`;
CREATE TABLE `album_i18n` (
  `i18n_id` int(10) unsigned NOT NULL auto_increment,
  `i18n_record_id` int(10) unsigned NOT NULL,
  `i18n_lang` char(2) NOT NULL,
  `description` mediumtext character set utf8 NOT NULL,
  PRIMARY KEY  (`i18n_id`),
  KEY `i18n` (`i18n_record_id`,`i18n_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `album_i18n`
-- 
INSERT INTO `album_i18n` (`i18n_id`, `i18n_record_id`, `i18n_lang`, `description`) VALUES 
(1, 1, 'fr', 'description test');

-- --------------------------------------------------------

-- 
-- Structure de la table `artist`
-- 

DROP TABLE IF EXISTS `artist`;
CREATE TABLE `artist` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `artist`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `artist_i18n`
-- 

DROP TABLE IF EXISTS `artist_i18n`;
CREATE TABLE `artist_i18n` (
  `i18n_id` int(10) unsigned NOT NULL auto_increment,
  `i18n_record_id` int(10) unsigned NOT NULL,
  `i18n_lang` char(2) NOT NULL,
  `description` mediumtext NOT NULL,
  PRIMARY KEY  (`i18n_id`),
  KEY `i18n` (`i18n_record_id`,`i18n_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `artist_i18n`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `notmigrated`
-- 
DROP TABLE IF EXISTS `notmigrated_i18n`;
DROP TABLE IF EXISTS `notmigrated`;
CREATE TABLE `notmigrated` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `titre` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `pays` char(2) NOT NULL,
  `testuser_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- 
-- Contenu de la table `notmigrated`
-- 

INSERT INTO `notmigrated` (`id`, `titre`, `description`, `pays`, `testuser_id`) VALUES 
(1, 'élément 1', 'Test de description élément 1 (en français)', 'fr', 1),
(2, 'élément 2', 'Test de description élément 2 (en français)', 'ma', 1),
(3, 'élément 3', 'Test de description élément 3', 'en', 2);

-- --------------------------------------------------------

-- 
-- Structure de la table `notmigrated_withspecialnames`
-- Le but de cette table est de tester la migration avec des champs qui ont des noms réservés (comme long et int)
-- 
DROP TABLE IF EXISTS `notmigrated_withspecialnames_i18n`;
DROP TABLE IF EXISTS `notmigrated_withspecialnames`;
CREATE TABLE `notmigrated_withspecialnames` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `long` varchar(255) NOT NULL,
  `int` mediumtext NOT NULL,
  `pays` char(2) NOT NULL,
  `testuser_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- 
-- Contenu de la table `notmigrated`
-- 

INSERT INTO `notmigrated_withspecialnames` (`id`, `long`, `int`, `pays`, `testuser_id`) VALUES 
(1, 'élément 1', 'Test de description élément 1 (en français)', 'fr', 1),
(2, 'élément 2', 'Test de description élément 2 (en français)', 'ma', 1),
(3, 'élément 3', 'Test de description élément 3', 'en', 2);

-- --------------------------------------------------------


-- 
-- Structure de la table `title`
-- 

DROP TABLE IF EXISTS `title`;
CREATE TABLE `title` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `album_id` int(10) unsigned NOT NULL,
  `ordre` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `album_id` (`album_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `title`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `translate`
-- 

DROP TABLE IF EXISTS `translate`;
CREATE TABLE `translate` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `language` char(2) NOT NULL default '',
  `targettable` varchar(30) NOT NULL default '',
  `targetfield` varchar(30) NOT NULL default '',
  `record_id` int(10) unsigned NOT NULL default '0',
  `translatedvalue` text character set utf8,
  PRIMARY KEY  (`id`),
  KEY `tLang` (`language`,`targettable`,`targetfield`,`record_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- 
-- Contenu de la table `translate`
-- 

INSERT INTO `translate` (`id`, `language`, `targettable`, `targetfield`, `record_id`, `translatedvalue`) VALUES 
(1, 'en', 'notmigrated', 'titre', 1, 'element one'),
(2, 'es', 'notmigrated', 'titre', 1, 'elemento uno'),
(3, 'en', 'notmigrated', 'description', 1, 'Test of element one description'),
(4, 'es', 'notmigrated', 'description', 1, 'Testo de la descripccion del elemento uno'),
(5, 'en', 'notmigrated', 'titre', 2, 'element two'),
(6, 'es', 'notmigrated', 'titre', 2, 'elemento dos'),
(7, 'en', 'notmigrated', 'description', 2, 'Test of description for element two'),
(8, 'es', 'notmigrated', 'description', 2, 'например, в Российской'),
(9, 'en', 'notmigrated', 'titre', 3, 'Element three'),
(10, 'es', 'notmigrated', 'titre', 3, 'elemento tres');

-- --------------------------------------------------------

-- 
-- Structure de la table `testuser`
-- 

DROP TABLE IF EXISTS `testuser`;
CREATE TABLE `testuser` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- 
-- Contenu de la table `testuser`
-- 

INSERT INTO `testuser` (`id`, `login`, `password`) VALUES 
(1, 'martin', 'test2'),
(2, 'lucien', 'test3');

DROP TABLE IF EXISTS `vanillainnodb`;
CREATE TABLE `vanillainnodb` (
`id` int(10) unsigned NOT NULL auto_increment,
`name` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `vanillainnodb` (
`id` ,
`name`
)
VALUES (
NULL , 'elem1'
), (
NULL , 'elem2'
);

DROP TABLE IF EXISTS `vanillainnodb2`;
CREATE TABLE `vanillainnodb2` (
`id` int(10) unsigned NOT NULL auto_increment,
`name` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `vanillainnodb2` (
`id` ,
`name`
)
VALUES (
NULL , '2elem1'
), (
NULL , '2elem2'
);


 DROP TABLE IF EXISTS `formtest_i18n` ;

 CREATE TABLE `formtest_i18n` (
 `titre` varchar( 255 ) NOT NULL ,
 `description` mediumtext NOT NULL ,
 `i18n_id` int( 10 ) unsigned NOT NULL AUTO_INCREMENT ,
 `i18n_lang` varchar( 2 ) NOT NULL default 'fr',
 `i18n_record_id` int( 10 ) unsigned NOT NULL default '0',
 PRIMARY KEY ( `i18n_id` ) ,
 KEY `i18n_idx` ( `i18n_lang` , `i18n_record_id` )
 ) ENGINE = MYISAM DEFAULT CHARSET = utf8; 

DROP TABLE IF EXISTS `formtest` ;
 CREATE TABLE `formtest` (
   `id` int(10) unsigned NOT NULL auto_increment,
   `pays` char(2) NOT NULL,
   `testuser_id` int(10) unsigned NOT NULL,
   PRIMARY KEY  (`id`)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
 

 INSERT INTO `formtest` (`id`, `pays`, `testuser_id`) VALUES 
 (1, 'fr', 1),
 (2, 'ma', 1),
 (3, 'en', 2);


 -- 
 -- Contenu de la table `formtest_i18n`
 -- 

 INSERT INTO `formtest_i18n` (`titre`, `description`, `i18n_id`, `i18n_lang`, `i18n_record_id`) VALUES 
 ('élément 1', 'Test de description élément 1 (en français)', 1, 'fr', 1),
 ('élément 2', 'Test de description élément 2 (en français)', 2, 'fr', 2),
 ('élément 3', 'Test de description élément 3', 3, 'fr', 3),
 ('element one', 'Test of element one description', 4, 'en', 1),
 ('element two', 'Test of description for element two', 5, 'en', 2),
 ('Element three', 'Test de description élément 3', 6, 'en', 3),
 ('elemento uno', 'Testo de la descripccion del elemento uno', 7, 'es', 1),
 ('elemento dos', 'Testo de descripccion por el elemento dos', 8, 'es', 2),
 ('elemento tres', 'Test de description élément 3', 9, 'es', 3);

