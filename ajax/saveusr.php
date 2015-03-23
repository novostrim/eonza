<?php

require_once 'ajax_common.php';

$fields = post( 'params' );

if ( defined( 'DEMO' )/* && ( isset( $fields['pass'] ) || isset( $fields['email'] ) ||
      isset( $fields['login'] ) )*/)
    api_error('This feature is disabled in the demo-version.');

if ( ANSWER::is_success())
{
    $ext = '';
    if ( isset( $fields['pass'] ))
    {
        $ipass = $fields['pass'];
        $ext = ", pass=X'".pass_md5( $fields['pass'], true )."'";
        unset( $field['pass'] );
    }
    ANSWER::success( $db->query( "update ?n set ?u $ext where id=?s", 
                                 CONF_PREFIX.'_users', $fields, GS::userid()));
    if ( ANSWER::is_success())
    {
        if ( isset( $ipass ))
            cookie_set( 'pass', md5( $ipass ), 120 );
    }
}
ANSWER::answer();
