<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

if ( $result['success'] )
{
	$pars = post( 'params' );
	$idi = $pars['id'];
	if ( $idi )
	{
		if ( $db->query("truncate table ?n", api_dbname( $idi ) ))
		{
			files_deltable( $idi );
			$result['success'] = $idi;
			api_log( $result['success'], 0, 'truncate' );
		}
	}
}
print json_encode( $result );
?>