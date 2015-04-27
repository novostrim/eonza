<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/
    
require_once 'ajax_common.php';

if ( ANSWER::is_success())
{
	$eonzaver = file_get_contents('http://www.eonza.org/eonza-version.html?ver='.APP_VERSION.'&lang='.get('lang'));
    ANSWER::result( array( array( 'name' => 'eonzaver', 'value' => APP_VERSION, 'date' => APP_DATE ), 
    		array( 'name' => 'yourip', 'value' => $_SERVER['REMOTE_ADDR'] ),
    		array( 'name' => 'phpver', 'value' => phpversion()),
    		array( 'name' => 'dbver', 'value' => $db->getone("select version()"))
    		));
//    print $eonzaver;
    ANSWER::set( 'latestver', json_decode( $eonzaver, true ));
}

ANSWER::answer();
