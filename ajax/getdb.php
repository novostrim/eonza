<?php

require_once 'ajax_common.php';

//$fields = post( 'params' );

if ( $result['success'] )
{
	$settings = json_decode( $db->getone( "select settings from ?n where id=?s && pass=?s", APP_DB, 
		                  CONF_DBID, pass_md5( CONF_PSW, true )), true );
	$result['result'] = array();
	if ( $settings )
	{	
		foreach ( $settings as $skey => $sval )
		{
			if ( isset( $sval['visible']) && $sval['visible'] )
			{
				$sval['name'] = $skey;
				if ( isset( $sval['par'] ))
					switch ( $sval['par'] )
					{
						case 'normal' : $sval['class'] = 'form-control wbig'; break;
						case 'number' : $sval['class'] = 'form-control wshort'; break;
					}
				$result['result'][] = $sval;
			}
		}
	}
	else
		api_error('');
}

print json_encode( $result );
?>