<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/admin/conf.inc.php';
require_once $_SERVER['DOCUMENT_ROOT']."/eonza/app.inc.php";
require_once 'ajax_common.php';
require_once '/../lib/export.php';

$pars = get( true );

$pars['output'] = '/backup';
$pars['filename'] = 'test_%Y-%m-%d_%H-%M';
$pars['table'] = array( 7 );
$filename = export( $pars );
print "Finish<br>";
 print "<a href='$filename'>$filename file</a>";

