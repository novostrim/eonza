<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once "upd0to1.php";

function extuptime( $dbname )
{
    $db = DB::getInstance();

    $db->query( "alter table ?n add `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
                 $dbname );
    $db->query( "alter table ?n add `_owner` smallint(5) unsigned NOT NULL",
                 $dbname );
    $db->query( "alter table ?n add index ( `_uptime` )", $dbname );
}

function addcolumn( $idtype, $title, $extend, $alias = '' )
{
    $sort = GS::get('sort') + 1;
    if ( !$alias )
        $alias = $title[0] == ':' ? substr( $title, 1) : $title; 
    if ( $idtype == FT_VAR )
        $extend = "{\"length\":\"$extend\"}";
    if ( $idtype == FT_ENUMSET )
        $extend = "{\"set\":\"$extend\"}";

    $ret = DB::insert( ENZ_COLUMNS, array( 'idtable' => GS::get('idcoltable'), 
                    'idtype' => $idtype, 'title' => $title, 'alias' => $alias,
                    'align' => GS::get('align'),
                    'visible' => 1, 'sort' => $sort, 'extend' => $extend ), '', true );
    GS::set('sort', $sort );
    return $ret;
}

function addset( $list )
{
    $db = DB::getInstance();
    $items = explode(',', $list );
    $dbset = ENZ_SETS;
    $ret = DB::insert( $dbset, array( 'idset' => SYS_ID, 'iditem' => 0, 
                     'title' => $items[0] ), '', true );
    for ( $i=1; $i < count( $items ); $i++ )
        DB::insert( $dbset, array( 'idset' => $ret, 'iditem' => $i, 
                     'title' => $items[$i] ));
    return $ret;
}

function init_update()
{
    init_v1();   
}