<?php

require_once 'ajax_common.php';

$form = post( 'params' );
if ( $result['success'] )
{
	require_once APP_EONZA.'lib/files.php';
	$result['success']  = files_edit( $form );
}
print json_encode( $result );
?>