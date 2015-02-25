<?php

require_once 'ajax_common.php';

//$fields = post( 'params' );

if ( ANSWER::is_success())
{
    ANSWER::result( json_decode( $db->getone( "select settings from ?n where id=?s && pass=?s", APP_DB, 
                          CONF_DBID, pass_md5( CONF_PSW, true )), true ));
}

ANSWER::answer();
