<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

if ( ANSWER::is_success())
{
    $pars = post( 'params' );

    files_delfile( (int)$pars['id'], true );
}
ANSWER::answer();
