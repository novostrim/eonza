<?php
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/
    
require_once 'ajax_common.php';
require_once 'index_common.php';

$pars = post( 'params' );

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $table = $db->getrow("select * from ?n where id=?s", ENZ_TABLES, $pars['id'] );
    if ( !$table )
        api_error( 'err_id', "id=$pars[id]" );
    else
    {
        ANSWER::success( $db->query( "alter table ?n drop index ?n", 
                                     alias( $table, ENZ_PREFIX ), $pars['field'] ));
        if ( ANSWER::is_success())
            ANSWER::set( 'index', index_list_table( $table ));
    }
}

ANSWER::answer();
