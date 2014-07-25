<?php

require_once 'ajax_common.php';

if ( $result['success'] )
{
	$pars = post( 'params' );
	$idi = (int)$pars['id'];
	$idset = (int)$pars['idset'];
	if ( $idi && $idset );
	{
		$setname = CONF_PREFIX.'_sets';
		$result['success'] = $db->query("delete from ?n where id=?s && idset=?s", $setname, $idi, $idset );
//			if ( $result['success'] )
//				api_log( $idtable, $idi, 'delete' );
	}
}
print json_encode( $result );
?>