<?php

require_once 'ajax_common.php';

$id = get( 'id' );

if ( ANSWER::is_success() && $id )
{
	$page = $db->getrow( "select * from ?n where ?n = ?s", 
                                  ENZ_WEBPAGES, $id[0] > '9' ? 'alias' : 'id', $id );
	if ( $page )
		ANSWER::result( $page );
}

ANSWER::answer();