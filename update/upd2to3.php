<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

function upd2to3()
{
    $db = DB::getInstance();

    $db->query( "CREATE TABLE IF NOT EXISTS `".ENZ_ONEMANY."` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcolumn` smallint(5) unsigned NOT NULL,
  `iditem` int(10) unsigned NOT NULL,
  `idmulti` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idcolumn` (`idcolumn`,`iditem`),
  KEY `idcolumn_2` (`idcolumn`,`idmulti`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );
}
