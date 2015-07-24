<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $idparent = get( 'parent' );
    $query = "select * from ".CONF_PREFIX."_tables where";
    if ( $idparent == -1 )
        ANSWER::resultset( 'list', $db->getall( $query." isfolder=0 && idparent != ?s order by title", SYS_ID ));
    else
        ANSWER::resultset( 'list', $db->getall( $query." idparent=?s order by isfolder desc, title", 
                                         $idparent ));
    getcrumbs( $idparent );
}
ANSWER::answer();
