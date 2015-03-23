
<?php

require_once 'ajax_common.php';
require_once 'index_common.php';

$pars = post( 'params' );

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $table = $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $pars['id'] );
    if ( !$table )
        api_error( 'err_id', "id=$pars[id]" );
    else
    {
        $dbname = alias( $table, CONF_PREFIX.'_' );
        ANSWER::success( $db->query( "alter table ?n drop index ?n", $dbname, $pars['field'] ));
        if ( ANSWER::is_success())
            ANSWER::set( 'index', index_list_table( $table ));
    }
}

ANSWER::answer();
