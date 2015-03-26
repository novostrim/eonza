<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

function init_v1()
{
    $db = DB::getInstance();
    $confupd = GS::get('confupd');
    GS::set('sort', 0 );
    GS::set('align', 0 );    
    $conf = GS::get('conf');
    if ( !isset( $conf['idgroups'] ))
    {
        $dbtables = ENZ_TABLES;
        $dbgroup = CONF_PREFIX.'_group';
        extuptime( $dbgroup );

        $params = array( 'idparent' => SYS_ID, 'alias' => $dbgroup,
                         'title' => ':usrgroups' );
        $idgroups = $db->insert( $dbtables, 
                    array( 'idparent' => SYS_ID, 'alias' => $dbgroup,
                           'title' => ':usrgroups' ), '', true );
        $confupd['idgroups'] = $idgroups;      
        GS::set( 'idcoltable', $idgroups );
        $groupcol = addcolumn( FT_VAR, ':name', 32 );

        extuptime( ENZ_USERS );

        $idusers = $db->insert( $dbtables, 
                    array( 'idparent' => SYS_ID, 'alias' => ENZ_USERS,
                           'title' => ':users' ), '', true );
        $confupd['idusers'] = $idusers;
        GS::set( 'idcoltable', $idusers );
        addcolumn( FT_VAR, ':name', 64 );
        addcolumn( FT_VAR, ':logname', 32, 'login' );
        addcolumn( FT_LINKTABLE, ':usrgroups', '{"table":"'.$idgroups.'", "column":"'.
                    $groupcol.'","extbyte":"0","filter":"0","aslink":"0","showid":"0"}', 'idgroup' );
        addcolumn( FT_SPECIAL, ':email', '{"type":"2"}' );
        addcolumn( FT_SPECIAL, ':password', '{"type":"4"}', 'pass' );
        addcolumn( FT_DATETIME, ':lastvisit', '{"date":"3","timenow":"0"}', 'uptime' );
        addcolumn( FT_VAR, ':languagejs', 10, 'lang' );
        $idacctype = addset( '*accesstype,circle*:allrecords,adjust*:ownrecords' );
        $confupd['idacctype'] = $idacctype;

        $idtables = $db->insert( $dbtables, 
                    array( 'idparent' => SYS_ID, 'alias' => $dbtables,
                           'title' => ':tables' ), '', true );
        $confupd['idtables'] = $idtables;
        GS::set( 'idcoltable', $idtables );
        $tablecol = addcolumn( FT_VAR, ':name', 128, 'title' );
        addcolumn( FT_VAR, ':alias', 24 );
        addcolumn( FT_VAR, ':commentjs', 128, 'comment' );

        $idaccess = $db->insert( $dbtables, 
                    array( 'idparent' => SYS_ID, 'alias' => ENZ_ACCESS,
                           'title' => ':accrights' ), '', true );
        $confupd['idaccess'] = $idaccess;
        GS::set( 'idcoltable', $idaccess );

        addcolumn( FT_LINKTABLE, ':usrgroups', '{"table":"'.$idgroups.'", "column":"'.
                    $groupcol.'","extbyte":"0","filter":"0","aslink":"0","showid":"0"}', 'idgroup' );
        addcolumn( FT_LINKTABLE, ':table', '{"table":"'.$idtables.'", "column":"'.
                    $tablecol.'","extbyte":"0","filter":"0","aslink":"0","showid":"0"}', 'idtable' );
        addcolumn( FT_VAR, ':mask', 32 );
        GS::set('align', 1 );    
        addcolumn( FT_CHECK, ':active', '{}' );
        addcolumn( FT_ENUMSET, ':read', $idacctype );
        addcolumn( FT_CHECK, ':create', '{}' );
        addcolumn( FT_ENUMSET, ':edit', $idacctype );
        addcolumn( FT_ENUMSET, ':del', $idacctype );
    }
    GS::set( 'confupd', $confupd );
} 

function upd0to1()
{
    $db = DB::getInstance();

    $db->query( "alter table ?n drop ?n", ENZ_USERS, 'ctime' );
    $pass = $db->getone( "select pass from ?n where id=1", ENZ_USERS );
    $db->query( "alter table ?n CHANGE `pass` `pass` BINARY( 16 ) NOT NULL", ENZ_USERS ); 
    $db->update( ENZ_USERS, '', array( "pass=X'$pass'" ), 1 );

    $db->query( "CREATE TABLE IF NOT EXISTS `".ENZ_PREFIX."access` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_owner` smallint(5) unsigned NOT NULL,
  `idgroup` smallint(5) unsigned NOT NULL,
  `idtable` int(10) unsigned NOT NULL,
  `mask` varchar(32) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `read` tinyint(3) unsigned NOT NULL,
  `create` tinyint(3) unsigned NOT NULL,
  `edit` tinyint(3) unsigned NOT NULL,
  `del` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `_uptime` (`_uptime`),
  KEY `idgroup` (`idgroup`,`idtable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );

    init_v1();
}