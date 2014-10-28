<?php

require_once 'ajax_common.php';
require_once 'index_common.php';

$pars = post( 'params' );

if ( $result['success'] )
{
    $list = '';
    $type = 'INDEX';
    foreach ( $pars['fields'] as $ifield )
    {
        if ( !(int)$ifield )
            continue;
        $col = $db->getrow("select alias, idtype from ?n where id=?s", CONF_PREFIX.'_columns', $ifield );
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
    $table = $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $pars['id'] );
    if ( !$table )
        api_error( 'err_id', "id=$pars[id]" );
    else
    {
        $result['success'] = $db->query( "alter table ?n add ?p ( ?p )", 
                  $table['alias'] ? $table['alias'] : CONF_PREFIX."_$idtable", $type, $list );
        if ( $result['success'] )
            $result['index'] = index_list_table( $table );
    }
}

print json_encode( $result );
