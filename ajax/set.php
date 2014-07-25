<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/utf.php';

$id = get( 'id' );
if ( $id && $result['success'] )
{
	$setname = CONF_PREFIX.'_sets';
	$result['title'] = $db->getone("select title from ?n where id=?s", $setname, $id );
	if ( $result['title'] )
		$result['result'] = $db->getall("select * from ?n where idset=?s order by title", $setname, $id );
	else
		$result['success'] = false;
}

print json_encode( $result );
?>