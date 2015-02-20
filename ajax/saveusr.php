<?php

require_once 'ajax_common.php';

$fields = post( 'params' );

if ( defined( 'DEMO' )/* && ( isset( $fields['pass'] ) || isset( $fields['email'] ) ||
      isset( $fields['login'] ) )*/)
    api_error('This feature is disabled in the demo-version.');

if ( $result['success'] )
{
    if ( isset( $fields['pass'] ))
    {
        $ipass = $fields['pass'];
        $fields['pass'] = pass_md5( $fields['pass'], true );
    }
    $result['success'] = $db->query( "update ?n set ?u where id=?s", 
                                 CONF_PREFIX.'_users', $fields, GS::userid());
    if ( $result['success'] )
    {
        if ( isset( $ipass ))
            cookie_set( 'pass', md5( $ipass ), 120 );
    }
}
print json_encode( $result );
