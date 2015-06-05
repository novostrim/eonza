<?php

require_once 'ajax_common.php';

$fields = post( 'params' );

if ( ANSWER::is_success( true ))
{
    $ext = '';
    if ( isset( $fields['pass'] ))
    {
        $ipass = $fields['pass'];
        $ext = array( "pass=X'".pass_md5( $fields['pass'], true )."'" );
        unset( $fields['pass'] );
    }
    ANSWER::success( $db->update( ENZ_USERS, $fields, $ext, GS::userid()));
    if ( ANSWER::is_success())
    {
        if ( isset( $ipass ))
            cookie_set( 'pass', md5( $ipass ), 120 );
    }
}
ANSWER::answer();
