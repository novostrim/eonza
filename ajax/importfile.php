<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once 'ajax_common.php';
//require_once APP_EONZA.'lib/files.php';
require_once 'import_csv.php';

$pars = postall( true );
if ( ANSWER::is_success( true ) && ANSWER::is_access( A_EDIT, $pars['idtable'], 0 ))
{
    ANSWER::result( csv_import( $pars ));
}
ANSWER::answer();

