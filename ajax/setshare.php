<?php

require_once 'ajax_common.php';
//require_once APP_EONZA.'lib/files.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
	$pars = postall();
	$fields = array();
	if ( !empty( $pars['idfile'] ))
	{
		$fields = $db->getrow("select idtable, id as idfile from ?n where id=?s", 
			                 ENZ_FILES, (int)$pars['idfile'] );
		$fields['idslice'] = 0;
		if ( !$fields )
			api_error('Unknown file');
	}
	$ext = array();
	$fields['viewlimit'] = -1;
	if ( !empty( $pars['during'] ))
		$ext[] = $db->parse( 'timelimit=DATE_ADD( NOW(), INTERVAL ?p MINUTE )', 
			                 (int)$pars['during'] );
	$fields['firstonly'] = empty( $pars['first'] ) ? 0 : 1;
	$fields['firstip'] = 0;
	$exist = $db->getrow( "select id, code from ?n where idtable=?s && idslice=?s && idfile=?s", 
		                   ENZ_SHARE, $fields['idtable'], $fields['idslice'], $fields['idfile'] );
	if ( $exist )
	{
		$db->update( ENZ_SHARE, $fields, $ext, $exist['id'] );
		ANSWER::resultset('code', base_convert( $exist['code'], 10, 32 ).$exist['id']);
	}
	else
	{
		$fields['code'] = mt_rand( 1100000000, 2100000000 ) + mt_rand();
		$idshare = $db->insert( ENZ_SHARE, $fields, $ext, true );
		ANSWER::resultset('code', base_convert( $fields['code'], 10, 32 ).$idshare );
	}
}
ANSWER::answer();
