<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/
    
require_once 'ajax_common.php';

if ( ANSWER::is_success())
    ANSWER::result( array( array( 'name' => 'eonzaver', 'value' => APP_VERSION, 'date' => APP_DATE ), 
    		array( 'name' => 'yourip', 'value' => $_SERVER['REMOTE_ADDR'] ),
    		array( 'name' => 'phpver', 'value' => phpversion()),
    		array( 'name' => 'dbver', 'value' => $db->getone("select version()"))
    		));

ANSWER::answer();
