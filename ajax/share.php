<?php

if ( empty( $_GET['uid'] ))
    exit;
$code = strtolower( substr( $_GET['uid'], 0, 7 ));
$uid = substr( $_GET['uid'], 7 );

require_once 'ajax_common.php';

$share = $db->getrow("select * from ?n where id=?s && ( YEAR( timelimit )=0 || timelimit > NOW()) &&
	           viewlimit!=0", ENZ_SHARE, $uid );
if ( !$share || $share['code'] != base_convert( $code, 32, 10 ))
	exit;

$ip = ip2long( $_SERVER['REMOTE_ADDR' ] );
if ( $share['firstonly'] )
{
	if ( $share['firstip'] )
	{
	 	if ( $share['firstip'] != $ip )
			exit;
	}
	else 
		$db->update( ENZ_SHARE, array( 'firstip' => $ip ), '', $share['id'] );
}

if ( $share['idfile'] )
{	
	require_once APP_EONZA.'lib/files.php';
	files_download( $share['idfile'], !empty( $_GET['view'] ), !empty( $_GET['thumb'] ));
}
