<?php
/*
    Eonza
    (c) 2014 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/
//print_r( $_SERVER );

require_once 'conf.inc.php';
require_once "app.inc.php";

if ( isset( $_GET['request'] ))
    require_once APP_EONZA."ajax/".$_GET['request'].".php";

?>