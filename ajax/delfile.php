<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

if ( $result['success'] )
{
    $pars = post( 'params' );

    files_delfile( (int)$pars['id'], true );
}
print json_encode( $result );
?>