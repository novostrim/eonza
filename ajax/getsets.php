<?php

require_once 'ajax_common.php';

if ( $result['success'] )
{
    $result['result'] = $db->getall( "select * from ".CONF_PREFIX."_sets where idset = 0 order by title" );
}
print json_encode( $result );
