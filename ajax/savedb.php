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
    $settings = GS::dbsettings();
    foreach ( $fields as $ikey => $ival )
        $settings[ $ikey ] = $ival;
    $db->query( "update ?n set settings=?s where id=1 && pass=?s", 
                 ENZ_DB, json_encode( $settings ), pass_md5( CONF_PSW, true ));
}

ANSWER::answer();
