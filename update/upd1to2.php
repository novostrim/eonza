<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

function upd1to2()
{
    $db = DB::getInstance();

    $db->query( "CREATE TABLE IF NOT EXISTS `".ENZ_SHARE."` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_owner` smallint(5) unsigned NOT NULL,
  `idtable` smallint(5) unsigned NOT NULL,
  `idslice` int(10) unsigned NOT NULL,
  `idfile` int(10) unsigned NOT NULL,
  `code` int(10) unsigned NOT NULL,
  `timelimit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `viewlimit` smallint(6) NOT NULL,
  `firstonly` tinyint(3) unsigned NOT NULL,
  `firstip` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idtable` (`idtable`,`idslice`,`idfile`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;" );

    $db->query( "CREATE TABLE IF NOT EXISTS `".ENZ_SLICES."` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_owner` smallint(5) unsigned NOT NULL,
  `idtable` smallint(5) unsigned NOT NULL,
  `params` varchar(255) NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `_owner` (`_owner`,`idtable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );

    $db->query( "CREATE TABLE IF NOT EXISTS `".ENZ_TAGS."` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_owner` smallint(5) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  `color` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );

    $db->query( "CREATE TABLE IF NOT EXISTS `".ENZ_TAGLIST."` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_owner` smallint(5) unsigned NOT NULL,
  `idtable` smallint(5) unsigned NOT NULL,
  `iditem` int(10) unsigned NOT NULL,
  `idtag` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idtable` (`idtable`,`iditem`),
  KEY `idtag` (`idtag`,`idtable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );

}
