<?php

require_once 'ajax_common.php';

if ( $result['success'] )
{
	$icol = $db->getrow("select * from ?n where id=?s && ( idtype=?s ||  idtype=?s )", 
		         CONF_PREFIX.'_columns', (int)get( 'id' ), FT_LINKTABLE, FT_PARENT );
	if ( $icol )
	{
		$icol['extend'] = json_decode( $icol['extend'], true );
		$result['result'] = get_linklist( $icol, (int)get( 'offset' ), get('search'), (int)get( 'parent' ));
	}
	else
		api_error('Link table');
}
print json_encode( $result );
?>