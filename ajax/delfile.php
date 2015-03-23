<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

if ( ANSWER::is_success() )
{
    $pars = post( 'params' );
    if ( ANSWER::is_access( A_FILESET, (int)$pars['id'] ))
        files_delfile( (int)$pars['id'], true );
}
ANSWER::answer();
