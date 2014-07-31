<?php

require_once 'ajax_common.php';

$id = get( 'id' );
$table = get( 'table' );
if ( $table && $result['success'] )
{
//    $result['listitems'] = '';
    $result['db'] = $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $table );
    if ( $result['db'] )
    {
        $columns = $db->getall("select * from ?n where idtable=?s order by `sort`", 
                                          CONF_PREFIX.'_columns', $table );
        foreach ( $columns as &$icol )
            $icol['idalias'] = alias( $icol );
           $dbname = alias( $result['db'], CONF_PREFIX.'_' );
        getitem( $result['db'], $id );
    }
    else
        $result['success'] = false;
//    $result['result'] = $db->getall("select * from")
}

print json_encode( $result );
?>