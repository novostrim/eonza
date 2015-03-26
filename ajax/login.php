<?php
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once 'ajax_common.php';

$form = post( 'form' );

$settings = GS::dbsettings();
$ext = empty( $settings['loginshort'] ) ? $db->parse( " && login=?s", $form['login'] ): '';
$usr = $db->getrow( "select id, login,lang from ?n where pass=X?s ?p", 
                          ENZ_USERS, pass_md5( $form['psw'], true ), $ext );
if ( !$usr )
    ANSWER::set( 'err', 'err_login' );
else
{
    ANSWER::success( true );
    ANSWER::set( 'user', $usr );
    cookie_set( 'pass', md5( $form['psw'] ), 120 );
    cookie_set( 'iduser', $usr['id'], 120 );
}
ANSWER::answer();

