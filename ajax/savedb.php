<?php

require_once 'ajax_common.php';

$fields = post( 'params' );

if ( defined( 'DEMO' ))
    api_error('This feature is disabled in the demo-version.');

if ( $result['success'] )
{
    $settings = json_decode( $db->getone( "select settings from ?n where id=?s && pass=?s", APP_DB, 
                          CONF_DBID, pass_md5( CONF_PSW, true )), true );
    foreach ( $fields as $ikey => $ival )
        $settings[ $ikey ]['value'] = $ival;
    $db->query( "update ?n set settings=?s where id=?s && pass=?s", 
                APP_DB, json_encode( $settings ), CONF_DBID, pass_md5( CONF_PSW, true ));
}
//$result['success'] = $result['success'] && $db->query( "update ?n set ?u where id=?s && pass=?s", 
//                APP_DB, $fields, CONF_DBID, pass_md5( CONF_PSW, true ));

print json_encode( $result );
?>