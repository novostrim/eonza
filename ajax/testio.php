<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/admin/conf.inc.php';
require_once $_SERVER['DOCUMENT_ROOT']."/eonza/app.inc.php";
require_once 'ajax_common.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/eonza/lib/export.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/eonza/lib/import.php';

if ( !$USER )
{
    print "USER error";
    exit();
}

$pars = get( true );

$pars['output'] = '/backup';
$pars['filename'] = 'test';//_%Y-%m-%d_%H-%M';
$pars['table'] = array( 3 );
//$filename = export( $pars );
$pars['filename'] = 'test.enz';//_%Y-%m-%d_%H-%M';
import( $pars );
print "Finish<br>";
// print "<a href='$filename'>$filename file</a>";

