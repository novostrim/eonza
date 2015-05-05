<?php
/*
    Eonza
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once 'conf.inc.php';
require_once "app.inc.php";

if ( isset( $_GET['request'] ))
    require_once APP_EONZA."ajax/".$_GET['request'].".php";
