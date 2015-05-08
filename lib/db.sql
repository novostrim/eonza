CREATE TABLE IF NOT EXISTS `xxx_db` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pass` char(32) NOT NULL,
  `ctime` datetime NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_group` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_tables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_owner` smallint(5) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `comment` varchar(128) NOT NULL,
  `alias` varchar(24) NOT NULL,
  `idparent` int(10) unsigned NOT NULL,
  `isfolder` tinyint(3) unsigned NOT NULL,
  `istree` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idparent` (`idparent`,`isfolder`,`title`)  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(32) NOT NULL,
  `pass` binary(16) NOT NULL,
  `email` varchar(32) NOT NULL,
  `idgroup` smallint(5) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_columns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idtable` smallint(5) unsigned NOT NULL,
  `idtype` tinyint(3) unsigned NOT NULL,
  `title` varchar(96) NOT NULL,
  `comment` varchar(128) NOT NULL,
  `sort` smallint(5) unsigned NOT NULL,
  `alias` varchar(24) NOT NULL,
  `visible` tinyint(3) unsigned NOT NULL,
  `align` tinyint(3) unsigned NOT NULL,
  `extend` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idtable` (`idtable`,`sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `sort` int(11) NOT NULL,
  `url` varchar(128) NOT NULL,
  `hint` varchar(128) NOT NULL,
  `idparent` int(10) unsigned NOT NULL,
  `isfolder` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idparent` (`idparent`,`sort`,`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_log` (
  `idtable` smallint(5) unsigned NOT NULL,
  `idrow` int(10) unsigned NOT NULL,
  `iduser` smallint(5) unsigned NOT NULL,
  `uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` set('create','edit','delete','') NOT NULL,
  KEY `uptime` (`uptime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_sets` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `_owner` smallint(5) unsigned NOT NULL,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idset` smallint(5) unsigned NOT NULL,
  `iditem` tinyint(3) unsigned NOT NULL,
  `title` varchar(48) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idset` (`idset`,`iditem`),
  KEY `idsetname` (`idset`,`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_owner` smallint(5) unsigned NOT NULL,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idtable` smallint(5) unsigned NOT NULL,
  `idcol` int(10) unsigned NOT NULL,
  `iditem` int(10) unsigned NOT NULL,
  `folder` tinyint(3) unsigned NOT NULL,
  `filename` varchar(128) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `storage` longblob NOT NULL,
  `w` mediumint(8) unsigned NOT NULL,
  `h` mediumint(8) unsigned NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL,
  `preview` blob NOT NULL,
  `mime` tinyint(4) NOT NULL,
  `ispreview` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idtable` (`idtable`,`idcol`,`iditem`,`sort`),
  KEY `folder` (`idtable`,`folder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_mimes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `ext` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_access` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_owner` smallint(5) unsigned NOT NULL,
  `idgroup` smallint(5) unsigned NOT NULL,
  `idtable` int(10) unsigned NOT NULL,
  `mask` varchar(32) NOT NULL,
  `active` tinyint(3) NOT NULL,
  `read` tinyint(3) unsigned NOT NULL,
  `create` tinyint(3) unsigned NOT NULL,
  `edit` tinyint(3) unsigned NOT NULL,
  `del` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `_uptime` (`_uptime`),
  KEY `idgroup` (`idgroup`,`idtable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
