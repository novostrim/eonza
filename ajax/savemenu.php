<?php

require_once 'ajax_common.php';

$pars = post( 'params' );
$idi = $pars['id'];

if ( $result['success'] )
{
	if ( !$idi )
	{
		$result['success'] = $db->insert( CONF_PREFIX.'_menu', pars_list( 'title,idparent,isfolder'.
			             ( $pars['isfolder'] ? '' : ',hint,url'), $pars ), 
			  array( 'sort=1000' ), true ); 
		if ($result['success'])
			require_once 'menu_common.php';
	}
	else
	{
//		print "=$idi=$pars[title]";
		$result['success'] = $db->update( CONF_PREFIX.'_menu', pars_list( 'title'.
			( $pars['isfolder'] ? '' : ',hint,url'), $pars ), '', $idi );
	}
}
print json_encode( $result );
?>