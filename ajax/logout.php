<?php
/*
    Eonza 
    (c) 2014 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/

require_once 'ajax_common.php';

cookie_set( 'pass' );
cookie_set( 'iduser' );

print json_encode( $result );

