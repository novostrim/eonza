<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once 'ajax_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
	$pars = getall();
	$from = empty( $pars['from'] ) ? 0 : (int)$pars['from'];
	$onpage = empty( $pars['op'] ) ? 25 : (int)$pars['op'];
	$where = $db->parse('where id > ?s', $from );
	if ( isset( $pars['onlyf'] ))
		$where .= $db->parse(' && folder>0' );

    ANSWER::result( $db->getall( "select id, _uptime, folder, filename, size, ispreview, comment 
    	                          from ?n ?p order by id limit 0,?p", 
                                  ENZ_FILES, $where, $onpage ));
}

ANSWER::answer();
