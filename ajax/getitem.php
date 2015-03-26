<?php
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/
    
require_once 'ajax_common.php';

$id = get( 'id' );
$table = get( 'table' );
if ( $table && ANSWER::is_success() && ANSWER::is_access( A_READ, $table, $id ))
{
    $ret = $db->getrow("select * from ?n where id=?s", ENZ_TABLES, $table );
    if ( $ret )
    {
        ANSWER::set( 'db', $ret );
        $columns = $db->getall("select * from ?n where idtable=?s order by `sort`", 
                                          ENZ_COLUMNS, $table );
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
