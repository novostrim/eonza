<?php
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

$isupdate = defined( 'NOANSWER' );
require_once 'ajax_common.php';

$fields = post( 'params' );
if ( defined( 'DEMO' ))
    api_error('This feature is disabled in the demo-version.');

if ( ANSWER::is_success() && ( $isupdate || ANSWER::is_access()))
{
    $psw = pass_md5( CONF_PSW, true );
    $settings = json_decode( $db->getone( "select settings from ?n where id=?s && pass=?s", APP_DB, 
                          CONF_DBID, $psw ), true );
    foreach ( $fields as $ikey => $ival )
        $settings[ $ikey ] = $ival;
    $db->query( "update ?n set settings=?s where id=?s && pass=?s", 
                APP_DB, json_encode( $settings ), CONF_DBID, $psw );
}

ANSWER::answer();
