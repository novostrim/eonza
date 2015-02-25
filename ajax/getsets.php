<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success())
{
    ANSWER::result( $db->getall( "select * from ".CONF_PREFIX."_sets where idset = 0 order by title" ));
}
ANSWER::answer();
