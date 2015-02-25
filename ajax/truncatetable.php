<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

if ( ANSWER::is_success())
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    if ( $idi )
    {
        if ( $db->query("truncate table ?n", api_dbname( $idi ) ))
        {
            files_deltable( $idi );
            ANSWER::success( $idi );
            api_log( ANSWER::is_success(), 0, 'truncate' );
        }
    }
}
ANSWER::answer();
