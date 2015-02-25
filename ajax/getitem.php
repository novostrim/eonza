<?php

require_once 'ajax_common.php';

$id = get( 'id' );
$table = get( 'table' );
if ( $table && ANSWER::is_success())
{
    $ret = $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $table );
    if ( $ret )
    {
        ANSWER::set( 'db', $ret );
        $columns = $db->getall("select * from ?n where idtable=?s order by `sort`", 
                                          CONF_PREFIX.'_columns', $table );
        foreach ( $columns as &$icol )
            $icol['idalias'] = alias( $icol );
        getitem( $ret['id'], $id, alias( $ret, CONF_PREFIX.'_' ), $columns );
    }
    else
    {
        ANSWER::success( false );
        ANSWER::set( 'db', array());
    }
}

ANSWER::answer();
