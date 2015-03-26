<?php

require_once 'ajax_common.php';
require_once 'index_common.php';

$pars = post( 'params' );

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $list = '';
    $type = 'INDEX';
    foreach ( $pars['fields'] as $ifield )
    {
        if ( !(int)$ifield )
            continue;
        $col = $db->getrow("select id, alias, idtype from ?n where id=?s", ENZ_COLUMNS, $ifield );
        if ( in_array( $col['idtype'], array( FT_UNKNOWN, FT_PARENT, FT_FILE, FT_IMAGE )))
            continue;
        $alias = $col['alias'] ? $col['alias'] : $col['id'];
        if ( $col['idtype'] == FT_TEXT )
        {
            $list = $db->parse( '?n', $alias );
            $type = 'FULLTEXT';
            break;
        }
        if ( $list )
            $list .= ', ';
        $list .= $db->parse( '?n', $alias );
    }
    $table = $db->getrow("select * from ?n where id=?s", ENZ_TABLES, $pars['id'] );
    if ( !$table )
        api_error( 'err_id', "id=$pars[id]" );
    else
    {
        $dbname = alias( $table, CONF_PREFIX.'_' );
        ANSWER::success( $db->query( "alter table ?n add ?p ( ?p )", $dbname, $type, $list ));
        if ( ANSWER::is_success())
            ANSWER::set( 'index', index_list_table( $table ));
    }
}
ANSWER::answer();
