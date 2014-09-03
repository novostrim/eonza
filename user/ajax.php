<?php
/*
	Eonza
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once 'conf.inc.php';
require_once $_SERVER['DOCUMENT_ROOT']."/eonza/app.inc.php";

if ( isset( $_GET['request'] ))
	require_once APP_EONZA."ajax/".$_GET['request'].".php";

?>