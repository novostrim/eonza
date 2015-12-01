<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

function init_v4()
{
    $db = DB::getInstance();
    $confupd = GS::get('confupd');
    $conf = GS::get('conf');
    GS::set('sort', 0 );
    GS::set('align', 0 );    
    if ( !isset( $conf['idwebpages'] ))
    {
        $idwebpages = $db->insert( ENZ_TABLES, 
                    array( 'idparent' => SYS_ID, 'alias' => ENZ_WEBPAGES,
                           'title' => ':webpages', 'helplink' => 'http://www.eonza.org/web-pages.html' ), '', true );
        $confupd['idwebpages'] = $idwebpages;      
        GS::set( 'idcoltable', $idwebpages );

        addcolumn( FT_VAR, ':titlejs', 128, 'title' );
        addcolumn( FT_VAR, ':alias', 64, 'alias' );
        addcolumn( FT_TEXT, ':contentjs', '{"weditor":"2","bigtext":"0"}', 'content', 0 );
        addcolumn( FT_TEXT, ':moreoptions', '{"weditor":"1","bigtext":"0"}', 'options', 0 );
    }
    GS::set( 'confupd', $confupd );
} 

function upd3to4()
{
    $db = DB::getInstance();

    $db->query( "CREATE TABLE IF NOT EXISTS `".ENZ_PREFIX."webpages` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_owner` smallint(5) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `alias` varchar(64) NOT NULL,
  `content` text NOT NULL,
  `options` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `_uptime` (`_uptime`),
  KEY `alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );

    $db->query( "alter table ?n add `help` varchar(128) NOT NULL", ENZ_TABLES );

    init_v4();
}
