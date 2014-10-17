<?php
/*
    Eonza 
    (c) 2014 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/

require_once 'ajax_common.php';

$form = post( 'form' );

$settings = json_decode( $db->getone( "select settings from ?n where id=?s", APP_DB, 
                          CONF_DBID ), true );
$ext = empty( $settings['loginshort']['value'] ) ? $db->parse( " && login=?s", $form['login'] ): '';
$USER = $db->getrow( "select id, login,lang from ?n where pass=?s ?p", 
                          CONF_PREFIX.'_users', pass_md5( $form['psw'], true ), $ext );
if ( !$USER )
    $result['err'] = 'err_login';
else
{
    $result['success'] = true;
    $result['user'] = $USER;
    cookie_set( 'pass', md5( $form['psw'] ), 120 );
    cookie_set( 'iduser', $USER['id'], 120 );
}
print json_encode( $result );

